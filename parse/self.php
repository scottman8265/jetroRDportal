<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/29/2019
 * Time: 11:07 AM
 */

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @return int
 */
function getVersion($sheet) {

    $range = $sheet->rangeToArray("H530:H550");

    switch ("TOTAL SCORE") {
        case $range[4][0]:
            $version = 1;
            break;
        case $range[10][0]:
            $version = 2;
            break;
        case $range[19][0]:
            $version = 3;
            break;
        case $range[20][0]:
            $version = 4;
            break;
        default:
            $version = "unknown";
            break;
    }

    return $version;
}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @return int
 */
function getAuditDates($version, $sheet) {

    switch ($version) {
        case 2:
            $cell = 'V541';
            break;
        case 3:
            $cell = 'V550';
            break;
        case 4:
            $cell = 'V551';
            break;
        case 1:
            $cell = 'V335';
            break;
        default:
            $cell = null;
            break;
    }

    $auditDate = $sheet->getCell($cell)->getValue();

    return $auditDate;
}

function getQuesArray() {
    $lnk = new Process();

    $sql = "SELECT auditCode, auditName FROM auditanalysis.auditlookup";
    $qry = $lnk->query($sql);

    foreach ($qry as $info) {
        $array[$info['auditName']] = $info['auditCode'];
    }

    return $array;
}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @return array
 */
function getFindings($sheet, $version) {

    $audits = getQuesArray();
    $findings = array();

    switch ($version) {
        case 2:
            $rowMax = 539;
            $findingStart = 567;
            break;
        case 1:
            $rowMax = 533;
            $findingStart = 558;
            break;
        case 3:
            $rowMax = 538;
            $findingStart = 576;
            break;
        case 4:
            $rowMax = 540;
            $findingStart = 577;
            break;
        default:
            $rowMax = null;
            $findingStart = null;
            break;
    }

    #getFindings
    if ($rowMax != null) {
        for ($i = 2; $i < $rowMax; $i++) {

            $qNum = $sheet->getCellByColumnAndRow(6, $i)->getValue();
            if ($qNum != null) {
                $qAudit = $sheet->getCellByColumnAndRow(9, $i)->getValue();;
                $qComm = $sheet->getCellByColumnAndRow(11, $i)->getOldCalculatedValue();;
                $response = $sheet->getCellByColumnAndRow(3, $i)->getOldCalculatedValue();;
                $code = $audits[$qAudit] . (string)$qNum . "." . $version;

                if ($response) {
                    $findings[$code]['comm'] = $qComm;
                } elseif ($audits[$qAudit] == "FL") {
                    $varArray = [1, 2, 3, 4, 5, 8];
                    if (in_array($qNum, $varArray) == true && strlen($qComm) > 1) {
                        $findings[$code]['comm'] = trim($qComm);
                    }
                }
            }
        }
    }

    return $findings;
}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @param $version     int
 * @return array
 */
function getScores($sheet, $version) {

    $b_array = array();
    switch ($version) {
        case 1:
            #$r_totScoreLoc = "L535";
            $b_totScoreLoc = "H535";
            #$r_freshScoreLoc = "L539";
            $b_freshScoreLoc = "L539";
            $deptScoreStart = 543;
            $deptScoreEnd = 559;
            $foodSafety = 560;
            $totArray = ['AF560', 'AL560', 'AR560', 'AX560'];
            #$repCol = "P";
            $baseCol = "M";
            $scores = true;
            break;
        case 2:
            #$r_totScoreLoc = "L541";
            $b_totScoreLoc = "H541";
            #$r_freshScoreLoc = "L545";
            $b_freshScoreLoc = "H545";
            $deptScoreStart = 549;
            $deptScoreEnd = 565;
            $foodSafety = 566;
            $totArray = ['AF566', 'AL566', 'AR566', 'AX566'];
            #$repCol = "P";
            $baseCol = "M";
            $scores = true;
            break;
        case 3:
            #$r_totScoreLoc = "L550";
            $b_totScoreLoc = "H550";
            #$r_freshScoreLoc = "L554";
            $b_freshScoreLoc = "H554";
            $deptScoreStart = 558;
            $deptScoreEnd = 574;
            $foodSafety = 575;
            $totArray = ['AF575', 'AL575', 'AR575', 'AX575'];
            #$repCol = "P";
            $baseCol = "M";
            $scores = true;
            break;
        case 4:
            #$r_totScoreLoc = "L551";
            $b_totScoreLoc = "H551";
            #$r_freshScoreLoc = "L555";
            $b_freshScoreLoc = "H555";
            $deptScoreStart = 559;
            $deptScoreEnd = 575;
            $foodSafety = 576;
            $totArray = ['AF576', 'AL576', 'AR576', 'AX576'];
            #$repCol = "P";
            $baseCol = "M";
            $scores = true;
            break;
        default:
            $scores = false;
            break;
    }

    if ($scores) {
        #$r_fsCell = $repCol . $foodSafety;
        $b_fsCell = $baseCol . $foodSafety;
        try {
            #$r_array[] = $sheet->getCell($r_totScoreLoc)->getOldCalculatedValue();
            $b_array[] = $sheet->getCell($b_totScoreLoc)->getOldCalculatedValue();
            #$r_array[] = $sheet->getCell($r_freshScoreLoc)->getOldCalculatedValue();
            $b_array[] = $sheet->getCell($b_freshScoreLoc)->getOldCalculatedValue();

            for ($i = $deptScoreStart; $i < $deptScoreEnd; $i++) {
                #$r_cell = $repCol . $i;
                $b_cell = $baseCol . $i;
                #$r_array[] = $sheet->getCell($r_cell)->getOldCalculatedValue();
                $score = $sheet->getCell($b_cell)->getOldCalculatedValue();
                $score = $score === '#DIV/0!' ? -1 : $score;
                $b_array[] = $score;
            }

            #$r_array[] = $sheet->getCell($r_fsCell)->getOldCalculatedValue();
            $b_array[] = $sheet->getCell($b_fsCell)->getOldCalculatedValue();

            foreach ($totArray as $tot) {
                #$r_array[] = $sheet->getCell($tot)->getOldCalculatedValue();
                $b_array[] = $sheet->getCell($tot)->getOldCalculatedValue();
            }
        }
        catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        }
    }

    return $b_array;
}

$version = 'version not got';
$auditDates = 'audit dates not got';
$findings = 'findings not got';
$scores = 'scores not got';

$version = getVersion($sheet);

$findings = getFindings($sheet, $version);

$scores = getScores($sheet, $version);

$testReturn = ['version' => $version,
               'findings' => $findings,
               'scores' => $scores];

