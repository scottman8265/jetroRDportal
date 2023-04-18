<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/1/2019
 * Time: 10:26 AM
 */

set_time_limit(300);

require('../class/Process.php');
require('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * @param $year
 * @param $quarter
 * @param $lnk Process
 *
 * @param $twoDigitBranches
 *
 * @return array
 */
function getAuditArray($year, $quarter, $lnk, $twoDigitBranches) {

    #echo "year: " . $year . "Quar#: " . $quarter."</br>";

    $array = [];


    $sql = "SELECT id, branch, version FROM jrd_stuff.enteredaudits where period = ? AND year = ?";
    $params = ['Q' . $quarter, $year];
    $qry = $lnk->query($sql, $params);

    foreach ($qry as $x) {
		echo "[original branch number: " . $x['branch'] . "] ";
		$branchLength = strlen($x['branch']);
	    if ($branchLength === 2) {
	    	if ($twoDigitBranches[$x['branch']] == 114) {
	    		echo "[two digit branch 114] ";
	    		$branch = 414;
		    } else {
	    		echo "[other two digit branch] ";
			    $branch = $twoDigitBranches[$x['branch']];
		    }
	    } else {
	    	echo "[other branch number] [string length: " . strlen($x['branch']) ."] ";
	    	$branch = $x['branch'];
	    }
			echo "[ending branch number: " . $branch . " ] </br>";
        $array[$x['id']] = ['branch' => $branch, 'version' => $x['version']];
    }

    #var_dump($twoDigitBranches);

    return $array;
}

/**
 * @param $lnk Process
 *
 * @return mixed
 */
function getCorpAuditArray($lnk) {

    $sql = "SELECT lcAuditCode, corpID FROM jrd_stuff.auditlookup WHERE corpID IS NOT NULL";
    $qry = $lnk->query($sql);

    foreach ($qry as $x) {
        $array[$x['corpID']] = $x['lcAuditCode'] . "Score";
    }

    return $array;

}

/**
 * @param $lnk        Process
 * @param $auditArray array
 * @param $fieldStr
 *
 * @return mixed
 */
function getScoresArray($lnk, $auditArray, $fieldStr, $twoDigitBranches) {

    foreach ($auditArray as $id => $data) {

        $fieldArray = explode(', ', $fieldStr);

	    if (strlen($data['branch']) == 2) {
		    if ($twoDigitBranches[$data['branch']] == 114) {
			    $branch = 414;
		    } else {
			    $branch = $twoDigitBranches[$data['branch']];
		    }
	    } else {
		    $branch = $data['branch'];
	    }

        /*if ($data['branch'] == 114) {
            $branch = 414;
        } else {
            $branch = $data['branch'];
        }*/

	    if (strlen($data['branch'] == 2)) {
		    $branch = $data['branch'] + 500;
	    }

        if (!is_null($data['version'])) {

            $str = "SELECT " . $fieldStr . " FROM jrd_stuff.auditscores WHERE auditID = ? and rep = 1";
            #echo $str . "</br>";

            $sql = $str;
            $params = [$id];
            $qry = $lnk->query($sql, $params);
            foreach ($qry as $x) {
                #var_dump($x);
                foreach ($fieldArray as $field) {
                    #$field = trim($field);
                    #echo $field . " - " . $x[$field] . "</br>";
                    $score = number_format($x[$field], 4);
                    #echo $score . "</br>";

                    $array[$branch][$field] = $score;
                }
            }
        } else {
            foreach ($fieldArray as $field) {
                $array[$branch][$field] = 'na';
            }
        }

    }

    return $array;
}

/**
 * @param $sheet Worksheet
 *
 * @return mixed
 */
function getMWcordsArray($sheet) {
    $numRows = $sheet->getHighestRow();
    $numRows++;

    for ($i = 2; $i<$numRows; $i++) {

        $region = $sheet->getCellByColumnAndRow(3, $i)->getValue();
        $branchNum = $sheet->getCellByColumnAndRow(4, $i)->getValue();
        $auditID = $sheet->getCellByColumnAndRow(6, $i)->getValue();


        if ($region != 'West' && is_numeric($branchNum)) {
            $cord = "J" . $i;

            /*echo "</br>";
            var_dump($sheet->getStyle($cord)->getNumberFormat()->getFormatCode());
            echo "</br>";*/
            $array[$branchNum][$i] = ['audit'=>$auditID];
        }
        
    }

    return $array;
}

/**
 * @param $cords  array
 * @param $scores array
 * @param $audits
 * @param $sheet  PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 *
 * @return Worksheet
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */
function writeMWscores($cords, $scores, $audits, $sheet) {
#echo "[fillCorpScores->line->155]</br>";
	#var_dump($scores);
    foreach($cords as $branch => $x) {

        foreach ($x as $row => $auditID) {
	        if (isset($scores[$branch])) {
		        $score = $scores[$branch][$audits[$auditID['audit']]];

		        if ($score <= 0) {
		        	$score = "";
		        }

		        $cord  = "J" . $row;
		        $sheet->getStyle($cord)->getNumberFormat()->setFormatCode('0.00');
		        $sheet->setCellValueByColumnAndRow(10, $row, $score);
	        } else {
	        	$missing[] = $branch;
	        }

        }
    }

    if (isset($missing)) {
    	foreach ($missing as $x) {
    		echo $x . " : ";
	    }
    }

    return $sheet;
}

function get2DigitBranch($lnk) {

	$twoDigitBranches = [];

	$sql = "SELECT branchNum, _2DigNum FROM jrd_stuff.branches WHERE _2DigNum IS NOT NULL";
	$qry = $lnk->query($sql);

	foreach($qry as $k => $v) {
		$twoDigitBranches[$v['_2DigNum']] = $v['branchNum'];
	}

	return $twoDigitBranches;

}

$file = '../input/bonusScoreFile/2022 Corp Audit Exported Results Q4.xlsx';
$quarter = 4;
$year = 2022;
$lnk = new Process();

$twoDigitBranches = get2DigitBranch($lnk);
$auditArray = getAuditArray($year, $quarter, $lnk, $twoDigitBranches);
$corpAuditArray = getCorpAuditArray($lnk);

$fieldString = implode(', ', $corpAuditArray);

$scoreArray = getScoresArray($lnk, $auditArray, $fieldString, $twoDigitBranches);

$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');

$spreadSheet = $reader->load($file);

$spreadSheet->getSheetByName('2022 Q4');
$sheet = $spreadSheet->getActiveSheet();

$mwCordsArray = getMWcordsArray($sheet);

$filledSheet = writeMWscores($mwCordsArray, $scoreArray, $corpAuditArray, $sheet);

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
$writer->save("../output/bonusScoreFile/2022 Corp Audit Exported Results Q4.xlsx");