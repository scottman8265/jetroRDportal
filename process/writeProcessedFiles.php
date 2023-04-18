<?php

session_start();

var_dump($_SESSION);

require_once('../class/Process.php');

$files = $_SESSION['name'];
$wkNum = $_SESSION['wkNum'];
$lnk = new Process();

$branchArray = $lnk->query("SELECT branchNum FROM branchinfo.branches");

foreach($branchArray as $branchNum) {
    $branches[$branchNum['branchNum']] = 'not processed';
}

foreach ($files as $file) {

    echo $file . "</br>";

    $name = explode(' ', $file);
    $branch = $name[0];

    $lnk->query("INSERT INTO cyclecounts.processedcounts (wkNum, branch) VALUES (" . $wkNum . ", " . $branch . ")");

    unset($branches[$branch]);

}


print_r($branches);