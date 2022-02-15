

<?php

include_once("config.php");
include_once("assets/autoload.php");

use ZipArchive;

$zipfile = tempnam("/tmp", "mercator.zip");
$zip = new ZipArchive;

$zip->open($zipfile, ZipArchive::CREATE | ZipArchive::OVERWRITE);


@mkdir($databaseDirectory, 0755, true);
$databaseDirectory = __DIR__ . "/database";
$uploadDatabase = new \SleekDB\Store("uploads", $databaseDirectory);

$datum = date("YmdGis");

if ($_GET["secret"] == ADMINPASSWORD)
  $allUploads = $uploadDatabase->findAll(["date" => "desc", "uploader" => "asc", "title" => "asc"]);
else
  $allUploads = $uploadDatabase->findBy(["secret", "=", urldecode($_GET["secret"])], ["date" => "desc", "uploader" => "asc", "title" => "asc"]);

$cnt = 10000;
foreach ($allUploads as &$upload) {
    $zip -> addFile(__DIR__ . "/" . $upload["filename"], "uploader".  $datum . $cnt  . basename($upload["filename"]));
    $cnt++;
}

$zip->close();

header('Content-Type: application/zip');
header('Content-disposition: attachment; filename=uploader_' . $datum . '.zip');
header('Content-Length: ' . filesize($zipfile));

readfile($zipfile);

?>
