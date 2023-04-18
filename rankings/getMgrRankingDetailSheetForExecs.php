<?php

/**
 * make sure to set correct locations for branch info
 */

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * @param $lnk Process
 *
 * @return array
 */
function getRegionals($lnk) {
	$regionalQry = $lnk->query("SELECT concat(fName, ' ', lName) as name, lName, regionID FROM jrd_stuff.opsExecs where active = 1");

	$arr = [];

	foreach ($regionalQry as $data) {
		$arr[$data['regionID']] = ['fullName' => $data['name'], 'lName' => $data['lName']];
	}

	return $arr;
}

/**
 * @param $lnk Process
 *
 * @return array
 */
function getDepartments($lnk) {
	$posQry = $lnk->query("SELECT dept, posName FROM jrd_stuff.posToDepts");
	$arr    = [];
	foreach ($posQry as $data) {
		$arr[$data['posName']] = $data['dept'];
	}

	return $arr;
}

/**
 * @param $lnk Process
 *
 * @return array
 */
function getBranchInfo($lnk) {
	$branchQry = $lnk->query("SELECT branchName, regional, location, branchNum FROM jrd_stuff.branches WHERE active  = 1");
	$arr       = [];
	foreach ($branchQry as $data) {
		$arr[$data['branchNum']] = ['regional' => $data['regional'],
		                            'name'     => $data['branchName'],
		                            'region'   => $data['location']];
	}

	#sort($arr);

	return $arr;
}

function getAuditScores($lnk) {
	#getting audit scores
	$sql1 = "SELECT * FROM jrd_stuff.auditscores WHERE auditID > 2509 && rep = 1";
	$qry1 = $lnk->query($sql1);

#getting branches
	$sql2 = "SELECT branchNum FROM jrd_stuff.branches WHERE location = 'WC' ORDER BY branchNum ASC";
	$qry2 = $lnk->query($sql2);

#getting audit ids
	$sql3 = "SELECT ID, branch FROM jrd_stuff.enteredaudits WHERE year = 2021 ORDER BY branch ASC, period DESC";
	$qry3 = $lnk->query($sql3);

#set score array to audit id
	foreach ($qry1 as $auditScores) {
		$adScore = $auditScores['adScore'] < 0 ? "N/A" : number_format($auditScores['adScore'] * 100, 2);
		$crScore = $auditScores['crScore'] < 0 ? "N/A" : number_format($auditScores['crScore'] * 100, 2);
		$daScore = $auditScores['daScore'] < 0 ? "N/A" : number_format($auditScores['daScore'] * 100, 2);
		$flScore = $auditScores['flScore'] < 0 ? "N/A" : number_format($auditScores['flScore'] * 100, 2);
		$feScore = $auditScores['feScore'] < 0 ? "N/A" : number_format($auditScores['feScore'] * 100, 2);
		$goScore = $auditScores['goScore'] < 0 ? "N/A" : number_format($auditScores['goScore'] * 100, 2);
		$icScore = $auditScores['icScore'] < 0 ? "N/A" : number_format($auditScores['icScore'] * 100, 2);
		$meScore = $auditScores['meScore'] < 0 ? "N/A" : number_format($auditScores['meScore'] * 100, 2);
		$prScore = $auditScores['prScore'] < 0 ? "N/A" : number_format($auditScores['prScore'] * 100, 2);
		$rvScore = $auditScores['rvScore'] < 0 ? "N/A" : number_format($auditScores['rvScore'] * 100, 2);
		$seScore = $auditScores['seScore'] < 0 ? "N/A" : number_format($auditScores['seScore'] * 100, 2);
		$swScore = $auditScores['swScore'] < 0 ? "N/A" : number_format($auditScores['swScore'] * 100, 2);


		$scoreArray[$auditScores['auditID']] = ['tot' => number_format($auditScores['totScore'] * 100, 2),
		                                        176   => $adScore,
		                                        137   => $crScore,
		                                        139   => $daScore,
		                                        143   => $flScore,
		                                        148   => $feScore,
		                                        136   => $goScore,
		                                        151   => $icScore,
		                                        152   => $meScore,
		                                        156   => $prScore,
		                                        159   => $rvScore,
		                                        163   => $seScore,
		                                        165   => $swScore
		];
	}

#set entered audits by branch
	foreach ($qry3 as $enteredAudits) {
		$enteredAuditArray[$enteredAudits['branch']][] = $enteredAudits['ID'];
	}

#set final array from branch array
	foreach ($qry2 as $branches) {

		$branchNum = $branches['branchNum'];

		echo $branchNum;

		if (isset($enteredAuditArray[$branchNum])) {
			$finalArray[$branchNum] = $scoreArray[$enteredAuditArray[$branchNum][0]];
		} else {
			$finalArray[$branchNum] = ['tot' => "N/A",
			                           176   => "N/A",
			                           137   => "N/A",
			                           139   => "N/A",
			                           143   => "N/A",
			                           148   => "N/A",
			                           136   => "N/A",
			                           151   => "N/A",
			                           152   => "N/A",
			                           156   => "N/A",
			                           159   => "N/A",
			                           163   => "N/A",
			                           165   => "N/A"
			];
		}
	}

	return $finalArray;
}

function getRankingArray($rankingQry, $branchInfo, $positions, $regionals) {

	$arr = [];
	foreach ($rankingQry as $data) {

		$yearCnt    = 0;
		$monthCount = 0;

		$brNum        = $data['mrBranch'];
		$brName       = $branchInfo[$brNum]['name'];
		$regionalCode = $branchInfo[$brNum]['regional'];
		$regionalName = $regionals[$regionalCode]['lName'];
		$regionCode   = $branchInfo[$brNum]['region'];
		$pos          = $data['mrPos'];
		$name         = $data['mrName'];
		$empID        = $data['mrEmpID'];
		$dept         = $positions[$pos];
		#$lastAudit    = isset($auditInfo[$brNum][$pos]) ? $auditInfo[$brNum][$pos] : "N/A";
		$scoreArr   = [$data['mrLdshp'],
			$data['mrMulti'],
			$data['mrPrior'],
			$data['mrMngPeo'],
			$data['mrPride'],
			$data['mrCusSer'],
			$data['mrProc'],
			$data['mrExec'],
			$data['mrKnow'],
			$data['mrCommu']];
		$totalScore = array_sum($scoreArr) / 10;
		$mrDate     = new DateTime($data['mrDate']);
		#$mrDate = "1/1/2022";

		$tenureText = null;

		#sets tenureText & figures totalTenure
		if ($mrDate != "N/A") {
			$today  = new DateTime ();
			$tenure = $mrDate->diff($today);

			if ($tenure->m != 0) {
				if ($tenure->y > 0) {
					$tenureText = $tenure->y . " Yrs " . $tenure->m . " Mts";
				} else {
					$tenureText = $tenure->m . " Mts";
				}
			} else {
				if ($tenure->y > 0) {
					$tenureText = $tenure->y . " Yrs";
				} else {
					$tenureText = "1 Mt";
				}
			}
			$mrDate = $mrDate->format("m/d/Y");
		} else {
			$tenureText = "N/A";
		}

		$raise = null;

		switch (true) {
			case $totalScore >= 8.5:
				$raise = .03;
				break;
			case $totalScore >= 7:
				$raise = .0275;
				break;
			case $totalScore >= 5:
				$raise = .02;
				break;
			case $totalScore >= 3:
				$raise = .01;
				break;
			default:
				$raise = "NA";
		}

		$arr[] = [$brNum,
		$brName,
		$name,
		$empID,
		$pos,
		$dept,
		$mrDate,
		$tenureText,
		$totalScore,
		$raise,
		$regionCode,
		$regionalName,
		$data['mrLdshp'],
		$data['mrMulti'],
		$data['mrPrior'],
		$data['mrMngPeo'],
		$data['mrPride'],
		$data['mrCusSer'],
		$data['mrProc'],
		$data['mrExec'],
		$data['mrKnow'],
		$data['mrCommu']];

}

	return $arr;
}

$lnk        = new Process();
$positions  = getDepartments($lnk);
$regionals  = getRegionals($lnk);
$branchInfo = getBranchInfo($lnk);
#$auditInfo  = getAuditScores($lnk);

$headerArray = ['Branch',
	'Branch Name',
	'Team Member',
	'Employee ID',
	'Position',
	'Dept',
	'Date in POS',
	'Tenure',
	'Total Score',
	"Pos Raise %",
	'Region',
	'Regional',
	'Leadership',
	'Multitasking',
	'Priorities',
	'Manage People',
	'Pride',
	'Customer Service',
	'Process Driven',
	'Execution',
	'Job Knowledge',
	'Communication Skills'];

$fillArray = [];
$rankingQry = [];

$rankingQry = $lnk->query("SELECT * FROM jrd_stuff.mrRankings WHERE mrYear = 2022 && mrID > 12164 ORDER BY mrBranch, mrPos");

$fillArray = getRankingArray($rankingQry, $branchInfo, $positions, $regionals);

echo "rankingQry: " . count($rankingQry) . "</br>" . "fillArray: " . count($fillArray);

$spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$sheet = $spreadsheet->getActiveSheet();
$row   = 1;
$col   = 1;

foreach ($headerArray as $header) {
	$sheet->setCellValueByColumnAndRow($col, $row, $header);
	$col ++;
}
$row ++;

foreach ($fillArray as $data) {
	#var_dump($data);
	$col = 1;

		for ($j=0; $j<22 ; $j++) {
			$sheet->setCellValueByColumnAndRow($col, $row, $data[$j]);
			$col ++;
		}

	$row++;
}

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
$writer->save("../io/output/2022 Manager Ranking Details WC.xlsx");



