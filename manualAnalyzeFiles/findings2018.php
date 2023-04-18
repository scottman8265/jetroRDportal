<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/3/2019
 * Time: 9:43 AM
 */

use PhpOffice\PhpSpreadsheet\Reader\Exception;

require('../vendor/autoload.php');
require('../class/Process.php');
require('../class/Format.php');

$findingLnk = new Process();
$quesLnk = new Process();
$luLnk = new Process();
$enteredLnk = new Process();


/**
 * @param $lnk Process
 */
function getFindingArray($lnk, $audits) {

    foreach ($audits as $id => $y) {
        $a = "SELECT auditID, qCode from auditanalysis.auditfindings WHERE auditID = " . $id;
        $b = $lnk->query($a);
        foreach ($b as $x) {

            $c = explode('.', $x['qCode']);

            $audit = substr($c[0], 0, 2);
            $ques = substr($c[0], 2);
            $vers = $c[1];

            if ($audit != 'WC') {
                $array[$audit][$vers][$ques][] = $x['auditID'];
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
    $a = "SELECT id, period, version, branch FROM auditanalysis.enteredaudits WHERE period = 'Q1' AND year = '2019'";
    $b = $lnk->query($a);

    foreach ($b as $x) {
        if (!is_null($x['version'])) {
            $array[$x['id']] = ['period' => $x['period'], 'version' => $x['version'], 'branch' => $x['branch']];
        }
    }

    return $array;
}

function getBranchCounts($entered) {

    foreach ($entered as $audit => $data) {
        $array[$data['period']][] = $data['branch'];
    }

    for ($i = 1; $i < 5; $i++) {

        $quarter = "Q" . $i;

        $count[$quarter] = count($array[$quarter]);

    }

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
                    $period = $enteredArray[$ID[$i]]['period'];

                    isset($array[$auditName][$version][$period][$ques]) ? $array[$auditName][$version][$period][$ques]++ : $array[$auditName][$version][$period][$ques] = 1;}
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

            for ($j = 1; $j < 5; $j++) {

                $quarter = "Q" . $j;

                if (array_key_exists($quarter, $countArray[$auditName][2])) {
                    if (isset($countArray[$auditName][2][$quarter][$quesNum])) {
                        $findCount = $countArray[$auditName][2][$quarter][$quesNum];

                    } else {
                        $findCount = 0;
                    }

                    $pointsLost = $points * $findCount;

                    $indArray[$auditCode][$quesNum][$quarter] = ['points' => $points, 'pointsLost' => $pointsLost, 'findCount' => $findCount];

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

                    if (!isset($totalArray[$auditName][$quarter])) {
                        $totalArray[$auditName][$quarter]['count'] = $findCount;
                        $totalArray[$auditName][$quarter]['points'] = $pointsLost;
                    } else {
                        $totalArray[$auditName][$quarter]['count'] += $findCount;
                        $totalArray[$auditName][$quarter]['points'] += $pointsLost;
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

    return [$indArray, $totalArray];
}

function createSpreadSheet($writeArrays, $auditLU, $branchCounts, $quesArray, $ini) {

    function writeSheet($spreadSheet) {
        $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
        $file = "../output/findings20189(test).xlsx";
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
     * @param $quarters
     * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @param $totalColCount
     * @param $row
     * @param $format
     * @return array
     */
    function writeTopHeaders($quarters, $sheet, $totalColCount, $row, $format, $letters, $singleHead, $total) {

        $col = 1;
        foreach ($singleHead as $column) {
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
        foreach ($quarters as $quarter) {
            $sheet->setCellValueByColumnAndRow($col, $row, $quarter);
            $col1 = $col;
            $col += $totalColCount;
            $outline[] = ['start' => $col1, 'end' => $col, 'row' => $row];
            $format['merge'][] = $letters[$col1 - 1] . $row . ":" . $letters[$col - 2] . $row;
            $format['hCenter'][] = $letters[$col1 - 1] . $row;
            $format['bold'][] = $letters[$col1 - 1] . $row;
            $format['size14'][] = $letters[$col1 - 1] . $row;
            $format['fillDarkerBlue'][] = $letters[$col1 - 1] . $row;
            $format['textWhite'][] = $letters[$col1 - 1] . $row;
        }
        $sheet->setCellValueByColumnAndRow($col, $row, "Total");
        if ($total) {
            $format['merge'][] = $letters[$col - 1] . $row . ":" . $letters[$col - 1 + $mergeColCount] . $row;
        } else {
            $format['merge'][] = $letters[$col - 1] . $row . ":" . $letters[$col - 2 + $mergeColCount] . $row;
        }
        $format['hCenter'][] = $letters[$col - 1] . $row;
        $format['bold'][] = $letters[$col - 1] . $row;
        $format['size14'][] = $letters[$col - 1] . $row;
        $format['fillDarkerBlue'][] = $letters[$col - 1] . $row;
        $format['textWhite'][] = $letters[$col - 1] . $row;
        $row++;

        return [$sheet, $row, $format];
    }

    /**
     * @param $quarters
     * @param $totalColCount
     * @param $sheet \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @param $col
     * @param $row
     * @param $colHeader
     * @param $letters
     */
    function writeSecondHeaders($quarters, $colCount, $sheet, $col, $row, $colHeader, $letters, $format, $total) {

        for ($j = 0; $j < count($quarters) + 1; $j++) {
            if ($j == count($quarters) && !$total) {
                $colCount = $colCount - 1;
            }
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

        #var_dump($outline);

        return [$row, $format, $sheet, $outline];
    }

    function createOutline($format, $outline, $hRow) {

        #var_dump($outline);

        foreach ($outline as $bLine) {
            $start = $bLine['start'];
            $end = $bLine['end'];
            $row = $bLine['row'];

            #echo "outline from build: " . $start . $row . ":" . $end . $hRow . "</br>";

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

        #var_dump($x);
    }

    $quarters = $ini[0];
    $totalSheet = $ini[1];
    $totalCol = $ini[2];
    $headerCount = $ini[3];
    $colHeader = $ini[4];
    $deptColCount = count($colHeader);
    $totalColCount = $deptColCount - 1;

    $letters = range("A", "Z");

    $format = [];
    $outline = [];

    $totalArray = $writeArrays[1];
    $indArray = $writeArrays[0];

    $spreadSheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
    $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, "Total");
    $spreadSheet->addSheet($newSheet);
    $spreadSheet->setActiveSheetIndexByName("Total");
    $sheet = $spreadSheet->getActiveSheet();

    $row = 1;

    $sheet->setCellValueByColumnAndRow(1, $row, "Total Findings By Dept");
    $row++;

    #write top headers
    $singleTotHeaders = ["Audit\nName"];
    $top = writetopHeaders($quarters, $sheet, $totalColCount, $row, $format, $letters, $singleTotHeaders, true);

    $sheet = $top[0];
    $row = $top[1];
    $format = $top[2];

    #write second headers
    $col = count($singleTotHeaders) + 1;
    $secHead = writeSecondHeaders($quarters, $totalColCount, $sheet, $col, $row, $colHeader, $letters, $format, true);
    $format = $secHead[1];
    $row = $secHead[0];
    $sheet = $secHead[2];
    $outline = $secHead[3];
    $row++;

    $dataRowStart = $row;

    #writes total data sheet
    foreach ($totalArray as $audit => $a) {
        $col = 1;
        $sheet->setCellValueByColumnAndRow($col, $row, $audit);
        $rowStart = $row;
        $col++;

        #writes quarter data
        $colStart = $col;
        for ($k = 0; $k < count($quarters); $k++) {
            $quar = $quarters[$k];
            $count = $a[$quar]['count'];
            $points = $a[$quar]['points'];
            isset($totalCount) ? $totalCount += $count : $totalCount = $count;
            isset($totalPoints) ? $totalPoints += $points : $totalPoints = $points;
            $sheet->setCellValueByColumnAndRow($col, $row, $count);
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, $points);
            $col++;
        }
        $sheet->setCellValueByColumnAndRow($col, $row, $totalCount);
        $col++;
        $sheet->setCellValueByColumnAndRow($col, $row, $totalPoints);
        $row++;

        unset($totalCount, $totalPoints);
    }
    $colEnd = $col;
    $rowEnd = $row;
    $dataRowEnd = $row;
    #$format['hCenter'][] = $letters[$colStart - 1] . $row . ":" . $letters[$colEnd - 1] . $row;
    $format['bold'][] = "A" . $dataRowStart . ":A" . $dataRowEnd;



    $format['hCenter'][] = $letters[$colStart - 1] . $dataRowStart . ":" . $letters[$colEnd - 1] . $dataRowEnd;

    $highestCol = $sheet->getHighestColumn();
    $highestRow = $sheet->getHighestRow();
    $format['merge'][] = "A1:" . $highestCol . "1";
    $format['bold'][] = "A1";
    $format['hCenter'][] = "A1";

    $format = createOutline($format, $outline, $highestRow);
    $format = createDataColor($format, $dataRowStart, $dataRowEnd, "A", $highestCol);

    $format['outline'][] = "A2:" . $highestCol . "3";
    $format['outline'][] = "A2:" . $highestCol . $highestRow;
    $format['allBorders'][] = "A2:" . $highestCol . $highestRow;
    $format['size18'][] = "A1";

    $format['fitToPage'] = true;
    $format['orientation'] = true;
    $format['zAutoSize'] = range("A", $highestCol);
    $format['freezePane'][] = "A4";

    #printFormat($format);

    $sheet = formatSheet($sheet, $format);

    foreach ($indArray as $aCode => $questionArray) {

        $format = [];
        $outline = [];

        $auditName = $auditLU[$aCode];

        $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $auditName);
        $spreadSheet->addSheet($newSheet);
        $spreadSheet->setActiveSheetIndexByName($auditName);
        $sheet = $spreadSheet->getActiveSheet();

        $row = 1;

        $sheet->setCellValueByColumnAndRow(1, $row, "Findings by " . $auditName . " Audit");
        $row++;

        $singleDeptHeaders = ["Question\n#", "Title", "Points"];

        $top = writeTopHeaders($quarters, $sheet, $deptColCount, $row, $format, $letters, $singleDeptHeaders, false);

        $sheet = $top[0];
        $row = $top[1];
        $format = $top[2];

        #write second headers
        $col = count($singleDeptHeaders) + 1;

        $secHead = writeSecondHeaders($quarters, $deptColCount, $sheet, $col, $row, $colHeader, $letters, $format, false);

        $format = $secHead[1];
        $row = $secHead[0];
        $sheet = $secHead[2];
        $outline = $secHead[3];
        $row++;

        #var_dump($quarters);

        $dataRowStart = $row;

        foreach ($questionArray as $questNumber => $a) {
            $questTitle = $quesArray[$aCode][2][$questNumber]['title'];
            $questPoints = $quesArray[$aCode][2][$questNumber]['points'];

            $col = 1;

            $colStart = $letters[$col - 1];

            $sheet->setCellValueByColumnAndRow($col, $row, $questNumber);
            $format['hCenter'][] = $letters[$col - 1] . $row;
            $format['size14'][] = $letters[$col - 1] . $row;
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, $questTitle);
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, $questPoints);
            $format['hCenter'][] = $letters[$col - 1] . $row;
            $colEnd = $letters[$col - 1];

            $format['bold'][] = $colStart . $row . ":" . $colEnd . $row;

            $col++;
            $findCount = 0;
            $missedPoints = 0;
            $colStart = $col;
            $rowStart = $row;
            foreach ($quarters as $quart) {
                $quar = $quart;

                #echo $quar . "</br>";

                $percent = number_format(($a[$quar]['findCount'] / $branchCounts[$quar]) * 100, 2);
                $findCount += $a[$quart]['findCount'];
                $missedPoints += $a[$quart]['pointsLost'];

                $sheet->setCellValueByColumnAndRow($col, $row, $a[$quar]['findCount']);
                $col++;
                $sheet->setCellValueByColumnAndRow($col, $row, $a[$quar]['pointsLost']);
                $col++;
                $sheet->setCellValueByColumnAndRow($col, $row, $percent);
                $col++;
            }
            $sheet->setCellValueByColumnAndRow($col, $row, $findCount);
            $col++;
            $sheet->setCellValueByColumnAndRow($col, $row, $missedPoints);
            $row++;
        }

        $colEnd = $col;
        $rowEnd = $row - 1;
        $dataRowEnd = $row;
        $format['hCenter'][] = $letters[$colStart - 1] . $dataRowStart . ":" . $letters[$colEnd - 1] . $dataRowEnd;



        $highestCol = $sheet->getHighestColumn();
        $highestRow = $sheet->getHighestRow();
        $format['merge'][] = "A1:" . $highestCol . "1";
        $format['bold'][] = "A1";
        $format['hCenter'][] = "A1";

        $format = createOutline($format, $outline, $highestRow);
        $format = createDataColor($format, $dataRowStart, $dataRowEnd, "A", $highestCol);

        $format['outline'][] = "A2:" . $highestCol . "3";
        $format['outline'][] = "A2:" . $highestCol . $highestRow;
        $format['allBorders'][] = "A2:" . $highestCol . $highestRow;
        $format['size18'][] = "A1";
        $format['zSetWidth'][] = ['col' => "B", 'width' => 50];

        $format['fitToPage'] = true;
        $format['orientation'] = true;
        $format['zAutoSize'] = range("A", $highestCol);
        $format['freezePane'][] = "A4";

        #printFormat($format);

        $sheet = formatSheet($sheet, $format);
    }

    $spreadSheet->setActiveSheetIndexByName("Worksheet");

    $spreadSheet->removeSheetByIndex(0);


    $spreadSheet->setActiveSheetIndexByName("Total");

    writeSheet($spreadSheet);
}


$reportingQuarters = ["Q1"];
$totalSheet = true;
$totalCol = true;
$headerCount = 2;
$colHeader = ["Count", "Points\nLost", "%\nMissed"];

$ini = [$reportingQuarters, $totalSheet, $totalCol, $headerCount, $colHeader];

$enteredArray = getEnteredAudits($enteredLnk);

$findingArray = getFindingArray($findingLnk, $enteredArray);

$quesArray = getQuesArray($quesLnk);

$auditLU = getAuditLookup($luLnk);

$branchCounts = getBranchCounts($enteredArray);

$countArray = getCountArray($findingArray, $auditLU, $enteredArray);

$writableArrays = getWritableArrays($quesArray, $auditLU, $countArray);

createSpreadSheet($writableArrays, $auditLU, $branchCounts, $quesArray, $ini);


#var_dump($branchCounts);

