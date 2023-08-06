<?php

#echo "inside jcmsTesting";

use PhpOffice\PhpSpreadsheet\Reader\Exception;

define('BR', '</br>');

$sendArray = [];
$bogusArray = [];

$sendArray = getBranchArrays();

$arrays = new Arrays($sendArray);

$branchArray = $arrays->getBranchArray();
$twoDigitArray = $arrays->getTwoDigitArray();

$count = 0;

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */

$sheetNames = $spreadSheet->getSheetNames();
$count = 0;
$totalCount = 0;
$parsedNotTaken = 0;
$parsedFailed = 0;
$testNames = [];
$testCount = [];


foreach ($sheetNames as $names) {
    if ($names !== 'Most Recent Tests' && preg_match('/Test/', $names)) {

        $testNames[] = $names;

        if (!isset($testCount[$names])) {
            $testCount[$names]['totalCount'] = 0;
            $testCount[$names]['notTaken'] = 0;
            $testCount[$names]['failed'] = 0;
        }
    }
}
#print_r($testNames);


#$namesPiece = explode('-', $names);
#$testnames = trim($namesPiece[0]);
#$testnames = trim($names);
#$testnamess[] = trim($namesPiece[0]);
/*if (!isset($testCount[$testnames])) {
                    $testCount[$testnames] = 0;
                }*/
foreach ($testNames as $key => $name) {

    $takeTest = false;

    $sheet = $spreadSheet->setActiveSheetIndexByName($name);
    $maxRow = $sheet->getHighestRow();

    /*if (preg_match('/Admin/', $names)) {
            $row = 7;
        } else {
            $row = 5;
        }*/
    $row = 7;
    for ($i = $row; $i < ($maxRow); $i++) {
        $count++;
        $id = $sheet->getCell('A' . $i)->getValue();

        $repName = $sheet->getCell('C' . $i)->getFormattedValue();
        $position = $sheet->getCell('E' . $i)->getFormattedValue();
        $hired = $sheet->getCell('F' . $i)->getFormattedValue();
        $branchNum = $sheet->getCell('G' . $i)->getFormattedValue();
        $score = $sheet->getCell('J' . $i)->getFormattedValue();

        $hashCode = $sheet->getCell('J' . $i)->getStyle()->getFill()->getStartColor()->getARGB();

        $hiredDate = new DateTime($hired);

        $today = new DateTime();

        date_sub($today, date_interval_create_from_date_string("30 days"));

        if ($hiredDate < $today) {
            $takeTest = true;
        }

        echo $takeTest . " *** ";

        if (strlen($branchNum) < 3) {
            $branchNum = $twoDigitArray[$branchNum];
        }

        #echo $branchNum . " *** ";

        if (strlen($score) > 0) {
            #$score = (float)substr($score, 1);
        } else {
            $score = null;
        }

        if (($score < 84.5 || is_null($score)) && strlen($branchNum) > 1) {
            if ($hashCode != 'FFF0E68C') {
                #$array[$branchNum][$testNames[$key]][] = ['name' => $repName, 'id' => $id, 'position' => $position, 'hired' => $hired, 'score' => $score];
                $testCount[$testNames[$key]]['totalCount']++;

                if (is_null($score) || $score == 0) {
                    $totalCount++;
                    #$parsedNotTaken++;
                    $testData['notTaken'][$testNames[$key]][$branchNum][] = ['name' => $repName, 'id' => $id, 'position' => $position, 'hired' => $hired, 'score' => $score];

                    $testCount[$testNames[$key]]['notTaken']++;
                } else {
                    $totalCount++;
                    #$parsedFailed++;
                    $testData['failed'][$testNames[$key]][$branchNum][] = ['name' => $repName, 'id' => $id, 'position' => $position, 'hired' => $hired, 'score' => $score];
                    $testCount[$testNames[$key]]['failed']++;
                }
            }
        }
        $row++;
    }
}

$parsedNotTaken = 0;

foreach ($testData['notTaken'] as $testNames => $x) {
    foreach ($x as $branchNumber => $y) {
        foreach ($y as $value) {
            $parsedNotTaken++;
        }
    }
}

foreach ($testData['failed'] as $testNames => $x) {
    foreach ($x as $branchNumber => $y) {
        foreach ($y as $value) {
            $parsedFailed++;
        }
    }
}

$return = [$bogusArray, $testCount, $testNames, $totalCount, $testData, $sheetNames];
