<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/7/2019
 * Time: 6:30 AM
 */

$sendArray = [];

require '../class/Arrays.php';

#branchArray
function getBranchArray($sendArray, $params = []) {
    $schema = 'branchinfo';
    $db = 'branches';
    $select = ['*'];
    $params[] = ['field' => 'active', 'value' => true, 'operand' => 'and'];
    $sendArray['branchArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#auditorArray
function getAuditorArray($sendArray, $params = []) {
    $schema = 'branchinfo';
    $db = 'auditors';
    $select = ['auditorID', 'auditorFName', 'auditorLName'];
    $params[] = ['field' => 'auditorFT', 'value' => true, 'operand' => 'and'];
    $sendArray['auditorArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#regionalArray
function getRegionalArray($sendArray, $params = []) {
    $schema = 'branchinfo';
    $db = 'regionals';
    $select = ['regionID', 'fName', 'lName', 'position'];
    $params[] = ['field' => 'active', 'value' => true, 'operand' => 'and'];
    $sendArray['regionalArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#mw entered audit array
function getAuditArray($sendArray, $params = []) {
    $schema = 'auditanalysis';
    $db = 'enteredAudits';
    $select = ['*'];
    /*$params[] = ['field' => 'year', 'value' => 2018, 'operand' => 'and'];
    $params[] = ['field' => 'period', 'value' => 'Q4', 'operand' => 'and'];
    $params[] = ['field' => 'version', 'value' => 2, 'operand' => 'and'];*/
    $sendArray['auditArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#mw scores array (must get audit array first)
function getScoreArray($sendArray, $params = []) {
    $schema = 'auditanalysis';
    $db = 'auditscores';
    $select = ["*"];
    $sendArray['scoreArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getLocationArray($sendArray, $params = []) {
    $schema = 'branchinfo';
    $db = 'locations';
    $select = ["*"];
    $sendArray['locationArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getFindingArray($sendArray, $params = []) {
    $schema = 'auditanalysis';
    $db = 'auditfindings';
    $select = ["*"];
    $params[] = ['field'=>'auditID', 'value'=>15, 'operand'=>'and'];
    $sendArray['findingArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getQuestionArray($sendArray, $params = []) {
    $schema = 'auditanalysis';
    $db = 'auditquestions';
    $select = ["*"];
    $sendArray['questionArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getAuditLU($sendArray, $params = []) {
    $schema = 'auditanalysis';
    $db = 'auditlookup';
    $select = ["*"];
    $sendArray['auditLU'] = [$select, $schema, $db, $params];

    return $sendArray;
}

$sendArray = getBranchArray($sendArray);
$sendArray = getAuditorArray($sendArray);
$sendArray = getRegionalArray($sendArray);
$sendArray = !isset($auditParams) ? getAuditArray($sendArray) : getAuditArray($sendArray, $auditParams);
$sendArray = getLocationArray($sendArray);
$sendArray = getFindingArray($sendArray);
$sendArray = getQuestionArray($sendArray);
$sendArray = getAuditLU($sendArray);
$sendArray = getScoreArray($sendArray);

$arrays = new Arrays($sendArray);