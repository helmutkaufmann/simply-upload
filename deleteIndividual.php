<?php

include_once("config.php");
include_once("assets/autoload.php");

@mkdir($databaseDirectory, 0755, true);
$databaseDirectory = __DIR__ . "/database";
$uploadDatabase = new \SleekDB\Store("uploads", $databaseDirectory);

$deleted = $uploadDatabase->deleteBy([["_id", "==", $_GET["ident"]], ["secret", "=", $_GET["secret"]]], 2);

unlink($deleted[0]["filename"]);

$filename_no_ext = reset(explode('.', $deleted[0]["filename"]));
$filename_thumb = $filename_no_ext . '___thumb' . '.jpg';
@unlink($filename_thumb);

echo ($_GET["ident"] . " / " . $_GET["secret"]);

?>
