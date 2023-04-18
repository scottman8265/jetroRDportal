<?php

$dir = '../input/auditsSelf';

#echo $dir;

$files = scandir($dir);

foreach ($files as $x) {
	if ($x !== '.' || $x !== '..') {
		$fileNames[] = $x;
	}
}

echo json_encode($fileNames);
