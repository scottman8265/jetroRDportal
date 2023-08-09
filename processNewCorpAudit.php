<?php

set_time_limit(300);
ini_set("log_errors", 1);
ini_set("error_log", "logs/php-error.log" . date('ymd'));
ini_set('memory_limit', '1G');

use PhpOffice\PhpSpreadsheet\Reader\Exception;

if (file_exists('../inc/readFileFunc.php')) {
    require_once '../inc/readFileFunc.php';
} else {
    require_once 'inc/readFileFunc.php';
}
if (file_exists('../class/Process.php')) {
    require_once '../class/Process.php';
} else {
    require_once 'class/Process.php';
}
if (file_exists('../vendor/autoload.php')) {
    require_once '../vendor/autoload.php';
} else {
    require_once 'vendor/autoload.php';
}
if (file_exists('../class/BranchInfo.php')) {
    require_once '../class/BranchInfo.php';
} else {
    require_once 'class/BranchInfo.php';
}
if (file_exists('../class/JRDaudits.php')) {
    require_once '../class/JRDaudits.php';
} else {
    require_once 'class/JRDaudits.php';
}

var_dump($_POST);

$file = isset($_POST['fileName']) ? $_POST['fileName'] : null;

try {
    $reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
    $reader->setReadDataOnly(TRUE);
    $spreadSheet = $reader->load($file);
} catch (Exception $e) {
    echo $e->getMessage();
    echo "<br>";
    echo $e->getTraceAsString();
    exit;
}

$sheet = $spreadSheet->getSheet(0);

$audit = new JRDaudits("newCorpAudit", $sheet);
