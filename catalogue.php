<html>
<head>

  <title>Simply Upload</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="/assets/uikit/uikit/dist/css/uikit.min.css" />
  <script src="/assets/uikit/uikit/dist/js/uikit.min.js"></script>
  <script src="/assets/uikit/uikit/dist/js/uikit-icons.min.js"></script>

</head>

<body>

  <div class="uk-padding-large uk-container">
  <h1>Dropload Reporting</h1>
  <table class="uk-table uk-table-striped">
    <thead>
        <tr>
            <th>Date</th>
            <th>Identifier</th>
            <th>Secret</th>
            <th>Uploader</th>
            <th>Title</th>
            <th>Download</th>
        </tr>
    </thead>
    <tbody>
<?php

include_once("config.php");
include_once("assets/autoload.php");

@mkdir($databaseDirectory, 0755, true);
$databaseDirectory = __DIR__ . "/database";
$uploadDatabase = new \SleekDB\Store("uploads", $databaseDirectory);
$uploadedFiles = $uploadDatabase->findAll();
if ($_GET["secret"] == ADMINPASSWORD)
  $allUplaods = $uploadDatabase->findAll(["date" => "desc", "uploader" => "asc", "title" => "asc"]);
else
  $allUplaods = $uploadDatabase->findBy(["secret", "=", urldecode($_GET["secret"])], ["date" => "desc", "uploader" => "asc", "title" => "asc"]);

foreach ($allUplaods as &$upload) {

    if (file_exists($upload["filename"])) {
      echo "<tr>";
      echo "<td>" . $upload["date"] . "</td>";
      echo "<td>" . $upload["identifier"] . "</td>";
      echo "<td>" . $upload["secret"] . "</td>";
      echo "<td>" . $upload["uploader"] . "</td>";
      echo "<td>" . $upload["title"] . "</td>";
      echo "<td><a href='" . $upload["filename"] . "' target='document'>link</a></td>";
    }
    else {
      $uploadDatabase->deleteBy(["_id", "=", $upload["_id"]]);
    }
    echo "</tr>";
}

?>
</body>
</table>

<?= (!$allUplaods ? "Apologies, no documents can be found." : ""); ?>

  </div>
</body>
