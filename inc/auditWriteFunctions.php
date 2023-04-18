<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/29/2019
 * Time: 11:07 AM
 */

function getAuditNum($file, $version, $auditDate, $status) {

    $lnk = new Process();

    $auditInfo = explode(" ", $file);

    $year = $auditInfo[0];
    $period = $auditInfo[1];
    $branch = $auditInfo[2];
    $sql = "INSERT INTO enteredaudits (year, period, branch, version, auditDate, auditStatus) VALUES (?, ?, ?, ?, ?, ?)";
    $params = [$year, $period, $branch, $version, $auditDate, $status];

    $lnk->query($sql, $params);

    $num = $lnk->getLastID();


    unset($lnk);

    return $num;

}

/**
 * @param $findings array
 * @param $auditID  int
 * @return string
 */
function setFindings($findings, $auditID) {

    $lnk = new Process();
    $count = 1;

    foreach ($findings as $qCode => $info) {
        $sql = "INSERT INTO auditanalysis.auditfindings (auditID, qCode, qComm, rep) VALUES (?, ?, ?, ?)";
        $params = [$auditID, $qCode, $info['comm'], $info['rep']];
        $insert = $lnk->query($sql, $params);
        $count++;
    }
    unset($lnk);

    return "</br>Finished with findings</br></br>";
}

/**
 * @param $scores array
 * @return bool
 */
function setScores($scores) {

    $lnk = new Process();

    $sql = "INSERT INTO auditscores (totScore, freshScore, adScore, crScore, daScore, flScore, feScore, goScore, 
                                    icScore, meScore, pcScore, prScore, rvScore, rpScore, saScore, seScore, swScore, 
                                    lqScore, fsScore, deptFreshScore, deptFSafeScore, deptOpsScore, deptSafeScore, rep, auditID) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $insert_r = $lnk->query($sql, $scores['rep']);
    $insert_b = $lnk->query($sql, $scores['base']);

    if ($insert_r && $insert_b) {
        $insert = true;
    } else {
        $insert = false;
    }

    unset($lnk);

    return $insert;
}

/**
 * @param $people array
 * @return boolean
 */
function setPeople($people) {

    $sql = 'INSERT INTO auditpeople (auditor, bm, abm1, abm2, ad, cr, da, fl, fe, go, ic, me, pc, pr, rv, rp, sa, se, sw, 
                                    lq, auditID) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)';
    $lnk = new Process();

    $insert = $lnk->query($sql, $people);

    return $insert;

}