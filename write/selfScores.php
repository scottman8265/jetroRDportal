<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 4/11/2019
 * Time: 5:35 AM
 */

session_start();

require('../vendor/autoload.php');
require('../class/Process.php');
require('../class/Format.php');

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 */
function createMWSheet($deptArray, $branchArray, $writeArray, $periods) {

    $noBranch = [401, 424, 427, 416, 412];

    $spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

    $periodHeaders = getPeriodHeaders($periods);
    $letters = getColumnLetters();
    $periodCount = 0;

    foreach ($periodHeaders as $year => $months) {
        $periodCount += count($months);
        $yearHeaders[] = ['year' => "20" . $year, 'count' => count($months)];
        foreach ($months as $month) {
            $monthHeaders[$year][] = $month;
        }
    }
    $headers1 = ['Branch Number', 'Branch Name'];

    foreach ($writeArray as $dept => $a) {
        ksort($a);

        $deptName = $deptArray[$dept];

        $format = [];

        $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $deptName);
        $spreadSheet->addSheet($newSheet);
        $spreadSheet->setActiveSheetIndexByName($deptName);
        $sheet = $spreadSheet->getActiveSheet();

        $title = $deptName . " SELF AUDIT SCORES";

        $row = 1;

        $sheet->setCellValueByColumnAndRow(1, $row, $title);
        $row++;
        $col = 1;
        $start = $col;
        foreach ($headers1 as $head1) {
            $sheet->setCellValueByColumnAndRow($col, $row, $head1);
            $range = $letters[$col] . $row . ":" . $letters[$col] . ($row + 1);
            $format['merge'][] = $range;
            $format['hCenter'][] = $letters[$col] . $row;
            $format['vCenter'][] = $letters[$col] . $row;
            $format['size18'][] = $letters[$col] . $row;
            $format['wrapText'][] = $letters[$col] . $row;
            $format['fillOrange'][] = $letters[$col] . $row;
            $end = $col;
            $col++;
        }

        $format['outline'][] = $letters[$start] . $row . ":" . $letters[$end] . ($row + 1);
        foreach ($yearHeaders as $years) {
            $sheet->setCellValueByColumnAndRow($col, $row, $years['year']);
            $yearCol = ['start' => $letters[$col], 'end' => $letters[($col + $years['count'] - 1)]];
            $range = $letters[$col] . $row . ":" . $letters[($col + $years['count'] - 1)] . $row;
            $format['merge'][] = $range;
            /*$format['hCenter'][] = $letters[$col] . $row;
            $format['size18'][] = $letters[$col] . $row;*/
            $format['fillOrange'][] = $letters[$col] . $row;
            $format['outline'][] = $range;
            $col += $years['count'];
        }
        $sheet->setCellValueByColumnAndRow($col, $row, 'Average');
        $range = $letters[$col] . $row . ":" . $letters[$col] . ($row + 1);
        $format['merge'][] = $range;
        $format['vCenter'][] = $letters[$col] . $row;
        $format['fillOrange'][] = $letters[$col] . $row;
        $format['outline'][] = $range;
        $row++;
        $col = 3;
        foreach ($monthHeaders as $year => $monthNames) {
            $start = $col;

            foreach ($monthNames as $months) {
                $sheet->setCellValueByColumnAndRow($col, $row, $months);
                #$format['hCenter'][] = $letters[$col] . $row;
                #$format['size18'][] = $letters[$col] . $row;
                $format['fillDarkBlue'][] = $letters[$col] . $row;
                $end = $col;
                $col++;
            }

            $format['outline'][] = $letters[$start] . "3:" . $letters[$end] . "3";
        }
        $row++;
        $branchCount = 1;

        foreach ($a as $branch => $x) {

            if (!array_search($branch, $noBranch)) {
                $col = 1;
                $branchName = $branchArray[$branch];
                $sheet->setCellValueByColumnAndRow($col, $row, $branch);
                $col++;
                $sheet->setCellValueByColumnAndRow($col, $row, $branchName);
                $col++;
                $colAvgStart = $col;
                foreach ($x as $y) {
                    $sheet->setCellValueByColumnAndRow($col, $row, $y);

                    if ($y == 'na') {
                        $format['fillRed'][] = $letters[$col] . $row;
                    }

                    $colAvgEnd = $col;
                    $col++;
                }
                $avgRange = $letters[$colAvgStart] . $row . ':' . $letters[$colAvgEnd] . $row;
                $sheet->setCellValueByColumnAndRow($col, $row, '=IF(ISERROR(AVERAGE(' . $avgRange . ')), "NA", AVERAGE(' . $avgRange . '))');
                $format['formatNum'][] = $letters[$col - 1] . $row;
                $row++;
            }
        }
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $row, 'Period Average');
        $format['merge'][] = $letters[$col] . $row . ":" . $letters[$col + 1] . $row;
        $format['size14'][] = $letters[$col] . $row;
        $col += 2;
        $colStart = $col;
        for ($i = $col; $i < ($periodCount + 1 + $col); $i++) {
            $range = $letters[$i] . '4:' . $letters[$i] . ($row - 1);
            $sheet->setCellValueByColumnAndRow($i, $row, '=IF(ISERROR(AVERAGE(' . $range . ')), "NA", AVERAGE(' . $range . '))');
            $format['formatNum'][] = $letters[$i - 1] . $row;
            $colEnd = $i;
        }

        $highestCol = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        foreach ($yearCol as $y) {
            $range = $y['start'] . '4:' . $y['end'] . $highestRow;
            $format['outline'][] = $range;
        }

        $format['outline'][] = "A4:B" . $highestRow;
        $format['outline'][] = "B1:" . $highestCol . $highestRow;
        $format['outline'][] = "A" . $highestRow . ":B" . $highestRow;
        $format['outline'][] = "A" . $highestRow . ":" . $highestCol . $highestRow;

        $format['hCenter'][] = "A4:A" . $highestRow;
        $format['hCenter'][] = "C4:" . $highestCol . $highestRow;
        $format['hCenter'][] = "A1:" . $highestCol . '3';

        $format['size12'][] = "A4:A" . $highestRow;
        $format['size12'][] = "C4:" . $highestCol . $highestRow;

        $format['size14'][] = "A4:B" . $highestRow;

        $format['size18'][] = "A1:" . $highestCol . '3';

        $format['allBorders'][] = "A2:" . $highestCol . $highestRow;

        $format['freezePane'][] = "A4";
        $format['wrapText'] = ['A2', 'B2'];
        $format['zAutoSize'] = range('A', $highestCol);

        $range = "A1:" . $highestCol . "1";
        $format['merge'][] = $range;
        $format['size22'][] = 'A1';

        $range = $letters[$colStart] . $row . ":" . $letters[$colEnd] . $row;
        $format['formatNum'][] = $range;

        for ($i = 4; $i < $highestRow + 1; $i++) {
            if ($i % 2 === 0) {
                $format['fillLightBlue'][] = "A" . $i . ":" . $highestCol . $i;
            }
        }

        $format['size18'][] = "A" . $highestRow . ":" . $highestCol . $highestRow;
        $format['fillDarkerBlue'][] = "A" . $highestRow . ":" . $highestCol . $highestRow;
        $format['textWhite'][] = "A" . $highestRow . ":" . $highestCol . $highestRow;

        $formatPage = new Format();
        $formatPage->formatSheet($sheet, $format, false);
    }

    $spreadSheet->setActiveSheetIndexByName('Worksheet');
    $remove = $spreadSheet->getActiveSheetIndex();
    $spreadSheet->removeSheetByIndex($remove);
    $spreadSheet->setActiveSheetIndexByName('TOTAL');

    $fileDate = new DateTime();
    $fileDate->modify('-1 Month');
    $date = $fileDate->format('m Y');

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
    $writer->save("../output/selfAuditAnalysis/" . $date . " selfScores.xlsx");
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 * @param $type
 */
function createarSheet($deptArray, $branchArray, $writeArray, $type, $periods) {

    $noBranch = [401, 424, 427, 416, 412];

    $spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $formatSheet = new Format();
    $periodHeaders = getPeriodHeaders($periods);
    $letters = getColumnLetters();
    $periodCount = 0;

    foreach ($periodHeaders as $year => $months) {
        $periodCount += count($months);
        $yearHeaders[] = ['year' => "20" . $year, 'count' => count($months)];
        foreach ($months as $month) {
            $monthHeaders[$year][] = $month;
        }
    }

    $headers1 = ['Branch Number', 'Branch Name'];

    foreach ($writeArray as $name => $x) {
        $names[] = $name;
    }

    foreach ($deptArray as $lc => $long) {

        $format = [];

        $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $long);
        $spreadSheet->addSheet($newSheet);
        $spreadSheet->setActiveSheetIndexByName($long);
        $sheet = $spreadSheet->getActiveSheet();

        $title = $long . " SELF AUDIT SCORES BY " . strtoupper($type);
        $row = 1;
        $sheet->setCellValueByColumnAndRow(1, $row, $title);
        $row++;
        $row++;

        $nameCount = count($names);

        $count = 0;
        $regionStartRow = [];
        $regionEndRow = [];
        $mergeRow = [];
        for ($i = 0; $i < $nameCount; $i++) {
            $count++;
            $sheet->setCellValueByColumnAndRow(1, $row, $names[$i]);
            $mergeRow[] = $row;

            $row++;
            $col = 1;
            $start = $col;
            foreach ($headers1 as $head1) {
                $sheet->setCellValueByColumnAndRow($col, $row, $head1);
                $range = $letters[$col] . $row . ":" . $letters[$col] . ($row + 1);
                $format['merge'][] = $range;
                $format['hCenter'][] = $letters[$col] . $row;
                $format['vCenter'][] = $letters[$col] . $row;
                $format['size18'][] = $letters[$col] . $row;
                $format['wrapText'][] = $letters[$col] . $row;
                $format['fillOrange'][] = $letters[$col] . $row;
                $end = $col;
                $col++;
            }

            $format['outline'][] = $letters[$start] . $row . ":" . $letters[$end] . ($row + 1);
            foreach ($yearHeaders as $years) {
                $sheet->setCellValueByColumnAndRow($col, $row, $years['year']);
                $yearCol = ['start' => $letters[$col], 'end' => $letters[($col + $years['count'] - 1)]];
                $range = $letters[$col] . $row . ":" . $letters[($col + $years['count'] - 1)] . $row;
                $format['merge'][] = $range;
                $format['hCenter'][] = $letters[$col] . $row;
                $format['size18'][] = $letters[$col] . $row;
                $format['fillOrange'][] = $letters[$col] . $row;
                $format['outline'][] = $range;
                $col += $years['count'];
            }
            $sheet->setCellValueByColumnAndRow($col, $row, 'Average');
            $range = $letters[$col] . $row . ":" . $letters[$col] . ($row + 1);
            $format['merge'][] = $range;
            $format['vCenter'][] = $letters[$col] . $row;
            $format['fillOrange'][] = $letters[$col] . $row;
            $format['outline'][] = $range;
            $row++;
            $col = 3;
            $monthColStart = $col;
            $yearCounts = [];
            foreach ($monthHeaders as $year => $monthNames) {
                $yearCounts[] = count($monthNames);
                foreach ($monthNames as $months) {
                    $sheet->setCellValueByColumnAndRow($col, $row, $months);
                    $monthColEnd = $col;
                    $col++;
                }

            }
            $format['fillDarkBlue'][] = $letters[$monthColStart] . $row . ":" . $letters[$monthColEnd] . $row;
            $row++;
            $branchCount = 1;
            $rowStart = $row;
            foreach ($writeArray[$names[$i]][$lc] as $branch => $data) {

                if (!array_search($branch, $noBranch)) {
                    $col = 1;
                    $sheet->setCellValueByColumnAndRow($col, $row, $branch);
                    $col++;
                    $sheet->setCellValueByColumnAndRow($col, $row, $branchArray[$branch]);
                    $col++;
                    $avgStart = $col;
                    foreach ($data as $q) {
                        $sheet->setCellValueByColumnAndRow($col, $row, $q);
                        if ($q === 'na') {
                            $format['fillRed'][] = $letters[$col] . $row;
                        }
                        $avgEnd = $col;
                        $col++;
                    }
                    $avgRange = $letters[$avgStart] . $row . ":" . $letters[$avgEnd] . $row;
                    $sheet->setCellValueByColumnAndRow($col, $row, '=IF(ISERROR(AVERAGE(' . $avgRange . ')), "NA", AVERAGE(' . $avgRange . '))');
                    $format['formatNum'][] = $letters[$col] . $row;
                    $row++;
                }
            }
            $col = 1;
            $sheet->setCellValueByColumnAndRow($col, $row, 'Period Average');
            $col += 2;
            for ($k = $col; $k < ($periodCount + 1 + $col); $k++) {
                $range = $letters[$k] . $rowStart . ':' . $letters[$k] . ($row - 1);
                $sheet->setCellValueByColumnAndRow($k, $row, '=IF(ISERROR(AVERAGE(' . $range . ')), "NA", AVERAGE(' . $range . '))');
                $format['formatNum'][] = $letters[$k] . $row;
            }
            $regionEndRow[] = $row;
            $row++;
            $row++;
        }

        $highestCol = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();

        foreach ($mergeRow as $key => $z) {
            $format['merge'][] = "A" . $z . ":" . $highestCol . $z;
            $format['size18'][] = "A" . $z . ":" . $highestCol . ($z + 2);
            $format['fillDarkerBlue'][] = "A" . $z . ":" . $highestCol . $z;
            $format['textWhite'][] = "A" . $z . ":" . $highestCol . $z;
            $format['outline'][] = "A" . $z . ":" . $highestCol . $regionEndRow[$key];
            $format['outline'][] = "A" . ($z + 3) . ":" . $highestCol . $regionEndRow[$key];
            $format['outline'][] = "A" . ($z + 2) . ":" . $highestCol . ($regionEndRow[$key] - 1);
            #$format['hCenter'][] = "A".$z;
            $format['allBorders'][] = "A" . $z . ":" . $highestCol . $regionEndRow[$key];
            $format['merge'][] = "A" . $regionEndRow[$key] . ":B" . $regionEndRow[$key];
            $format['fillDarkerBlue'][] = "A" . $regionEndRow[$key] . ":" . $highestCol . $regionEndRow[$key];
            $format['size18'][] = "A" . $regionEndRow[$key] . ":" . $highestCol . $regionEndRow[$key];
            $format['textWhite'][] = "A" . $regionEndRow[$key] . ":" . $highestCol . $regionEndRow[$key];
            $format['outline'][] = "A" . $z . ":B" . $regionEndRow[$key];
            $format['size14'][] = "A" . ($z + 3) . ":B" . $regionEndRow[$key];
            $format['size12'][] = "A" . ($z + 3) . ":" . $highestCol . $regionEndRow[$key];
            $format['size14'][] = $highestCol . ($z + 3) . ":" . $highestCol . $regionEndRow[$key];
            $format['outline'][] = $highestCol . $z . ":" . $highestCol . $regionEndRow[$key];
            $col = 3;

            foreach ($yearCounts as $years) {
                $format['outline'][] = $letters[$col] . ($z + 2) . ":" . $letters[$col + $years] . $regionEndRow[$key];
                $col += $years;
            }


            for ($o = ($z + 3); $o < $regionEndRow[$key]; $o++) {
                if ($o % 2 === 0) {
                    $format['fillLightBlue'][] = "A" . $o . ":" . $highestCol . $o;
                }
            }

        }

        $format['hcenter'][] = "A1:A" . $highestRow;
        $format['hcenter'][] = "C1:" . $highestCol . $highestRow;
        $format['merge'][] = "A1:" . $highestCol . "1";
        $format['size22'][] = "A1:" . $highestCol . "1";
        $format['zAutoSize'] = range("A", $highestCol);

        $formatSheet->formatSheet($sheet, $format, false);
    }

    $spreadSheet->setActiveSheetIndexByName('Worksheet');
    $remove = $spreadSheet->getActiveSheetIndex();
    $spreadSheet->removeSheetByIndex($remove);
    $spreadSheet->setActiveSheetIndexByName('TOTAL');

    $fileDate = new DateTime();
    $fileDate->modify('-1 Month');
    $date = $fileDate->format('m Y');

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
    $file = "../output/selfAuditAnalysis/" . $date . " " . $type . "SelfAuditScores.xlsx";
    $writer->save($file);
}

function getPeriodHeaders($periods) {
    foreach ($periods as $period) {
        $p = explode(":", $period);
        $year = $p[0];
        $dt = DateTime::createFromFormat('!m', $p[1]);
        $monthName = $dt->format('M');
        $periodHeaders[$year][] = $monthName;
    }
    return $periodHeaders;
}

function getColumnLetters() {
    $letters = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J',
                11 => 'K', 12 => 'L', 13 => 'M', 14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S',
                20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z'];

    return $letters;
}

$type = $_POST['type'];
$count = $_POST['count'];
$x = $_POST['x'];
$deptArray = $_SESSION['deptArray'];
$branchArray = $_SESSION['branchArray'];
$writeArray = $_SESSION[$type];
$periods = $_SESSION['periods'];

switch ($type) {
    case 'Division':
        createTotalSheet($deptArray, $branchArray, $writeArray, $periods);
        break;
    default:
        createGroupSheet($deptArray, $branchArray, $writeArray, $type, $periods);
        break;
}

echo json_encode(['type' => $type, 'count' => $count, 'x' => $x]);
#echo "wtf";