<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/1/2019
 * Time: 8:32 AM
 */

require('../class/Process.php');
require('../vendor/autoload.php');
include('../inc/readFileFunc.php');

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * @param $branchArray
 * @param $branchLnk Process
 * @param $chkBranch
 */
function insertBranches($branchArray, $branchLnk, $chkBranch) {
    $counted = 0;
    $notCounted = 0;
    var_dump($chkBranch);
    foreach ($branchArray as $bNum => $data) {

        if (!array_key_exists($bNum, $chkBranch)) {
            $branchSql = "INSERT INTO branchInfo.corpbranches (branchNum, branchName, region) VALUES (?, ?, ?)";
            $branchParams = [$bNum, $data['branchName'], $data['region']];
            $branchQry = $branchLnk->query($branchSql, $branchParams);
            if (!$branchQry) {
                $notCounted++;
            } else {
                $counted++;
            }
        }
    }

    echo "Inserted " . $counted . " Branches </br>".$notCounted." Branches Not Inserted</br>";
}

/**
 * @param $insertArray
 * @param $insertLnk Process
 * @param $branchArray
 * @param $entered
 */
function insertScores($insertArray, $insertLnk, $branchArray, $entered) {
    $counted = 0;
    $notCounted = 0;
    #var_dump($entered);
    foreach ($insertArray as $ayear => $aperiodArray) {
        foreach ($aperiodArray as $aperiod => $aregionArray) {
            foreach ($aregionArray as $aregion => $abranchArray) {
                foreach ($abranchArray as $abranchNum => $aauditArray) {
                    foreach ($aauditArray as $aauditID => $ascore) {

                    	$insertQry = null;

                        if (!isset($entered[$ayear][$aperiod][$aregion][$abranchNum])) {

                            $insertSql = "INSERT INTO auditAnalysis.corpscores (region, branchNum, branchName, auditID, period, year, score) VALUES (?, ?, ?, ?, ?, ?, ?)";
                            $insertParams = [$aregion, $abranchNum, $branchArray[$abranchNum]['branchName'], $aauditID, $aperiod, $ayear, $ascore];
                            echo "[importCorpScores.php->line->63] Insert Sql: " . $insertSql;
                            $insertQry = $insertLnk->query($insertSql, $insertParams);
                        }

                        if (!$insertQry) {
                            $notCounted++;
                        } else {
                            $counted++;
                        }

                    }
                }
            }
        }
    }

    echo "</br>[importCorpScores.php->line->78] Inserted " . $counted . " Scores</br>".$notCounted." Scores Not Inserted</br>";
}

$file = 'C:\Users\scrip\OneDrive - Jetro Holdings LLC\Audit Analysis\2019 Corp Audit Exported results  Q1-Q2-Q3-Q4 12-30-2019.xlsx';
$year = 2020;
$quarter = 1;

$enteredLnk = new Process();
$insertLnk = new Process();
$branchLnk = new Process();

$spreadSheet = readFileData($file);

#echo count($spreadSheet);

$sheetNames = $spreadSheet->getSheetNames();
$sheetCounts = count($sheetNames);

$enteredSql = "SELECT branchNum, period, lineID, region from auditAnalysis.corpscores where year = ?";
$enteredParams = [$year];
$enteredQry = $enteredLnk->query($enteredSql, $enteredParams);

foreach ($enteredQry as $entered) {
    $enteredArray[$year][$entered['period']][$entered['region']][$entered['branchNum']] = $entered['lineID'];
}

$branchSql = "SELECT branchNum FROM branchInfo.corpbranches";
$branchQry = $branchLnk->query($branchSql);

foreach($branchQry as $branch) {
    $branchChk[$branch['branchNum']] = 0;
}

foreach ($sheetNames as $sheets) {

    $col = 3;
    $row = 2;

    $enteredSql = "SELECT branchNum, period, year, lineID from auditAnalysis.corpscores where period = ? AND year = ?";
    $enteredParams = [$quarter, $year];
    $enteredQry = $enteredLnk->query($enteredSql, $enteredParams);

    $spreadSheet->setActiveSheetIndexByName($sheets);
    $a = $spreadSheet->getActiveSheet();
    $highestRow = $spreadSheet->getActiveSheet()->getHighestRow();
    $highestRow++;

    for ($j = $row; $j < $highestRow; $j++) {
        $region = $a->getCellByColumnAndRow(3, $j)->getValue();
        $branchNum = $a->getCellByColumnAndRow(4, $j)->getValue();
        $branchName = $a->getCellByColumnAndRow(5, $j)->getValue();
        $auditID = $a->getCellByColumnAndRow(6, $j)->getValue();
        $auditName = $a->getCellByColumnAndRow(7, $j)->getValue();
        $period = $a->getCellByColumnAndRow(8, $j)->getValue();
        $year = $a->getCellByColumnAndRow(9, $j)->getValue();
        $score = $a->getCellByColumnAndRow(10, $j)->getValue();

        echo "[importCorpScores.php->line->135]" . $sheets;

        if (trim($sheets) === 'Q4 2019') {
            $period = 4;
        }

        echo $year . " Q" . $period . " " . $region . " " . $branchNum . " " . $auditID . " " . $score . "</br>";

        if ($region != 'MidWest' && is_numeric($branchNum)) {
            $insertArray[$year][$period][$region][$branchNum][$auditID] = $score;
            $branchArray[$branchNum] = ['branchName' => $branchName, 'region' => $region];
            $auditArray[$auditID] = $auditName;
        }

    }
}


echo "</br>[importCorpScores.php->line->153]  From Array: </br>";

#var_dump($insertArray);

#insertBranches($branchArray, $branchLnk, $branchChk);
insertScores($insertArray, $insertLnk, $branchArray, $enteredArray);



