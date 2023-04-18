<?php

use PhpOffice\PhpSpreadsheet\Reader\Exception;

session_start();

require('inc/getCCArrays.php');

date_default_timezone_set("US/Central");

function getBranchNum($name, $branchArray) {

    $namePiece = explode(" ", $name);
    $branch = $namePiece[0];

    $doubleDigit = array_search($branch, $branchArray);

    $branchNum = $doubleDigit ? $doubleDigit : $branch;

    #echo $branchNum . '</br>';

    return $branchNum;
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $branchArray array
 */
function getCCbranches($spreadSheet, $branchArray, $row) {

    $sheet = $spreadSheet->getActiveSheet();
    $col = 6;
    #$row = 2;

    for ($i = $col; $i < 54; $i++) {
        $ccBranch = $sheet->getCellByColumnAndRow($i, $row)->getCalculatedValue();

        $branchNum = strlen($ccBranch) < 3 && strlen($ccBranch) > 1 ? $branchArray[$ccBranch] : $ccBranch;

        $branches[] = $branchNum;
    }


    return $branches;
}

$start = microtime(true);

$branchArray = $arrays->getTwoDigitArray();

$branchNum = getBranchNum($name, $branchArray);

$tableCol = [];

$sheet = $spreadSheet->getActiveSheet();

/*$wkNum = date("W", strtotime("last saturday"));
#$wkNum = 5;*/

$rowStart = 3 + (51 * ($wkNum - 26));
#$rowStart = 2;

$row = $rowStart;
$rowEnd = $row + 40;
$bcr = $rowEnd + 4;

for ($i = $row; $i < $rowEnd; $i++) {

    $entry[] = $sheet->getCellByColumnAndRow(6, $i)->getCalculatedValue();

}

#echo $branchNums ."</br>";

$bcp[$branchNum] = $sheet->getCellByColumnAndRow(6, $bcr)->getCalculatedValue();

$data[$branchNum] = $entry;
$_SESSION['rowStart'] = $rowStart + 1323;
#$_SESSION['rowStart'] = $rowStart + 51;
$_SESSION['rowEnd'] = $rowEnd + 49;
$_SESSION['bcp'] = $bcp;





