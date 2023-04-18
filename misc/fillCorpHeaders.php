<?php

set_time_limit(300);
ini_set("log_errors", 1);
ini_set("error_log", "logs/php-error.log" . date('ymd'));
ini_set('memory_limit', '1GF');
ini_set('max_execution_time', '1000');
date_default_timezone_set('America/Chicago');

require_once '../inc/readFileFunc.php';
require_once '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Reader\Exception;

#$dir = '../input/auditsCorp';

$file = isset($_POST) ? $_POST : "broke";

var_dump($file);

$pieces = explode(" ", $file['fileName']);

$branch  = (int)$pieces[3];
$year    = (int)$pieces[0];
$quarter = (int)$pieces[1][1];
#$type = $pieces[2];

$path = "../input/auditsCorp/" . $file['fileName'];

if ($file !== 'broke') {
	$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($path);
	$reader->setReadDataOnly(false);
	$spreadSheet = $reader->load($path);
	echo $path . " -- read</br>";

	$spreadSheet->setActiveSheetIndexByName('OPS AUDIT RECAP');
	$sht = $spreadSheet->getActiveSheet();

	$sht->setCellValue("V577", $branch);
	$sht->setCellValue("V578", $year);
	$sht->setCellValue("V579", $quarter);
	$sht->setCellValue("V1", '');
	$sht->setCellValue("W1", '');


	$outPath = "../output/auditsCorp/" . $file['fileName'] . "x";

	#echo $outPath . "</br>";

	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
	$writer->save($outPath);

}

var_dump($file);