<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/5/2018
 * Time: 7:06 PM
 */

use PhpOffice\PhpSpreadsheet\Reader\Exception;

require '../vendor/autoload.php';

require("../class/Process.php");

$auditLnk = new Process();
$findingLnk = new Process();
$questLnk = new Process();

$findingSQL = "SELECT findID, qCode from auditanalysis.auditfindings WHERE auditID = ?";
$auditSql = "SELECT * from enteredaudits WHERE period = ? AND year = ?";
$questSql = "SELECT qTitle, qPoints FROM auditanalysis.auditquestions WHERE qCode = ?";

$auditParams = ['Q3', 2018];

$auditQry = $auditLnk->query($auditSql, $auditParams);
$numOfAudits = $auditLnk->getQryCount();
$count = 0;
$freshnessCount = 0;
$blockTagCount = 0;
$shelfTagsCount = 0;
$freshnessPoints = 0;
$blockTagPoints = 0;
$shelfTagsPoints = 0;
$lowStockPoints = 0;
$lowStockCount = 0;
$a = [];

foreach ($auditQry as $audit) {
    $auditID = $audit['id'];

    $findingParams = [$auditID];
    $findingQRY = $findingLnk->query($findingSQL, $findingParams);

    foreach ($findingQRY as $value) {
        $findingArray[$value['qCode']][] = $value['findID'];
    }
}

foreach ($findingArray as $qCode => $fCount) {
    $missPercent = count($fCount) / $numOfAudits;

    if ($missPercent >= .5) {

        $questParams = [$qCode];
        $questQry = $questLnk->query($questSql, $questParams);

        $points = $questQry[0]['qPoints'];
        $title = $questQry[0]['qTitle'];

        $missedPoints = $questQry[0]['qPoints'] * count($fCount);

        $count++;
        #$a .= $qCode . " - " . count($fCount) . " - " . $questQry[0]['qTitle'] . " - " . $missedPoints . "</br>";

        switch (true) {
            case preg_match('/OUTDATED/', $title):
                $freshnessCount += count($fCount);
                $freshnessPoints += $missedPoints;
                $a['FRESHNESS'] = ['audit' => "", 'question' => "", 'count' => 0, 'points' => 0];
                break;
            case preg_match('/BLOCK/', $title):
                $blockTagCount += count($fCount);
                $blockTagPoints += $missedPoints;
                $a['BLOCK TAGS'] = ['audit' => "", 'question' => "", 'count' => 0, 'points' => 0];
                break;
            case preg_match('/CORRECT PRICING SAMPLING/', $title):
                $shelfTagsCount += count($fCount);
                $shelfTagsPoints += $missedPoints;
                $a['SHELF TAGS'] = ['audit' => "", 'question' => "", 'count' => 0, 'points' => 0];
                break;
            case preg_match('/LOW STOCK/', $title) || preg_match('/LOWSTOCK', $title) || preg_match('/LOW STOCKS', $title):
                $lowStockCount += count($fCount);
                $lowStockPoints += $missedPoints;
                $a['LOW STOCKS'] = ['audit' => '', 'question' => '', 'count' => 0, 'points' => 0];
                break;
            default:
                $auditLookup = substr($qCode, 0, 2);
                $questionNum = substr($qCode, 2, 2);
                $lu = new Process();
                $lookupSQL = "SELECT auditName FROM auditanalysis.auditlookup WHERE auditCode = ?";
                $lookupParams = [$auditLookup];
                $lookupQry = $lu->query($lookupSQL, $lookupParams);
                $audit = $lookupQry[0]['auditName'];
                $a[$title] = ['audit' => $audit, 'question' => $questionNum, 'count' => count($fCount), 'points' => $missedPoints];
        }
    }
}
$a['FRESHNESS'] = ['count' => $freshnessCount, 'points' => $freshnessPoints];
$a['BLOCK TAGS'] = ['count' => $blockTagCount, 'points' => $blockTagPoints];
$a['SHELF TAGS'] = ['count' => $shelfTagsCount, 'points' => $shelfTagsPoints];
$a['LOW STOCKS'] = ['count' => $lowStockCount, 'points' => $lowStockPoints];


$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$header = ['Audit', 'Question #', 'Question', '# Branches Missed', 'Total Points Missed'];

$row = 1;

$spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, "Q3 Most Common Missed Audit Questions");
$row++;
$col = 1;
foreach ($header as $head) {
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $head);
    $col++;
    #$row++;
}
$row++;
$col = 1;

foreach ($a as $question => $info) {
    echo $question . " - # Missed: " . $info['count'] . "; Total Missed Points: " . $info['points'] . "</br>";
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $info['audit']);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $info['question']);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $question);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $info['count']);
    $col++;
    $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $info['points']);
    $row++;
    $col = 1;
}

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
$writer->save("../output/Q3Analysis.xlsx");