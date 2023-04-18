<?php

#require_once '../vendor/autoload.php';
require_once '../class/Process.php';

$colLink = new Process();
$dataLnk = new Process();
$freqLnk = new Process();
$deptLnk = new Process();
$branchLnk = new Process();

$weekNum = [2];

$colSql = "SHOW COLUMNS FROM cyclecounts.enteredcounts";
$colQry = $colLink->colNames($colSql);

foreach ($colQry as $fields) {
    $field = $fields['Field'];
    if (substr($field, 0, 1) == '_') {
        $colArray[] = $field;
    }
}

asort($colArray);

foreach ($weekNum as $week) {
    $dataSql = "SELECT * FROM cyclecounts.enteredcounts WHERE wkNum = " . $week;
    $dataQry = $dataLnk->query($dataSql);

    foreach ($dataQry as $data) {

        $deptID = "'" . $data['deptID'] . "'";

        $freqSql = "SELECT countFreq, deptName FROM cyclecounts.deptinfo where deptID = " . $deptID;
        $freqQry = $freqLnk->query($freqSql);

        $frequency = $freqQry[0]['countFreq'];

        foreach ($colArray as $branch) {

            $entry = $data[$branch];

            $bigArray[$week][$frequency][$deptID][] = $branch;
            $typeArray = ['nc' => 'Not Counted', 'dw' => 'Dollar Wash', 'ns' => 'Not Sold', 'inv' => 'Inventory',
                          'counted' => 'Counted', 'total' => 'Total'];

            if (!isset($branchCounts[$branch])) {
                $branchCounts[$branch] = ['ncCount' => 0, 'dwCount' => 0, 'nsCount' => 0, 'invCount' => 0,
                                          'countedCount' => 0, 'totalCount' => 0];
            }
            if (!isset($deptCounts[$deptID])) {
                $deptCounts[$deptID] = ['ncCount' => 0, 'dwCount' => 0, 'nsCount' => 0, 'invCount' => 0,
                                        'countedCount' => 0, 'totalCount' => 0];
            }
            if (!isset($freqCounts[$frequency])) {
                $freqCounts[$frequency] = ['ncCount' => 0, 'dwCount' => 0, 'nsCount' => 0, 'invCount' => 0,
                                           'countedCount' => 0, 'totalCount' => 0];
            }
            if (!isset($weekCounts[$week])) {
                $weekCounts[$week] = ['ncCount' => 0, 'dwCount' => 0, 'nsCount' => 0, 'invCount' => 0,
                                      'countedCount' => 0, 'totalCount' => 0];
            }

            switch ($entry) {
                case 'NC':
                    $ncArray[$week][$frequency][$deptID][] = $branch;
                    $branchCounts[$branch]['ncCount']++;
                    $branchCounts[$branch]['totalCount']++;
                    $deptCounts[$deptID]['ncCount']++;
                    $deptCounts[$deptID]['totalCount']++;
                    $freqCounts[$frequency]['ncCount']++;
                    $freqCounts[$frequency]['totalCount']++;
                    $weekCounts[$week]['ncCount']++;
                    $weekCounts[$week]['totalCount']++;
                    #$bigArray[$week][$frequency][$deptID][$branch]['ncCount']++;
                    #$countArray[$week][$frequency][$deptID][$branch]['totalCount']++;
                    break;
                case 'DW':
                    $dwArray[$week][$frequency][$deptID][] = $branch;
                    $branchCounts[$branch]['dwCount']++;
                    $branchCounts[$branch]['totalCount']++;
                    $deptCounts[$deptID]['dwCount']++;
                    $deptCounts[$deptID]['totalCount']++;
                    $freqCounts[$frequency]['dwCount']++;
                    $freqCounts[$frequency]['totalCount']++;
                    $weekCounts[$week]['dwCount']++;
                    $weekCounts[$week]['totalCount']++;
                    #$bigArray[$week][$frequency][$deptID][$branch]['dwCount']++;
                    #$countArray[$week][$frequency][$deptID][$branch]['totalCount']++;
                    break;
                case 'NS':
                    $nsArray[$week][$frequency][$deptID][] = $branch;
                    $branchCounts[$branch]['nsCount']++;
                    $branchCounts[$branch]['totalCount']++;
                    $deptCounts[$deptID]['nsCount']++;
                    $deptCounts[$deptID]['totalCount']++;
                    $freqCounts[$frequency]['nsCount']++;
                    $freqCounts[$frequency]['totalCount']++;
                    $weekCounts[$week]['nsCount']++;
                    $weekCounts[$week]['totalCount']++;
                    #$bigArray[$week][$frequency][$deptID][$branch]['nsCount']++;
                    #$countArray[$week][$frequency][$deptID][$branch]['totalCount']++;
                    break;
                case 'INV':
                    $invArray[$week][$frequency][$deptID][] = $branch;
                    $branchCounts[$branch]['invCount']++;
                    $branchCounts[$branch]['totalCount']++;
                    $deptCounts[$deptID]['invCount']++;
                    $deptCounts[$deptID]['totalCount']++;
                    $freqCounts[$frequency]['invCount']++;
                    $freqCounts[$frequency]['totalCount']++;
                    $weekCounts[$week]['invCount']++;
                    $weekCounts[$week]['totalCount']++;
                    #$bigArray[$week][$frequency][$deptID][$branch]['invCount']++;
                    #$countArray[$week][$frequency][$deptID][$branch]['totalCount']++;
                    break;
                default:
                    #$entry = number_format($entry, 2, '.', ',');
                    $entry = floatval($entry);
                    $branchAmount[$week][$frequency][$branch][] = $entry;
                    $deptAmount[$week][$frequency][$deptID][] = $entry;
                    $freqAmount[$week][$frequency][] = $entry;
                    $weekAmount[$week][] = $entry;
                    $totalAmount[] = $entry;
                    $branchCounts[$branch]['countedCount']++;
                    $branchCounts[$branch]['totalCount']++;
                    $deptCounts[$deptID]['countedCount']++;
                    $deptCounts[$deptID]['totalCount']++;
                    $freqCounts[$frequency]['countedCount']++;
                    $freqCounts[$frequency]['totalCount']++;
                    $weekCounts[$week]['countedCount']++;
                    $weekCounts[$week]['totalCount']++;
                    #$bigArray[$week][$frequency][$deptID][$branch]['amountCount']++;
                    #$countArray[$week][$frequency][$deptID][$branch]['totalCount']++;
                    break;
            }

            $mainArray[$week][$frequency][$deptID][$branch][] = $entry;
        }

    }
}

var_dump($deptAmount);

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

            $deptSql = "SELECT deptName from cyclecounts.deptinfo WHERE deptID = " . $dept;
            $deptQry = $deptLnk->query($deptSql);
            $deptName = $deptQry[0]['deptName'];
            $deptAmt = isset($deptAmount[$wk][$freq][$dept]) ? $deptAmount[$wk][$freq][$dept] : 0;
            echo "<h3 style='text-align:left;'>Dept Name: " . $deptName . " (Adj Amount: " . array_sum($deptAmount[$wk][$freq][$dept]) . ")</h3>";

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

