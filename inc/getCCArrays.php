<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/7/2019
 * Time: 6:30 AM
 */


$arrayStartTime = microtime(true);

$sendArray = [];

#branchArray[branchNum][branchName, regional, auditor, location, twoDigit]
function getBranchArray($sendArray) {
    $schema = 'branchinfo';
    $db = 'branches';
    $select = ['*'];
    $params[] = ['field' => 'active', 'value' => true, 'operand' => 'and'];
    $sendArray['branchArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#auditorArray[auditorID][fName, lName, fullName]
function getAuditorArray($sendArray) {
    $params = [];
    $schema = 'branchinfo';
    $db = 'auditors';
    $select = ['auditorID', 'auditorFName', 'auditorLName'];
    $params[] = ['field' => 'auditorFT', 'value' => true, 'operand' => 'and'];
    $sendArray['auditorArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#regionalArray[regionID][fName, lName, fullName]
function getRegionalArray($sendArray) {
    $params = [];
    $schema = 'branchinfo';
    $db = 'regionals';
    $select = ['regionID', 'fName', 'lName'];
    $params[] = ['field' => 'active', 'value' => true, 'operand' => 'and'];
    $sendArray['regionalArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#mw entered audit array[year][period][id][branchNum, version]
function getAuditArray($sendArray) {
    $params = [];
    $schema = 'auditanalysis';
    $db = 'enteredAudits';
    $select = ['*'];
    $params[] = ['field' => 'year', 'value' => 2019, 'operand' => 'and'];
    #$params[] = ['field' => 'period', 'value' => 'Q4', 'operand' => 'and'];
    #$params[] = ['field' => 'version', 'value' => 2, 'operand' => 'and'];
    $sendArray['auditArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#mw scores array (must get audit array first)
#array[auditID][base/repeat][scores]
function getScoreArray($sendArray) {
    $params = [];
    $schema = 'auditanalysis';
    $db = 'auditscores';
    $select = ["*"];
    $sendArray['scoreArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#locationArray[locID][locCode, locName]
function getLocationArray($sendArray) {
    $params = [];
    $schema = 'branchinfo';
    $db = 'locations';
    $select = ["*"];
    $sendArray['locationArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#must get audit array first
#findingArray[auditID][question, comment, id, rep]
function getFindingArray($sendArray) {
    $params = [];
    $schema = 'auditanalysis';
    $db = 'auditfindings';
    $select = ["*"];
    #$params[] = ['field'=>'auditID', 'value'=>15, 'operand'=>'and'];
    $sendArray['findingArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#questionArray[auditCode][version][qNum, title, points]
function getQuestionArray($sendArray) {
    $params = [];
    $schema = 'auditanalysis';
    $db = 'auditquestions';
    $select = ["*"];
    $sendArray['questionArray'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#auditLU[auditCode][auditName, corpID]
function getAuditLU($sendArray) {
    $params = [];
    $schema = 'auditanalysis';
    $db = 'auditlookup';
    $select = ["*"];
    $sendArray['auditLU'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#weekDates[wkNum][wkStart, wkEnd]
function getWeekDates($sendArray) {
    $params = [];
    $schema = 'branchinfo';
    $db = 'weekdates';
    $select = ["*"];
    $params[] = ['field'=>'year', 'value'=>2019, 'operand'=>'and'];
    $sendArray['weekDates'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#periiodDates[monthNum][periodName, wkStart, wkEnd, perStart, PerEnd, julStart, julEnd]
function getPeriodDates($sendArray) {
    $params = [];
    $schema = 'branchinfo';
    $db = 'periodDates';
    $select = ["*"];
    $params[] = ['field'=>'year', 'value'=>'2019', 'operand'=>'and'];
    $sendArray['periodDates'] = [$select, $schema, $db, $params];

    return $sendArray;
}

#deptInfo[groupNum][deptNum, deptName, freq, countWeeks]
function getDeptInfo($sendArray) {
    $params = [];
    $schema = 'cyclecounts';
    $db = 'deptinfo';
    $select = ["*"];
    #$params[] = ['field'=>'year', 'value'=>'2019', 'operand'=>'and'];
    $sendArray['periodDates'] = [$select, $schema, $db, $params];

    return $sendArray;
}

 $sendArray = getBranchArray($sendArray);
# $sendArray = getAuditArray($sendArray);
# $sendArray = getAuditorArray($sendArray);
# $sendArray = getRegionalArray($sendArray);
# $sendArray = getLocationArray($sendArray);
# $sendArray = getFindingArray($sendArray);
# $sendArray = getQuestionArray($sendArray);
# $sendArray = getAuditLU($sendArray);
# $sendArray = getScoreArray($sendArray);
# $sendArray = getPeriodDates($sendArray);
# $sendArray = getWeekDates($sendArray);
# $sendArray = getDeptInfo($sendArray);

$arrays = new Arrays($sendArray);

$arrayEndTime = microtime(true);

#echo "finished arrays in " . ($arrayEndTime - $arrayStartTime) . " seconds</br></br>";