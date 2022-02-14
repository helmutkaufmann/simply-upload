<html>
<head>

  <title>Simply Upload</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/uikit/uikit/dist/css/uikit.min.css" />
  <script src="/assets/uikit/uikit/dist/js/uikit.min.js"></script>
  <script src="/assets/uikit/uikit/dist/js/uikit-icons.min.js"></script>

  <script src="/assets/enyo/dropzone/dist/min/dropzone.min.js"></script>
  <link href="/assets/enyo/dropzone/dist/min/dropzone.min.css" rel="stylesheet" type="text/css" />

</head>

<body>

  <?php
    include_once("config.php");
    $uniqid=uniqid();
    $hash=crypt($uniqid, MYSALT);
  ?>

  <div class="uk-padding-large uk-container">
    <h1>Dropload</h1>

    <p>Your public upload URL is: https://<?= $_SERVER["HTTP_HOST"] ?>?identifier=<?=$uniqid ?>
    <?= (!$_GET['identifier'] ? "<br><b>Please safe the following admin URL as you will not see it again: </b><a href='https://" . $_SERVER["HTTP_HOST"] . "/catalogue.php?secret=" .$hash . "' target='admin'>secret admin link</a>": "") ?></p>

    <form action="upload.php" class="uk-form-stacked dropzone" uk-height-viewport="expand: true" method="post" enctype="multipart/form-data" id="dropper">
          <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text">Uploader (may be empty)</label>
              <input class="uk-input" type="text" placeholder="Uploader's name or reference" name="uploader" id="uploader">
          </div>
          <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text">Event/Title (may be empty)</label>
              <input class="uk-input" type="text" placeholder="Event/Title of uploaded files" name="title" id="title">
          </div>
          <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text">Imnage resizeing (leave empty or set to 0 to maintain original)</label>
              <input class="uk-input" type="number" min="0" placeholder="Maximum width/height for images" id="resizeto" name="resizeto">
          </div>
          <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text" style="display:inline;">Just to make sure you are not a BOT...<br>What is </label>
              <div id="captchaOne" class="uk-form-label" style="display:inline;"></div>
              <div class="uk-form-label" style="display:inline;">+</div>
              <div id="captchaTwo" class="uk-form-label" style="display:inline;"></div>
              <div class="uk-form-label" style="display:inline;">?</div>
              <input class="uk-input" type="number" placeholder="Enter the sum before trying to upload any files" id="captchaResult" name="captchaResult">
          </div>

          <input class="uk-input" value="<?= $uniqid; ?>" id="identifier" name="identifier" hidden>
    </form>

    <script>

      Dropzone.options.dropper = {

        resizeHeight: 1,
        thumbnailWidth: 150,
        thumbnailHeight: 150,
        thumbnailMethod: "contain",
        resizeQuality: 0.6,

        resize: function resize (file, width, height, resizeMethod) {

          var newsize = document.getElementById("resizeto").value;

          if (!newsize)
            newsize = 1000000;

          width = newsize;
          height = newsize;

          // the following is the original code

          var info = {
            srcX: 0,
            srcY: 0,
            srcWidth: file.width,
            srcHeight: file.height
          };
          var srcRatio = file.width / file.height; // Automatically calculate dimensions if not specified

          if (width == null && height == null) {
            width = info.srcWidth;
            height = info.srcHeight;
          } else if (width == null) {
            width = height * srcRatio;
          } else if (height == null) {
            height = width / srcRatio;
          } // Make sure images aren't upscaled

          width = Math.min(width, info.srcWidth);
          height = Math.min(height, info.srcHeight);
          var trgRatio = width / height;

          if (info.srcWidth > width || info.srcHeight > height) {
            // Image is bigger and needs rescaling
            if (resizeMethod === "crop") {
              if (srcRatio > trgRatio) {
                info.srcHeight = file.height;
                info.srcWidth = info.srcHeight * trgRatio;
              } else {
                info.srcWidth = file.width;
                info.srcHeight = info.srcWidth / trgRatio;
              }
            } else if (resizeMethod === "contain") {
              // Method 'contain'
              if (srcRatio > trgRatio) {
                height = width / srcRatio;
              } else {
                width = height * srcRatio;
              }
            } else {
              throw new Error("Unknown resizeMethod '".concat(resizeMethod, "'"));
            }
          }

          info.srcX = (file.width - info.srcWidth) / 2;
          info.srcY = (file.height - info.srcHeight) / 2;
          info.trgWidth = width;
          info.trgHeight = height;
          return info;
        },

        addedfile(file) {

          if (captchaCheck("")) {
            console.log("files added");
            this.checkcaptchs = false;
          } else {
            this.checkcaptchs = true;
            console.log("files not added");
            this.removeAllFiles(true);

            return this.removeFile(file);
          }

          // the following is the original code

          if (this.element === this.previewsContainer) {
            this.element.classList.add("dz-started");
          }

          if (this.previewsContainer && !this.options.disablePreviews) {
            file.previewElement = Dropzone.createElement(
              this.options.previewTemplate.trim()
            );
            file.previewTemplate = file.previewElement; // Backwards compatibility

            this.previewsContainer.appendChild(file.previewElement);
            for (var node of file.previewElement.querySelectorAll("[data-dz-name]")) {
              node.textContent = file.name;
            }
            for (node of file.previewElement.querySelectorAll("[data-dz-size]")) {
              node.innerHTML = this.filesize(file.size);
            }

            if (this.options.addRemoveLinks) {
              file._removeLink = Dropzone.createElement(
                `<a class="dz-remove" href="javascript:undefined;" data-dz-remove>${this.options.dictRemoveFile}</a>`
              );
              file.previewElement.appendChild(file._removeLink);
            }

            let removeFileEvent = (e) => {
              e.preventDefault();
              e.stopPropagation();
              if (file.status === Dropzone.UPLOADING) {
                return Dropzone.confirm(
                  this.options.dictCancelUploadConfirmation,
                  () => this.removeFile(file)
                );
              } else {
                if (this.options.dictRemoveFileConfirmation) {
                  return Dropzone.confirm(
                    this.options.dictRemoveFileConfirmation,
                    () => this.removeFile(file)
                  );
                } else {
                  return this.removeFile(file);
                }
              }
            };

            for (let removeLink of file.previewElement.querySelectorAll(
              "[data-dz-remove]"
            )) {
              removeLink.addEventListener("click", removeFileEvent);
            }
          }
        }

      };

      function captchaGenerate(prefix){
          var random_number_1 = Math.floor(Math.random() * 20) + 1;
          var random_number_2 = Math.floor(Math.random() * 10) + 1;
          document.getElementById(prefix+"captchaOne").innerHTML = random_number_1;
          document.getElementById(prefix+"captchaTwo").innerHTML = random_number_2;
      }

      function captchaCheck(prefix){
        var captchaResult = document.getElementById(prefix + "captchaResult").value;
        var captchaOne = parseInt(document.getElementById(prefix + "captchaOne").innerHTML);
        var captchaTwo = parseInt(document.getElementById(prefix + "captchaTwo").innerHTML);
        var sum = captchaOne + captchaTwo;

        if(captchaResult == "") {
          alert("Please solve the captcha - it is really simple!");
          return false;
        }
        else if(captchaResult != sum) {
          alert("Please solve the captcha - it is really simple!");
          return false;
        }
        else{
          return true;
        }
      }

      captchaGenerate("");

      // Set parameters - if there are any and make them readonly
      const urlParams = new URLSearchParams(window.location.search);
      urlParams.forEach((x, y) => document.getElementById(y).value = x);
      urlParams.forEach((x, y) => document.getElementById(y).readOnly = true);

    </script>

      <p class="uk-text-small uk-text-center" >&copy; 2022 Helmut Kaufmann</p>

  </div>
</body>
</html>
