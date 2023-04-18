<?php

ini_set('memory_limit', '2048M');


require_once '../class/Process.php';
require_once '../vendor/autoload.php';
require_once '../util/cleanInput.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$file = '../io/input/Combined Manager Rankings 09 2020.xlsx';
$lnk = new Process();
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

	$col = 2;
	$arr = [];
	$mrBranch = (int)$branch;

	for ($i = $col; $i < 18; $i++) {
		$mrPos    = getPosNum($sheet->getCellByColumnAndRow($i, 1)->getValue());
		$mrName = clean($sheet->getCellByColumnAndRow($i, 3)->getValue());
		$mrDate = preg_replace('/\\\\/', "/", $sheet->getCellByColumnAndRow($i, 2)->getFormattedValue());
		$mrLdshp = (int)$sheet->getCellByColumnAndRow($i, 4)->getValue();
		$mrMulti = (int)$sheet->getCellByColumnAndRow($i, 5)->getValue();
		$mrPrior = (int)$sheet->getCellByColumnAndRow($i, 6)->getValue();
		$mrMngPeo =(int)$sheet->getCellByColumnAndRow($i, 7)->getValue();
		$mrPride = (int)$sheet->getCellByColumnAndRow($i, 8)->getValue();
		$mrCusSer = (int)$sheet->getCellByColumnAndRow($i, 9)->getValue();
		$mrProc = (int)$sheet->getCellByColumnAndRow($i, 10)->getValue();
		$mrExec = (int)$sheet->getCellByColumnAndRow($i, 11)->getValue();
		$mrKnow = (int)$sheet->getCellByColumnAndRow($i, 12)->getValue();
		$mrCommu = (int)$sheet->getCellByColumnAndRow($i, 13)->getValue();
		$mrComment = clean($sheet->getCellByColumnAndRow($i, 15)->getValue());

		$test = array_sum([$mrLdshp, $mrMulti, $mrPrior, $mrMngPeo, $mrPride, $mrCusSer, $mrProc, $mrExec, $mrKnow, $mrCommu]);
		#echo $test . "</br>";
		if ($test > 0) {
			$arr[] = [$mrBranch,
			          $mrPos,
			          $mrName,
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
			          $mrComment];
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

$data = [];
$insert = [];
$insertSql = "INSERT INTO staffing.mrRankings (mrBranch, mrPos, mrName, mrDate, mrLdshp, mrMulti, mrPrior, mrMngPeo, mrPride, mrCusSer, mrProc, mrExec, mrKnow, mrCommu, mrComment) VALUES";
$spreadSheet = loadSheet($file);
$sheetNames = $spreadSheet->getSheetNames();

var_dump($sheetNames);

/*foreach ($sheetNames as $name) {
	$spreadSheet->setActiveSheetIndexByName($name);
	$sheet = $spreadSheet->getActiveSheet();
	$data[] = getData($sheet, $name);
}*/

/*foreach ($data as $x) {
	foreach ($x as $y) {
		try {
			$lnk->query($insertSql . "(" . $y[0] . ", " . $y[1] . ", '" . $y[2] . "', '" . $y[3] . "', " . $y[4] . ", " . $y[5] . ", " . $y[6] . ", " . $y[7] . ", " . $y[8] . ", " . $y[9] . ", " . $y[10] . ", " . $y[11] . ", " . $y[12] . ", " . $y[13] . ", '" . $y[14] . "')");
		} catch (\Exception $e) {
			echo "failed at record " . $count ."</br></br>";
		}
	}
}*/
