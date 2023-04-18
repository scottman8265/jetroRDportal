<?php

set_time_limit(1200);

require_once '../vendor/autoload.php';
require_once '../class/Process.php';

function getDateFromCode($dateCode) {
	$month = substr($dateCode, 0, 2);
	$day   = substr($dateCode, 2, 2);
	$year  = substr($dateCode, 4, 2);

	return new DateTime($month . "/" . $day . "/" . $year);
}

function readFileToArray($path) {
	if ($xlsx = SimpleXLSX::parse($path)) {
		echo "file read: " . $path . "</br>";

		return $xlsx;
	}
	else {
		die("error: " . SimpleXLSX::parseError());
	}
}

function getAODarray($lnk) {

	$arr = [];

	$aodArray = $lnk->query("SELECT * FROM staffing.aodData WHERE aodDateOut IS NULL");

	if (!$aodArray) {
		return null;
	}
	else {
		foreach ($aodArray as $line) {
			$arr[$line['aodEmpID']] = ['lineID' => $line['aodID'],
			                           'check'  => $line['aodJobID'] . ":" . $line['aodLocation']];
		}
	}


	return $arr;
}

function insertNew($lnk, $params) {
	$insertNewSql = "INSERT INTO staffing.aodData (aodEmpID, aodJobID, aodFname, aodLname, aodLocation, aodDateIn, aodHiredDate) VALUES (?, ?, ?, ?, ?, ?, ?)";
	$lnk->query($insertNewSql, $params);
}

function updateOld($lnk, $params) {
	$updateSql = "UPDATE staffing.aodData SET aodDateOut = ?, outStatus = ? WHERE aodID = ?";
	$lnk->query($updateSql, $params);
}

function chkFile($lnk, $file) {
	$fileChkSql    = "SELECT fileID FROM trackers.processedFiles WHERE fileName = ?";
	$fileChkParams = [$file];
	$fileChkQry    = $lnk->query($fileChkSql, $fileChkParams);

	return $fileChkQry ? false : true;
}

$file     = $_POST['file'];
$path     = '../input/aodFiles/' . $file;
$dateCode = substr($file, strpos($file, '.') - 6, 6);
$lnk      = new Process();

$count = $update = $new = $old = 0;

$xlsx    = null;
$aodArr  = [];
$newFile = chkFile($lnk, $file);

if ($newFile) {
	$date    = getDateFromCode($dateCode);
	$inDate  = $date->format('Y-m-d');
	$outDate = $date->sub(new DateInterval('P1D'))->format('Y-m-d');
	$xlsx    = readFileToArray($path);
	$records = count($xlsx->rows());

	$aodArr = getAODarray($lnk);

	#var_dump($aodArr);

	foreach ($xlsx->rows() as $fields) {
		if ($count > 1) {
			$empID    = $fields[0];
			$jobID    = $fields[1];
			$fName    = $fields[3];
			$lName    = $fields[4];
			$location = $fields[7];
			$hired = isset($fields[8]) ? new DateTime($fields[8]) : null;
			$sqlHired = !is_null($hired) ? $hired->format("Y-m-d") : $hired;

			echo $file . ": (" . $records . " lines) " . "[empID: " . $empID . "][arraySearch: *]</br>";

			if (isset($aodArr[$empID])) {
				if ($aodArr[$empID]['check'] != $jobID . ":" . $location) {
					insertNew($lnk, [$empID, $jobID, $fName, $lName, $location, $inDate, $sqlHired]);
					updateOld($lnk, [$outDate, 'c', $aodArr[$empID]['lineID']]);
					#echo "Update from [" . $file . "][empID: " . $empID . "][location: ".$location."]</br>";
					$update ++;
				}
				unset($aodArr[$empID]);
			}
			else {
				insertNew($lnk, [$empID, $jobID, $fName, $lName, $location, $inDate, $sqlHired]);
				$new ++;
				#echo "New from [" . $file . "][empID: " . $empID . "][location: ".$location."]</br>";
			}
		}
		$count ++;
	}

	foreach ($aodArr as $tmID => $data) {
		$old ++;
		updateOld($lnk, [$outDate, 't', $data['lineID']]);
		#echo "Old from [" . $file . "][empID: " . $tmID . "][lineID: " . $data['lineID']."][location: ".$location."]</br>";
	}
}

$trackerUpdated = $lnk->query("INSERT INTO trackers.processedFiles (fileType, fileName) VALUES ('aod', ?)", [$file]);

echo "[NEW: " . $new . "][UPDATE: " . $update . "][OLD: " . $old . "]</br>";