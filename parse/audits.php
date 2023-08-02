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
            $version = 6;
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
            $cell = 'V579';
            break;
    }

    $auditDate = $sheet->getCell($cell)->getValue();

    return $auditDate;
}

function getQuesArray() {
    $lnk = new Process();
    $array = [];

    $sql = "SELECT auditCode, auditName FROM jrd_stuff.auditlookup";
    $qry = $lnk->query($sql);

    var_dump($qry);

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
            $rowMax = 597;
            $findingStart = 630;
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

    #find repeats
    $count = 0;
    $testCode = 'FFFFFFFF';

    foreach ($findings as $code => $value) {

        $commentCell = "H" . ($findingStart + ($count * 8) + 2);

        $hashCode = $sheet->getCell($commentCell)->getStyle()->getFill()->getStartColor()->getARGB();

        if ($hashCode != $testCode) {
            $findings[$code]['rep'] = 1;
        } else {
            $findings[$code]['rep'] = 0;
        }

        $count++;
    }

    return $findings;
}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @param $version     int
 * @return array
 */
function getScores($sheet) {

    $searchStartRow = 550;
    $baseStartRow = 0;

    for ($h = $searchStartRow; $h < 1000; $h++) {
        $cell = "H" . $h;
        $cellVal = $sheet->getCell($cell)->getValue();
        if ($cellVal == "TOTAL SCORE") {
            $baseStartRow = $h + 1;
            break;
        }
    }

    echo $baseStartRow;

    $array = array();

            $r_totScoreLoc = "L". $baseStartRow;
            $b_totScoreLoc = "H" . $baseStartRow;
            $r_freshScoreLoc = "L" . $baseStartRow +4; #4
            $b_freshScoreLoc = "H" . $baseStartRow +4; #4
            $deptScoreStart = $baseStartRow + 8;#8
            $deptScoreEnd = $baseStartRow + 14;#14
            $foodSafety = $baseStartRow + 15;#15
            $totArray = ['Z' . $baseStartRow + 15, 'AH' . $baseStartRow + 15, 'AP' . $baseStartRow + 15, 'AX' . $baseStartRow + 15];#15
            $repCol = "N";
            $baseCol = "L";
            $scores = true;

    if ($scores) {
        $r_fsCell = $repCol . $foodSafety;
        $b_fsCell = $baseCol . $foodSafety;
        try {
            $r_array[] = $sheet->getCell($r_totScoreLoc)->getOldCalculatedValue();
            $b_array[] = $sheet->getCell($b_totScoreLoc)->getOldCalculatedValue();
            $r_array[] = $sheet->getCell($r_freshScoreLoc)->getOldCalculatedValue();
            $b_array[] = $sheet->getCell($b_freshScoreLoc)->getOldCalculatedValue();

            for ($i = $deptScoreStart; $i < $deptScoreEnd; $i++) {
                $r_cell = $repCol . $i;
                $b_cell = $baseCol . $i;
                $b_score = $sheet->getCell($b_cell)->getOldCalculatedValue();
                $r_score = $sheet->getCell($r_cell)->getOldCalculatedValue();
                $baseScore = $b_score === 'N/A' ? -1 : $b_score;
                $repScore = $r_score === 'N/A' ? -1 : $r_score;
                $r_array[] = $repScore;
                $b_array[] = $baseScore;
            }

            $r_array[] = $sheet->getCell($r_fsCell)->getOldCalculatedValue();
            $b_array[] = $sheet->getCell($b_fsCell)->getOldCalculatedValue();

            foreach ($totArray as $tot) {
                $r_array[] = $sheet->getCell($tot)->getOldCalculatedValue();
                $b_array[] = $sheet->getCell($tot)->getOldCalculatedValue();
            }
        }
        catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            echo $e->getMessage();
        }
    }

    #sets repeat to true/false
    $r_array[] = 1;
    $b_array[] = 0;

    $array = ['rep' => $r_array, 'base' => $b_array];

    return $array;
}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @param $version     int
 * @return array
 */
function getPeople($sheet, $version) {

    $array = array();
    $range = array();
    $people = array();

    switch ($version) {
        case 1:
            $range = ["V537:V540", "AG534:AG540", "AP534:AP540", "AY534:AY535"];
            break;
        case 2:
            $range = ["V543:V546", "AG540:AG546", "AP540:AP546", "AY540:AY541"];
            break;
        case 3:
            $range = ["V552:V555", "AG549:AG555", "AP549:AP555", "AY549:AY550"];
            break;
        case 4:
            $range = ["V553:V556", "AG550:AG556", "AP550:AP556", "AY550:AY551"];
            break;
        default:
            $range = ["V575:V578", "AG572:AG578", "AP571:AP578", "AY572:AY573"];
            break;
    }

    if ($range !== null) {
        foreach ($range as $rng) {
            try {
                $people[] = $sheet->rangeToArray($rng, null, true, true, true);
            }
            catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            }
        }
    }

    foreach ($people as $column => $data) {
        foreach ($data as $key => $cells) {
            foreach ($cells as $key2 => $value) {
                $array[] = "'" . $value . "'";
            }
        }
    }

    return $array;
}

$version = 'version not got';
$auditDates = 'audit dates not got';
$findings = 'findings not got';
$people = 'people not got';
$scores = 'scores not got';

#$version = getVersion($sheet);

#$auditDates = getAuditDates($version, $sheet);

#$findings = getFindings($sheet, $version);

#$people = getPeople($sheet, $version);
#$people = null;

$scores = getScores($sheet);

$testReturn = ['version' => $version,
               'auditDates' => $auditDates,
               'findings' => $findings,
               'people' => $people,
               'scores' => $scores];

