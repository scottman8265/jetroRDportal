<?php

$dir = 'C:\Users\Scott\Desktop\wc audits';

if ($handle = opendir($dir)) {
	while (false !== ($fileName = readdir($handle))) {
		if ($fileName !== "." && $fileName !== "..") {
			$newName = substr($fileName, 14);
			echo $newName . "</br>";
			copy($dir . "\\" . $fileName, $dir . "\\" . $newName);
		}
	}
	closedir($handle);
}
