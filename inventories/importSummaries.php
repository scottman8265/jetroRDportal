<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '1500');

echo "test";

require_once '../class/Process.php';
require_once '../vendor/autoload.php';
require_once '../class/inventoryReadFilter.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$file  = isset($_POST['file']) ? '../input/inventories/' . $_POST['file'] : '../input/inventories/Company WE 011619.xlsx';
$lnk   = new Process();
$count = 0;

function loadSheet($file) {
	$myFilter = new InventoryReadFilter();
	try {
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
		#$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		$reader->setReadDataOnly(true);
		$reader->setReadFilter($myFilter);
		$spreadSheet = $reader->load($file);

		return $spreadSheet;
	} catch (Exception $e) {
		die('Error Loading File: ' . $e->getMessage());
	}

}

function cleanVar($var) {
	return trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $var));
}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @param $highCol
 *
 * @param $branchNums
 *
 * @param $invDate
 *
 * @return array
 */
function getData($sheet, $highCol, $branchNums, $invDate) {

	$col = 1;
	$arr = [];
	$noBranchNum = [];

	for ($i = $col; $i < 26; $i ++) {

		$branch = cleanVar($sheet->getCellByColumnAndRow($i, 1)->getValue());

		if (strlen($branch) > 5) {
			$invCount    = cleanVar($sheet->getCellByColumnAndRow($i, 3)->getValue());
			$invShrink   = cleanVar($sheet->getCellByColumnAndRow($i, 5)->getValue());
			$interim     = cleanVar($sheet->getCellByColumnAndRow($i, 8)->getValue());
			$grossShrink = cleanVar($sheet->getCellByColumnAndRow($i, 9)->getValue());
			$apShrink    = cleanVar($sheet->getCellByColumnAndRow($i, 10)->getValue());
			$bottleDep   = cleanVar($sheet->getCellByColumnAndRow($i, 11)->getValue());
			$negRec      = cleanVar($sheet->getCellByColumnAndRow($i, 12)->getValue());
			$recCor      = cleanVar($sheet->getCellByColumnAndRow($i, 13)->getValue());
			$netShrink   = cleanVar($sheet->getCellByColumnAndRow($i, 14)->getValue());
			$shrinkPer   = cleanVar($sheet->getCellByColumnAndRow($i, 16)->getValue());
			$grossDam    = cleanVar($sheet->getCellByColumnAndRow($i, 20)->getValue());
			$damSale     = cleanVar($sheet->getCellByColumnAndRow($i, 21)->getValue());
			$avs         = cleanVar($sheet->getCellByColumnAndRow($i, 22)->getValue());
			$netDam      = cleanVar($sheet->getCellByColumnAndRow($i, 24)->getValue());
			$damPer      = cleanVar($sheet->getCellByColumnAndRow($i, 25)->getValue());
			$intDamPer   = cleanVar($sheet->getCellByColumnAndRow($i, 28)->getValue());

			var_dump($apShrink[0]);

			$branchSplit = explode(" - ", $branch);
			$branchName  = trim($branchSplit[0]);
			echo "</br>".$branchName . "</br>";

			if ($branchNums[trim($branchName)]) {
				$branchNum = $branchNums[$branchName];
			} else {
				$noBranchNum[] = $branchName;
				$branchNum = null;
			}

			$arr[] = [$branchNum,
			          $branchName,
			          $invDate->format('Y-m-d'),
			          $invCount,
			          $invShrink,
			          $interim,
			          $grossShrink,
			          $apShrink,
			          $bottleDep,
			          $negRec,
			          $recCor,
			          $netShrink,
			          $shrinkPer,
			          $grossDam,
			          $damSale,
			          $avs,
			          $netDam,
			          $damPer,
			          $intDamPer];

		}
	}

	if (!empty($noBranchNum)) {
		var_dump($noBranchNum);
	}

	var_dump($arr);

	return $arr;
}

function getBranchNums() {
	$lnk = new Process();
	$qry = $lnk->query("SELECT branchNum, branchName, _2DigNum FROM branchInfo.branches");
	$arr = [];

	foreach ($qry as $x) {
		if (is_null($x['_2DigNum'])) {
			$arr[$x['branchName']] = $x['branchNum'];
		}
		else {
			$arr[$x['branchName']] = $x['_2DigNum'];
		}
	}

	return $arr;
}

function writeData($params) {

	$lnk    = new Process();
	$errors = 0;
	$good   = 0;

	$insertSql = "INSERT INTO branchInfo.inventories (bNum, bName, invDate, invCount, invShrink, interims, grossShrink, apShrink, botDep, negRec, recCor, netShrink, shrinkPer, grossDam, damSales, avs, netDam, damPer, intDamPer) 
							VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
	foreach ($params as $param) {
		if ($lnk->query($insertSql, $param)) {
			$good ++;
		}
		else {
			$errors ++;
		}
	}

	return "[good: " . $good . "][errors: " . $errors . "]";

}

function getDateFromCode($dateCode) {
	echo $dateCode;
	$month = substr($dateCode, 0, 2);
	$day   = substr($dateCode, 2, 2);
	$year  = substr($dateCode, 4, 2);

	return new DateTime($month . "/" . $day . "/" . $year);
}

$spreadSheet = loadSheet($file);
$sheet       = $spreadSheet->getActiveSheet();
$highRow     = $sheet->getHighestRow();
$highCol     = $sheet->getHighestColumn();
$dateCode    = substr($file, strpos($file, '.xlsx') - 6, 6);
$invDate     = getDateFromCode($dateCode);

$branchNums = getBranchNums();
$params     = getData($sheet, $highCol, $branchNums, $invDate);
$written    = writeData($params);
#$written = null;

$fileSplit = explode($file);
$fileName = $fileSplit[3];

$lnk->query("INSERT INTO trackers.processedFiles (fileType, fileName) VALUES ('inv', fileName)");

echo "</br>[file: " . $fileName . "][col: " . $highCol . "][params: " . count($params) . "][written: " . $written . "]</br>";

unset($spreadSheet);
unset($sheet);
unset($highRow);




