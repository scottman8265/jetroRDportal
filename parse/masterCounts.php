<?php

session_start();

use PhpOffice\PhpSpreadsheet\Reader\Exception;

require('inc/getCCArrays.php');

define("BR", "</br>");
define("DATE", 'Y-m-d');
define("mySqlDate", 'Y-m-d');

date_default_timezone_set("US/Central");

function getBranchNum($branchNumber, $branchArray)
{

    $number = null;

    foreach ($branchArray as $branchNum => $info) {
        if ($branchNumber == $info['twoDigit']) {
            $number = $branchNum;
            break 1;
        }
    }

    return $number;
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $branchArray array
 */
function getCCbranches($spreadSheet, $branchArray, $row)
{

    $sheet = $spreadSheet->getActiveSheet();
    $col = 6;
    $row = $row - 2;

    for ($i = $col; $i < 54; $i++) {
        $ccBranch = $sheet->getCellByColumnAndRow($i, $row)->getCalculatedValue();

        $branchNum = strlen($ccBranch) < 3 && strlen($ccBranch) > 1 ? $branchArray[$ccBranch] : $ccBranch;

        $branches[] = $branchNum;
    }


    return $branches;
}

$start = microtime(true);

$branchArray = $arrays->getTwoDigitArray();

$tableCol = [];

$sheet = $spreadSheet->getActiveSheet();

$maxCol = $sheet->getHighestColumn();
$numCols = 57;


/*if (!isset($_SESSION['wkNum'])) {
    $wkNum = date("W", strtotime("last saturday"));
} else {
    $wkNum = $_SESSION['wkNum'];
}
#$wkNum = 5;*/

#$rowStart = 49 + (51 * ($wkNum - 2));
$rowStart = $wkNum * 51;
$ccBranches = getCCbranches($spreadSheet, $branchArray, $rowStart);

foreach ($ccBranches as $branch) {
    $tableCol[] = "_" . $branch;
}

$row = $rowStart;
$rowEnd = $row + 40;
$wkRow = $rowEnd + 4;
$yrRow = $wkRow + 4;

for ($i = $row; $i < $rowEnd; $i++) {
    $col = 3;

    $deptData = [];

    $groupNum = $sheet->getCellByColumnAndRow($col, $i)->getValue();

    if ($groupNum > 0) {
        $col++;
        $deptNum = $sheet->getCellByColumnAndRow($col, $i)->getValue();
        $col++;
        $deptDesc = $sheet->getCellByColumnAndRow($col, $i)->getValue();
        $col++;
        for ($j = $col; $j < 54; $j++) {
            $deptData[] = $sheet->getCellByColumnAndRow($j, $i)->getCalculatedValue();
            /*$wkPer[] = $sheet->getCellByColumnAndRow($j, $wkRow)->getCalculatedValue();
            $yrPer[] = $sheet->getCellByColumnAndRow($j, $yrRow)->getCalculatedValue();*/
        }
        if ($deptNum == "ALL") {
            $deptNum = $groupNum;
        } elseif (strpos($deptNum, ',') !== false) {
            $deptNum = 14431;
        }
        if ($deptNum == 22 && $groupNum == 22) {
            $deptID = "'" . $groupNum . "." . $deptNum . "." . ($wkNum % 2) . "'";
        } else {
            $deptID = $groupNum . "." . $deptNum;
        }
        $data[] = [$deptData, $groupNum, $deptNum, $wkNum, $deptDesc, $deptID];
    }
}

$dataCnt = count($data);
$tblColCnt = count($tableCol);

foreach ($data as $countNum => $info) {

    if (trim($info[4]) != 'STORE LEVEL COUNT') {
        $cols = [];
        $values = [];

        $cols[] = 'wkNum';
        $cols[] = 'deptID';


        $values[] = $info[3];
        $values[] = $info[5];


        foreach ($info[0] as $key => $value) {

            if (strlen($tableCol[$key]) > 1 && $tableCol[$key] != '_434' && $tableCol[$key] !== '_0') {
                $cols[] = $tableCol[$key];

                if (!is_numeric($value)) {
                    $values[] = "'" . strtoupper($value) . "'";
                } else {
                    $values[] = $value;
                }
            }

            /*$cols[] = 'wkPer';
            $cols[] = 'yrPer';
            $values[] = $info[6][$key];
            $values[] = $info[7][$key];*/
        }

        $colStr = implode(', ', $cols);
        $valStr = implode(', ', $values);

        $insert[] = "INSERT INTO cyclecounts.enteredcounts (" . $colStr . ") VALUES (" . $valStr . ")";
    }

    #$insert[] = "INSERT INTO cyclecounts.enteredcounts (" . $colStr . ") VALUES (" . $valStr . ")";

}


for ($j = 6; $j < 54; $j++) {

    $week = $sheet->getCellByColumnAndRow($j, $wkRow)->getCalculatedValue();
    $year = $sheet->getCellByColumnAndRow($j, $yrRow)->getCalculatedValue();
    if (!preg_match('/COMPLIANCE/', $week) && $j !== 48 && $week > 0) {
        $wkPer[] = number_format($week, 4);
    }
    if (!preg_match('/COMPLIANCE/', $year) && $j !== 48 & $year > 0) {
        $yrPer[] = number_format($year, 4);
    }
}



$insert[] = "INSERT INTO cyclecounts.enteredcounts (" . $colStr . ") VALUES (" . $wkNum . ", 'wkPer', " . implode(', ', $wkPer) . ")";
$insert[] = "INSERT INTO cyclecounts.enteredcounts (" . $colStr . ") VALUES (" . $wkNum . ", 'yrPer', " . implode(', ', $yrPer) . ")";
