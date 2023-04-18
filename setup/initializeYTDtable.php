<?php

include ('../class/Process.php');


function firstSetup() {
    $lnk = new Process();

    $getSql = "SELECT branchNum from branchinfo.branches WHERE active = 1";
    $getQry = $lnk->query($getSql);
    foreach ($getQry as $branch) {
        $insertSql[] = "INSERT INTO cyclecounts.ytdper (branch, ytdPercent) VALUES (" . $branch['branchNum'] . ", 0)";
    }
    foreach ($insertSql as $insert) {
        $insertQry[] = $lnk->query($insert);
    }
}

$lnk = new Process();

$zeroArray = ['NC' => 0, 'DW' => 0, 'NS' => 0, 'INV' => 0, 'Count' => 0, 'Counted' => 0, 'ADJ' => 0];

$getSql = "SELECT * FROM cyclecounts.ytdper";
$getQry = $lnk->query($getSql);

foreach ($getQry as $info) {
    $branchData[$info['branch']] = $zeroArray;
}

$wkSql = "SELECT * FROM cyclecounts.branchwk";
$wkQry = $lnk->query($wkSql);

foreach ($wkQry as $x) {
    $weeks[] = $x['wkNum'];
    $branchData[$x['branch']]['NC'] += $x['wkNC'];
    $branchData[$x['branch']]['ADJ'] += $x['wkADJ'];
    $branchData[$x['branch']]['Count'] += $x['wkCount'];
    $branchData[$x['branch']]['Counted'] += $x['wkCounted'];
    $branchData[$x['branch']]['DW'] += $x['wkDW'];
    $branchData[$x['branch']]['INV'] += $x['wkINV'];
    $branchData[$x['branch']]['NS'] += $x['wkNS'];
}

foreach ($branchData as $branchNum => $y) {
    foreach ($y as $item => $value) {
        $key = "ytd".$item;

        $update[] = "UPDATE cyclecounts.ytdper SET " . $key . " = " . $value . " WHERE branch = " . $branchNum;
    }
}

foreach ($update as $upd) {
    $lnk->query($upd);
}

var_dump($update);