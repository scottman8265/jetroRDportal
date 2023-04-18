<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/13/2019
 * Time: 12:14 PM
 */

use PhpOffice\PhpSpreadsheet\Reader\Exception;
require_once('../class/getJCMSArrays.php');

define("BR", "</br>");
define("DATE", 'Y-m-d');
define("mySqlDate", 'Y-m-d');

date_default_timezone_set("US/Central");

function getBranchNum($branchNumber, $branchArray) {

    $number = null;

    foreach ($branchArray as $branchNum => $info) {
        if ($branchNumber == $info['twoDigit']) {
            $number = $branchNum;
            break 1;
        }
    }

    return $number;

}

function timed($start, $end, $timed) {

    echo $timed . " completed in " . ($end - $start) . BR;

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

        strlen($ccBranch) < 3 && strlen($ccBranch) > 1 ? $branchNum = getBranchNum($ccBranch, $branchArray) : $branchNum = $ccBranch;

        $branches[] = $branchNum;
    }


    return $branches;
}

$start = microtime(true);

$branchArray = $arrays->getBranchArray();
#$weekDates = $arrays->getWeekDates();
#$periodDates = $arrays->getPeriodDates();
$tableCol = [];

#$fileDate = date("mdy", strtotime("last saturday"));

$sheet = $spreadSheet->getActiveSheet();

$maxCol = $sheet->getHighestColumn();
$numCols = 57;

$wkNum = date("W", strtotime("last saturday"));
#$wkNum = 1;

$rowStart = 49 + (51 * ($wkNum - 2));
#$rowStart = 2;

$ccBranches = getCCbranches($spreadSheet, $branchArray, $rowStart);

foreach ($ccBranches as $branch) {
    $tableCol[] = "_" . $branch;
}


$row = $rowStart + 2;
$rowEnd = $row + 40;

for ($i = $row; $i < $rowEnd; $i++) {
    $col = 3;

    $deptData = [];

    $groupNum = $sheet->getCellByColumnAndRow($col, $i)->getValue();
    $col++;
    $deptNum = $sheet->getCellByColumnAndRow($col, $i)->getValue();
    $col++;
    $deptDesc = $sheet->getCellByColumnAndRow($col, $i)->getValue();
    $col++;

    for ($j = $col; $j < 54; $j++) {
        $deptData[] = $sheet->getCellByColumnAndRow($j, $i)->getCalculatedValue();
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

$dataCnt = count($data);
$tblColCnt = count($tableCol);

foreach ($data as $countNum => $info) {

    if (trim($info[4]) != 'STORE LEVEL COUNT') {
        $cols = [];
        $values = [];

        #$cols[] = 'groupNum';
        #$cols[] = 'deptNum';
        $cols[] = 'wkNum';
        $cols[] = 'deptID';
        #$values[] = $info[1];

        $values[] = $info[3];
        $values[] = $info[5];

        foreach ($info[0] as $key => $value) {

            if (strlen($tableCol[$key]) > 1 && $tableCol[$key] != '_463' && $tableCol[$key] != '_434') {
                $cols[] = $tableCol[$key];

                if (!is_numeric($value)) {
                    $values[] = "'" . strtoupper($value) . "'";
                } else {
                    $values[] = $value;
                }
            }
        }

        $colStr = implode(', ', $cols);
        $valStr = implode(', ', $values);

        $insert[] = "INSERT INTO cyclecounts.enteredcounts (" . $colStr . ") VALUES (" . $valStr . ")";
    }
}

$end = microtime(true);

timed($start, $end, 'Total Function');

