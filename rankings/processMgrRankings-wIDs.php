<?php

require_once '../class/Process.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * @param $lnk Process
 *
 * @return array
 */
function getRegionals($lnk) {
	$regionalQry = $lnk->query("SELECT concat(fName, ' ', lName) as name, lName, regionID FROM branchInfo.opsExecs where active = 1");

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
	$posQry = $lnk->query("SELECT dept, posName FROM staffing.posToDepts");
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
	$branchQry = $lnk->query("SELECT branchName, regional, location, branchNum FROM branchInfo.branches");
	$arr       = [];
	foreach ($branchQry as $data) {
		$arr[$data['branchNum']] = ['regional' => $data['regional'],
		                            'name'     => $data['branchName'],
		                            'region'   => $data['location']];
	}

	return $arr;
}

function getAuditScores($lnk) {
	#getting audit scores
	$sql1 = "SELECT * FROM auditAnalysis.auditscores WHERE auditID > 1987 && rep = 1";
	$qry1 = $lnk->query($sql1);

#getting branches
	$sql2 = "SELECT branchNum FROM branchInfo.branches WHERE location != 'WC' ORDER BY branchNum ASC";
	$qry2 = $lnk->query($sql2);

#getting audit ids
	$sql3 = "SELECT ID, branch FROM auditAnalysis.enteredaudits WHERE year = 2021 ORDER BY branch ASC, period DESC";
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
		$lqScore = $auditScores['lqScore'] < 0 ? "N/A" : number_format($auditScores['lqScore'] * 100, 2);


		$scoreArray[$auditScores['auditID']] = ['tot'       => number_format($auditScores['totScore'] * 100, 2),
		                                        "Admin"     => $adScore,
		                                        "CashRoom"  => $crScore,
		                                        "Deli"      => $daScore,
		                                        "Floor"     => $flScore,
		                                        "Front End" => $feScore,
		                                        "Senior"    => $goScore,
		                                        "IC"        => $icScore,
		                                        "Meat"      => $meScore,
		                                        "Produce"   => $prScore,
		                                        "Receiving" => $rvScore,
		                                        "Seafood"   => $seScore,
		                                        "Smwares"   => $swScore,
		                                        "WS"        => $lqScore
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

function getRankingArray($rankingQry, $branchInfo, $positions, $regionals, $auditInfo) {

	$count       = 0;
	$totalTenure = 0;

	$rankingArr = [];
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
		$lastAudit    = isset($auditInfo[$brNum][$pos]) ? $auditInfo[$brNum][$pos] : "N/A";
		$scoreArr     = [$data['mrLdshp'],
			$data['mrMulti'],
			$data['mrPrior'],
			$data['mrMngPeo'],
			$data['mrPride'],
			$data['mrCusSer'],
			$data['mrProc'],
			$data['mrExec'],
			$data['mrKnow'],
			$data['mrCommu']];
		$totalScore   = array_sum($scoreArr) / 10;
		$mrDate       = $data['mrDate'] !== '1/1/2020' ? new DateTime($data['mrDate']) : "N/A";
		$dept         = $positions[$pos];

		#sets tenureText & figures totalTenure
		if ($mrDate !== "N/A") {
			$today      = new DateTime ();
			$tenure     = $mrDate->diff($today);
			$yearCnt    += $tenure->y;
			$monthCount += ($tenure->y * 12) + $tenure->m;
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
			$count ++;
			$totalTenure += ($today->getTimestamp() - $mrDate->getTimestamp());
		} else {
			$tenureText = "N/A";
		}

		#initializes $rankingArr
		if (!isset($rankingArr[$regionCode][$regionalName][$brName])) {
			$rankingArr[$regionCode][$regionalName][$brNum . " - " . $brName][$dept][] = ['Position'       => $pos,
			                                                             'Team Member' => $name,
			                                                             'Score'     => 'N/A',
			                                                             'Tenure'  => 'N/A',
			                                                             'Last Audit'  => 'N/A'],
			                                                    '136a' => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '126'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '126a' => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '126b' => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '151'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '191'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '139'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '152'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '156'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '163'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '143'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '159'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '176'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '165'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '137'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A'],
			                                                    '148'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A',
			                                                               'lastAudit'  => 'N/A']];
		}

		#initializes $regionalScores array
		if (!isset($regionalScores[$regionCode][$regionalName][$pos])) {
			$regionalScores[$regionCode][$regionalName][$pos] = ['score' => 0, 'count' => 0];
		}

		$regionalScores[$regionCode][$regionalName][$pos]['score'] += $totalScore;
		$regionalScores[$regionCode][$regionalName][$pos]['count'] ++;

		$postionalArr[$pos][$regionCode][] = [$brNum,
			$brName2,
			$regionalName,
			$name,
			$totalScore,
			$tenureText,
			$lastAudit];

		$origPos = $pos;

		if (($pos == 136 || $pos == 126)) {
			if ($rankingArr[$regionCode][$regionalName][$brName][$pos]['name'] !== 'N/A' && $rankingArr[$regionCode][$regionalName][$brName][$pos . 'a']['name'] == 'N/A') {
				$pos = $pos . 'a';
			} elseif ($rankingArr[$regionCode][$regionalName][$brName][$pos]['name'] !== 'N/A' && $rankingArr[$regionCode][$regionalName][$brName][$pos . 'a']['name'] !== 'N/A') {
				$pos = $pos . 'b';
			}
		}

		$rankingArr[$regionCode][$regionalName][$brName][$pos] = ['name'       => $name,
		                                                          'totalScore' => $totalScore,
		                                                          'tenure'     => $tenureText,
		                                                          'tenureObj'  => $tenure,
		                                                          'lastAudit'  => $lastAudit];


	}

	return [$rankingArr, $regionalScores, $postionalArr];
}

$lnk        = new Process();
$positions  = getDepartments($lnk);
$regionals  = getRegionals($lnk);
$branchInfo = getBranchInfo($lnk);
$auditInfo  = getAuditScores($lnk);

$rankingQry = $lnk->query("SELECT * FROM staffing.mrRankings WHERE mrYear = 2021");


if ($rankingQry) {
	$arr            = getRankingArray($rankingQry, $branchInfo, $positions, $regionals, $auditInfo);
	$rankingArr     = $arr[0];
	$regionalScores = $arr[1];
	$positionArr    = $arr[2];
}

$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$EC_Sheet    = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'EC');
$MW_Sheet    = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'MW');
#$WC_Sheet    = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'WC');
$spreadSheet->addSheet($EC_Sheet);
$spreadSheet->addSheet($MW_Sheet);
#$spreadSheet->addSheet($WC_Sheet);

$regionScore  = 0;
$regionTenure = 0;

$headingArray  = ['BM',
	'BM',
	'ABM',
	'ABM',
	'ABM',
	'IC',
	'PERISHABLE',
	'DAIRY',
	'MEAT',
	'PRODUCE',
	'SEAFOOD',
	'FLOOR',
	'RECEIVING',
	'HRADMIN',
	'SMALLWARES',
	'CASHROOM',
	'FRONT END',
	'TOTAL AVG'];
$regSummHeader = ['BM',
	'ABM',
	'IC',
	'PERISHABLE',
	'DAIRY',
	'MEAT',
	'PRODUCE',
	'SEAFOOD',
	'FLOOR',
	'RECEIVING',
	'HRADMIN',
	'SMALLWARES',
	'CASHROOM',
	'FRONT END',
	'TOTAL AVG'];
$posLookup     = [136 => 'BM',
                  126 => 'ABM',
                  151 => 'IC',
                  191 => 'PERISHABLE',
                  139 => 'DAIRY',
                  152 => 'MEAT',
                  156 => 'PRODUCE',
                  163 => 'SEAFOOD',
                  143 => 'FLOOR',
                  159 => 'RECEIVING',
                  176 => 'HRADMIN',
                  165 => 'SMALLWARES',
                  137 => 'CASHROOM',
                  148 => 'FRONT END'];
$regionLookup  = ['EC' => 'East Coast', 'MW' => "Mid-West"];

#writes the regional summary tabs
foreach ($rankingArr as $region => $regionals) {
	$regionScore  = 0;
	$regionMonths = 0;
	$regionCount  = 0;
	$row          = 1;
	$col          = 2;
	$spreadSheet->setActiveSheetIndexByName($region);
	$sheet = $spreadSheet->getActiveSheet();
	foreach ($regSummHeader as $head) {
		$sheet->setCellValueByColumnAndRow($col, $row, $head);
		$col ++;
	}
	$sheet->setCellValueByColumnAndRow(1, 2, 'Region Avg');
	$sheet->setCellValueByColumnAndRow(1, 4, 'Click Branch Name To Go To Branch Tab');
	$row = 6;
	foreach ($regionals as $regional => $branches) {
		ksort($branches);
		$regionalScore  = 0;
		$regionalMonths = 0;
		$regionalCount  = 0;
		$col            = 1;
		$sheet->setCellValueByColumnAndRow($col, $row, $regional);
		$col = 3;
		foreach ($headingArray as $head) {
			$sheet->setCellValueByColumnAndRow($col, $row, $head);
			$col ++;
		}
		$row ++;
		$col = 1;


		foreach ($branches as $branch => $mrPos) {
			$branchScore  = 0;
			$branchMonths = 0;
			$branchCount  = 0;
			$oldPos       = null;
			$initRow      = $row;
			$sheet->setCellValueByColumnAndRow(1, $row, $branch);
			$branchSplit = explode('-', $branch);
			$branchNum   = trim($branchSplit[0]);
			$sheet->getCellByColumnAndRow(1, $row)->getHyperlink()->setURL("sheet://'" . $branchNum . "'!A1");
			$sheet->setCellValueByColumnAndRow(2, $row, 'Name');
			$row ++;
			$sheet->setCellValueByColumnAndRow(2, $row, 'Score');
			$row ++;
			$sheet->setCellValueByColumnAndRow(2, $row, 'Tenure');
			$row ++;
			$sheet->setCellValueByColumnAndRow(2, $row, 'Last Audit');
			$row ++;
			$col = 3;
			foreach ($mrPos as $mrPosition => $info) {
				$row = $initRow;
				#echo $oldPos . ": " . $mrPosition . "</br>";
				$sheet->setCellValueByColumnAndRow($col, $row, $info['name']);
				$row ++;
				$sheet->setCellValueByColumnAndRow($col, $row, $info['totalScore']);
				$row ++;
				$sheet->setCellValueByColumnAndRow($col, $row, $info['tenure']);
				$row ++;
				$sheet->setCellValueByColumnAndRow($col, $row, $info['lastAudit']);
				$col ++;
				$oldPos = $mrPosition;

				if ($info['name'] !== 'N/A') {
					$branchScore    += $info['totalScore'];
					$regionalScore  += $info['totalScore'];
					$regionScore    += $info['totalScore'];
					$branchMonths   += $info['tenureObj']->m;
					$branchMonths   += $info['tenureObj']->y * 12;
					$regionalMonths += $info['tenureObj']->m;
					$regionalMonths += $info['tenureObj']->y * 12;
					$regionMonths   += $info['tenureObj']->m;
					$regionMonths   += $info['tenureObj']->y * 12;
					$branchCount ++;
					$regionalCount ++;
					$regionCount ++;
				}
			}
			#echo $branchMonths . ": " .$branchCount ."</br>";
			$branchAvgScore = number_format($branchScore / $branchCount, 2);
			$branchMonths   = floor($branchMonths / $branchCount);
			$brYears        = floor($branchMonths / 12);
			$brMonths       = $branchMonths - ($brYears * 12);
			$avgBrTenure    = $brYears . " Yrs " . $brMonths . " Mts";
			$sheet->setCellValueByColumnAndRow(20, $initRow + 1, $branchAvgScore);
			$sheet->setCellValueByColumnAndRow(20, $initRow + 2, $avgBrTenure);
			$sheet->setCellValueByColumnAndRow(20, $initRow + 3, $auditInfo[$branchNum]['tot']);
			$row ++;
			#$regionalCount += $branchCount;
		}
		$initRow = $row;
		$sheet->setCellValueByColumnAndRow(1, $row, 'Regional Avg Score');
		if (!isset($regionScores[$region][136])) {
			$regionScores[$region][136] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][136])) {
			$regionalScores[$region][$regional][136] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][136]['count'] != 0) {
			$bmScore = number_format($regionalScores[$region][$regional][136]['score'] / $regionalScores[$region][$regional][136]['count'], 2);
		} else {
			$bmScore = "N/A";
		}
		$regionScores[$region][136]['score'] += $regionalScores[$region][$regional][136]['score'];
		$regionScores[$region][136]['count'] += $regionalScores[$region][$regional][136]['count'];
		$sheet->setCellValueByColumnAndRow(3, $row, $bmScore);
		if (!isset($regionScores[$region][126])) {
			$regionScores[$region][126] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][126])) {
			$regionalScores[$region][$regional][126] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][126]['count'] != 0) {
			$abmScore = number_format($regionalScores[$region][$regional][126]['score'] / $regionalScores[$region][$regional][126]['count'], 2);
		} else {
			$abmScore = "N/A";
		}
		$regionScores[$region][126]['score'] += $regionalScores[$region][$regional][126]['score'];
		$regionScores[$region][126]['count'] += $regionalScores[$region][$regional][126]['count'];
		$sheet->setCellValueByColumnAndRow(5, $row, $abmScore);
		$col = 8;
		if (!isset($regionScores[$region][151])) {
			$regionScores[$region][151] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][151])) {
			$regionalScores[$region][$regional][151] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][151]['count'] != 0) {
			$icScore = number_format($regionalScores[$region][$regional][151]['score'] / $regionalScores[$region][$regional][151]['count'], 2);
		} else {
			$icScore = "N/A";
		}
		$regionScores[$region][151]['score'] += $regionalScores[$region][$regional][151]['score'];
		$regionScores[$region][151]['count'] += $regionalScores[$region][$regional][151]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $icScore);
		$col ++;
		if (!isset($regionScores[$region][191])) {
			$regionScores[$region][191] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][191])) {
			$regionalScores[$region][$regional][191] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][191]['count'] != 0) {
			$periScore = number_format($regionalScores[$region][$regional][191]['score'] / $regionalScores[$region][$regional][191]['count'], 2);
		} else {
			$periScore = "N/A";
		}
		$regionScores[$region][191]['score'] += $regionalScores[$region][$regional][191]['score'];
		$regionScores[$region][191]['count'] += $regionalScores[$region][$regional][191]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $periScore);
		$col ++;
		if (!isset($regionScores[$region][139])) {
			$regionScores[$region][139] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][139])) {
			$regionalScores[$region][$regional][139] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][139]['count'] != 0) {
			$deliScore = number_format($regionalScores[$region][$regional][139]['score'] / $regionalScores[$region][$regional][139]['count'], 2);
		} else {
			$deliScore = "N/A";
		}
		$regionScores[$region][139]['score'] += $regionalScores[$region][$regional][139]['score'];
		$regionScores[$region][139]['count'] += $regionalScores[$region][$regional][139]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $deliScore);
		$col ++;
		if (!isset($regionScores[$region][152])) {
			$regionScores[$region][152] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][152])) {
			$regionalScores[$region][$regional][152] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][152]['count'] != 0) {
			$meatScore = number_format($regionalScores[$region][$regional][152]['score'] / $regionalScores[$region][$regional][152]['count'], 2);
		} else {
			$meatScore = "N/A";
		}
		$regionScores[$region][152]['score'] += $regionalScores[$region][$regional][152]['score'];
		$regionScores[$region][152]['count'] += $regionalScores[$region][$regional][152]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $meatScore);
		$col ++;
		if (!isset($regionScores[$region][156])) {
			$regionScores[$region][156] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][156])) {
			$regionalScores[$region][$regional][156] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][156]['count'] != 0) {
			$prodScore = number_format($regionalScores[$region][$regional][156]['score'] / $regionalScores[$region][$regional][156]['count'], 2);
		} else {
			$prodSore = "N/A";
		}
		$regionScores[$region][156]['score'] += $regionalScores[$region][$regional][156]['score'];
		$regionScores[$region][156]['count'] += $regionalScores[$region][$regional][156]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $prodScore);
		$col ++;
		if (!isset($regionScores[$region][163])) {
			$regionScores[$region][163] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][163])) {
			$regionalScores[$region][$regional][163] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][163]['count'] != 0) {
			$sfScore = number_format($regionalScores[$region][$regional][163]['score'] / $regionalScores[$region][$regional][163]['count'], 2);
		} else {
			$sfScore = "N/A";
		}
		$regionScores[$region][163]['score'] += $regionalScores[$region][$regional][163]['score'];
		$regionScores[$region][163]['count'] += $regionalScores[$region][$regional][163]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $sfScore);
		$col ++;
		if (!isset($regionScores[$region][143])) {
			$regionScores[$region][143] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][143])) {
			$regionalScores[$region][$regional][143] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][143]['count'] != 0) {
			$flrScore = number_format($regionalScores[$region][$regional][143]['score'] / $regionalScores[$region][$regional][143]['count'], 2);
		} else {
			$flrScore = "N/A";
		}
		$regionScores[$region][143]['score'] += $regionalScores[$region][$regional][143]['score'];
		$regionScores[$region][143]['count'] += $regionalScores[$region][$regional][143]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $flrScore);
		$col ++;
		if (!isset($regionScores[$region][159])) {
			$regionScores[$region][159] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][159])) {
			$regionalScores[$region][$regional][159] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][159]['count'] != 0) {
			$recScore = number_format($regionalScores[$region][$regional][159]['score'] / $regionalScores[$region][$regional][159]['count'], 2);
		} else {
			$recScore = "N/A";
		}
		$regionScores[$region][159]['score'] += $regionalScores[$region][$regional][159]['score'];
		$regionScores[$region][159]['count'] += $regionalScores[$region][$regional][159]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $recScore);
		$col ++;
		if (!isset($regionScores[$region][176])) {
			$regionScores[$region][176] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][176])) {
			$regionalScores[$region][$regional][176] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][176]['count'] != 0) {
			$admScore = number_format($regionalScores[$region][$regional][176]['score'] / $regionalScores[$region][$regional][176]['count'], 2);
		} else {
			$admScore = "N/A";

		}
		$regionScores[$region][176]['score'] += $regionalScores[$region][$regional][176]['score'];
		$regionScores[$region][176]['count'] += $regionalScores[$region][$regional][176]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $admScore);
		$col ++;
		if (!isset($regionScores[$region][165])) {
			$regionScores[$region][165] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][165])) {
			$regionalScores[$region][$regional][165] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][165]['count'] != 0) {
			$smScore = number_format($regionalScores[$region][$regional][165]['score'] / $regionalScores[$region][$regional][165]['count'], 2);
		} else {
			$smScore = "N/A";

		}
		$regionScores[$region][165]['score'] += $regionalScores[$region][$regional][165]['score'];
		$regionScores[$region][165]['count'] += $regionalScores[$region][$regional][165]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $smScore);
		$col ++;
		if (!isset($regionScores[$region][137])) {
			$regionScores[$region][137] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][137])) {
			$regionalScores[$region][$regional][137] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][137]['count'] != 0) {
			$crScore = number_format($regionalScores[$region][$regional][137]['score'] / $regionalScores[$region][$regional][137]['count'], 2);
		} else {
			$crScore = "N/A";

		}
		$regionScores[$region][137]['score'] += $regionalScores[$region][$regional][137]['score'];
		$regionScores[$region][137]['count'] += $regionalScores[$region][$regional][137]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $crScore);
		$col ++;
		if (!isset($regionScores[$region][148])) {
			$regionScores[$region][148] = ['score' => 0, 'count' => 0];
		}
		if (!isset($regionalScores[$region][$regional][148])) {
			$regionalScores[$region][$regional][148] = ['score' => 0, 'count' => 0];
		}
		if ($regionalScores[$region][$regional][148]['count'] != 0) {
			$feScore = number_format($regionalScores[$region][$regional][148]['score'] / $regionalScores[$region][$regional][148]['count'], 2);
		} else {
			$feScore = "N/A";

		}
		$regionScores[$region][148]['score'] += $regionalScores[$region][$regional][148]['score'];
		$regionScores[$region][148]['count'] += $regionalScores[$region][$regional][148]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $feScore);
		$col ++;
		$avgRegionScore = number_format($regionalScore / $regionalCount, 2);
		$sheet->setCellValueByColumnAndRow($col, $row, $avgRegionScore);
		$row ++;
		$row ++;
	}
	$col = 2;
	$row = 2;

	$totalScore = 0;
	$totalCount = 0;
	foreach ($regionScores[$region] as $k => $x) {

		if ($x['count'] != 0) {
			$avgScore = number_format($x['score'] / $x['count'], 2);
		} else {
			$avgScore = "N/A";
		}
		$totalScore += $x['score'];
		$totalCount += $x['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $avgScore);
		$col ++;
	}
	$totalAvg = number_format($totalScore / $totalCount, 2);
	$sheet->setCellValueByColumnAndRow($col, $row, $totalAvg);
}

foreach ($positionArr as $posCode => $regions) {
	echo $posLookup[$posCode] . "</br>";
	$deptSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $posLookup[$posCode]);
	$spreadSheet->addSheet($deptSheet);
	$spreadSheet->setActiveSheetIndexByName($posLookup[$posCode]);
	$sheet = $spreadSheet->getActiveSheet();
	$row   = 1;
	$col   = 1;
	$sheet->setCellValueByColumnAndRow($col, $row, $posLookup[$posCode] . " Position Summary");
	$row ++;
	$sheet->setCellValueByColumnAndRow($col, $row, "Click Branch Number To Go To Branch Tab");
	$row     = $row + 2;
	$initRow = $row;
	$initCol = 1;
	foreach ($regions as $reg => $info) {
		$row = $initRow;
		$sheet->setCellValueByColumnAndRow($col, $row, $regionLookup[$reg]);
		$row ++;
		$sheet->setCellValueByColumnAndRow($col, $row, 'Branch');
		$sheet->setCellValueByColumnAndRow($col + 1, $row, 'Branch Name');
		$sheet->setCellValueByColumnAndRow($col + 2, $row, 'Regional');
		$sheet->setCellValueByColumnAndRow($col + 3, $row, 'Team Member');
		$sheet->setCellValueByColumnAndRow($col + 4, $row, 'Ranking');
		$sheet->setCellValueByColumnAndRow($col + 5, $row, 'Tenure');
		$sheet->setCellValueByColumnAndRow($col + 6, $row, 'Last Audit');
		$row ++;
		foreach ($info as $key => $data) {
			$col = $initCol;
			$sheet->setCellValueByColumnAndRow($col, $row, $data[0]);
			$sheet->getCellByColumnAndRow($col, $row)->getHyperlink()->setURL("sheet://'" . $data[0] . "'!A1");
			$col ++;
			$sheet->setCellValueByColumnAndRow($col, $row, $data[1]);
			$col ++;
			$sheet->setCellValueByColumnAndRow($col, $row, $data[2]);
			$col ++;
			$sheet->setCellValueByColumnAndRow($col, $row, $data[3]);
			$col ++;
			$sheet->setCellValueByColumnAndRow($col, $row, $data[4]);
			$col ++;
			$sheet->setCellValueByColumnAndRow($col, $row, $data[5]);
			$col ++;
			$sheet->setCellValueByColumnAndRow($col, $row, $data[6]);
			$col ++;
			$row ++;
		}
		$col ++;
		$initCol = $col;
	}
}

#var_dump($positionArr);

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadSheet);
$writer->save("../io/output/2022 H1 Manager Rankings.xlsx");

