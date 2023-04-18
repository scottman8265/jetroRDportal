<?php

require_once '../class/Process.php';

$lnk = new Process();

$audits = [];
$branches = [];

foreach ($periods as $period) {

    $periodSplit = explode(":", $period);

    $year = $periodSplit[0];
    $quar = $periodSplit[1];

    $sql = "SELECT id, branch FROM auditanalysis.enteredaudits WHERE year = ? AND period = ?";
    $params = [$year, $quar];
    $qry = $lnk->query($sql, $params);

    foreach ($qry as $info) {
        $audits[$year][$quar][] = $info['id'];
        $branches[$year][$quar][] = $info['branch'];
    }
}


