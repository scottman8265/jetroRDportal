<?php

session_start();

$wkNum = $_SESSION['wkNum'];

$fileToMove = $_SESSION['update'] === 'false' ? '../output/cycleCountLogs/masterLogs/2019 Cycle Count Master - wkNum ' . ($wkNum - 1) . '.xlsx'
    : '../output/cycleCountLogs/masterLogs/2019 Cycle Count Master - wkNum ' . $wkNum . '.xlsx';
$movedFile = '../input/2019 Cycle Count Master.xlsx';

copy($fileToMove, $movedFile);

return json_encode(['fileType' => 'fileUpload', 'fileName' => $movedFile]);
