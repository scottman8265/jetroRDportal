<?php

session_start();

$files = $_POST['files'];
$clicker = $_POST['clicker'];
$fileType = $_POST['fileType'];
$fileCount = $_POST['fileCount'];

$sessFiles = $_SESSION['name'];

foreach ($sessFiles as $key => $file) {
    $prosFiles[] = $key;
}

echo "\n\n Files: \n";
print_r($_POST);

foreach ($files as $key => $value) {
    echo $key . ":";
}

print_r($_FILES);
echo "\n\n Processed Files: \n";
print_r($prosFiles);