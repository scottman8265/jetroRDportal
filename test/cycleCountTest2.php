<?php

#require_once '../vendor/autoload.php';
require_once '../class/cycleCounts.php';

$week = 2;

$data = new \cycleCounts\cycleCounts($week);

$bigArray = $data->getBigArray();
$typeArray = $data->getTypeArray();
$freqArray = $data->getFreqArray();
$weekAmount = $data->getWeekAmount();
$weekCounts = $data->getWeekCounts();
$freqAmount = $data->getFreqAmount();
$freqCounts = $data->getFreqCounts();
$deptAmount = $data->getDeptAmount();
$deptCounts = $data->getDeptCounts();

#var_dump($bigArray);

foreach ($bigArray as $wk => $frequencyArray) {

    echo "<h1 style='text-align:center;'>Week#: " . $wk . " (Adj Amount: " . array_sum($weekAmount[$wk]) . ")</h1>";
    echo "<p style='text-align: center;'>";

    foreach ($typeArray as $type => $title) {
        $selector = $type . "Count";
        echo "<span style='margin: 15px;'>" . $title . ": " . $weekCounts[$wk][$selector] . "</span>";
    }

    echo "</p>";

    foreach ($frequencyArray as $freq => $deptArray) {

        #var_dump($freqAmount);

        echo "<h2 style='text-align:center;'>Frequency Counted: " . $freq . "(Adj Amount: " . array_sum($freqAmount[$wk][$freq]) . ")</h2>";
        echo "<p style='text-align: center;'>";

        foreach ($typeArray as $type => $title) {
            $selector = $type . "Count";
            echo "<span style='margin: 15px;'>" . $title . ": " . $freqCounts[$freq][$selector] . "</span>";
        }

        echo "</p>";

        foreach ($deptArray as $dept => $branchN) {

            /*$deptSql = "SELECT deptName from cyclecounts.deptinfo WHERE deptID = " . $dept;
            $deptQry = $deptLnk->query($deptSql);
            $deptName = $deptQry[0]['deptName'];*/
            $deptName = $freqArray[$dept]['deptName'];
            $deptAmt = isset($deptAmount[$wk][$freq][$dept]) ? $deptAmount[$wk][$freq][$dept] : 0;
            echo "<h3 style='text-align:left;'>" . $dept . ": " . $deptName . " (Adj Amount: " . array_sum($deptAmount[$wk][$freq][$dept]) . ")</h3>";

            echo "<p style='text-align: center;'>";

            foreach ($typeArray as $type => $title) {
                $selector = $type . "Count";
                echo "<span style='margin: 15px;'>" . $title . ": " . $deptCounts[$dept][$selector] . "</span>";
            }

            echo "</p>";

            /*foreach ($branchN as $branchNumber) {
                $branchNum = substr($branchNumber, 1);

                $branchSql = "SELECT branchName FROM branchinfo.branches WHERE branchNum = " . $branchNum;
                $branchQry = $branchLnk->query($branchSql);
                $branchName = $branchQry[0]['branchName'];

            }*/

        }
    }
}

