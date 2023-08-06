<?php

ini_set('max_execution_time', 300);

require_once '../class/Process.php';

$file = '../input/mwSafetyTips.csv';
$lnk = new Process();

$sql = "INSERT INTO staffing.wklySafetyTips (bNum, empID, weekNum) VALUES (?, ?, ?)";

if (($h = fopen($file, "r")) !== false) {
	while (($data = fgetcsv($h, 100, ",")) !== false) {
		#var_dump($data);
		for ($i = 1; $i < count($data); $i++) {
			if ($data[$i] == 'X') {
				$params = [$data[0], '9999', $i];
				$lnk->query($sql, $params);
			}
		}
	}
}
