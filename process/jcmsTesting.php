<?php

#session_start();

#require_once '../vendor/autoload.php';

/*var_dump($_SESSION);

$arrays = $_SESSION['writeData'][0]['sentData'];

$bogusArray = $arrays[0];
$testCount = $arrays[1];
$testNames = $arrays[2];
$totalCount = $arrays[3];
$testData = $arrays[4];
$sheetNames = $arrays[5];*/


$outputFile = 'output/jcmsTesting/jcmsTesting ' . date('m-d-y') . '.xlsx';
$writeToFile = '../' . $outputFile;

$newSpreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

foreach ($testData as $setup => $testArray) {

    switch ($setup) {
        case 'notTaken':
            $title = 'Not Taken';
            $sheetTitle = 'Tests Not Taken';
            $headerArray = ['Test Name', 'Branch', 'Name', 'Position', 'Hired'];
            $colArray = ['name', 'position', 'hired'];
            #$notTakenCount++;
            break;
        case 'failed':
            $title = 'Failed';
            $sheetTitle = 'Tests Failed';
            $headerArray = ['Test Name', 'Branch', 'Name', 'Position', 'Hired', 'Score'];
            $colArray = ['name', 'position', 'hired', 'score'];
            #$failedCount++;
            break;
        default:
            $title = null;
            $sheetTitle = null;
            $headerArray = null;
            $colArray = null;
            break;
    }

    $addSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($newSpreadsheet, $title);

    $newSpreadsheet->addSheet($addSheet);

    $newSpreadsheet->setActiveSheetIndexByName($title);

    $newSheet = $newSpreadsheet->getActiveSheet();

    $newSheet->setCellValueByColumnAndRow(1, 1, $sheetTitle);

    $row = 3;
    $colCount = count($headerArray);

    $col = 1;
    for ($j = 0; $j < $colCount; $j++) {
        $newSheet->setCellValueByColumnAndRow($col, $row, $headerArray[$j]);
        $col++;
    }
    $row++;
    foreach ($testArray as $test => $branchArray) {

        $tests = explode('-', $test);

        foreach ($branchArray as $branch => $value) {

            $setup == 'notTaken' ? $processedNotTaken++ : $processedFailed++;


            for ($i = 0; $i < count($value); $i++) {
                #$setup == 'failed' ? $failedCount++ : $notTakenCount++;
                $col = 1;
                $newSheet->setCellValueByColumnAndRow($col, $row, $tests[0]);
                $col++;
                $newSheet->setCellValueByColumnAndRow($col, $row, $branch);
                $col++;
                foreach ($colArray as $colName) {
                    $newSheet->setCellValueByColumnAndRow($col, $row, $value[$i][$colName]);
                    $col++;
                }

                $row++;
            }
            #$row++;
        }
    }
}
$addSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($newSpreadsheet, 'Summary');

$newSpreadsheet->addSheet($addSheet);

$newSpreadsheet->setActiveSheetIndexByName('Summary');
$newSheet = $newSpreadsheet->getActiveSheet();


$row = 1;

$newSheet->setCellValueByColumnAndRow(1, $row, 'Total Tests to Take');
$row++;

#$newSheet->setCellValueByColumnAndRow(1, $row, count($testNames[0]));
$row++;

foreach ($testCount as $test => $value) {
    if (preg_match('/-/', $test)) {
        $tests = explode('-', $test);
        $col = 2;
        $newSheet->setCellValueByColumnAndRow($col, $row, $tests[0]);
        $col++;
        $newSheet->setCellValueByColumnAndRow($col, $row, $value);
        $row++;
    }
}

$newSheet->setCellValueByColumnAndRow(2, $row, 'Total Tests To Take');
$newSheet->setCellValueByColumnAndRow(3, $row, $totalCount);


$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($newSpreadsheet, "Xlsx");
$writer->save($outputFile);

echo '<a href="' . $outputFile . '" download><button class="ui-corner-all ui-button">Output File</button></a>';


