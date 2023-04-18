<?php

session_start();

use PhpOffice\PhpSpreadsheet\Reader\Exception;

require_once '../inc/readFileFunc.php';
require_once '../class/Arrays.php';
require_once '../class/Process.php';
require_once '../vendor/autoload.php';

$files = scandir('../input/jcms');

$count = 0;

print_r($files);

foreach ($files as $file) {
    if (strlen($file) > 2) {
        $sheet = readFileData($file, '.xls', true);
        $count++;
    }
}

echo $count;