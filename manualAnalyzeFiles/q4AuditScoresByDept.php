<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/26/2018
 * Time: 9:10 AM
 */

require('../vendor/autoload.php');
require('../class/Process.php');

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$branchLnk = new Process();
$auditLnk = new Process();
$deptLnk = new Process();
$scoreLnk = new Process();

$branchSql = "SELECT branchNum, branchName FROM branchinfo.branches where active = TRUE";
$auditSql = "SELECT id, branch FROM auditanalysis.enteredaudits where period = 'Q4'";
$scoreSql = "SELECT * FROM auditanalysis.auditscores where auditID = ?";
$deptSql = "SELECT lcAuditCode, auditName FROM auditanalysis.auditlookup WHERE active = TRUE";

$branchQry = $branchLnk->query($branchSql);

$auditQry = $auditLnk->query($auditSql);

$deptQry = $deptLnk->query($deptSql);

echo $auditLnk->getQryCount() . "</br>";

foreach ($deptQry as $dept) {
    $scoreField[] = $dept['lcAuditCode'] . "Score";
}

foreach ($branchQry as $a) {
    $branchArray[$a['branchNum']] = $a['branchName'];
}

foreach ($deptQry as $b) {
    $deptArray[$b['lcAuditCode'] . "Score"] = $b['auditName'];
}

foreach ($deptArray as $c => $d) {

    $totalScores[$c] = 0;
}

#var_dump($totalScores);

foreach ($auditQry as $value) {
    $branch = $value['branch'];
    $id = $value['id'];
    $auditArray[] = $branch;
    $scoreParams = [$id];
    $scoreQry = $scoreLnk->query($scoreSql, $scoreParams);

    foreach ($scoreField as $field) {

        $scoreArray[$field][$branch][] = number_format($scoreQry[0][$field] * 100, 2);

    }

}

foreach ($scoreArray as $department => $branchInfo) {
    foreach ($branchInfo as $branch => $score) {

        $auditName = $deptArray[$department];
        $branchName = $branchArray[$branch];

        $totalScores[$department] += $score[0];

        #echo $department . "</br>";
        echo $auditName . ": " . $branchName . " (" . $score[0] . ") </br>";

        $writeArray[$auditName][] = ['branchNum' => $branch, 'branchName' => $branchName, 'score' => $score[0]];
    }
}

#var_dump($writeArray);

foreach ($totalScores as $x => $score) {

    $average = $score / 38;

    $averages[$deptArray[$x]] = number_format($average, 2);
}

foreach ($averages as $audit => $avg) {
    $summaryArray[$audit] = $avg;
}

$spreadSheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

$newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'Summary');
$spreadSheet->addSheet($newSheet);
$spreadSheet->setActiveSheetIndexByName('Summary');

$row = 1;

$spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Midwest Q4 Audit Scores");
$row ++;
$spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Department");
$spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(2, $row, "Avg Score");
$row++;
foreach ($summaryArray as $v => $q) {
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $v);
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $q);
    $row++;
}

$row = 1;
foreach ($writeArray as $i => $t) {
    $row = 1;
    $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $i);
    $spreadSheet->addSheet($newSheet);
    $spreadSheet->setActiveSheetIndexByName($i);

    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1,$row, "Q4 ".$i." Scores by Branch");
    $row++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, 'Branch Number');

    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(2, $row, 'Branch Name');

    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(3, $row, 'Branch Score');

$row++;

    foreach ($t as $info) {
        $col = 1;
        $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $info['branchNum']);
    $col++;
        $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $info['branchName']);
    $col++;
        $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $info['score']);
        $row++;
    };

}

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
$writer->save("../output/Q4ScoresByDept.xlsx");




