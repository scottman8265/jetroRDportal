<?php

session_start();

$wkNum = $_SESSION['wkNum'];
$bcp = $_SESSION['bcp'];

require('../class/Process.php');

$lnk = new Process();

$ytdSql = "SELECT * FROM cyclecounts.ytdper";
$ytdQry = $lnk->query($ytdSql);

$zeroArray = ['NC' => 0, 'DW' => 0, 'NS' => 0, 'INV' => 0, 'Count' => 0, 'Counted' => 0, 'ADJ' => 0];

foreach ($ytdQry as $y) {
    $branch = (int)$y['branch'];
    $ytd = $y['ytdPercent'];

    foreach ($y as $item => $value) {
        $ytds[$branch][$item] = $value;
    }
    $branches[$branch] = $zeroArray;
}

#var_dump($branches);

$wklySql = "SELECT * FROM cyclecounts.enteredcounts WHERE wkNum = " . $wkNum;
$wklyQry = $lnk->query($wklySql);
#var_dump($wklyQry);

foreach ($wklyQry as $key => $data) {
    #$wkNum = $data['wkNum'];
    foreach ($data as $xKey => $x) {
        if (preg_match('/_/', $xKey)) {
            $branchNum = (int)substr($xKey, 1);
            $ytds[$branchNum]['weeks'] = $wkNum;
            switch ($x) {
                case 'NC':
                    $branches[$branchNum]['NC']++;
                    $branches[$branchNum]['Count']++;
                    $ytds[$branchNum]['ytdNC']++;
                    $ytds[$branchNum]['ytdCount']++;
                    break;
                case 'NS':
                    $branches[$branchNum]['NS']++;
                    $ytds[$branchNum]['ytdNS']++;
                    break;
                case 'DW':
                    $branches[$branchNum]['DW']++;
                    $branches[$branchNum]['Count']++;
                    $branches[$branchNum]['Counted']++;
                    $ytds[$branchNum]['ytdDW']++;
                    $ytds[$branchNum]['ytdCount']++;
                    $ytds[$branchNum]['ytdCounted']++;
                    break;
                case 'INV':
                    $branches[$branchNum]['INV']++;
                    $branches[$branchNum]['Count']++;
                    $branches[$branchNum]['Counted']++;
                    $ytds[$branchNum]['ytdINV']++;
                    $ytds[$branchNum]['ytdCount']++;
                    $ytds[$branchNum]['ytdCounted']++;
                    break;

                default:
                    $branches[$branchNum]['ADJ'] += $x;
                    $branches[$branchNum]['Count']++;
                    $branches[$branchNum]['Counted']++;
                    $ytds[$branchNum]['ytdADJ']++;
                    $ytds[$branchNum]['ytdCount']++;
                    $ytds[$branchNum]['ytdCounted']++;
                    break;
            }
        }
    }

}

foreach ($branches as $branch => $data) {
    $cols[] = 'wkNum';
    $values[] = $wkNum;
    $cols[] = 'branch';
    $values[] = $branch;
    $cols[] = 'wkPercent';
    $wkPercent = $data['Counted'] / $data['Count'];
    $values[] = $wkPercent;
    #$cols[] = 'wkBCP';
    #$values[] = $bcp[$branch];
    $ytds[$branch]['ytdPercent'] = ($ytds[$branch]['ytdPercent'] + $wkPercent) / 2;
    foreach ($data as $col => $value) {
        $cols[] = "wk" . $col;
        $values[] = $value;
    }
    $columns = implode(', ', $cols);
    $inserted = implode(', ', $values);
    #echo $columns . "<br>";
    #echo $inserted . "<br>";
    $inserts[] = 'INSERT INTO cyclecounts.branchwk (' . $columns . ') VALUES (' . $inserted . ')';
    $cols = [];
    $values = [];
}

foreach ($ytds as $branch => $info) {
    foreach ($info as $key => $value) {
        $updates[] = "UPDATE cyclecounts.ytdper SET " . $key . " = " . $value . " WHERE branch = " . $branch;
    }
}


var_dump($bcp);

foreach ($inserts as $insert) {
    $lnk->query($insert);
}

/*foreach ($updates as $update) {
    $lnk->query($update);
}*/




