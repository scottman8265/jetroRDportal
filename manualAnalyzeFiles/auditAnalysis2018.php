<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/26/2018
 * Time: 9:10 AM
 */

session_start();

require('../vendor/autoload.php');
require('../class/Process.php');

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 */
function createMWSheet($spreadSheet, $deptArray, $branchArray, $writeArray, $averages) {

    foreach ($writeArray as $dept => $a) {

        $deptName = $deptArray[$dept];

        $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $deptName);
        $spreadSheet->addSheet($newSheet);
        $spreadSheet->setActiveSheetIndexByName($deptName);

        $title = '2018 MW ' . $deptName . " Scores";
        $headers = ['Branch Number', 'Branch Name', 'Q1', 'Q2', 'Q3', 'Q4', 'Average'];
        $row = 1;

        $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $title);
        $row++;
        $col = 1;
        foreach ($headers as $head) {
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $head);
            $col++;
        }
        $row++;
        foreach ($a as $branch => $data) {
            $branchName = $branchArray[$branch]['branchName'];
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $branch);
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $branchName);
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $data['Q1']);
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $data['Q2']);
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $data['Q3']);
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $data['Q4']);
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $data['average']);
            $row++;
        }

    }

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
    $writer->save("../output/mw2018TotalScores.xlsx");
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 * @param $type
 */
function createarSheet($spreadSheet, $deptArray, $branchArray, $writeArray, $type) {

    foreach ($writeArray as $name => $x) {
        $names[] = $name;
    }

    $headers = ['Branch Number', 'Branch Name', 'Q1', 'Q2', 'Q3', 'Q4', 'Average'];

    foreach ($deptArray as $lc => $long) {

        #echo $lc . "</br>";

        $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($spreadSheet, $long);
        $spreadSheet->addSheet($newSheet);
        $spreadSheet->setActiveSheetIndexByName($long);

        $title = "2018 " . $long . " Scores by " . $type;
        $row = 1;
        $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $title);
        $row++;

        $nameCount = count($names);

        for ($i = 0; $i < $nameCount; $i++) {
            $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $names[$i]);
            $row++;
            $col = 1;
            foreach ($headers as $head) {
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $head);
                $col++;
            }
            $row++;
            #var_dump($writeArray$names[$i][$lc]);
            foreach ($writeArray[$names[$i]][$lc] as $branch => $data) {
                var_dump($data);
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(1, $row, $branch);
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(2, $row, $branchArray[$branch]['branchName']);
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(3, $row, $data['Q1']);
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(4, $row, $data['Q2']);
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(5, $row, $data['Q3']);
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(6, $row, $data['Q4']);
                $spreadSheet->getActiveSheet()->setCellValueByColumnAndRow(7, $row, $data['average']);
                $row++;
            }
            $row++;
        }
    }

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
    $file = "../output/mw2018" . $type . "Scores.xlsx";
    $writer->save($file);
}

$branchLnk = new Process();
$auditLnk = new Process();
$deptLnk = new Process();
$scoreLnk = new Process();
$auditorLnk = new Process();
$regionalLnk = new Process();


$branchSql = "SELECT branchNum, branchName, regional, interimRegional, director, auditor FROM branchinfo.branches where active = TRUE";
$auditSql = "SELECT id, branch, version, period FROM auditanalysis.enteredaudits where year = 2018";
$scoreSql = "SELECT * FROM auditanalysis.auditscores where auditID = ?";
$deptSql = "SELECT lcAuditCode, auditName FROM auditanalysis.auditlookup WHERE active = TRUE";
$auditorSql = "SELECT auditorFName, auditorLName FROM branchinfo.auditors WHERE auditorID = ? AND auditorFT = TRUE";
$regionalSql = "SELECT fName, lName FROM branchinfo.regionals WHERE regionID = ?";

$branchQry = $branchLnk->query($branchSql);

$auditQry = $auditLnk->query($auditSql);

$deptQry = $deptLnk->query($deptSql);

#echo $auditLnk->getQryCount() . "</br>";

$scoreField[] = 'totScore';
$scoreField[] = 'freshScore';
foreach ($deptQry as $dept) {
    $scoreField[] = $dept['lcAuditCode'] . "Score";
}


foreach ($branchQry as $a) {
    $branchArray[$a['branchNum']] = ['branchName' => $a['branchName'], 'regional' => $a['regional'],
                                     'interimRegional' => $a['interimRegional'], 'director' => $a['director'],
                                     'auditor' => $a['auditor']];

    $auditorArray[$a['auditor']][] = $a['branchNum'];

    $regionalArray[$a['regional']][] = ['branchNum' => $a['branchNum'], 'interim' => $a['interimRegional']];
}

$deptArray['totScore'] = 'Total Score';
$deptArray['freshScore'] = 'Fresh Score';
foreach ($deptQry as $b) {
    $deptArray[$b['lcAuditCode'] . "Score"] = $b['auditName'];
}

foreach ($deptArray as $c => $d) {

    $totalScores[$c] = 0;
}


foreach ($auditQry as $value) {
    $branch = $value['branch'];
    $id = $value['id'];
    $version = $value['version'];
    $period = $value['period'];
    $auditArray[] = $branch;

    if (!is_null($version)) {
        $scoreParams = [$id];
        $scoreQry = $scoreLnk->query($scoreSql, $scoreParams);
        foreach ($scoreField as $field) {

            if ($scoreQry[0][$field] != 0) {
                $score = number_format($scoreQry[0][$field] * 100, 2);
                $scoreArray[$period][$branch][$field][] = $score;
                $branchScoreArray[$branch][$field][$period] = $score;
            } else {
                $scoreArray[$period][$branch][$field][] = 'na';
                $branchScoreArray[$branch][$field][$period] = 'na';
            }
        }
    } else {
        foreach ($scoreField as $field) {
            $scoreArray[$period][$branch][$field][] = 'na';
            $branchScoreArray[$branch][$field][$period] = 'na';

        }
    }

}

/*foreach ($scoreArray as $period => $a) {
    foreach ($a as $branch => $b) {
        foreach ($b as $field => $score) {

            echo $period . ": (" . $branch . ") " . $branchArray[$branch]['branchName'] . " - " . $field . " - " . $score[0] . "</br>";

        }
    }
}*/

foreach ($branchArray as $branch => $x) {
    $totAverage['Q1'] = ['count' => 0, 'score' => 0];
    $totAverage['Q2'] = ['count' => 0, 'score' => 0];
    $totAverage['Q3'] = ['count' => 0, 'score' => 0];
    $totAverage['Q4'] = ['count' => 0, 'score' => 0];
    foreach ($branchScoreArray[$branch] as $field => $period) {
        $auditCount = 0;
        $totScore = 0;
        for ($i = 1; $i < 5; $i++) {
            $quar = "Q" . $i;

            if (is_numeric($period[$quar])) {
                $auditCount++;
                #var_dump($totAverage);
                $totAverage[$quar]['count'] += 1;
                $totAverage[$quar]['score'] += $period[$quar];
                $totScore += $period[$quar];
            }

            #echo "[".$quar."]" .$branch . " " . $branchArray[$branch]['branchName'] . ": " . $field . " - " . $period[$quar] . "</br>";
        }

        $totalAverage = number_format($totScore / $auditCount, 2);
        $branchScoreArray[$branch][$field]['average'] = $totalAverage;
    }

}

var_dump($totAverage);

foreach ($totAverage as $q => $nums) {
    $totAverage[$q]['average'] = number_format($nums['score'] / $nums['count'], 2);
}

foreach ($auditorArray as $auditor => $branch) {
    $branchCount = count($branch);
    $auditorParams = [$auditor];
    $auditorQry = $auditorLnk->query($auditorSql, $auditorParams);
    $name = $auditorQry[0]['auditorFName'] . " " . $auditorQry[0]['auditorLName'];
    for ($i = 0; $i < $branchCount; $i++) {
        $auditorScoreArray[$name][$branch[$i]][] = $branchScoreArray[$branch[$i]];
    }
}

foreach ($regionalArray as $regional => $branch) {
    $branchCount = count($branch);
    $regionalParams = [$regional];
    $regionalQry = $regionalLnk->query($regionalSql, $regionalParams);
    $name = $regionalQry[0]['fName'] . " " . $regionalQry[0]['lName'];
    for ($i = 0; $i < $branchCount; $i++) {
        $regionalScoreArray[$name][$branch[$i]['branchNum']][] = $branchScoreArray[$branch[$i]['branchNum']];
    }
}

foreach ($branchScoreArray as $branch => $a) {
    foreach ($a as $field => $b) {
        $mwWriteArray[$field][$branch] = $b;
    }
}

foreach ($regionalScoreArray as $regional => $e) {
    foreach ($e as $branch => $c) {
        foreach ($c[0] as $field => $d) {
            # echo "field - " . $field . ": branch - " . $branch . "</br>";
            $regionalWriteArray[$regional][$field][$branch] = $d;
        }
    }
}

foreach ($auditorScoreArray as $auditor => $f) {
    foreach ($f as $branch => $g) {
        foreach ($g[0] as $field => $h) {
            $auditorWriteArray[$auditor][$field][$branch] = $h;
        }
    }
}


$MWspreadSheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$regionalSheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();
$auditorSheet = new PhpOffice\PhpSpreadsheet\Spreadsheet();


#createMWSheet($MWspreadSheet, $deptArray, $branchArray, $mwWriteArray, $totAverage);
createGroupSheet($regionalSheet, $deptArray, $branchArray, $regionalWriteArray, 'Regional');
createGroupSheet($auditorSheet, $deptArray, $branchArray, $auditorWriteArray, 'Auditor');


var_dump($branchScoreArray);








