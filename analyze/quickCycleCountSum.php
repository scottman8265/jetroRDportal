<?php

session_start();

require_once("../class/Process.php");
require_once('../vendor/autoload.php');
require_once('../class/Format.php');

$wkNum = date("W", strtotime("last saturday"));
#$wkNum = 26;

$wkLnk = new Process();
#$wkSql = "SELECT * FROM cyclecounts.branchwk WHERE wkNum = " . $wkNum;
$wkSql = "SELECT * FROM cyclecounts.enteredcounts WHERE deptID = 'wkPer' AND wkNum = " . $wkNum;
$wkQry = $wkLnk->query($wkSql);
$wkCount = $wkLnk->getQryCount();

$ytdLnk = new Process();
#$ytdSql = "SELECT * FROM cyclecounts.ytdper";
$yrSql = "SELECT * FROM cyclecounts.enteredcounts WHERE deptID = 'yrPer' AND wkNum = " . $wkNum;
$yrQry = $ytdLnk->query($yrSql);
$ytdCount = $ytdLnk->getQryCount();

$branchLnk = new Process();
$branchSql = "SELECT branchNum, branchName FROM branchinfo.branches";
$branchQry = $branchLnk->query($branchSql);
foreach ($branchQry as $info) {
    $branches[$info['branchNum']] = $info['branchName'];
}

$iniGroups = ['perfect', 'under95', 'under90', 'under85', 'under75', 'under50', 'under25'];

foreach ($iniGroups as $ini) {
    $weekPer[$ini] = [];
    $ytdPer[$ini] = [];
}

$compCount = 0;

arsort($wkQry[0]);
arsort($yrQry[0]);

foreach ($wkQry[0] as $wkKey => $wkValue) {

    if (preg_match('/_/', $wkKey)) {

        $wkPercent = $wkValue;
        $branch = substr($wkKey, 1);
        switch (true) {
            case $wkPercent == 1 || $wkPercent > .95:
                $weekPer['perfect'][] = ['branch' => $branch, 'percent' => $wkPercent];
                break;
            case $wkPercent < .25:
                $weekPer['under25'][] = ['branch' => $branch, 'percent' => $wkPercent];
                break;
            case $wkPercent < .50:
                $weekPer['under50'][] = ['branch' => $branch, 'percent' => $wkPercent];
                break;
            case $wkPercent < .75:
                $weekPer['under75'][] = ['branch' => $branch, 'percent' => $wkPercent];
                break;
            case $wkPercent < .85:
                $weekPer['under85'][] = ['branch' => $branch, 'percent' => $wkPercent];
                break;
            case $wkPercent < .90:
                $weekPer['under90'][] = ['branch' => $branch, 'percent' => $wkPercent];
                break;
            case $wkPercent < .95:
                $weekPer['under95'][] = ['branch' => $branch, 'percent' => $wkPercent];
                break;

        }
    }
}

/*foreach ($weekPer as $group => $branches) {
    $count = count($branches['branches']);
    $weekPer[$group]['percent'] = number_format($count / $wkCount, 4);
}*/

foreach ($yrQry[0] as $yrKey => $yrValue) {


    if (preg_match('/_/', $yrKey)) {
        $percent = $yrValue;
        $branch = substr($yrKey, 1);
        switch (true) {
            case $percent == 1 || $percent > .95:
                $ytdPer['perfect'][] = ['branch' => $branch, 'percent' => $percent];
                break;
            case $percent < .25:
                $ytdPer['under25'][] = ['branch' => $branch, 'percent' => $percent];
                break;
            case $percent < .50:
                $ytdPer['under50'][] = ['branch' => $branch, 'percent' => $percent];
                break;
            case $percent < .745:
                $ytdPer['under75'][] = ['branch' => $branch, 'percent' => $percent];
                break;
            case $percent < .85:
                $ytdPer['under85'][] = ['branch' => $branch, 'percent' => $percent];
                break;
            case $percent < .90:
                $ytdPer['under90'][] = ['branch' => $branch, 'percent' => $percent];
                break;
            case $percent < .95:
                $ytdPer['under95'][] = ['branch' => $branch, 'percent' => $percent];
                break;

        }
    }
}

var_dump($bra);

/*foreach ($ytdPer as $xgroup => $xbranches) {
    $count = count($xbranches['branches']);
    $ytdPer[$xgroup]['percent'] = number_format($count / $wkCount, 4);
}*/

$formats = new Format();
$letters = $formats->getLetters();

$lastCol = 21;

$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$sheet = $spreadSheet->getActiveSheet();

$sheet->setCellValueByColumnAndRow(1, 1, 'Weekly & YTD Completion Percentages');

$format['merge'][] = "A1:" . $letters[$lastCol] . "1";
$format['hCenter'][] = "A1:" . $letters[$lastCol] . "1";
$format['size22'][] = "A1:" . $letters[$lastCol] . "1";

$sheet->setCellValueByColumnAndRow(1, 3, 'Weekly Percent Completion');
$format['merge'][] = "A3:" . $letters[$lastCol] . "3";
$format['hCenter'][] = "A3:" . $letters[$lastCol] . "3";
$format['size18'][] = "A3:" . $letters[$lastCol] . "3";
$format['outline'][] = "A3:" . $letters[$lastCol] . "3";
$format['fillOrange'][] = "A3:" . $letters[$lastCol] . "3";

$branchCol = 1;

foreach ($weekPer as $wGroup => $wInfo) {
    $row = 4;
    $nameCol = $branchCol + 1;
    $perCol = $branchCol + 2;
    $mergeColsTop[] = ['start' => $branchCol, 'end' => $perCol];
    $sheet->setCellValueByColumnAndRow($branchCol, $row, $wGroup);
    $topRowStart = $row;
    $row++;
    #var_dump($wInfo);

    foreach ($wInfo as $w) {
        $sheet->setCellValueByColumnAndRow($branchCol, $row, $w['branch']);
        $sheet->setCellValueByColumnAndRow($nameCol, $row, $branches[$w['branch']]);
        $sheet->setCellValueByColumnAndRow($perCol, $row, $w['percent']);
        $row++;
    }
    $branchCol = $branchCol + 3;
}

$highestRowTop = $sheet->getHighestRow();


$row++;

$row = $highestRowTop + 3;

if($row % 2 === 0) {
    $row++;
}

$sheet->setCellValueByColumnAndRow(1, $row, 'YTD Completion Percentages');
$format['merge'][] = "A" . $row . ":" . $letters[$lastCol] . $row;
$format['hCenter'][] = "A" . $row . ":" . $letters[$lastCol] . $row;
$format['size18'][] = "A" . $row . ":" . $letters[$lastCol] . $row;
$format['outline'][] = "A" . $row . ":" . $letters[$lastCol] . $row;
$format['fillOrange'][] = "A" . $row . ":" . $letters[$lastCol] . $row;
$row++;

$branchCol = 1;

foreach ($ytdPer as $yGroup => $yInfo) {
    $ytdRow = $row;
    $nameCol = $branchCol + 1;
    $perCol = $branchCol + 2;

    $mergeColsBottom[] = ['start' => $branchCol, 'end' => $perCol];
    $bottomRowStart = $row;
    $sheet->setCellValueByColumnAndRow($branchCol, $ytdRow, $yGroup);
    $ytdRow++;
    foreach ($yInfo as $y) {

        var_dump($y);
        $sheet->setCellValueByColumnAndRow($branchCol, $ytdRow, $y['branch']);
        $sheet->setCellValueByColumnAndRow($nameCol, $ytdRow, $branches[$y['branch']]);
        $sheet->setCellValueByColumnAndRow($perCol, $ytdRow, $y['percent']);
        $ytdRow++;
    }
    $branchCol = $branchCol + 3;
}

$highestRowBottom = $sheet->getHighestRow();

foreach ($mergeColsTop as $cols) {
    $format['merge'][] = $letters[$cols['start']] . $topRowStart . ":" . $letters[$cols['end']] . $topRowStart;
    $format['hCenter'][] = $letters[$cols['start']] . $topRowStart . ":" . $letters[$cols['end']] . $topRowStart;
    $format['size14'][] = $letters[$cols['start']] . $topRowStart . ":" . $letters[$cols['end']] . $topRowStart;
    $format['fillDarkBlue'][] = $letters[$cols['start']] . $topRowStart . ":" . $letters[$cols['end']] . $topRowStart;
    $format['outline'][] = $letters[$cols['start']] . $topRowStart . ":" . $letters[$cols['end']] . $topRowStart;
    $format['outline'][] = $letters[$cols['start']] . ($topRowStart + 1) . ":" . $letters[$cols['end']] . $highestRowTop;
    $format['hcenter'][] = $letters[$cols['start']] . ($topRowStart + 1) . ":" . $letters[$cols['start']] . $highestRowTop;
    $format['hcenter'][] = $letters[$cols['end']] . ($topRowStart + 1) . ":" . $letters[$cols['end']] . $highestRowTop;
    $format['formatPercent'][] = $letters[$cols['end']] . ($topRowStart + 1) . ":" . $letters[$cols['end']] . $highestRowTop;
}

foreach ($mergeColsBottom as $cols) {
    $format['hCenter'][] = $letters[$cols['start']] . $bottomRowStart . ":" . $letters[$cols['end']] . $bottomRowStart;
    $format['merge'][] = $letters[$cols['start']] . $bottomRowStart . ":" . $letters[$cols['end']] . $bottomRowStart;
    $format['size14'][] = $letters[$cols['start']] . $bottomRowStart . ":" . $letters[$cols['end']] . $bottomRowStart;
    $format['fillDarkBlue'][] = $letters[$cols['start']] . $bottomRowStart . ":" . $letters[$cols['end']] . $bottomRowStart;
    $format['outline'][] = $letters[$cols['start']] . $bottomRowStart . ":" . $letters[$cols['end']] . $bottomRowStart;
    $format['outline'][] = $letters[$cols['start']] . ($bottomRowStart + 1) . ":" . $letters[$cols['end']] . $highestRowBottom;
    $format['hcenter'][] = $letters[$cols['start']] . ($bottomRowStart + 1) . ":" . $letters[$cols['start']] . $highestRowBottom;
    $format['hcenter'][] = $letters[$cols['end']] . ($bottomRowStart + 1) . ":" . $letters[$cols['end']] . $highestRowBottom;
    $format['formatPercent'][] = $letters[$cols['end']] . ($bottomRowStart + 1) . ":" . $letters[$cols['end']] . $highestRowBottom;

}

$format['allBorders'][] = "A" . ($topRowStart + 1) . ":" . $letters[$lastCol] . $highestRowTop;
$format['size12'][] = "A" . ($topRowStart + 1) . ":" . $letters[$lastCol] . $highestRowTop;
$format['outline'][] = "A" . ($topRowStart + 1) . ":" . $letters[$lastCol] . $highestRowTop;
for ($i = ($topRowStart + 1); $i < $highestRowTop + 1; $i++) {
    if ($i % 2 === 0) {
        $format['fillLightBlue'][] = "A" . $i . ":" . $letters[$lastCol] . $i;
    }
}

$format['allBorders'][] = "A" . ($bottomRowStart + 1) . ":" . $letters[$lastCol] . $highestRowBottom;
$format['size12'][] = "A" . ($bottomRowStart + 1) . ":" . $letters[$lastCol] . $highestRowBottom;
$format['outline'][] = "A" . ($bottomRowStart + 1) . ":" . $letters[$lastCol] . $highestRowBottom;
for ($k = ($bottomRowStart + 1); $k < $highestRowBottom + 1; $k++) {
    if ($k % 2 === 0) {
        $format['fillLightBlue'][] = "A" . $k . ":" . $letters[$lastCol] . $k;
    }
}

$format['zAutoSize'][] = "A:" . $letters[$lastCol];

$formats->formatSheet($sheet, $format);

$date = date('m-d-y');

$file = '../output/cycleCountLogs/quickAnalysis/quickAnalysis ' . $date . '.xlsx';


$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
$writer->save($file);

echo '<a href="' . $file . '" download><button class="ui-corner-all ui-button">Output File</button></a>';