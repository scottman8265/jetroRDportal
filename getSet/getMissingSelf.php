<?php

session_start();

require ('../class/Process.php');

$lnk = new Process();

$insert = [];

$periods = isset($_SESSION['periods']) ?  $_SESSION['periods'] : ["19:3"];

$branchSql = "SELECT branchNum FROM branchinfo.branches WHERE active = 1";
$branchQry = $lnk->query($branchSql);

foreach ($branchQry as $data) {
    $branchArray[] = $data['branchNum'];
}

foreach ($periods as $period) {
    $pieces = explode(":", $period);
    $year = $pieces[0];
    $month = $pieces[1];

    $saSql = "SELECT branch FROM auditanalysis.selfaudits WHERE year = ? AND month = ?";
    $saParams = [$year, $month];
    $saQry = $lnk->query($saSql, $saParams);

    $count = 1;

    foreach ($saQry as $x) {
        $saSubmitted[$count] = $x['branch'];
        $count++;
    }

    foreach($branchArray as $branches) {

        echo array_search($branches, $saSubmitted) . " : " . $branches."</br>";

        $valuesArray = [$year, $month, $branches, 0];
        $values = implode(", ", $valuesArray);

        if (!array_search($branches, $saSubmitted)) {
            echo "</br>" . $branches . "</br>";
            $insertArray[] = "INSERT INTO auditanalysis.selfaudits (year, month, branch, auditStatus) VALUES (".$values.")";
        }
    }
}

$insertStr = implode("; ", $insertArray);

echo $insertStr;

if (!is_null($insertStr)) {
    $lnk->query($insertStr);
}


