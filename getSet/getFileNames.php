<?php

session_start();

$fileType = $_POST['fileType'];
$path = [];

switch ($fileType) {
    case 'self':
        $dir = 'input/auditsSelf';
        break;
    case 'audits':
        $dir = 'input/auditsCorp';
        break;
    case 'branchCounts':
        $dir = 'input/branchCounts';
        break;
    case 'cycleCounts':
        $dir = 'input/masterCounts';
        break;
        break;
    case 'jcmsTesting':
        $dir = 'input/jcms';
        break;
    default:
        $dir = null;
        break;
}

console.log($dir);

$files = scandir("../" . $dir);
$dirLength = count_chars($dir);

foreach ($files as $file) {
    if ($file !== "." && $file !== "..") {
        #$path[] = "../" . $dir . "/" . $file;
        $path[] = $dir . "/" . $file;
        #$path[] = $dir . "/" . $file;
    }
}

echo json_encode(['fileNames' => $path, 'dirLength' => $dirLength]);