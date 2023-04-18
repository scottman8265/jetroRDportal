<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/16/2018
 * Time: 9:02 PM
 */

set_time_limit(300);
ini_set("log_errors", 1);
ini_set("error_log", "logs/php-error.log" . date('ymd'));
ini_set('memory_limit', '1G');

require ('../class/Process.php');

$branchLnk = new Process();
$lookupLnk = new Process();
$insertLnk = new Process();

$quarterArray = ['Q1', 'Q2', 'Q3'];
$year = 2019;

$branchSQL = "SELECT branchNum, branchName FROM branchInfo.branches WHERE active = TRUE && location = 'MW'";
$branchQRY = $branchLnk->query($branchSQL);

foreach ($branchQRY as $branch) {
   foreach ($quarterArray as $quarter) {
        $lookupSql = "SELECT branch FROM auditAnalysis.enteredaudits WHERE branch = ? AND period = ? AND year = ?";
        $lookupParams = [$branch['branchNum'], $quarter, $year];
        $lookupQry = $lookupLnk->query($lookupSql, $lookupParams);
        if (!$lookupQry) {
            $missingAudit[$quarter][] = [$branch['branchNum'], $branch['branchName']];
            $insertMissingArray[] = "INSERT INTO auditAnalysis.enteredaudits (year, period, branch, version, auditDate, auditStatus)
									VALUES (?, ?, ?, NULL, 'SKIPPED', 2)";
            $insertMissingParams[] = [$year, $quarter, $branch['branchNum']];
        }
    }
}

$missingCount = count($insertMissingArray);
$paramCount = count($insertMissingParams);

if ($missingCount = $paramCount) {
	for ($i = 0; $i < $missingCount; $i++) {
		$inserted[] = $insertLnk->query($insertMissingArray[$i], $insertMissingParams[$i]);
	}
}

$insertCount = 0;

foreach ($inserted as $x) {

	$str = $insertMissingParams[$insertCount][1] . " "  .  $insertMissingParams[$insertCount][0] . " for " . $insertMissingParams[$insertCount][2];

	if (x) {
		echo $str . " inserted correctly.</br>";
	} else {
		echo $str . " not inserted!!!! </br>";
	}
	$insertCount++;
}

