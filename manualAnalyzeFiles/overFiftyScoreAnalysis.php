<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/5/2018
 * Time: 7:06 PM
 */

require("../class/Process.php");

$auditLnk = new Process();
$findingLnk = new Process();
$questLnk = new Process();
$branchLnk = new Process();
$scoreLnk = new Process();

$findingSQL = "SELECT findID, qCode, auditID from auditanalysis.auditfindings WHERE auditID = ?";
$auditSql = "SELECT * from enteredaudits WHERE period = ? AND year = ?";
$questSql = "SELECT qTitle, qPoints FROM auditanalysis.auditquestions WHERE qCode = ?";
$branchSQL = "SELECT branch FROM auditanalysis.enteredaudits WHERE id = ?";
$scoreSql = "SELECT totScore FROM auditanalysis.auditscores WHERE auditID = ?";

$auditParams = ['Q3', 2018];

$auditQry = $auditLnk->query($auditSql, $auditParams);
$numOfAudits = $auditLnk->getQryCount();
$count = 0;
$findingCount = 0;
$a = '';

foreach ($auditQry as $audit) {
    $auditID = $audit['id'];

    $findingParams = [$auditID];
    $findingQRY = $findingLnk->query($findingSQL, $findingParams);

    foreach ($findingQRY as $value) {
        $findingArray[$value['qCode']][] = ['finding' => $value['findID'], 'audit' => $value['auditID']];
    }
}

#var_dump($findingArray);

foreach ($findingArray as $qCode => $fCount) {
    $missPercent = count($fCount) / $numOfAudits;

    if ($missPercent >= .5) {

        #var_dump($fCount);

        for ($i = 0; $i < count($fCount); $i++) {
            $over50Array[$fCount[$i]['audit']][] = $fCount[$i]['finding'];
        }

        $findingCount += count($fCount);

        $questParams = [$qCode];
        $questQry = $questLnk->query($questSql, $questParams);

        $missedPoints = $questQry[0]['qPoints'] * count($fCount);

        $count++;
        #$a .= $qCode . " - " . count($fCount) . " - " . $questQry[0]['qTitle'] . " - " . $missedPoints . "</br>";
    }
}

foreach ($over50Array as $audit => $finding) {
    $branchParams = [$audit];
    $branchQry = $branchLnk->query($branchSQL, $branchParams);

    #var_dump($branchQry);

    $scoreParams = [$audit];
    $scoreQry = $scoreLnk->query($scoreSql, $scoreParams);

    $scores[$branchQry[0]['branch']][] = $scoreQry[0]['totScore'];
}



var_dump($scores);