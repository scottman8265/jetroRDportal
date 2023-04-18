<?php

ini_set('memory_limit', '2048M');
ini_set('max_execution_time', '1500');

echo "test";

require_once '../class/Process.php';
require_once '../vendor/autoload.php';
require_once '../class/jcmsReadFilter.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$file  = isset($_POST['file']) ? '../input/jcms/' . $_POST['file'] : '../input/jcms/Employee Tests 032221 raw.xlsx';
$lnk   = new Process();
$count = 0;

$test = isset($_POST['test']) ? $_POST['test'] : 'Admin Test ';

function loadSheet($file, $sheet) {
	$myFilter = new jcmsReadFilter();
	try {
		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader("Xlsx");
		#$reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
		$reader->setReadDataOnly(true);
		$reader->setLoadSheetsOnly($sheet);
		$reader->setReadFilter($myFilter);
		$spreadSheet = $reader->load($file);

		#$spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
		return $spreadSheet;
	} catch (Exception $e) {
		#die('Error Loading File: ' . $e->getMessage());
		echo "Error";
	}

}

/**
 * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
 * @param $highRow
 *
 * @return array
 * @throws \Exception
 */
function getData($sheet, $highRow) {

	$row = 8;
	$arr = [];

	for ($i = $row; $i < ($highRow + 1); $i ++) {

		$testDateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($sheet->getCellByColumnAndRow(10, $i)->getValue());
		$testDate = $testDateObj->format('Y-m-d');

		if ($testDate != '1970-01-01') {
			$tmID    = $sheet->getCellByColumnAndRow(1, $i)->getValue();
			$tmName  = $sheet->getCellByColumnAndRow(2, $i)->getValue();
			$jobCode = $sheet->getCellByColumnAndRow(3, $i)->getValue();
			$posDateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($sheet->getCellByColumnAndRow(5, $i)->getValue());
			$posDate = $posDateObj->format('Y-m-d');
			$branch  = $sheet->getCellByColumnAndRow(6, $i)->getValue();
			$test    = trim($sheet->getCellByColumnAndRow(8, $i)->getValue());
			$score   = abs($sheet->getCellByColumnAndRow(9, $i)->getValue());

			$test = str_replace(' Test', '', $test);

			$score = $score > 100 ? 100 : $score;

			$insertParams[] = [$tmID, $tmName, $jobCode, $posDate, $branch, $test, $testDate, $score];
			#echo $tmID ." : ". $tmName ." : ". $jobCode ." : ". $sqlPosDate ." : ". $branch ." : ". $test ." : ". $sqlTestDate ." : ". $score . "</br>";
		}
	}

	return $insertParams;
}

function writeData($params) {

	$lnk    = new Process();
	$errors = 0;
	$good   = 0;

	$insertSql = "INSERT INTO staffing.jcmsTests (tmID, tmName, jobCode, posDate, branch, test, dateTaken, score) 
							VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
	foreach ($params as $param) {

		$param = checkData($param);

		if ($param) {
			if ($lnk->query($insertSql, $param)) {
				$good ++;
			}
			else {
				$errors ++;
			}
		}
	}

	return "[good: " . $good . "][errors: " . $errors . "]";

}

function checkData($param) {
	#var_dump ($param);

	$lnk = new Process();

	$empID = $param[0];
	$test = $param[5];
	$taken = $param[6];
	$score = $param[7];

	$checkSql = "SELECT jcmsID, dateTaken FROM staffing.jcmsTests WHERE tmID = ? && test = ?";
	$checkParams = [$empID, $test];
	$checkQry = $lnk->query($checkSql, $checkParams);

	if (!$checkQry) {
		return $param;
	} else {
		$dbLineID = $checkQry[0]['jcmsID'];
		$dbTaken = $checkQry[0]['dateTaken'];

		if ($dbTaken != $taken) {
			$lnk->query("DELETE FROM staffing.jcmsTests WHERE jcmsID = " . $dbLineID);
			return $param;
		} else {
			return null;
		}
	}

}

$spreadSheet = loadSheet($file, $test);
$sheet       = $spreadSheet->getActiveSheet();
$highRow     = $sheet->getHighestRow();
$highCol     = $sheet->getHighestColumn();

$params  = getData($sheet, $highRow);
$written = writeData($params);
#$written = null;

echo "</br>[test: " . $test . "][rows: " . $highRow . "][params: " . count($params) . "][written: " . $written . "]</br>";

unset($spreadSheet);
unset($sheet);
unset($highRow);




