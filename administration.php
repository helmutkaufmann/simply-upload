<html>
<head>

  <title>Simply Upload - Administration</title>
  <meta charset="utf-8">

  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/uikit/uikit/dist/css/uikit.min.css" />
  <script src="/assets/uikit/uikit/dist/js/uikit.min.js"></script>
  <script src="/assets/uikit/uikit/dist/js/uikit-icons.min.js"></script>

  <script src="assets/components/jquery/jquery.min.js"></script>

  <script>


  function deleteDocument(document) {

    $("#"+document).submit(function(event){
	     event.preventDefault(); //prevent default action
       var post_url = $(this).attr("action"); //get form action url
       var request_method = $(this).attr("method"); //get form GET/POST method
       var form_data = $(this).serialize(); //Encode form elements for submission

	     $.ajax({
      		url : post_url,
      		type: request_method,
      		data : form_data
	     }).done (function (response){ //
		        // alert("done -- " + response);
            $("#row"+document).remove();
	        });
    });

  }

  function deleteAll(ident) {

    $("#"+ident).submit(function(event){
	     event.preventDefault(); //prevent default action
       var post_url = $(this).attr("action"); //get form action url
       var request_method = $(this).attr("method"); //get form GET/POST method
       var form_data = $(this).serialize(); //Encode form elements for submission

	     $.ajax({
      		url : post_url,
      		type: request_method,
      		data : form_data
	     }).done (function (response){ //
          $("#thetable").remove();
	        });
    });

  }


  <?
  function generateThumbnail($img, $width = 80, $height = 80, $quality = 80) {

    $filename_no_ext = reset(explode('.', $img));
    $filename_thumb = $filename_no_ext . '___thumb' . '.jpg';

    if (is_file($img) && (substr(mime_content_type($img), 0, 5) == "image") && !is_file($filename_thumb)) {
        $imagick = new Imagick(realpath($img));
        $imagick->setImageFormat('jpeg');
        $imagick->setImageCompression(Imagick::COMPRESSION_JPEG);
        $imagick->setImageCompressionQuality($quality);
        $imagick->thumbnailImage($width, $height, true, false);

        if (file_put_contents($filename_no_ext . '___thumb' . '.jpg', $imagick) === false) {
            throw new Exception("Could not put contents.");
        }
        return true;
    }
    else {
        return false;
    }
  }
  ?>

  </script>

</head>

<body> <div class="uk-padding-large uk-container">

  <h1>Simply Upload - Administration</h1>
  <h5>Documents</h5>

  <table class="uk-table  uk-table-striped uk-table-small  uk-table-divider uk-table-middle" id="thetable">
      <thead>
            <tr>
                <th>Date</th>
                <th>Uploader</th>
                <th>Title</th>
                <th>Filename</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
      <?php

      include_once("config.php");
      include_once("qrcode.php");
      include_once("assets/autoload.php");

      @mkdir($databaseDirectory, 0755, true);
      $databaseDirectory = __DIR__ . "/database";
      $uploadDatabase = new \SleekDB\Store("uploads", $databaseDirectory);

      if ($_GET["secret"] == ADMINPASSWORD)
        $allUploads = $uploadDatabase->findAll(["date" => "desc", "uploader" => "asc", "title" => "asc"]);
      else
        $allUploads = $uploadDatabase->findBy(["secret", "=", urldecode($_GET["secret"])], ["date" => "desc", "uploader" => "asc", "title" => "asc"]);

      foreach ($allUploads as &$upload) {

          if (file_exists($upload["filename"])) {

            generateThumbnail(__DIR__ . "/" . $upload["filename"]);
            $filename_no_ext = reset(explode('.', $upload["filename"]));
            $filename_thumb = $filename_no_ext . '___thumb' . '.jpg';

            echo "<tr id='rowdocument" . $upload["_id"] . "'>";
            // echo "<td>" . $upload["_id"] . "</td>";
            echo "<td>" . $upload["date"] . "</td>";
            echo "<td>" . $upload["uploader"] . "</td>";
            echo "<td>" . $upload["title"] . "</td>";
            if (substr(mime_content_type($upload['filename']), 0, 5) == "image")
              echo "<td><img src='$filename_thumb' ></td>";
            else
              echo "<td>" . basename($upload["filename"]) . "</td>";

            // echo "<td>" . $upload["secret"] . "</td>";
            echo "<td><a class='uk-icon-button' uk-icon='download' href='" . $upload['filename'] . "' download></a></td>";

            $id=$upload["_id"];
            $secret=$upload["secret"];

            echo "<td><form action='deleteIndividual.php' method='get' id='document$id'>";
            echo "<input id='ident' name='ident' value='$id' hidden>";
            echo "<input id='secret' name='secret' value='$secret' hidden>";
            echo "<button class='uk-icon-button' uk-icon='trash' type='submit'></button></td>";
            echo "</form></tr>";
            echo "<script>deleteDocument('document$id');</script>";



          }
          else {
            $uploadDatabase->deleteBy(["_id", "=", $upload["_id"]]);
          }

      }

      ?>

  </table>

  <form action="deleteAll.php" method="get" id="deleteAll">
    <input class="uk-input" type="text"  id="secret" name="secret" value="<?= urldecode($_GET['secret'])?>" hidden>
    <button class="uk-button uk-button-small uk-button-danger uk-width-1-3@s uk-width-1-6@m" type="submit" form="deleteAll">Delete All</button>
    <div class="uk-button uk-button-small uk-button-default uk-width-1-3@s uk-width-1-6@m"><a href="https://<?= $_SERVER['HTTP_HOST']?>?identifier=<?= $_GET['identifier'] ?>" target="uploader<?= $_GET['identifier']; ?>">Goto Uploader</a></div>
    <div class="uk-button uk-button-small uk-button-default uk-width-1-3@s uk-width-1-6@m"><a href="https://<?= $_SERVER['HTTP_HOST']?>" target="_blank">New Uploader</a></div>
  </form>

  <script>
    deleteAll("deleteAll");
  </script>


  <h5>Download all files</h5>
  <div class="uk-button uk-button-small uk-button-default uk-width-1-3@s uk-width-1-6@m"><a href="https://<?= $_SERVER['HTTP_HOST']?>/download.php/?secret=<?= $_GET['secret']; ?>" download">Download</a></div>
  <p><img src="<?= qrcode("https://" . $_SERVER["HTTP_HOST"] . "/download.php?secret=" . $_GET["secret"], 3, 'XXXXXX', '000000'); ?>" ></p>
</div></body>
</html>
