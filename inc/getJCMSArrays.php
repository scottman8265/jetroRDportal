<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/7/2019
 * Time: 6:30 AM
 */



#branchArray
function getBranchArrays() {
    $schema = 'branchInfo';
    $db = 'branches';
    $select = ['*'];
    $params[] = ['field' => 'active', 'value' => true, 'operand' => 'and'];
    $sendArray['branchArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#auditorArray
function getAuditorArray() {
    $params = [];
    $schema = 'branchInfo';
    $db = 'auditors';
    $select = ['auditorID', 'auditorFName', 'auditorLName'];
    $params[] = ['field' => 'auditorFT', 'value' => true, 'operand' => 'and'];
    $sendArray['auditorArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#regionalArray
function getRegionalArray() {
    $params = [];
    $schema = 'branchInfo';
    $db = 'regionals';
    $select = ['regionID', 'fName', 'lName', 'position'];
    $params[] = ['field' => 'active', 'value' => true, 'operand' => 'and'];
    $sendArray['regionalArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#mw entered audit array
function getAuditArray() {
    $params = [];
    $schema = 'auditAnalysis';
    $db = 'enteredaudits';
    $select = ['*'];
    $params[] = ['field' => 'year', 'value' => 2018, 'operand' => 'and'];
    $params[] = ['field' => 'period', 'value' => 'Q4', 'operand' => 'and'];
    $params[] = ['field' => 'version', 'value' => 2, 'operand' => 'and'];
    $sendArray['auditArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#mw scores array (must get audit array first)
function getScoreArray() {
    $params = [];
    $schema = 'auditAnalysis';
    $db = 'auditscores';
    $select = ["*"];
    $sendArray['scoreArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getLocationArray() {
    $params = [];
    $schema = 'branchInfo';
    $db = 'locations';
    $select = ["*"];
    $sendArray['locationArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getFindingArray() {
    $params = [];
    $schema = 'auditAnalysis';
    $db = 'auditfindings';
    $select = ["*"];
    $params[] = ['field'=>'auditID', 'value'=>15, 'operand'=>'and'];
    $sendArray['findingArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getQuestionArray() {
    $params = [];
    $schema = 'auditAnalysis';
    $db = 'auditquestions';
    $select = ["*"];
    $sendArray['questionArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

function getAuditLU() {
    $params = [];
    $schema = 'auditAnalysis';
    $db = 'auditlookup';
    $select = ["*"];
    $sendArray['auditLU'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#var_dump($sendArray);

/*$sendArray = getBranchArrays();
$sendArray = getAuditorArray();
$sendArray = getRegionalArray();
$sendArray = getAuditArray();
$sendArray = getLocationArray();
$sendArray = getFindingArray();
$sendArray = getQuestionArray();
$sendArray = getAuditLU();
$sendArray = getScoreArray();*/

