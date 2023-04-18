<?php

require('../class/Process.php');

$lnk = new Process();

$ytdSql = "SELECT * FROM cyclecounts.ytdper";
$ytdQry = $lnk->query($ytdSql);

$zeroArray = ['NC' => 0, 'DW' => 0, 'NS' => 0, 'INV' => 0, 'Count' => 0, 'Counted' => 0, 'ADJ' => 0];

foreach ($ytdQry as $ytd) {
    $branch = (int)$ytd['branch'];
    $percent = $ytd['ytdPercent'];

    $percents[$branch] = $percent;
    $branches[$branch] = $zeroArray;
}

#var_dump($branches);

$wklySql = "SELECT * FROM cyclecounts.enteredcounts";
$wklyQry = $lnk->query($wklySql);
#var_dump($wklyQry);

foreach ($wklyQry as $key => $data) {
        $wkNum = $data['wkNum'];
    foreach ($data as $xKey => $x) {
        if (preg_match('/_/', $xKey)) {
            $branchNum = (int)substr($xKey, 1);

            if(!isset($weekly[$wkNum][$branchNum])) {
                $weekly[$wkNum][$branchNum] = $zeroArray;
            }
            
            switch ($x) {
                case 'NC':
                    $branches[$branchNum]['NC']++;
                    $branches[$branchNum]['Count']++;
                    $weekly[$wkNum][$branchNum]['NC']++;
                    $weekly[$wkNum][$branchNum]['Count']++;
                    break;
                case 'NS':
                    $branches[$branchNum]['NS']++;
                    $weekly[$wkNum][$branchNum]['NS']++;
                    break;
                case 'DW':
                    $branches[$branchNum]['DW']++;
                    $branches[$branchNum]['Count']++;
                    $branches[$branchNum]['Counted']++;
                    $weekly[$wkNum][$branchNum]['DW']++;
                    $weekly[$wkNum][$branchNum]['Count']++;
                    $weekly[$wkNum][$branchNum]['Counted']++;
                    break;
                case 'INV':
                    $branches[$branchNum]['INV']++;
                    $weekly[$wkNum][$branchNum]['INV']++;
                    break;
                default:
                    $branches[$branchNum]['ADJ'] += $x;
                    $branches[$branchNum]['Count']++;
                    $branches[$branchNum]['Counted']++;
                    $weekly[$wkNum][$branchNum]['ADJ'] += $x;
                    $weekly[$wkNum][$branchNum]['Count']++;
                    $weekly[$wkNum][$branchNum]['Counted']++;
                    break;
            }
        }
    }

}

function iniWeekBranches($weekly) {
    foreach ($weekly as $weekNum => $branchInfo) {
        foreach ($branchInfo as $branch => $data) {
            $cols[] = 'wkNum';
            $values[] = $weekNum;
            $cols[] = 'branch';
            $values[] = $branch;
            $cols[] = 'wkPercent';
            $values[] = $data['Counted'] / $data['Count'];
            foreach ($data as $col => $value) {
                # echo $weekNum . " - " . $branch . ': ' . $col . ' = ' . $value . "<br>";
                $cols[] = "wk" . $col;
                $values[] = $value;
            }
            $columns = implode(',', $cols);
            $inserted = implode(',', $values);
            echo $columns . "<br>";
            echo $inserted . "<br>";
            $insert[] = 'INSERT INTO cyclecounts.branchwk (' . $columns . ') VALUES (' . $inserted . ')';
            $cols = [];
            $values = [];
        }
    }
    $ins = new Process();
    for ($i = 0; $i < count($insert); $i++) {
        $ins->query($insert[$i]);
    }
}

foreach ($branches as $branchNum => $info) {
    echo $branchNum . ": " . $info['Counted'] . "/" . $info['Count'] ."<br>";

    $update[] = 'UPDATE cyclecounts.ytdper SET ytdPercent = '.$info['Counted'] / $info['Count'].' WHERE branch = ' .$branchNum;
}

$upd = new Process();

for ($j = 0; $j < count($update); $j++) {
    $upd->query($update[$j]);
}

