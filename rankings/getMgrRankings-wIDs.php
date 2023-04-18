<?php

/**
 * make sure rows are picking up correctly in getData function
 * make sure year is set correctly on line 41 in getData function
 * make sure file name is correct on line 14
 */

require_once '../class/Process.php';
require_once '../vendor/autoload.php';
require_once '../util/cleanInput.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$file  = '../io/input/AC Manager Ranking 2022.xlsx';
$lnk   = new Process();
$count = 0;

function loadSheet($file) {
	try {
		$reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
		$spreadSheet = $reader->load($file);
		echo "Properly Read File</br>";

		return $spreadSheet;
	} catch (Exception $e) {
		echo $e->getMessage();
	}

}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @param $branch
 */
function getData($sheet, $branch) {

	$row     = 9;
	$arr      = [];
	$mrBranch = (int)$branch;
	$mrYear   = 2022;
	$mrHalf = 1;

	for ($i = $row; $i < 200; $i ++) {
		$mrPos     = $sheet->getCellByColumnAndRow(5, $i)->getValue();
		if (strlen($mrPos) > 5) {
			$mrName    = clean($sheet->getCellByColumnAndRow(6, $i)->getValue());
			$mrDate    = $sheet->getCellByColumnAndRow(8, $i)->getFormattedValue();
			$mrEmpID   = $sheet->getCellByColumnAndRow(7, $i)->getValue();
			$mrLdshp   = (int)$sheet->getCellByColumnAndRow(9, $i)->getValue();
			$mrMulti   = (int)$sheet->getCellByColumnAndRow(10, $i)->getValue();
			$mrPrior   = (int)$sheet->getCellByColumnAndRow(11, $i)->getValue();
			$mrMngPeo  = (int)$sheet->getCellByColumnAndRow(12, $i)->getValue();
			$mrPride   = (int)$sheet->getCellByColumnAndRow(13, $i)->getValue();
			$mrCusSer  = (int)$sheet->getCellByColumnAndRow(14, $i)->getValue();
			$mrProc    = (int)$sheet->getCellByColumnAndRow(15, $i)->getValue();
			$mrExec    = (int)$sheet->getCellByColumnAndRow(16, $i)->getValue();
			$mrKnow    = (int)$sheet->getCellByColumnAndRow(17, $i)->getValue();
			$mrCommu   = (int)$sheet->getCellByColumnAndRow(18, $i)->getValue();
			$mrComment = clean($sheet->getCellByColumnAndRow(20, $i)->getValue());
			$test      = array_sum([$mrLdshp,
				$mrMulti,
				$mrPrior,
				$mrMngPeo,
				$mrPride,
				$mrCusSer,
				$mrProc,
				$mrExec,
				$mrKnow,
				$mrCommu]);#echo $test . "</br>";
			if ($test > 0) {
				$mrTotal = number_format($test / 10, 2);
				$arr[]   = [$mrYear,
					$mrHalf,
					$mrBranch,
					$mrPos,
					$mrName,
					$mrEmpID,
					$mrDate,
					$mrLdshp,
					$mrMulti,
					$mrPrior,
					$mrMngPeo,
					$mrPride,
					$mrCusSer,
					$mrProc,
					$mrExec,
					$mrKnow,
					$mrCommu,
					$mrTotal,
					$mrComment];
			} else {
				echo "no score for [" . $mrBranch . "][" . $mrPos . "][" . $test . "]" . "</br>";
			}
		}
	}

	return $arr;
}

function getPosNum($pos) {
	switch ($pos) {
		case "BM":
			return 136;
		case "ABM":
			return 126;
		case "IC":
			return 151;
		case "PERISHABLE":
			return 191;
		case "DAIRY":
			return 139;
		case "MEAT":
			return 152;
		case "PRODUCE":
			return 156;
		case "SEAFOOD":
			return 163;
		case "FLOOR":
			return 143;
		case "RECEIVING":
			return 159;
		case "HRADMIN":
			return 176;
		case "SMALLWARES":
			return 165;
		case "CASHROOM":
			return 137;
		case "FRONT END":
			return 148;
	}
}

$data        = [];
$insert      = [];
$insertSql   = "INSERT INTO staffing.mrRankings (mrYear, mrHalf, mrBranch, mrPos, mrName, mrEmpID, mrDate, mrLdshp, mrMulti, mrPrior, mrMngPeo, mrPride, mrCusSer, mrProc, mrExec, mrKnow, mrCommu, mrTotal, mrComment) VALUES ";
$spreadSheet = loadSheet($file);
$sheetNames  = $spreadSheet->getSheetNames();
$record      = 0;

foreach ($sheetNames as $name) {
	$spreadSheet->setActiveSheetIndexByName($name);
	$sheet  = $spreadSheet->getActiveSheet();
	$data[] = getData($sheet, $name);
}

foreach ($data as $x) {
	foreach ($x as $y) {
		#var_dump($y);
		$record++;
		try {
			$lnk->query($insertSql . "(" . $y[0] . ", " . $y[1] . ", " . $y[2] . ", '" . $y[3] . "', '" . $y[4] . "', '" . $y[5] . "', '" . $y[6] . "', " . $y[7] . ", " . $y[8] . ", " . $y[9] . ", " . $y[10] . ", " . $y[11] . ", " . $y[12] . ", " . $y[13] . ", " . $y[14] . ", " . $y[15] . ", " . $y[16] . ", " . $y[17] .", '" . $y[18] ."')");
		} catch (\Exception $e) {
			#echo "failed at record " . $record . "</br></br>";
			echo $e->getMessage() . "</br>";
		}
	}
}
$time = new DateTime();

echo "</br></br>File Upload Complete!!!!  ---  " . $time->format('m/d/y h:i:s');
