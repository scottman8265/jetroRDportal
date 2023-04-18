<?php

require_once ('../class/Process.php');
$lnk = new Process();

$tracking = $_POST['tracking'];
$date = $_POST['date'];
$namesArr = [];

$trackedSql = "SELECT identifier FROM trackers.trackers where tracking = ? and date = ?";
$trackedParams = [$tracking, $date];
$trackedQry = $lnk->query($trackedSql, $trackedParams);

$branchSql = "SELECT branchName, branchNum FROM branchInfo.branches WHERE active = 1";
$branchQry = $lnk->query($branchSql);

$regionalSql = "SELECT lName from branchInfo.regionals WHERE active = 1";
$regionalQry = $lnk->query($regionalSql);

$auditorSql = "SELECT auditorLName from branchInfo.auditors WHERE auditorFT = 1";
$auditorQry = $lnk->query($auditorSql);

foreach ($regionalQry as $item) {
	$namesArr[] = $item['lName'];
}

foreach ($auditorQry as $item) {
	$namesArr[] = $item['auditorLName'];
}

foreach ($branchQry as $branch) {
		$allBranches[$branch['branchNum']] = $branch['branchName'];
	if (array_search($branch['branchNum'], array_column($trackedQry, 'identifier')) === false) {
		$missedBranches[$branch['branchNum']] = $branch['branchName'];
	}
}

ksort($allBranches);
ksort($missedBranches);
sort($namesArr);

echo json_encode(['missed' => $missedBranches, 'allBranches' => $allBranches, 'names' => $namesArr]);

