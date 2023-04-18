<?php

use PhpOffice\PhpSpreadsheet\Reader\Exception;

require_once '../inc/readFileFunc.php';
require_once '../class/Arrays.php';
require_once '../class/Process.php';
require_once '../vendor/autoload.php';

$aodFileName = "C:\Users\Robert Brandt\OneDrive\OneDrive - Jetro Holdings LLC\Learning Center\AODActiveRoster.xlsx";

$reportFileName = "C:\Users\Robert Brandt\OneDrive\OneDrive - Jetro Holdings LLC\Learning Center\Reporting\HACCP\haccpComp.xlsx";

$aodSpreadSheet = readFileData($aodFileName, 'xlsx', true);
$reportSpreadSheet = readFileData($reportFileName, 'xlsx', true);

$aodSheet = $aodSpreadSheet->getActiveSheet();

$aodRows = $aodSheet->getHighestRow();

for ($i = 3; $i < ($aodRows + 1); $i++) {
    if ($aodSheet->getCell("E".$i)->getValue() == 'OM') {
        $location = $aodSheet->getCell("F".$i)->getValue();
        $id = $aodSheet->getCell("A".$i)->getValue();
        $fName = $aodSheet->getCell("B".$i)->getValue();
        $lName = $aodSheet->getCell("C".$i)->getValue();

        $aodArray[] = ['id' => $id, 'name' => $fName. " " . $lName, 'location'=>$location];
    }
}

$rptSheet = $reportSpreadSheet->getActiveSheet();

$rptRows = $rptSheet->getHighestRow();

for ($k = 2; $k < $rptRows + 1; $k++) {

    $rawName = $rptSheet->getCell("B".$k)->getValue();
    $start = $rptSheet->getCell("C".$k)->getValue();
    $end = $rptSheet->getCell("D".$k)->getValue();
    $status = $rptSheet->getCell("E".$k)->getValue();


    $idSplit = explode("(", $rawName);

    $id = substr($idSplit[1], 0, -1);
    $name = $idSplit[0];

    $rptArray[] = ['id' => $id, 'name' => $name, 'start'=>$start, 'end'=>$end, 'status'=>$status];
}

$rptCnt = count($rptArray);

for ($l = 0; $l < $rptCnt + 1; $l ++) {

    if (array_search($rptArray[$l]['id'], array_column($aodArray, 'id'))) {
        $key = array_search($rptArray[$l]['id'], array_column($aodArray, 'id'));
        $mwHaccpCertKey[] = $key;
        $rptInfo[$key] = $rptArray[$l];
    } else {
        $notActiveInMw[] = $rptArray[$l]['name'];
    };
}

$mwHaccpCertSheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

$thisSheet = $mwHaccpCertSheet->getActiveSheet();

$certCnt = count($mwHaccpCertKey);

$thisSheet->setCellValueByColumnAndRow(1, 1, 'MW Haccp Certified By Learning Center');
$thisSheet->setCellValueByColumnAndRow(1, 2, 'Branch');
$thisSheet->setCellValueByColumnAndRow(2, 2, 'ID');
$thisSheet->setCellValueByColumnAndRow(3, 2, 'Name');
$thisSheet->setCellValueByColumnAndRow(4, 2, 'Started');
$thisSheet->setCellValueByColumnAndRow(5, 2, 'Completed');
$thisSheet->setCellValueByColumnAndRow(6, 2, 'Status');

for ($x = 0; $x < $certCnt + 1; $x++) {
    $row = $x + 3;
    $col = 1;

    $branch = $aodArray[$mwHaccpCertKey[$x]]['location'];
    $name = $aodArray[$mwHaccpCertKey[$x]]['name'];
    $id = $aodArray[$mwHaccpCertKey[$x]]['id'];
    $started = $rptInfo[$mwHaccpCertKey[$x]]['start'];
    $finished = $rptInfo[$mwHaccpCertKey[$x]]['end'];
    $curStatus = $rptInfo[$mwHaccpCertKey[$x]]['status'];

    $thisSheet->setCellValueByColumnAndRow($col, $row, $branch);
    $col++;
    $thisSheet->setCellValueByColumnAndRow($col, $row, $id);
    $col++;
    $thisSheet->setCellValueByColumnAndRow($col, $row, $name);
    $col++;
    $thisSheet->setCellValueByColumnAndRow($col, $row, $started);
    $col++;
    $thisSheet->setCellValueByColumnAndRow($col, $row, $finished);
    $col++;
    $thisSheet->setCellValueByColumnAndRow($col, $row, $curStatus);
}

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($mwHaccpCertSheet, "Xlsx");
$writer->save('../output/mwHACCPrpt.xlsx');

var_dump($mwHaccpCertKey);



