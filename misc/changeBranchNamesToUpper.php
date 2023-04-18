<?php

require ('../class/Process.php');

$branches = new Process();

$sql = "SELECT branchNum, branchName FROM branchInfo.branches";
$qry = $branches->query($sql);

foreach ($qry as $x) {

	$num = $x['branchNum'];
	$name = strtolower($x['branchName']);
	$name = ucwords($name);

	$updateSql = "UPDATE branchInfo.branches SET branchName = ? WHERE branchNum = ?";
	$updateParams = [$name, $num];
	$updateQry = $branches->query($updateSql, $updateParams);

	if ($updateQry) {
		echo $num . " " . $name . " ***UPDATED***</br>";
	} else {
		echo $num . " " . $name . " ***NOT UPDATED***</br>";
	}
	// echo $num . " " . $name . "</br>";
}