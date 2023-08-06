<?php

/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/25/2019
 * Time: 6:18 AM
 */

session_start();

require_once '../class/Process.php';

function getAuditID($array)
{

    $lnk = new Process();

    foreach ($array as $field => $value) {
        $fields[] = $field;
        $values[] = $value;
    }

    $fList = implode(', ', $fields);
    $vList = implode(', ', $values);

    $sql = "INSERT INTO auditanalysis.selfaudits (" . $fList . ") VALUES (" . $vList . ")";
    $qry = $lnk->query($sql);

    if (!$qry) {
        return false;
    } else {
        return $lnk->getLastID();
    }

    #return $sql;

}

function writeScores($scores, $auditID = 0)
{

    $errors = null;

    $lnk = new Process();

    $scores[] = $auditID;
    $fields = [
        'totScore', 'freshScore', 'adScore', 'crScore', 'daScore', 'flScore', 'feScore', 'goScore',
        'icScore', 'meScore', 'pcScore', 'prScore', 'rvScore', 'rpScore', 'saScore', 'seScore', 'swScore',
        'lqScore', 'fsScore', 'deptFreshScore', 'deptFSafeScore', 'deptOpsScore', 'deptSafeScore', 'auditID'
    ];


    #$repScoreStr = implode(', ', $scores['rep']);
    $scoreStr = implode(', ', $scores);
    $fieldsStr = implode(', ', $fields);

    /*$repSQL = "INSERT INTO auditanalysis.auditscores (" . $fieldsStr . ") VALUES (" . $repScoreStr . ")";
    $repQRY = $lnk->query($repSQL);*/

    $baseSQL = "INSERT INTO auditanalysis.selfscores (" . $fieldsStr . ") VALUES (" . $scoreStr . ")";
    $baseQRY = $lnk->query($baseSQL);

    /*$message[] = !$repQRY ? 'Error writing Repeat Scores for AuditID: ' . $auditID . "\n"
        : 'Completed writing Repeat Scores for AuditID: ' . $auditID . "\n";*/

    $message[] = !$baseQRY ? 'Error writing Base Scores for AuditID: ' . $auditID . "\n"
        : 'Completed writing Repeat Scores for AuditID: ' . $auditID . "\n";


    #return $message;
}

function writeFindings($findings, $auditID = 0)
{
    $lnk = new Process();

    foreach ($findings as $qCode => $info) {
        $sql = "INSERT INTO auditanalysis.selffindings (auditID, qCode, qComm) VALUES (?, ?, ?)";
        $params = [$auditID, $qCode, $info['comm']];
        $qry = $lnk->query($sql, $params);

        $message[] = !$qry ? $message[] = 'Error Processing Findings for AuditID# ' . $auditID . ' Ques# ' . $qCode . "\n"
            : $message[] = 'Processed Findings for AuditID# ' . $auditID . ' Ques# ' . $qCode . "\n";
    }

    #return $message;
}

/*function getAuditNum($file, $version, $auditDate, $status) {

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

}*/

/**
 * @param $findings array
 * @param $auditID  int
 * @return string
 */
function setFindings($findings, $auditID)
{

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
function setScores($scores)
{

    $lnk = new Process();

    $sql = "INSERT INTO auditanalysis.selfscores (totScore, freshScore, adScore, crScore, daScore, flScore, feScore, goScore, 
                                    icScore, meScore, pcScore, prScore, rvScore, rpScore, saScore, seScore, swScore, 
                                    lqScore, fsScore, deptFreshScore, deptFSafeScore, deptOpsScore, deptSafeScore, auditID) 
                                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    #$insert_r = $lnk->query($sql, $scores['rep']);
    $insert_b = $lnk->query($sql, $scores);

    if ($insert_b) {
        $insert = true;
    } else {
        $insert = false;
    }

    unset($lnk);

    return $insert;
}

$lnk = new Process();

$fileCount = count($_SESSION['writeData']);

for ($i = 0; $i < $fileCount; $i++) {

    if ($_SESSION['writeData'][$i]['sentData']) {
        $version = $_SESSION['writeData'][$i]['sentData']['version'];
        $findings = $_SESSION['writeData'][$i]['sentData']['findings'];
        $scores = $_SESSION['writeData'][$i]['sentData']['scores'];
        $fileName = $_SESSION['name'][$i];
        $filePieces = explode(' ', $fileName);
        $branch = $filePieces[0];
        $month = substr($filePieces[3], 0, 2);
        $year = substr($filePieces[3], 2, 2);
        $submitted = $filePieces[2];

        $submitted = strlen($submitted) < 6 ? '0' . $submitted : "'" . $submitted . "'";

        $idArray = ['year' => $year, 'month' => $month, 'branch' => $branch, 'version' => $version, 'submitteddate' => $submitted];

        $auditID = getAuditID($idArray);

        /*if (!$auditID) {
            echo 'failure';
        } else {
            echo $auditID . "\n";
        }*/

        writeScores($scores, $auditID);
        writeFindings($findings, $auditID);
    }
}
