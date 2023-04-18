<?php

require '../vendor/autoload.php';
include '../inc/readFileFunc.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$file = 'C:\Users\scrip\OneDrive - Jetro Holdings LLC\Staffing Sheet\STAFFING COLLECTOR.xls';

$spreadSheet = readFileData($file);

$sheetNames = $spreadSheet->getSheetNames();

foreach ($sheetNames as $branch) {
    echo $branch . "</br>";
}
