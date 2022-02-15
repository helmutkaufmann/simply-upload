<html>
<head>

  <title>Delete Upload</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/uikit/uikit/dist/css/uikit.min.css" />
  <script src="/assets/uikit/uikit/dist/js/uikit.min.js"></script>
  <script src="/assets/uikit/uikit/dist/js/uikit-icons.min.js"></script>

</head>

<body>

  <div class="uk-padding-large uk-container">
  <h1>Dropload Documents</h1>
<?= urldecode($_GET['secret']) ?>
<?php

include_once("config.php");
include_once("assets/autoload.php");

@mkdir($databaseDirectory, 0755, true);
$databaseDirectory = __DIR__ . "/database";
$uploadDatabase = new \SleekDB\Store("uploads", $databaseDirectory);
$uploadedFiles = $uploadDatabase->findAll();
if ($_GET["secret"] == ADMINPASSWORD)
  $allUploads = $uploadDatabase->findAll(["date" => "desc", "uploader" => "asc", "title" => "asc"]);
else
  $allUploads = $uploadDatabase->findBy(["secret", "=", urldecode($_GET["secret"])], ["date" => "desc", "uploader" => "asc", "title" => "asc"]);


foreach ($allUploads as &$upload) {

    if (file_exists($upload["filename"])) {
      unlink($upload["filename"]);
    }
    $uploadDatabase->deleteById($upload["_id"]);

}

?>


<script>
window.location.href = "https://upload.mercator.li/catalogue.php?secret=<?= urldecode($_GET['secret']) ?>";
</script>
<?php
  if ($_GET["secret"] == ADMINPASSWORD)
    $allUploads = $uploadDatabase->findAll(["date" => "desc", "uploader" => "asc", "title" => "asc"]);
  else
    $allUploads = $uploadDatabase->findOneBy(["secret", "=", urldecode($_GET["secret"])], ["date" => "desc", "uploader" => "asc", "title" => "asc"]);
  echo (!$allUploads ? "Apologies, no documents can be found." : "");
?>

  </div>
</body>
