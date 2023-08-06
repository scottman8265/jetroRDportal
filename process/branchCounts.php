<?php

/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/20/2019
 * Time: 3:43 PM
 */

session_start();

require_once '../vendor/autoload.php';
require_once '../inc/readFileFunc.php';
require_once '../class/Process.php';

$fileCount = $_SESSION['fileCount'];
$wkNum = isset($_SESSION['wkNum']) ? $_SESSION['wkNum'] : date("W", strtotime("last saturday"));
$inputFile = null;
$a = 1;

/*if (isset($_POST['file'])) {
    $inputFile = $_POST['file'];
}*/

#$inputFile = fopen('input/2019 Cycle Count Master.xlsx', 'r');

$inputFile = $_POST['inputFile'];

$type = gettype($inputFile);

if (!is_null($inputFile)) {
    try {
        $spreadSheetA = readFileData($inputFile, 'Xlsx', false);

        $spreadSheet = $spreadSheetA['spreadsheet'];
        $type = $spreadSheetA['type'];
    } catch (Error $q) {
        #echo "reading the input spreadsheet: ";
        $r = $q->getMessage();
    }
}

#var_dump($spreadsheet);

if (!is_null($spreadSheet)) {
    $sheet = $spreadSheet->getActiveSheet();

    for ($i = 6; $i < 54; $i++) {
        $branch = $sheet->getCellByColumnAndRow($i, 2)->getCalculatedValue();
        $branchCols[$branch] = $i;
    }

    $_SESSION['branchCols'] = $branchCols;
    $count = 0;
    for ($j = 0; $j < $fileCount; $j++) {
        $count++;
        $data = $_SESSION['writeData'][$j]['sentData'];

        foreach ($data as $branch => $adjs) {
            $col = $branchCols[$branch];
            $row = $_SESSION['rowStart'];

            foreach ($adjs as $adj) {

                if (strtoupper($adj) === 'INVENTORY') {
                    $adj = "INV";
                }

                $sheet->setCellValueByColumnAndRow($col, $row, $adj);
                $row++;
            }
        }
        #echo $count . "</br>";
    }


    #$spreadSheet2 = $spreadSheet;

    $file = 'output/cycleCountLogs/masterLogs/2019 Cycle Count Master - wkNum ' . $wkNum . '.xlsx';


    try {
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
        $error[] = $e;
    }
    try {
        $writer->save('../' . $file);
    } catch (\PhpOffice\PhpSpreadsheet\Writer\Exception $e) {
        $error[] = $e;
    }
} else {
    $a = 5;
    #echo "SpreadSheet is Null";
}

echo json_encode([
    'html' => '<a href="' . $file . '" download><button class="ui-corner-all ui-button">Output File</button></a>',
    'inputFile' => $inputFile, 'weekNum' => $wkNum, 'errors' => $error, 'r' => $input
]);
