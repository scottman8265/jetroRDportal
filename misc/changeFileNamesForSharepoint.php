<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls\Workbook;

require('../class/Process.php');
require('../vendor/autoload.php');

function getBranchNames($sheetInfo) {
	$lnk       = new Process();
	$branchSql = "SELECT branchNum, branchName, _2DigNum, altName FROM branchInfo.branches";
	$branchQry = $lnk->query($branchSql);
	$count = 0;
	$count2 = 0;

	#echo count($branchQry) . "</br>";

	foreach ($branchQry as $z) {
		if ($z['_2DigNum']) {
			$twoDig[$z['_2DigNum']]  = $z['branchNum'];
		}
		if ($z['altName']) {
			$altName[$z['altName']] = strtolower($z['branchName']);
		}
		$names[strtolower($z['branchName'])] = $z['branchNum'];
		$branch[$z['branchNum']] = strtolower($z['branchName']);
	}
	#var_dump($sheetInfo);

	#var_dump($altName);

	foreach ($sheetInfo as $x => $y) {
		$count2++;
		#echo strlen($y[7]) . "</br>";
		$branchNum = $y[2];
		#var_dump($altName);
		if ($y[3] !== "***no name***") {
			$n = strtolower($y[3]);
			if (array_key_exists($n, $altName)) {
				$branchName = $altName[$n];

			} else {
				$branchName = $n;
			}
		} else {
			$branchName = strtolower($y[3]);
		}
		#echo $branchName . "</br>";
		if ($branchNum === "***NO NUM***") {
			if (isset($names[$branchName])) {
				$_3Dig = $names[$branchName];
			} else {
				$_3Dig = "***ERROR***";
				echo $branchName . " : " . $y[7] . "</br>";
			}
		} else {
			if (strlen($branchNum) < 3) {
				$_3Dig = $twoDig[$branchNum];
			} else {
				$_3Dig = $branchNum;
			}
		}
		#echo $_3Dig ."</br>";
		if ($_3Dig !== "***ERROR***") {
			$branchNameArray[] = $branch[$_3Dig];
		} else {
			$branchNameArray[] = '***ERROR***';
		}
		$branchNumArray[] = $_3Dig;
		$count ++;
	}

	#echo $count2 . " : " .$count . " : " . count($sheetInfo) . " : " . count($branchNameArray) . " : " . count($branchNumArray);

	if (count($sheetInfo) === count($branchNameArray)) {
		for ($i = 0; $i<count($sheetInfo); $i++) {
			$sheetInfo[$i][3] = ucwords($branchNameArray[$i]);
			$sheetInfo[$i][2] = $branchNumArray[$i];
		}
	}

	return $sheetInfo;
}

/**
 * @return array
 */
function reNameFiles($names) {
	$count = 1;
	foreach ($names as $x) {

		if (strlen($x) > 2) {
			$expName = explode(" ", $x);
			for ($j = 0; $j < count($expName); $j ++) {
				if ($expName[$j] === "") {
					array_splice($expName, $j, 1);
				}
			}
			$branchName = '***NO NAME***';
			$branchNum  = '***NO NUM***';
			$year       = '***ERROR***';
			$quarter    = '***ERROR***';
			$if         = "";
			if (count($expName) > 1) {

				if (preg_match('/_/', $expName[0])) {
					$if .= '[1]';
					$count ++;
					$dateMark = explode("_", $expName[0]);

					if (substr($dateMark[1], 0, 1) === 'Q' || substr($dateMark[1], 0, 1) === 'q') {
						$quarter = strtoupper(substr($dateMark[1], 0, 2));
						$if      .= '[2]';
						$count ++;

						if (strlen($expName[1] < 4)) {
							$branchNum = (int)$expName[1];
							$year      = (int)$expName[3];
							$if        .= '[3]';
							$count ++;
						} else {
							$year       = (int)$expName[1];
							$branchName = $expName[2];
							$if         .= '[4]';
							$count ++;
						}

						if (preg_match('/Operational/', $expName[2])) {
							$year = (int)$expName[1];
							$if   .= '[5]';
							$count ++;
							if (preg_match('/\d/', $expName[4])) {
								$branchNum = (int)$expName[4];
								$if        .= '[6]';
								$count ++;
							} else {
								$branchName = $expName[4];
								$if         .= '[7]';
								$count ++;
							}
						}
					} else {
						if (strlen($dateMark[1]) < 4) {
							$branchNum = (int)$dateMark[1];
							$if        .= '[8]';
							$count ++;
							if (substr($expName[1], 0, 1) === 'Q' || substr($expName[1], 0, 1) === 'q') {
								$quarter = strtoupper(substr($expName[1], 0, 2));
								$year    = (int)$expName[2];
								$if      .= '[9]';
								$count ++;
							} else {
								$quarter    = strtoupper(substr($expName[2], 0, 2));
								$year       = (int)$expName[3];
								$branchName = $expName[1];
								$if         .= '[10]';
								$count ++;
							}
						} else {
							$year    = (int)$dateMark[1];
							$quarter = strtoupper(substr($expName[1], 0, 2));
							$if      .= '[11]';
							$count ++;
							if (preg_match('/[\d]/', $expName[2])) {
								$branchNum = (int)$expName[2];
								$if        .= '[12]';
								$count ++;
							} else {
								$branchName = $expName[2];
								$if         .= '[13]';
								$count ++;
							}
						}
					}
				} elseif (preg_match('/Copy/', $expName[0])) {
					if (substr($expName[2], 0, 1) === 'Q' || substr($expName[2], 0, 1) === 'q') {
						$quarter = strtoupper(substr($expName[2], 0, 2));
						$if      .= '[14]';
						$count ++;
						if (preg_match('/[\d]/', $expName[3])) {
							$year      = (int)$expName[3];
							$branchNum = (int)$expName[4];
							$if        .= '[15]';
							$count ++;
						} else {
							$branchName = $expName[3];
							$year       = (int)substr($expName[4], 0, 4);
							$if         .= '[16]';
							$count ++;
						}
					} elseif (preg_match('/[\d]/', $expName[2])) {
						if (strlen($expName[2]) < 4) {
							$branchNum = (int)$expName[2];
							$if        .= '[17]';
							$count ++;
							if (substr($expName[2], 0, 1) === 'Q' || substr($expName[2], 0, 1) === 'q') {
								$quarter = strtoupper(substr($expName[2], 0, 2));
								$year    = (int)$expName[3];
								$if      .= '[18]';
								$count ++;
							} else {
								if (substr($expName[3], 0, 1) === 'Q' || substr($expName[3], 0, 1) === 'q') {
									$quarter = strtoupper(substr($expName[3], 0, 2));
									$year    = (int)$expName[4];
									$if      .= '[19]';
									$count ++;
								} else {
									$branchName = $expName[3];
									$quarter    = strtoupper(substr($expName[4], 0, 2));
									$year       = (int)$expName[5];
									$if         .= '[19(a)]';
									$count ++;
								}
							}
						} else {
							$year      = (int)$expName[2];
							$quarter   = strtoupper(substr($expName[3], 0, 2));
							$branchNum = (int)$expName[4];
							$if        .= '[20]';
							$count ++;
						}
					} else {
						$branchName = $expName [2];
						$quarter    = strtoupper(substr($expName[3], 0, 2));
						$year       = (int)substr($expName[4], 0, 4);
						$if         .= '[21]';
						$count ++;
					}
				} elseif (substr($expName[0], 0, 1) === 'Q' || substr($expName[0], 0, 1) === 'q') {
					$quarter = strtoupper(substr($expName[0], 0, 2));
					$year    = (int)$expName[1];
					$if      .= '[22]';
					$count ++;
					if (preg_match('/Operational/', $expName[2])) {

						if (preg_match('/[\d]/', $expName[4])) {
							$branchNum = (int)$expName[4];
							$if        .= '[23]';
							$count ++;
						} else {
							$branchName = $expName[4];
							$if         .= '[24]';
							$count ++;
						}
					} else {
						if (preg_match('/[0-9]/', $expName[2])) {
							$branchNum = (int)$expName[2];
							$if        .= '[25]';
							$count ++;
						} else {
							$branchName = $expName[2];
							$if         .= '[26]';
							$count ++;
						}
					}
				} elseif (preg_match('/[\d]/', $expName[0])) {
					if (strlen($expName[0]) < 4) {
						$branchNum = (int)$expName[0];
						$if        .= '[27]';
						$count ++;
						if (substr($expName[1], 0, 1) === 'Q' || substr($expName[1], 0, 1) === 'q') {
							$quarter = strtoupper(substr($expName[1], 0, 2));
							$year    = (int)$expName[2];
							$if      .= '[28]';
							$count ++;
						} else {
							$branchName = $expName[1];
							$quarter    = strtoupper(substr($expName[2], 0, 2));
							$year       = (int)$expName[3];
							$if         .= '[29]';
							$count ++;
						}
					} else {
						$year      = (int)$expName[0];
						$quarter   = strtoupper(substr($expName[1], 0, 2));
						$branchNum = (int)$expName[2];
						$if        .= '[30]';
						$count ++;
					}
				} else {
					$branchName = $expName[0];
					$quarter    = strtoupper(substr($expName[1], 0, 2));
					$year       = (int)$expName[2];
					$if         .= '[31]';
					$count ++;
				}
			}

			if(preg_match('/\./', $branchName)) {
				$q = explode('.', $branchName);
				$branchName = $q[0];
			}
			if(preg_match('/-/', $branchName)) {
				$q = explode('-', $branchName);
				$branchName = $q[0];
			}

			$branchNumTest = preg_match('/[0-9]/', $branchNum);
			$quarterTest   = substr($quarter, 0, 1) === 'Q';
			$yearTest      = strlen($year) === 4;
			$sheetInfo[]   = [$year,
				$quarter,
				$branchNum,
				$branchName,
				$yearTest,
				$quarterTest,
				$branchNumTest,
				$x,
				$if];
		}
	}

	return $sheetInfo;
}

/**
 * @param $sheetInfo array
 *
 * @return Spreadsheet
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */
function createSheet($sheetInfo) {

	$spreadSheet = new Spreadsheet();
	$spreadSheet->setActiveSheetIndex(0);
	$sheet = $spreadSheet->getActiveSheet();
	$headers = ['Year', 'Quarter', 'Number', 'Name', 'Year Test', 'Quarter Test', 'Number Test', 'File Name', 'Ifs'];
	$row = 1;

	for ($i = 1; $i<count($headers) + 1; $i++) {
		$sheet->setCellValueByColumnAndRow($i, $row, $headers[$i - 1]);
	}
	$row++;

	foreach ($sheetInfo as $a => $x) {
		for ($k = 0; $k < count($x); $k++) {
			$sheet->setCellValueByColumnAndRow($k + 1, $row, $x[$k]);
		}
		$row++;
	}

	return $spreadSheet;
}

/**
 * @param $sheet Spreadsheet
 *
 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
 */
function writeSheet($sheet) {
	$file = '../output/reFmts/renamedFiles.xls';
	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($sheet, "Xls");
	$writer->save($file);

	echo '<a href="' . $file . '" download><button class="ui-corner-all ui-button">Output File</button></a>';
}

function changedName($dir, $names, $fileInfo) {
	$newDir = "C:\Users\scrip\OneDrive - Jetro Holdings LLC\Audits - Schedules\changedNames";

	for($i=0; $i < count($names); $i++) {
		$newFile = $fileInfo[$i][0]. " " . $fileInfo[$i][1] . " " . $fileInfo[$i][2] . " " . $fileInfo[$i][3] . ".xls";

		$path = $dir."\\".$names[$i];
		$newPath = $newDir."\\". $newFile;

		if(!copy($path, $newPath)) {
			echo $fileInfo[$i][2] . " : " . $fileInfo[$i][3] . "</br>";
		};

		#echo $path . " -> " . $newFile . "</br>";
	}

}

$dir = "C:\Users\scrip\OneDrive - Jetro Holdings LLC\Audits - Schedules\Q4 2019 Corporate Operational Audit West Coast - Copy";
$names = scandir($dir);

array_splice($names, 0, 2);

$fileInfo = reNameFiles($names);
$fileInfo = getBranchNames($fileInfo);
changedName($dir, $names, $fileInfo);
$sheet    = createSheet($fileInfo);
writeSheet($sheet);
