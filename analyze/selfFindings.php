<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/3/2019
 * Time: 9:43 AM
 */

session_start();

$_SESSION = [];

use PhpOffice\PhpSpreadsheet\Reader\Exception;

require('../vendor/autoload.php');
require('../class/Process.php');
require('../class/Format.php');

$findingLnk = new Process();
$quesLnk = new Process();
$luLnk = new Process();
$enteredLnk = new Process();
$versionLnk = new Process();


/**
 * @param $lnk Process
 * @return mixed
 */
function getCurrVersion($lnk) {
    $sql = "SELECT max(version) FROM auditanalysis.selfaudits";
    $a = $lnk->query($sql);

    return $a[0]['max(version)'];
}

/**
 * @param $version
 * @param $lnk Process
 */
function getCurrQuesArray($version, $lnk) {
    $search = "." . $version;
    $sql = "SELECT qCode, qTitle, qPoints, qWConly FROM auditanalysis.auditquestions WHERE qCode LIKE '%" . $search . "'";
    $x = $lnk->query($sql);


    foreach ($x as $a) {

        $pieces = explode(".", $a['qCode']);
        $auditCode = substr($pieces[0], 0, 2);
        $quesNum = substr($pieces[0], 2);
        $array[$auditCode][$quesNum][$a['qTitle']] = ['points' => $a['qPoints']];
    }

    return $array;
}

/**
 * @param $lnk Process
 */
function getFindingArray($lnk, $enteredArray, $quesArray) {

    foreach ($enteredArray as $auditID => $data) {
        $a = "SELECT qCode from auditanalysis.selffindings WHERE auditID = ?";
        $aParams = [$auditID];
        $b = $lnk->query($a, $aParams);
        foreach ($b as $x) {

            $pieces = explode('.', $x['qCode']);
            $auditCode = substr($pieces[0], 0, 2);
            $quesNum = substr($pieces[0], 2);
            $array[$auditCode][$quesArray[$x['qCode']]][$data['month']][] = $auditID;
        }
    }

    return $array;
}

function combineFindings($findings, $questions) {

    $months = date('n') == 1 ? 1 : range(2, date('n') - 1);

    foreach ($months as $month) {
        foreach ($questions as $qAuditCode => $qQuess) {
            foreach ($qQuess as $qQuesNum => $qTitles) {
                foreach ($qTitles as $qTitle => $qX) {
                    $array[$qAuditCode][$qQuesNum][$qTitle][$questions[$qAuditCode][$qQuesNum][$qTitle]['points']][$month] = ['count' => count($findings[$qAuditCode][$qTitle][$month])];
                }
            }
        }
    }

    return $array;
}

/**
 * @param $lnk Process
 * @return mixed
 */
function getQuesArray($lnk) {

    $a = "SELECT qCode, qTitle, qPoints FROM auditanalysis.auditquestions WHERE qWConly = 0";
    $b = $lnk->query($a);

    foreach ($b as $x) {

        $array[$x['qCode']] = $x['qTitle'];
    }
    #asort($array);

    return $array;
}

/**
 * @param $lnk Process
 * @return mixed
 */
function getAuditLookup($lnk) {
    $a = "SELECT auditCode, auditName FROM auditanalysis.auditlookup";
    $b = $lnk->query($a);

    foreach ($b as $x) {
        $array[$x['auditCode']] = $x['auditName'];
    }

    return $array;
}

/**
 * @param $lnk Process
 * @return mixed
 */
function getEnteredAudits($lnk) {

    $months = [];

    $currYear = date('y');
    $currMonth = date('n');
    $month = $currMonth === 1 ? 12 : $currMonth - 1;
    $year = $currMonth === 1 ? $currYear - 1 : $currYear;
    $months = $month !== 1 ? range(1, $month) : [1];

    foreach ($months as $m) {
        $a = "SELECT id, month, branch, auditStatus FROM auditanalysis.selfaudits WHERE year = ? AND month = ?";
        $aParams = [$year, $m];
        $b = $lnk->query($a, $aParams);
        foreach ($b as $x) {
            $array[$x['id']] = ['month' => $x['month'], 'branch' => $x['branch'], 'status' => $x['auditStatus']];
        }
    }

    return $array;
}

function getBranchCounts($entered) {

    $count = [];

    foreach ($entered as $audit => $data) {
        if ($data['status'] == 1) {
            isset ($count['count'][$data['month']]) ? $count['count'][$data['month']]++ : $count['count'][$data['month']] = 1;
        } else {
            $missing[$data['month']][] = $data['branch'];
        }
    }

    $_SESSION['missing'] = $missing;

    return $count;

}

function getCountArray($findingArray, $auditLU, $enteredArray) {

    $array = [];
    foreach ($findingArray as $audit => $a) {
        foreach ($a as $version => $b) {
            asort($b);
            foreach ($b as $ques => $ID) {
                $count = count($ID);
                for ($i = 0; $i < $count; $i++) {
                    $auditName = $auditLU[$audit];
                    $year = $enteredArray[$ID[$i]]['year'];
                    $month = $enteredArray[$ID[$i]]['year']['month'];

                    isset($array[$auditName][$version][$year][$month][$ques])
                        ? $array[$auditName][$version][$year][$month][$ques]++
                        : $array[$auditName][$version][$year][$month][$ques] = 1;
                }
            }
        }
    }

    return $array;
}

function getWritableArrays($quesArray, $auditLU, $countArray) {

    $indArray = [];
    $totalArray = [];

    foreach ($quesArray as $auditCode => $data) {

        $auditName = $auditLU[$auditCode];

        foreach ($data[2] as $quesNum => $data2) {

            $title = $quesArray[$auditCode][2][$quesNum]['title'];
            $points = $quesArray[$auditCode][2][$quesNum]['points'];

            foreach ($countArray[$auditName][2] as $year => $months) {

                foreach ($months as $month => $quess) {

                    foreach ($quess as $quesNum) {
                        if (isset($countArray[$auditName][2][$year][$month][$quesNum])) {
                            $findCount = $countArray[$auditName][2][$year][$month][$quesNum];

                        } else {
                            $findCount = 0;
                        }

                        $pointsLost = $points * $findCount;

                        $indArray[$auditCode][$quesNum][$year][$month] = ['points' => $points, 'pointsLost' => $pointsLost, 'findCount' => $findCount];

                        if (!isset($indArray[$auditCode][$quesNum]['total']['pointsLost'])) {
                            $indArray[$auditCode][$quesNum]['total']['pointsLost'] = $pointsLost;
                        } else {
                            $indArray[$auditCode][$quesNum]['total']['pointsLost'] += $pointsLost;
                        }

                        if (!isset($indArray[$auditCode][$quesNum]['total']['findCount'])) {
                            $indArray[$auditCode][$quesNum]['total']['findCount'] = $findCount;
                        } else {
                            $indArray[$auditCode][$quesNum]['total']['findCount'] += $findCount;
                        }

                        if (!isset($totalArray[$auditName][$year][$month])) {
                            $totalArray[$auditName][$year][$month]['count'] = $findCount;
                            $totalArray[$auditName][$year][$month]['points'] = $pointsLost;
                        } else {
                            $totalArray[$auditName][$year][$month]['count'] += $findCount;
                            $totalArray[$auditName][$year][$month]['points'] += $pointsLost;
                        }

                        if (!isset($totalArray[$auditName]['total'])) {
                            $totalArray[$auditName]['total']['count'] = $findCount;
                            $totalArray[$auditName]['total']['points'] = $pointsLost;
                        } else {
                            $totalArray[$auditName]['total']['count'] += $findCount;
                            $totalArray[$auditName]['total']['points'] += $pointsLost;
                        }
                    }
                }
            }
        }
    }

    return [$indArray, $totalArray];
}

function createSpreadSheet($combinedFindings, $auditLU, $branchCounts, $ini) {

    function writeSheet($spreadSheet) {
        $fileDate = new DateTime();
        $fileDate->modify('-1 Month');
        $date = $fileDate->format('F Y');

        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
        $file = "../output/selfAuditAnalysis/selfFindings - " . $date . ".xlsx";
        $writer->save($file);
    }

    /**
     * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    function formatSheet($sheet, $format) {

        $a = new Format();

        $sheet = $a->formatSheet($sheet, $format);

        return $sheet;

    }

    /**
     * @param $months
     * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @param $totalColCount
     * @param $row
     * @param $format
     * @return array
     */
    function writeTopHeaders($months, $sheet, $totalColCount, $row, $format, $letters, $singleHead, $total) {

        $col = 1;

        $singleCount = 0;
        foreach ($singleHead as $column) {
            $singleCount++;
            $sheet->setCellValueByColumnAndRow($col, $row, $column);
            $colLet = $letters[$col - 1];
            $format['merge'][] = $colLet . $row . ":" . $colLet . ($row + 1);
            $format['bold'][] = $colLet . $row;
            $format['hCenter'][] = $colLet . $row;
            $format['vCenter'][] = $colLet . $row;
            $format['size14'][] = $colLet . $row;
            $format['fillDarkBlue'][] = $colLet . $row;
            $format['wrapText'][] = $colLet . $row;
            $col++;
        }

        $mergeColCount = $totalColCount - 1;

        foreach ($months as $key => $value) {
            $dateObj = DateTime::createFromFormat('!m', $value);
            $monthName = $dateObj->format('F');
            $sheet->setCellValueByColumnAndRow($col, $row, $monthName);
            $col1 = $col;
            $col += $totalColCount;
            $outline[] = ['start' => $col1, 'end' => $col, 'row' => $row];
            $mergeRange = $letters[$col1 - 1] . $row . ":" . $letters[$col - 2] . $row;
            $format['merge'][] = $mergeRange;
            $format['hCenter'][] = $letters[$col1 - 1] . $row;
            $format['bold'][] = $letters[$col1 - 1] . $row;
            $format['size14'][] = $letters[$col1 - 1] . $row;
            $format['fillDarkerBlue'][] = $letters[$col1 - 1] . $row;
            $format['textWhite'][] = $letters[$col1 - 1] . $row;
        }
        $sheet->setCellValueByColumnAndRow($col, $row, "Average");
        $format['merge'][] = $letters[$col - 1] . $row . ":" . $letters[$col - 1 + $mergeColCount] . $row;

        $format['hCenter'][] = $letters[$col - 1] . $row;
        $format['bold'][] = $letters[$col - 1] . $row;
        $format['size14'][] = $letters[$col - 1] . $row;
        $format['fillDarkerBlue'][] = $letters[$col - 1] . $row;
        $format['textWhite'][] = $letters[$col - 1] . $row;
        $row++;

        return [$sheet, $row, $format];
    }

    /**
     * @param $months
     * @param $totalColCount
     * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @param $col
     * @param $row
     * @param $colHeader
     * @param $letters
     */
    function writeSecondHeaders($months, $colCount, $sheet, $col, $row, $colHeader, $letters, $format, $total) {

        for ($j = 0; $j < count($months) + 1; $j++) {
            /* if ($j == count($months) && !$total) {
                 $colCount = $colCount - 1;
             }*/
            $outline[] = ['start' => $letters[$col - 1], 'end' => $letters[$col + $colCount - 2], 'row' => $row - 1];
            for ($i = 0; $i < $colCount; $i++) {
                $sheet->setCellValueByColumnAndRow($col, $row, $colHeader[$i]);
                $format['bold'][] = $letters[$col - 1] . $row;
                $format['hCenter'][] = $letters[$col - 1] . $row;
                $format['vCenter'][] = $letters[$col - 1] . $row;
                $format['size14'][] = $letters[$col - 1] . $row;
                $format['fillDarkBlue'][] = $letters[$col - 1] . $row;
                $format['wrapText'][] = $letters[$col - 1] . $row;
                #$format['wrapText'][] = $letters[$col - 1] . $row;
                $col++;
            }
        }

       return [$row, $format, $sheet, $outline];
    }

    function createOutline($format, $outline, $hRow) {

        foreach ($outline as $bLine) {
            $start = $bLine['start'];
            $end = $bLine['end'];
            $row = $bLine['row'];

            $format['outline'][] = $start . $row . ":" . $end . $hRow;

        }

        return $format;
    }

    function createDataColor($format, $rowStart, $rowEnd, $colStart, $colEnd) {

        $rowCount = $rowEnd - $rowStart - 1;

        for ($i = 0; $i < $rowCount; $i++) {
            if (($i % 2) == 0) {
                $row = $i + 5;
                $format['fillLightBlue'][] = $colStart . $row . ":" . $colEnd . $row;
            }
        }

        return $format;
    }

    function printFormat($x) {
        foreach ($x as $a => $b) {
            foreach ($b as $c) {
                echo $a . " : " . $c . "</br>";
            }
        }
    }

    $months = $ini[0];
    $totalSheet = $ini[1];
    $totalCol = $ini[2];
    $headerCount = $ini[3];
    $colHeader = $ini[4];
    $deptColCount = count($colHeader);
    $totalColCount = $deptColCount - 1;

    $letters = range("A", "Z");

    $format = [];
    $outline = [];

    $spreadSheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();

    foreach ($combinedFindings as $aCode => $questionArray) {

        $format = [];
        $outline = [];

        $auditName = $auditLU[$aCode];

        $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $auditName);
        $spreadSheet->addSheet($newSheet);
        $spreadSheet->setActiveSheetIndexByName($auditName);
        $sheet = $spreadSheet->getActiveSheet();

        $row = 1;

        $sheet->setCellValueByColumnAndRow(1, $row, "SELF-FINDINGS FOR " . $auditName . " 2019");
        $row++;
        $sheet->setCellValueByColumnAndRow(1, $row, "click here to go back to summary");
        $sheet->getCell("A" . $row)->getHyperlink()->setUrl("sheet://'TOTAL'!A1");
        $format['size12'][] = "A" . $row;
        $row++;

        $singleDeptHeaders = ["Question\n#", "Title", "Points"];

        $top = writeTopHeaders($months, $sheet, $deptColCount, $row, $format, $letters, $singleDeptHeaders, false);

        $sheet = $top[0];
        $row = $top[1];
        $format = $top[2];

        #write second headers
        $col = count($singleDeptHeaders) + 1;

        $secHead = writeSecondHeaders($months, $deptColCount, $sheet, $col, $row, $colHeader, $letters, $format, false);

        $format = $secHead[1];
        $row = $secHead[0];
        $sheet = $secHead[2];
        $outline = $secHead[3];
        $row++;

        $dataRowStart = $row;

        foreach ($questionArray as $questNumber => $titles) {

            foreach ($titles as $title => $points) {

                foreach ($points as $point => $periods) {
                    $col = 1;
                    $colStart = $letters[$col - 1];
                    $sheet->setCellValueByColumnAndRow($col, $row, $questNumber);
                    $format['hCenter'][] = $letters[$col - 1] . $row;
                    $format['size14'][] = $letters[$col - 1] . $row;
                    $col++;
                    $sheet->setCellValueByColumnAndRow($col, $row, $title);
                    $col++;
                    $sheet->setCellValueByColumnAndRow($col, $row, $point);
                    $format['hCenter'][] = $letters[$col - 1] . $row;
                    $colEnd = $letters[$col - 1];
                    $format['bold'][] = $colStart . $row . ":" . $colEnd . $row;
                    $col++;
                    $findCount = 0;
                    $missedPoints = 0;
                    $colStart = $col;
                    $rowStart = $row;

                    $average = [];
                    foreach ($periods as $m => $count) {

                        if (isset($branchCounts['count'][$m])) {
                            $fPercent = number_format(($count['count'] / $branchCounts['count'][$m]) * 100, 2);
                            $fCount = $count['count'];
                            $fPoints = $count['count'] * $point;
                        } else {
                            $fPercent = 0;
                            $fCount = 0;
                            $fPoints = 0;
                        }

                        $totalCounts[$auditName][$m]['count'][] = $fCount;
                        $totalCounts[$auditName][$m]['points'][] = $fPoints;
                        $totalCounts[$auditName][$m]['percent'][] = $fPercent;

                        $sheet->setCellValueByColumnAndRow($col, $row, $fCount);
                        $average['count'][] = $letters[$col - 1] . $row;
                        $col++;
                        $sheet->setCellValueByColumnAndRow($col, $row, $fPoints);
                        $average['points'][] = $letters[$col - 1] . $row;
                        $col++;
                        $sheet->setCellValueByColumnAndRow($col, $row, $fPercent);
                        $average['percent'][] = $letters[$col - 1] . $row;
                        $col++;
                    }

                    $averageCount = implode(", ", $average['count']);
                    $averagePoints = implode(", ", $average['points']);
                    $averagePercent = implode(", ", $average['percent']);

                    $sheet->setCellValueByColumnAndRow($col, $row, '=average(' . $averageCount . ')');
                    $format['formatNum'][] = $letters[$col - 1] . $row;
                    $col++;
                    $sheet->setCellValueByColumnAndRow($col, $row, '=average(' . $averagePoints . ')');
                    $format['formatNum'][] = $letters[$col - 1] . $row;
                    $col++;
                    $sheet->setCellValueByColumnAndRow($col, $row, '=average(' . $averagePercent . ')');
                    $format['formatNum'][] = $letters[$col - 1] . $row;
                    $col++;
                    $row++;

                }
            }
        }

        $colEnd = $col;
        $rowEnd = $row - 1;
        $dataRowEnd = $row;
        $format['hCenter'][] = $letters[$colStart - 1] . $dataRowStart . ":" . $letters[$colEnd - 1] . $dataRowEnd;


        $highestCol = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $format['merge'][] = "A1:" . $highestCol . "1";
        $format['merge'][] = "A2:" . $highestCol . "2";
        $format['bold'][] = "A1";
        $format['bold'][] = "A2";
        #$format['hCenter'][] = "A1";
        #$format['hCenter'][] = "A2";

        $format = createOutline($format, $outline, $highestRow);
        $format = createDataColor($format, $dataRowStart, $dataRowEnd, "A", $highestCol);

        $format['outline'][] = "A3:" . $highestCol . "4";
        $format['outline'][] = "A3:" . $highestCol . $highestRow;
        $format['allBorders'][] = "A3:" . $highestCol . $highestRow;
        $format['size18'][] = "A1";

        $format['fitToPage'] = true;
        $format['orientation'] = true;
        $format['zAutoSize'] = range("A", $highestCol);
        $format['freezePane'][] = "D5";

        #printFormat($format);

        formatSheet($sheet, $format);


    }

    $spreadSheet->setActiveSheetIndexByName("Worksheet");

    $spreadSheet->getActiveSheet()->setTitle("TOTAL");
    $sheet = $spreadSheet->getActiveSheet();

    $format = [];
    $average = [];

    $row = 1;

    $sheet->setCellValueByColumnAndRow(2, $row, "SELF-FINDINGS SUMMARY 2019");
    $row++;
    $sheet->setCellValueByColumnAndRow(2, $row, "click department name to go to that tab");
    $format['size12'][] = "B" . $row;

    $row++;

    $singleDeptHeaders = ["Audit\nName"];

    $top = writeTopHeaders($months, $sheet, $deptColCount, $row, $format, $letters, $singleDeptHeaders, false);

    $sheet = $top[0];
    $row = $top[1];
    $format = $top[2];

    #write second headers
    $col = count($singleDeptHeaders) + 1;

    $secHead = writeSecondHeaders($months, $deptColCount, $sheet, $col, $row, $colHeader, $letters, $format, false);

    $format = $secHead[1];
    $row = $secHead[0];
    $sheet = $secHead[2];
    $outline = $secHead[3];
    $row++;

    $dataRowStart = $row;

    #writes total data sheet
    foreach ($totalCounts as $audit => $a) {
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $row, $audit);
        $cell = $letters[$col - 1] . $row;
        $sheet->getCell($cell)->getHyperlink()->setUrl("sheet://'" . $audit . "'!A1");
        #->getCell('E26')->getHyperlink()->setUrl("sheet://'Sheetname'!A1");
        $rowStart = $row;
        $col++;

        #writes quarter data
        $colStart = $col;
        $average = [];
        foreach ($a as $time => $b) {
            $findingCount = number_format(array_sum($b['count']) / count($b['count']), 2);
            $findingPoints = number_format(array_sum($b['points']) / count($b['points']), 2);
            $findingPercent = number_format(array_sum($b['percent']) / count($b['percent']), 2);
            $sheet->setCellValueByColumnAndRow($col, $row, $findingCount);
            $average['count'][] = $letters[$col - 1] . $row;
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, $findingPoints);
            $average['points'][] = $letters[$col - 1] . $row;
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, $findingPercent);
            $average['percent'][] = $letters[$col - 1] . $row;
            $col++;
        }
        $averageCount = implode(", ", $average['count']);
        $averagePoints = implode(", ", $average['points']);
        $averagePercent = implode(", ", $average['percent']);
        $sheet->setCellValueByColumnAndRow($col, $row, '=average(' . $averageCount . ')');
        $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, '=average(' . $averagePoints . ')');
        $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, '=average(' . $averagePercent . ')');
        $col++;
        $row++;
    }
    $colEnd = $col;
    $rowEnd = $row;
    $dataRowEnd = $row;
    $format['bold'][] = "A" . $dataRowStart . ":A" . $dataRowEnd;


    $format['hCenter'][] = $letters[$colStart - 1] . $dataRowStart . ":" . $letters[$colEnd - 1] . $dataRowEnd;

    $highestCol = $sheet->getHighestColumn();
    $highestRow = $sheet->getHighestRow();
    $format['merge'][] = "B1:" . $highestCol . "1";
    $format['merge'][] = "B2:" . $highestCol . "2";
    $format['bold'][] = "B1";
    $format['bold'][] = "B2";
    #$format['hCenter'][] = "A1";
    #$format['hCenter'][] = "A2";

    $format = createOutline($format, $outline, $highestRow);
    $format = createDataColor($format, $dataRowStart, $dataRowEnd, "A", $highestCol);

    $format['outline'][] = "A3:" . $highestCol . "4";
    $format['outline'][] = "A3:" . $highestCol . $highestRow;
    $format['allBorders'][] = "A3:" . $highestCol . $highestRow;
    $format['size18'][] = "B1";

    $format['fitToPage'] = true;
    $format['orientation'] = true;
    $format['zAutoSize'] = range("A", $highestCol);
    $format['freezePane'][] = "B5";

    #printFormat($format);

    $sheet = formatSheet($sheet, $format);

    writeSheet($spreadSheet);

}

$currYear = date('y');
$currMonth = date('n');
$month = $currMonth === 1 ? 12 : $currMonth - 1;
$year = $currMonth === 1 ? $currYear - 1 : $currYear;
$months = $month !== 1 ? range(2, $month) : [1];
$totalSheet = true;
$totalCol = true;
$headerCount = 2;
$colHeader = ["Count", "Points\nLost", "%\nMissed"];

$ini = [$months, $totalSheet, $totalCol, $headerCount, $colHeader];

$currVersion = getCurrVersion($versionLnk);

$enteredArray = getEnteredAudits($enteredLnk);

$currQuestions = getCurrQuesArray($currVersion, $quesLnk);

$quesArray = getQuesArray($quesLnk);

$findingArray = getFindingArray($findingLnk, $enteredArray, $quesArray);

$combinedFindings = combineFindings($findingArray, $currQuestions);

$auditLU = getAuditLookup($luLnk);

$branchCounts = getBranchCounts($enteredArray);

#createSpreadSheet($combinedFindings, $auditLU, $branchCounts, $ini);

$_SESSION['arrays'] = [$combinedFindings, $auditLU, $branchCounts, $ini];


