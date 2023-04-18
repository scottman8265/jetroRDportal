<?php

set_time_limit(300);
ini_set("log_errors", 1);
ini_set("error_log", "logs/php-error.log" . date('ymd'));
ini_set('memory_limit', '1G');
ini_set('max_execution_time', '1000');
date_default_timezone_set('America/Chicago');

require_once '../inc/readFileFunc.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

$dir = '../input/auditsSelf';

$file = isset($_POST) ? $_POST : "broke";

var_dump($file);

$pieces = explode(" ", $file['fileName']);

$branch = (int)$pieces[3];
$year = (int)$pieces[0];
$quarter = (int)$pieces[1][1];
$type = $pieces[2];

$path = $dir . "/" . $file['fileName'];

if ($file !== 'broke') {
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
	$reader->setReadDataOnly(false);
	#$reader->setLoadSheetsOnly('OPS AUDIT RECAP');
	$spreadSheet = $reader->load($path);
	echo $path . " -- read</br>";

	$spreadSheet->setActiveSheetIndexByName('OPS AUDIT RECAP');
	$sht = $spreadSheet->getActiveSheet();

	$sht->setCellValue("V585", $branch);
	$sht->setCellValue("V586", $year);
	$sht->setCellValue("V587", $quarter);
	$sht->setCellValue("V588", 'Self');
	$sht->setCellValue("V1", '');
	$sht->setCellValue("W1", '');

	$outPath = "../output/auditsSelf/".$file['fileName']."x";

	echo $outPath . "</br>";

	/*$clonedSheet = clone $spreadSheet->getSheetByName('OPS AUDIT RECAP');
	$newSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$newSheet->addExternalSheet($clonedSheet);
	$index = $newSheet->getIndex($newSheet->getSheetByName('Worksheet'));
	$newSheet->removeSheetByIndex($index);*/


	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
	$writer->save($outPath);

}

var_dump($file);