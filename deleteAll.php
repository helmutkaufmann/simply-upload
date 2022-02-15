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
