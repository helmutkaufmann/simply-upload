<?php

include_once("config.php");
if (array_key_exists("identifier", $_GET))
  $uniqid=$_GET["identifier"];
else {
  $uniqid=uniqid();
}
$hash=crypt($uniqid, MYSALT);

function chooseRandomString() {
  $var = file_get_contents('captcha.txt'); //Take the contents from the file to the variable
  $result=explode(PHP_EOL, $var); //Split it by ','
  shuffle($result);
  return $result[0]; //Return a random entry from the array.
}

function generateRandomString($length = 10) {
     $characters = '23456789abcdefghkmnopqrstwxyzABCDEFGHKLMNPQRSTWXYZ!?';
     $randomString = '';
     for ($i = 0; $i < $length; $i++) {
         $randomString .= $characters[rand(0, strlen($characters) - 1)];
     }
     return $randomString;
 }

 function generateCaptchaImage($text = "verbatim"){

      $width  = 150;
      $height = 30;
      // Create the image
      $im = imagecreatetruecolor($width, $height);

      // Create some colors
      $black  = imagecolorallocate($im, 255, 255, 255);
      $grey   = imagecolorallocate($im, 128, 128, 128);
      $white  = imagecolorallocate($im, 0, 0, 0);
      imagefilledrectangle($im, 0, 0, 399, 29, $white);

      //ADD NOISE - DRAW background squares
      $square_count = 6;
      for($i = 0; $i < $square_count; $i++){
          $cx = rand(0,$width);
          $cy = (int)rand(0, $width/2);
          $h  = $cy + (int)rand(0, $height/5);
          $w  = $cx + (int)rand($width/3, $width);
          imagefilledrectangle($im, $cx, $cy, $w, $h, $white);
      }

      //ADD NOISE - DRAW ELLIPSES
      $ellipse_count = 5;
      for ($i = 0; $i < $ellipse_count; $i++) {
        $cx = (int)rand(-1*($width/2), $width + ($width/2));
        $cy = (int)rand(-1*($height/2), $height + ($height/2));
        $h  = (int)rand($height/2, 2*$height);
        $w  = (int)rand($width/2, 2*$width);
        imageellipse($im, $cx, $cy, $w, $h, $grey);
      }

      // Replace path by your own font path
      $font = './monofont.ttf';

      // Add some shadow to the text
      // imagettftext($im, 20, 0, 11, 21, $grey, $font, $text);

      // Add the text
      imagettftext($im, 20, 0, 10, 20, $black, $font, $text);

      // Using imagepng() results in clearer text compared with imagejpeg()
      return $im;
      imagepng($im);
      imagedestroy($im);
  }

 ob_start();
 // $randomString = generateRandomString(6);
 $randomString = chooseRandomString();
 imagepng(generateCaptchaImage($randomString));
 $captcha = ob_get_clean();

?>

<html>
<head>

  <title>Simply Upload</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/uikit/uikit/dist/css/uikit.min.css" />
  <script src="/assets/uikit/uikit/dist/js/uikit.min.js"></script>
  <script src="/assets/uikit/uikit/dist/js/uikit-icons.min.js"></script>

  <script src="/assets/enyo/dropzone/dist/dropzone-min.js"></script>
  <link href="/assets/enyo/dropzone/dist/dropzone.css" rel="stylesheet" type="text/css" />

</head>

<body>

  <script>

    function captchaCheck(){
      var captchaResult = document.getElementById("captchaResult").value;
      if(captchaResult != "<?= $randomString ?>") {
        alert("Please solve the captcha before uploading files.");
        return false;
      }
      else{
        return true;
      }
    };

    Dropzone.options.dropper = {

      resizeHeight: 1,
      thumbnailWidth: 150,
      thumbnailHeight: 150,
      thumbnailMethod: "contain",
      resizeQuality: 0.6,

      resize: function resize (file, width, height, resizeMethod) {

        var newsize = document.getElementById("resizeto").value;

        if (!newsize)
          newsize = 100000000;

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

        if (captchaCheck()) {
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


    // Set parameters - if there are any and make them readonly
    const urlParams = new URLSearchParams(window.location.search);
    urlParams.forEach((x, y) => document.getElementById(y).value = x);
    urlParams.forEach((x, y) => document.getElementById(y).readOnly = true);

  </script>

  <div class="uk-padding-large uk-container">
    <h1>Dropload</h1>

    <? $url="https://" . $_SERVER['HTTP_HOST'] . "?identifier=" . $uniqid; ?>
    <p>The public upload URL is: <a href="<?= $url ?>" target='upload'><?= $url ?></a>
    <? $url="https://" . $_SERVER['HTTP_HOST'] . "/administration.php?secret=" .$hash . "&identifier=" .  $uniqid;?>
    <?= (!array_key_exists("identifier", $_GET) ? "<br>And the admin URL, which you will NOT see again, is: <a href='" . $url . "' target='administration'>$url</a>": "") ?></p>

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
              <label class="uk-form-label" for="form-stacked-text">Image resizeing (leave empty or set to 0 to maintain original)</label>
              <input class="uk-input" type="number" min="0" placeholder="Maximum width/height for images" id="resizeto" name="resizeto">
          </div>
          <div class="uk-margin">
              <label class="uk-form-label" for="form-stacked-text" style="display:inline;">Just making sure you are human... </label>
              <p><img src="data:image/png;base64,<?= base64_encode($captcha); ?>" /></p>
              <input class="uk-input" type="text" placeholder="Type the above string here" id="captchaResult" name="captchaResult">
          </div>

          <input class="uk-input" value="<?= $uniqid; ?>" id="identifier" name="identifier" hidden>
    </form>

    <script>
      Dropzone.discover();
    </script>

    <p class="uk-text-small uk-text-center" >&copy; 2022 Helmut Kaufmann</p>

  </div>

</body>
</html>
