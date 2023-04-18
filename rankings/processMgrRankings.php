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
function getPositions($lnk) {
	$posQry = $lnk->query("SELECT posID, posName FROM staffing.positions");
	$arr    = [];
	foreach ($posQry as $data) {
		$arr[$data['posID']] = $data['posName'];
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

function getRankingArray($rankingQry, $branchInfo, $positions, $regionals) {

	$count       = 0;
	$totalTenure = 0;
	$yearCnt     = 0;
	$monthCount  = 0;
	$rankingArr  = [];
	foreach ($rankingQry as $data) {

		$brNum        = $data['mrBranch'];
		$brName       = $brNum . " - " . $branchInfo[$brNum]['name'];
		$brName2      = $branchInfo[$brNum]['name'];
		$regionalCode = $branchInfo[$brNum]['regional'];
		$regionalName = $regionals[$regionalCode]['lName'];
		$regionCode   = $branchInfo[$brNum]['region'];
		$pos          = $data['mrPos'];
		$name         = $data['mrName'];
		$posName      = $positions[$pos];
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
		$totalScore   = array_sum($scoreArr);
		$mrDate       = $data['mrDate'] !== '1/1/2020' ? new DateTime($data['mrDate']) : "N/A";

		#sets tenureText & figures totalTenure
		if ($mrDate !== "N/A") {
			$today      = new DateTime ();
			$tenure     = $mrDate->diff($today);
			$yearCnt    += $tenure->y;
			$monthCount += ($tenure->y * 12) + $tenure->m;
			if ($tenure->m != 0) {
				if ($tenure->y > 0) {
					$tenureText = $tenure->y . " Yrs " . $tenure->m . " Mts";
				}
				else {
					$tenureText = $tenure->m . " Mts";
				}
			}
			else {
				if ($tenure->y > 0) {
					$tenureText = $tenure->y . " Yrs";
				}
				else {
					$tenureText = "1 Mt";
				}
			}
			$count ++;
			$totalTenure += ($today->getTimestamp() - $mrDate->getTimestamp());
		}

		#initializes $rankingArr
		if (!isset($rankingArr[$regionCode][$regionalName][$brName])) {
			$rankingArr[$regionCode][$regionalName][$brName] = ['136'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '136a' => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '126'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '126a' => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '126b' => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '151'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '191'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '139'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '152'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '156'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '163'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '143'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '159'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '176'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '165'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '137'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A'],
			                                                    '148'  => ['name'       => 'N/A',
			                                                               'totalScore' => 'N/A',
			                                                               'tenure'     => 'N/A',
			                                                               'tenureObj'  => 'N/A']];
		}

		#initializes $regionalScores array
		if (!isset($regionalScores[$regionCode][$regionalName][$pos])) {
			$regionalScores[$regionCode][$regionalName][$pos] = ['score' => 0, 'count' => 0];
		}

		$regionalScores[$regionCode][$regionalName][$pos]['score'] += $totalScore;
		$regionalScores[$regionCode][$regionalName][$pos]['count'] ++;

		$postionalArr[$pos][$regionCode][] = [$brNum, $brName2, $regionalName, $name, $totalScore, $tenureText];

		$origPos = $pos;

		if (($pos == 136 || $pos == 126)) {
			if ($rankingArr[$regionCode][$regionalName][$brName][$pos]['name'] !== 'N/A' && $rankingArr[$regionCode][$regionalName][$brName][$pos . 'a']['name'] == 'N/A') {
				$pos = $pos . 'a';
			}
			elseif ($rankingArr[$regionCode][$regionalName][$brName][$pos]['name'] !== 'N/A' && $rankingArr[$regionCode][$regionalName][$brName][$pos . 'a']['name'] !== 'N/A') {
				$pos = $pos . 'b';
			}
		}

		$rankingArr[$regionCode][$regionalName][$brName][$pos] = ['name'       => $name,
		                                                          'totalScore' => $totalScore,
		                                                          'tenure'     => $tenureText,
		                                                          'tenureObj'  => $tenure];


	}

	return [$rankingArr, $regionalScores, $postionalArr];
}

$lnk        = new Process();
$positions  = getPositions($lnk);
$regionals  = getRegionals($lnk);
$branchInfo = getBranchInfo($lnk);

$rankingQry = $lnk->query("SELECT * FROM staffing.mrRankings");


if ($rankingQry) {
	$arr            = getRankingArray($rankingQry, $branchInfo, $positions, $regionals);
	$rankingArr     = $arr[0];
	$regionalScores = $arr[1];
	$positionArr    = $arr[2];
}

$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$EC_Sheet    = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'EC');
$MW_Sheet    = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'MW');
$WC_Sheet    = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, 'WC');
$spreadSheet->addSheet($EC_Sheet);
$spreadSheet->addSheet($MW_Sheet);
$spreadSheet->addSheet($WC_Sheet);

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
$regionLookup = ['EC'=>'East Coast', 'MW'=>"Mid-West", 'WC'=>'West Coast'];

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
			$col = 3;
			foreach ($mrPos as $mrPosition => $info) {
				$row = $initRow;
				#echo $oldPos . ": " . $mrPosition . "</br>";
				$sheet->setCellValueByColumnAndRow($col, $row, $info['name']);
				$row ++;
				$sheet->setCellValueByColumnAndRow($col, $row, $info['totalScore']);
				$row ++;
				$sheet->setCellValueByColumnAndRow($col, $row, $info['tenure']);
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
			$row ++;
			#$regionalCount += $branchCount;
		}
		$initRow = $row;
		$sheet->setCellValueByColumnAndRow(1, $row, 'Regional Avg Score');
		$bmScore = number_format($regionalScores[$region][$regional][136]['score'] / $regionalScores[$region][$regional][136]['count'], 2);
		if (!isset($regionScores[$region][136])) {
			$regionScores[$region][136] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][136]['score'] += $regionalScores[$region][$regional][136]['score'];
		$regionScores[$region][136]['count'] += $regionalScores[$region][$regional][136]['count'];
		$sheet->setCellValueByColumnAndRow(3, $row, $bmScore);
		$abmScore = number_format($regionalScores[$region][$regional][126]['score'] / $regionalScores[$region][$regional][126]['count'], 2);
		if (!isset($regionScores[$region][126])) {
			$regionScores[$region][126] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][126]['score'] += $regionalScores[$region][$regional][126]['score'];
		$regionScores[$region][126]['count'] += $regionalScores[$region][$regional][126]['count'];
		$sheet->setCellValueByColumnAndRow(5, $row, $abmScore);
		$col     = 8;
		$icScore = number_format($regionalScores[$region][$regional][151]['score'] / $regionalScores[$region][$regional][151]['count'], 2);
		if (!isset($regionScores[$region][151])) {
			$regionScores[$region][151] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][151]['score'] += $regionalScores[$region][$regional][151]['score'];
		$regionScores[$region][151]['count'] += $regionalScores[$region][$regional][151]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $icScore);
		$col ++;
		$periScore = number_format($regionalScores[$region][$regional][191]['score'] / $regionalScores[$region][$regional][191]['count'], 2);
		if (!isset($regionScores[$region][191])) {
			$regionScores[$region][191] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][191]['score'] += $regionalScores[$region][$regional][191]['score'];
		$regionScores[$region][191]['count'] += $regionalScores[$region][$regional][191]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $periScore);
		$col ++;
		$deliScore = number_format($regionalScores[$region][$regional][139]['score'] / $regionalScores[$region][$regional][139]['count'], 2);
		if (!isset($regionScores[$region][139])) {
			$regionScores[$region][139] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][139]['score'] += $regionalScores[$region][$regional][139]['score'];
		$regionScores[$region][139]['count'] += $regionalScores[$region][$regional][139]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $deliScore);
		$col ++;
		$meatScore = number_format($regionalScores[$region][$regional][152]['score'] / $regionalScores[$region][$regional][152]['count'], 2);
		if (!isset($regionScores[$region][152])) {
			$regionScores[$region][152] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][152]['score'] += $regionalScores[$region][$regional][152]['score'];
		$regionScores[$region][152]['count'] += $regionalScores[$region][$regional][152]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $meatScore);
		$col ++;
		$prodScore = number_format($regionalScores[$region][$regional][156]['score'] / $regionalScores[$region][$regional][156]['count'], 2);
		if (!isset($regionScores[$region][156])) {
			$regionScores[$region][156] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][156]['score'] += $regionalScores[$region][$regional][156]['score'];
		$regionScores[$region][156]['count'] += $regionalScores[$region][$regional][156]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $prodScore);
		$col ++;
		$sfScore = number_format($regionalScores[$region][$regional][163]['score'] / $regionalScores[$region][$regional][163]['count'], 2);
		if (!isset($regionScores[$region][163])) {
			$regionScores[$region][163] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][163]['score'] += $regionalScores[$region][$regional][163]['score'];
		$regionScores[$region][163]['count'] += $regionalScores[$region][$regional][163]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $sfScore);
		$col ++;
		$flrScore = number_format($regionalScores[$region][$regional][143]['score'] / $regionalScores[$region][$regional][143]['count'], 2);
		if (!isset($regionScores[$region][143])) {
			$regionScores[$region][143] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][143]['score'] += $regionalScores[$region][$regional][143]['score'];
		$regionScores[$region][143]['count'] += $regionalScores[$region][$regional][143]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $flrScore);
		$col ++;
		$recScore = number_format($regionalScores[$region][$regional][159]['score'] / $regionalScores[$region][$regional][159]['count'], 2);
		if (!isset($regionScores[$region][159])) {
			$regionScores[$region][159] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][159]['score'] += $regionalScores[$region][$regional][159]['score'];
		$regionScores[$region][159]['count'] += $regionalScores[$region][$regional][159]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $recScore);
		$col ++;
		$admScore = number_format($regionalScores[$region][$regional][176]['score'] / $regionalScores[$region][$regional][176]['count'], 2);
		if (!isset($regionScores[$region][176])) {
			$regionScores[$region][176] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][176]['score'] += $regionalScores[$region][$regional][176]['score'];
		$regionScores[$region][176]['count'] += $regionalScores[$region][$regional][176]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $admScore);
		$col ++;
		$smScore = number_format($regionalScores[$region][$regional][165]['score'] / $regionalScores[$region][$regional][165]['count'], 2);
		if (!isset($regionScores[$region][165])) {
			$regionScores[$region][165] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][165]['score'] += $regionalScores[$region][$regional][165]['score'];
		$regionScores[$region][165]['count'] += $regionalScores[$region][$regional][165]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $smScore);
		$col ++;
		$crScore = number_format($regionalScores[$region][$regional][137]['score'] / $regionalScores[$region][$regional][137]['count'], 2);
		if (!isset($regionScores[$region][137])) {
			$regionScores[$region][137] = ['score' => 0, 'count' => 0];
		}
		$regionScores[$region][137]['score'] += $regionalScores[$region][$regional][137]['score'];
		$regionScores[$region][137]['count'] += $regionalScores[$region][$regional][137]['count'];
		$sheet->setCellValueByColumnAndRow($col, $row, $crScore);
		$col ++;
		$feScore = number_format($regionalScores[$region][$regional][148]['score'] / $regionalScores[$region][$regional][148]['count'], 2);
		if (!isset($regionScores[$region][148])) {
			$regionScores[$region][148] = ['score' => 0, 'count' => 0];
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
		$avgScore   = number_format($x['score'] / $x['count'], 2);
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
	$deptSheet   = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $posLookup[$posCode]);
	$spreadSheet->addSheet($deptSheet);
	$spreadSheet->setActiveSheetIndexByName($posLookup[$posCode]);
	$sheet = $spreadSheet->getActiveSheet();
	$row = 1;
	$col = 1;
	$sheet->setCellValueByColumnAndRow($col, $row, $posLookup[$posCode] . " Position Summary");
	$row++;
	$sheet->setCellValueByColumnAndRow($col, $row, "Click Branch Number To Go To Branch Tab");
	$row = $row + 2;
	$initRow = $row;
	$initCol = 1;
	foreach ($regions as $reg => $info) {
		$row = $initRow;
		$sheet->setCellValueByColumnAndRow($col, $row, $regionLookup[$reg]);
		$row++;
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
			$row++;
		}
		$col++;
		$initCol = $col;
	}
}

#var_dump($positionArr);

$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadSheet);
$writer->save("../io/output/testFile.xlsx");

