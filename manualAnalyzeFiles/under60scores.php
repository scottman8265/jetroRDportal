<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/16/2018
 * Time: 9:23 PM
 */

require('../class/Process.php');
require('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Reader\Exception;


$period = 'Q4';
$year = 2018;
$threshHold = .6;

$deptLnk = new Process();
$auditLnk = new Process();
$scoreLnk = new Process();
$branchLnk = new Process();

$deptSql = "SELECT lcAuditCode, auditName from auditanalysis.auditlookup WHERE active = true";
$deptQry = $deptLnk->query($deptSql);

$auditSql = "SELECT id, branch, period from auditanalysis.enteredaudits WHERE period = ? AND year = ?";
$auditParams = [$period, $year];
$auditQry = $auditLnk->query($auditSql, $auditParams);

$branchSql = "SELECT branchNum, branchName FROM branchinfo.branches WHERE active = true";
$branchQry = $branchLnk->query($branchSql);

foreach ($branchQry as $value) {
    $branches[$value['branchNum']] = $value['branchName'];
}

foreach ($auditQry as $audit) {
    foreach ($deptQry as $depart) {

        $dept = $depart['lcAuditCode'] . "Score";
        $scoreSql = "SELECT " . $dept . " FROM auditanalysis.auditscores WHERE auditID = ?";
        $scoreParams = [$audit['id']];
        $scoreQry = $scoreLnk->query($scoreSql, $scoreParams);

        $score = $scoreQry[0][$dept] * 100;
        $score = number_format($score, 2);

        if ($score < ($threshHold * 100)) {
            echo $audit['branch'] . " - " . $branches[$audit['branch']] . ":  " . $depart['auditName'] . "(" . $score . ") " . $audit['period'] . "</br>";
            $a[] = ['branchNum'=>$audit['branch'], 'branchName'=>$branches[$audit['branch']], 'dept'=>$depart['auditName'], 'score'=>$score, 'period'=>$audit['period']];
        }


    }
    unset($branches[$audit['branch']]);
}

#var_dump($branches);

foreach ($branches as $branchNum => $branchName) {
    $auditSql = "SELECT id, branch, period from auditanalysis.enteredaudits WHERE period = ? AND year = ? AND branch = ?";
    $auditParams = ['Q3', $year, $branchNum];
    $auditQry2 = $auditLnk->query($auditSql, $auditParams);

    foreach ($auditQry2 as $audit) {
        foreach ($deptQry as $depart) {

            $dept = $depart['lcAuditCode'] . "Score";
            $scoreSql = "SELECT " . $dept . " FROM auditanalysis.auditscores WHERE auditID = ?";
            $scoreParams = [$audit['id']];
            $scoreQry = $scoreLnk->query($scoreSql, $scoreParams);

            $score = $scoreQry[0][$dept] * 100;
            $score = number_format($score, 2);

            if ($score < ($threshHold * 100)) {
                echo $audit['branch'] . " - " . $branches[$audit['branch']] . ":  " . $depart['auditName'] . "(" . $score . ") " . $audit['period'] . "</br>";
                $a[] = ['branchNum'=>$audit['branch'], 'branchName'=>$branches[$audit['branch']], 'dept'=>$depart['auditName'], 'score'=>$score, 'period'=>$audit['period']];

            }

            #unset($branches[$audit['branch']]);
        }
    }
}

$header = ['Branch', 'Branch Name', 'Department', 'Score', 'Period'];

$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$spreadSheet->getActiveSheet();

$row = 1;

$spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Last Audit Department Scores under 60");

$row++;
$col  = 1;

foreach ($header as $title) {
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $title);
    $col++;
}
$row++;

foreach ($a as $head => $value) {
    $col = 1;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value['branchNum']);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value['branchName']);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value['dept']);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value['score']);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $value['period']);
    $row++;
}

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
$writer->save("../output/under60scores.xlsx");


