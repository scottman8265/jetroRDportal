<?php

require ('../class/Process.php');

$wb = new Process();

$sql = "SELECT branchNum, branchName from branchInfo.corpbranches where corpbranches.region = 'West'";
$qry = $wb->query($sql);

foreach ($qry as $x) {

	$num = $x['branchNum'];
	$name = strtoupper($x['branchName']);
	$newNum = null;

	if (($num > 99 && $num < 200) || ($num > 499 && $num < 600 )) {
		$newNum = substr($num, 1);
	}

	$insertSql = "INSERT INTO branchInfo.branches (branchNum, branchName, _2DigNum, location) VALUES (?, ?, ?, 'WC')";
	$insertParam = [$num, $name, $newNum];
	$insertQry = $wb->query($insertSql, $insertParam);

	if ($insertQry) {
		echo $num . "(". $newNum .") - " . $name . " ***INSERTED***</br>";
	} else {
		echo $num . "(". $newNum .") - " . $name . " *** NOT INSERTED***</br>";
	}
}