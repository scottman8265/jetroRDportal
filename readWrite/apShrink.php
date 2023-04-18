<?php

require_once '../vendor/autoload.php';
require_once '../inc/readFileFunc.php';
require_once '../class/Process.php';
require_once '../class/Format.php';

function getMonthName($month) {

    switch ($month) {
        case 43480:
            $month = "January";
            break;
        case 43511:
            $month = "February";
            break;
        case 43539:
            $month = "March";
            break;
        case 43570:
            $month = "April";
            break;
        case 43600:
            $month = "May";
            break;
        default:
            $month = null;
            break;
    }

    return $month;
}

function getItemStatus($apComments) {

    switch ($apComments) {

        case 'offset':
            $status = 'offset';
            break;
        case 'ok to pay' || 'okay to pay' || 'ok to pya':
            $status = 'ok to pay';
            break;
        default:
            $status = "misc";
            break;
    }

    #echo $apComments . ' - ' . $status . '</br>';

    return $status;
}

$inputFile = "../input/AP Shrink Allocations 071019.xls";

$spreadsheet = readFileData($inputFile, "xls", true);

$lnk = new Process();

$sql = "SELECT branchNum, branchName FROM branchInfo.branches WHERE active = 1";
$qry = $lnk->query($sql);

foreach ($qry as $branch) {
    $branchArray[$branch['branchNum']] = $branch['branchName'];
}

$sheet = $spreadsheet->getActiveSheet();

$lRow = $sheet->getHighestRow();
$fRow = 5;

for ($i = $fRow; $i < $lRow + 1; $i++) {
    $month = $sheet->getCell("A" . $i)->getFormattedValue();
    $vendorNum = $sheet->getCell("B" . $i)->getFormattedValue();
    $vendorName = $sheet->getCell("C" . $i)->getFormattedValue();
    $poNum = $sheet->getCell("F" . $i)->getFormattedValue();
    $branchNum = $sheet->getCell("I" . $i)->getFormattedValue();
    $origAmount = $sheet->getCell("J" . $i)->getFormattedValue();
    $reversed = $sheet->getCell("K" . $i)->getFormattedValue();
    $ttlShk = $sheet->getCell("L" . $i)->getFormattedValue();
    $apComments = strtolower($sheet->getCell("M" . $i)->getFormattedValue());

    $month = getMonthName($month);

    if (strlen($poNum) > 2) {
        $po1 = substr($poNum, 0, 3);
        $po2 = substr($poNum, 3);
        $poNum = $po1 . "-" . $po2;
    }

    if ($branchNum == 414) {
        $branchNum = 114;
    }

    $status = getItemStatus($apComments);

    if ($reversed !== "0") {
        $status = 'reversed';
    }

    if (strpos($apComments, 'val') !== false) {
        $status = 'valid claim';
    }

    if (strpos($apComments, 'retur') !== false) {
        $status = 'not returned';
    }

    #var_dump($reversed);

    #echo $month . " " . $vendorNum . " " . $vendorName . " " . $poNum . " " . $branchNum . " " . $origAmount . " " . $reversed . ' ' . $ttlShk . ' ' . $apComments . " " . $status . '</br>';

    #print_r($reversed);

    $lineArray[$branchNum][$month][$status][] = ['vendorNum' => $vendorNum, 'vendorName' => $vendorName, 'poNum' => $poNum,
                                                 'originalAmt' => $origAmount, 'reversed' => $reversed, 'ttlShr' => $ttlShk, 'apComments' => $apComments];
}

$headerArray = ['Vendor#', 'Vendor', 'PO#', 'Original', 'Rev', 'Ttl', 'AP Com'];

$refmt = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
$sht = $refmt->getActiveSheet();
#var_dump($lineArray);
foreach ($lineArray as $bNum => $mths) {
    $branchNumber = (string)$bNum;

    $ttlItemArray[$branchNumber] = ['originalAmount' => 0, 'ttlCnt' => 0, 'reversed' => 0, 'revCnt' => 0,
                                    'offset' => 0, 'offCnt' => 0, 'validClaim' => 0, 'validCnt' => 0, 'okToPay' => 0, 'okToPayCnt' => 0,
                                    'notReturned' => 0, 'notReturnedCnt' => 0, 'ttlShr' => 0, '%rev' => 0];
    #var_dump($branchNumber);

    if ($branchNumber != "") {
        try {
            $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($refmt, $branchNumber);
            $refmt->addSheet($newSheet);
            $refmt->setActiveSheetIndexByName($branchNumber);
            $reSht = $refmt->getActiveSheet();
        }
        catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            echo $e;
        }
        $row = 1;
        $col = 1;
        $reSht->setCellValueByColumnAndRow($col, $row, $bNum . " (-) " . $branchArray[$bNum]);
        $format['merge'][] = "A" . $row . ":G" . $row;
        $format['hCenter'][] = "A" . $row . ":G" . $row;
        $format['size22'][] = "A" . $row . ":G" . $row;

        $row++;
        $row++;
        #echo "<h1>" . $bNum . " " . $branchArray[$bNum] . "</h1>";
        foreach ($mths as $mnth => $sta) {

            $reSht->setCellValueByColumnAndRow($col, $row, $mnth);
            $format['merge'][] = "A" . $row . ":G" . $row;
            $format['outline'][] = "A" . $row . ":G" . $row;
            $format['size18'][] = "A" . $row . ":G" . $row;
            $format['fillOrange'][] = "A" . $row . ":G" . $row;

            #$format['hCenter'][] = "A".$row.":G".$row;
            $row++;

            #echo $mnth . "</br>";

            foreach ($sta as $value => $items) {

                $reSht->setCellValueByColumnAndRow($col, $row, $value);


                $format['merge'][] = "A" . $row . ":G" . $row;
                $format['hCenter'][] = "A" . $row . ":G" . $row;
                $format['fillDarkBlue'][] = "A" . $row . ":G" . $row;
                $format['size16'][] = "A" . $row . ":G" . $row;
                $format['outline'][] = "A" . $row . ":G" . $row;

                $row++;

                #echo $value . "</br>";

                for ($k = 0; $k < count($headerArray); $k++) {
                    $reSht->setCellValueByColumnAndRow($k + 1, $row, $headerArray[$k]);

                }
                $format['hCenter'][] = "A" . $row . ":G" . $row;
                $format['fillLightBlue'][] = "A" . $row . ":G" . $row;
                $format['size14'][] = "A" . $row . ":G" . $row;
                $format['outline'][] = "A" . $row . ":G" . $row;
                $row++;

                $rowStart = $row;

                $monthItemArray = ['originalAmount' => 0, 'reversed' => 0, 'ttlShr' => 0, '%rev' => 0];

                foreach ($items as $item) {
                    $reSht->setCellValueByColumnAndRow(1, $row, $item['vendorNum']);
                    $reSht->setCellValueByColumnAndRow(2, $row, $item['vendorName']);
                    $reSht->setCellValueByColumnAndRow(3, $row, $item['poNum']);
                    $reSht->setCellValueByColumnAndRow(4, $row, $item['originalAmt']);
                    $reSht->setCellValueByColumnAndRow(5, $row, $item['reversed']);
                    $reSht->setCellValueByColumnAndRow(6, $row, $item['ttlShr']);
                    $reSht->setCellValueByColumnAndRow(7, $row, $item['apComments']);

                    $monthItemArray['originalAmount'] += $item['originalAmt'];
                    $monthItemArray['reversed'] += $item['reversed'];
                    $monthItemArray['ttlShr'] += $item['ttlShr'];
                    $ttlItemArray[$branchNumber]['ttlCnt']++;
                    if ($item['reversed'] != "0") {
                        $ttlItemArray[$branchNumber]['revCnt']++;
                        $ttlItemArray[$branchNumber]['reversed'] += $item['reversed'];
                    }

                    if ($value === 'offset') {
                        $ttlItemArray[$branchNumber]['offCnt']++;
                        $ttlItemArray[$branchNumber]['offset'] += $item['ttlShr'];
                    }

                    if ($value === 'valid claim') {
                        $ttlItemArray[$branchNumber]['validClaim'] += $item['ttlShr'];
                        $ttlItemArray[$branchNumber]['validCnt']++;
                    }

                    if ($value === 'ok to pay') {
                        $ttlItemArray[$branchNumber]['okToPay'] += $item['ttlShr'];
                        $ttlItemArray[$branchNumber]['okToPayCnt']++;
                    }

                    if ($value === 'not returned') {
                        $ttlItemArray[$branchNumber]['notReturned'] += $item['ttlShr'];
                        $ttlItemArray[$branchNumber]['notReturnedCnt']++;
                    }

                    #echo $value . "</br>";


                    $row++;

                    #echo $item['vendorNum'] . ' : ' . $item['vendorName'] . ' : ' . $item['poNum'] . ' : ' . $item['originalAmt'] .
                    #" : " . $item['reversed'] . " : " . $item['ttlShr'] . " : " . $item['apComments'] . "</br>";
                }


                $ttlItemArray[$branchNumber]['originalAmount'] += $monthItemArray['originalAmount'];
                $ttlItemArray[$branchNumber]['reversed'] += $monthItemArray['reversed'];
                $ttlItemArray[$branchNumber]['ttlShr'] += $monthItemArray['ttlShr'];

                $reSht->setCellValueByColumnAndRow(4, $row, $monthItemArray['originalAmount']);
                $reSht->setCellValueByColumnAndRow(5, $row, $monthItemArray['reversed']);
                $reSht->setCellValueByColumnAndRow(6, $row, $monthItemArray['ttlShr']);

                $format['allBorders'][] = "A" . $rowStart . ":G" . ($row - 1);
                $format['outline'][] = "A" . $rowStart . ":G" . ($row - 1);
                $format['size12'][] = "A" . $rowStart . ":G" . ($row - 1);
                $row++;
                $row++;
            }
        }
    }

    $format['zAutoSize'][] = "A:G";
    $fmtSht = new Format();
    $fmtSht->formatSheet($reSht, $format);

    $format = [];
}
#var_dump($ttlItemArray);
$fmtSht->setLetters();

$letters = $fmtSht->getLetters();

$refmt->setActiveSheetIndexByName('Worksheet');
$resht = $refmt->getActiveSheet();
$resht->setTitle('Summary');

$r = 1;
$c = 1;

$resht->setCellValueByColumnAndRow($c, $r, 'AP Shrink Summary Jan - May 2019');
$r++;
$r++;

$summaryHeads = ['Branch Number', 'Branch Name', 'Original Amount', 'Total Count', 'Reversed', 'Reverse Count', '% Rev', 'Offset',
                 'Offset Count', '% Offset', 'Valid Claim', 'Valid Count', '% Valid', 'Ok To Pay', 'OTP Count', '% OTP',
                 'Not Returned', 'Not Returned Count', '% NR', 'Total Shrink'];

foreach ($summaryHeads as $head) {
    $resht->setCellValueByColumnAndRow($c, $r, $head);
    $c++;
}
$r++;

#var_dump($ttlItemArray);

foreach ($ttlItemArray as $brNumber => $brStf) {
    if (is_numeric($brNumber)) {
        var_dump($brNumber);
        $c = 1;
        $resht->setCellValueByColumnAndRow($c, $r, $brNumber);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $branchArray[$brNumber]);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['originalAmount']);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['ttlCnt']);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['reversed']);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['revCnt']);
        $c++;
        $percent = number_format(($brStf['revCnt'] / $brStf['ttlCnt']) * 100, 2);
        $resht->setCellValueByColumnAndRow($c, $r, $percent);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['offset']);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['offCnt']);
        $c++;
        $percent = number_format(($brStf['offCnt'] / $brStf['ttlCnt']) * 100, 2);
        $resht->setCellValueByColumnAndRow($c, $r, $percent);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['validClaim']);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['validCnt']);
        $c++;
        $percent = number_format(($brStf['validCnt'] / $brStf['ttlCnt']) * 100, 2);
        $resht->setCellValueByColumnAndRow($c, $r, $percent);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['okToPay']);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['okToPayCnt']);
        $c++;
        $percent = number_format(($brStf['okToPayCnt'] / $brStf['ttlCnt']) * 100, 2);
        $resht->setCellValueByColumnAndRow($c, $r, $percent);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['notReturned']);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['notReturnedCnt']);
        $c++;
        $percent = number_format(($brStf['notReturnedCnt'] / $brStf['ttlCnt']) * 100, 2);
        $resht->setCellValueByColumnAndRow($c, $r, $percent);
        $c++;
        $resht->setCellValueByColumnAndRow($c, $r, $brStf['ttlShr']);
        $r++;
    }
}

$resht->setCellValueByColumnAndRow(1, $r, 'Totals');

$ttlCols = [3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 8 => 'H', 9 => 'I', 11 => 'K', 12 => 'L', 14 => 'N', 15 => 'O', 17 => 'Q', 18 => 'R', 20 => 'T'];

foreach($ttlCols as $cols => $lets) {
    $resht->setCellValue($lets.$r, '=sum('.$lets.'4:'.$lets.($r-1).')');
}

$highRow = $resht->getHighestRow();
$highCol = $resht->getHighestColumn();

$format = [];

$format['merge'][] =


$date = date('m-d-y');

$file = '../output/reFmts/apShrinkAllocations ' . $date . '.xlsx';

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($refmt, "Xlsx");
$writer->save($file);

echo '<a href="' . $file . '" download><button class="ui-corner-all ui-button">Output File</button></a>';

#var_dump($ttlItemArray);

