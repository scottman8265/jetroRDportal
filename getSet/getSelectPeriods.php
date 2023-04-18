<?php

session_start();

#$_SESSION = [];

require '../inc/getAuditsArrays.php';


$audits = $arrays->getAuditArray();

foreach ($audits as $year => $periodArray) {
    foreach ($periodArray as $period => $idArray) {
        $periods[$year][] = $period;
    }
}
echo json_encode(['return'=>$periods]);


