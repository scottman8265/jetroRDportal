<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 6/15/2019
 * Time: 9:18 AM
 */

require_once '../inc/readFileFunc.php';
require_once '../class/Arrays.php';
require_once '../class/Process.php';
require_once '../class/Format.php';
require_once '../vendor/autoload.php';

function checkBranch($branchNumber, $brNumber) {
    if ($branchNumber != $brNumber) {
        $branchNumber = substr($branchNumber, 1, 2);
    }

    return $branchNumber;
}

function writeSheet($spreadSheet, $file) {

    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadSheet, "Xlsx");
    $writer->save($file);
}

/**
 * @param $spreadsheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $lrnSheet
 */
function setIDsToBranch($spreadsheet, $lrnArray, $branchArray, $aodArray) {

    $sheet = $spreadsheet->getActiveSheet();

    $rows = $sheet->getHighestRow();

    $count = 0;
    $notFoundCount = 0;

    for ($i = 2; $i<$rows + 1; $i++) {
        $name = trim(strtoupper($sheet->getCellByColumnAndRow(2, $i)->getFormattedValue()));
        $branchNum = $sheet->getCellByColumnAndRow(1, $i)->getValue();
        $brID = $sheet->getCellByColumnAndRow(3, $i)->getValue();


        if (isset($aodArray[$name])) {
            $sheet->setCellValueByColumnAndRow(3, $i, $aodArray[$name]);
            $sheet->setCellValueByColumnAndRow(2, $i, $name);
            $branchArray[$branchNum][$name] = ['id' => $lrnArray[$name]['id']];
            $count++;
        } elseif (!preg_Match(',', $name)) {
            $expName = explode(" ", $name);
            $newName = trim($expName[1]) . " " . trim($expName[0]);
            if (isset($aodArray[$newName])) {
                $sheet->setCellValueByColumnAndRow(3, $i, $aodArray[$newName]);
                $branchArray[$branchNum][$name] = ['id' => $lrnArray[$newName]['id']];
                $count++;
            }
        } else {
            $expName2 = explode(" ", $name);
            $newName2 = trim($expName2[1]) . " " . trim($expName2[0]);
            if (isset($aodArray[$newName2])) {
                $sheet->setCellValueByColumnAndRow(3, $i, $aodArray[$newName2]);
                $branchArray[$branchNum][$name] = ['id' => $lrnArray[$newName2]['id']];
                $count++;
            }
        }
        $count++;
    }

    writeSheet($spreadsheet, "../output/LrnCtrOutput/neOperatorsWids.xlsx");
    return $branchArray;
}

$lrnCtrRpt = "C:\Users\Robert Brandt\OneDrive\OneDrive - Jetro Holdings LLC\Reports to Make\NE Equip Operator Info\lrnCtrEquipTestRpt 061519.xlsx";
$aodRpt = "C:\Users\Robert Brandt\OneDrive\OneDrive - Jetro Holdings LLC\Reports to Make\NE Equip Operator Info\AODActiveRoster.xlsx";
$branchDir = "C:\Users\Robert Brandt\OneDrive\OneDrive - Jetro Holdings LLC\Reports to Make\NE Equip Operator Info\branches with IDs";

$aodSsheet = readFileData($aodRpt, 'xlsx', true);
$aodSht = $aodSsheet->getActiveSheet();
$aodRows = $aodSht->getHighestRow();
$aodArray = [];

#reading aodReport and setting aodArray
for ($x=3; $x<$aodRows + 1; $x++) {
    $aodID = $aodSht->getCellByColumnAndRow(1, $x)->getValue();
    $aodFName = strtoupper($aodSht->getCellByColumnAndRow(2, $x)->getValue());
    $aodLName = strtoupper($aodSht->getCellByColumnAndRow(3, $x)->getValue());

    $aodArray[$aodID] = ['fName'=>$aodFName, 'lName'=>$aodLName];

    $aodNameArray[$aodFName . " " . $aodLName] = $aodID;

}

$lrnSsheet = readFileData($lrnCtrRpt, 'xlsx', true);
$lrnSht = $lrnSsheet->getActiveSheet();
$lrnRows = $lrnSht->getHighestRow();
$lrnArray = [];

#reading learning center report and setting lrnArray
for ($i = 2; $i<$lrnRows + 1; $i++) {

    $id = $lrnSht->getCellByColumnAndRow(2, $i)->getValue();
    $start = $lrnSht->getCellByColumnAndRow(5, $i)->getValue();
    $end = $lrnSht->getCellByColumnAndRow(6, $i)->getValue();
    $score = $lrnSht->getCellByColumnAndRow(7, $i)->getValue();

    if (isset($lrnArray[$name])) {
        if ($lrnArray[$id]['score'] < $score) {
            $lrnArray[$id]['score'] = $score;
        }
    } else {
        $lrnArray[$id] = ['start'=>$start, 'end'=>$end, 'score'=>$score];
    }
}

#reading branch reports and setting branch array
$branchArray = [];
foreach (scandir($branchDir) as $file) {
    if(strlen($file) > 2) {
        $branchNumber = substr($file, 0, 3);

        $tempBrName = substr($file, 3);
        $tempBrName2 = explode('.', $tempBrName);
        $branchName = $tempBrName2[0];

        $branchRpt = $branchDir . "/" . $file;

        $branchSsheet = readFileData($branchRpt, 'xlsx', true);
        $branchSht = $branchSsheet->getActiveSheet();
        $branchRows = $branchSht->getHighestRow();

        $branchCount = 0;

        for ($k = 2; $k < $branchRows + 1; $k++) {
            $col = 1;
            $brNumber = $branchSht->getCellByColumnAndRow($col, $k)->getValue();
            $col++;
            $brName = $branchSht->getCellByColumnAndRow($col, $k)->getValue();
            $col++;
            $iD = $branchSht->getCellByColumnAndRow($col, $k)->getValue();
            $col++;
            $dept = $branchSht->getCellByColumnAndRow($col, $k)->getValue();
            $col++;
            $equip = $branchSht->getCellByColumnAndRow($col, $k)->getValue();
            $col++;
            $cert = $branchSht->getCellByColumnAndRow($col, $k)->getFormattedValue();
            $col++;
            $reCert = $branchSht->getCellByColumnAndRow($col, $k)->getFormattedValue();

            if(is_null($iD)) {
                $iD = $k;
               };

            if (strlen($branchNumber) > 1) {
                $newBranchNumber = checkBranch($branchNumber, $brNumber);
            }

            if ($iD !== $k) {

                if ($newBranchNumber == $brNumber) {
                    $branchArray[$branchNumber][$iD] = ['dept' => $dept, 'equip' => $equip, 'cert' => $cert, 'reCert' => $reCert, 'branchName' => $branchName];
                    $branchNameArray[$branchNumber] = $branchName;
                    $branchCount++;
                }
            }
        }

    }
}
#$newBRArray = setIDsToBranch($branchSsheet, $lrnArray, $branchArray, $aodNameArray);

$lrnCtrComp = [];
$lrnCtrNotComp = [];

foreach ($branchArray as $branch => $IDs) {

    foreach ($IDs as $ID => $details) {

        $Dept = $branchArray[$branch][$ID]['dept'];
        $Equip = $branchArray[$branch][$ID]['equip'];
        $Cert = $branchArray[$branch][$ID]['cert'];
        $ReCert = $branchArray[$branch][$ID]['reCert'];
        $aodFName = $aodArray[$ID]['fName'];
        $aodLName = $aodArray[$ID]['lName'];

        if (isset($lrnArray[$ID])) {
            $Start = $lrnArray[$ID]['start'];
            $End = $lrnArray[$ID]['end'];
            $Score = $lrnArray[$ID]['score'];

            $lrnCtrComp[$branch]['comp'][$ID] = ['aodFName' => $aodFName, 'aodLName'=>$aodLName, 'Dept'=>$Dept,
                                                 'Equip'=>$Equip, 'Cert'=>$Cert, 'reCert'=>$ReCert, 'testStart'=>$Start,
                                                 'testEnd' => $End, 'testScore'=>$Score];
        } else {
            $lrnCtrComp[$branch]['notComp'][$ID] = ['aodFName' => $aodFName, 'aodLName'=>$aodLName, 'Dept'=>$Dept,
                                                    'Equip'=>$Equip, 'Cert'=>$Cert, 'reCert'=>$ReCert];
        }
    }
}

$lrnCtrSsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$sht = $lrnCtrSsheet->getActiveSheet();

$sht->setTitle('Quick Summary');

$sumHeaders = ['Branch Number', 'Branch Name', '# Completed', '# Not Completed'];
$compBranchHeaders = ['ID', 'First Name', 'Last Name', 'Department', 'Cert Equipment', 'Cert Date',
                  'ReCert Date', 'LC Start Date', 'LC Comp Date', 'LC Test Score'];
$notCompBranchHeaders = ['ID', 'First Name', 'Last Name', 'Department', 'Cert Equipment', 'Cert Date',
                         'ReCert Date'];

$row = 1;

$sht->setCellValueByColumnAndRow(1, $row, 'East LC Equip Summary');
$row++;
$col = 1;

#write summary headers
foreach ($sumHeaders as $head1) {
    $sht->setCellValueByColumnAndRow($col, $row, $head1);
    $col++;
}

$row++;

#write summary info
foreach ($lrnCtrComp as $brNum => $status) {
    $col = 1;
    $compCount = count($status['comp']);
    $notCompCount = count($status['notComp']);

    $sht->setCellValueByColumnAndRow($col, $row, $brNum);
    $sht->getCellByColumnAndRow($col, $row)->getHyperlink()->setUrl("sheet://'".$brNum."'!A1");
    $col++;
    $sht->setCellValueByColumnAndRow($col, $row, $branchNameArray[$brNum]);
    $col++;
    $sht->setCellValueByColumnAndRow($col, $row, $compCount);
    $col++;
    $sht->setCellValueByColumnAndRow($col, $row, $notCompCount);
    $row++;
   echo $brNum . ": [comp: " . $compCount . "] [not comp: " . $notCompCount . "]</br>";
}

foreach ($lrnCtrComp as $brNum => $status) {

    $newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet($lrnCtrSsheet, (string)$brNum);
    $lrnCtrSsheet->addSheet($newSheet);
    $lrnCtrSsheet->setActiveSheetIndexByName((string)$brNum);
    $sht = $lrnCtrSsheet->getActiveSheet();

    $row = 1;

    $sht->setCellValueByColumnAndRow(1, $row, $brNum . " - " . $branchNameArray[$brNum] . " LC Completion Status)");
    $row++;
    $sht->setCellValueByColumnAndRow(1, $row, "Back To Summary");
    $sht->getCellByColumnAndRow(1, $row)->getHyperlink()->setUrl("sheet://'Quick Summary'!A1");
    $row++;
    $row++;
    $col = 1;

    $sht->setCellValueByColumnAndRow($col, $row, 'Not Certified on Learning Center');
    $row++;

    foreach ($notCompBranchHeaders as $cc) {
        $sht->setCellValueByColumnAndRow($col, $row, $cc);
        $col++;
    }

    $row++;

    foreach ($status['notComp'] as $Id => $det) {
        $col = 1;

        $sht->setCellValueByColumnAndRow($col, $row, $Id);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $det['aodFName']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $det['aodLName']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $det['Dept'])  ;
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $det['Equip']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $det['Cert']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $det['reCert']);
        $row++;
    }

    $row++;
    $sht->setCellValueByColumnAndRow(1, $row, 'Certified on Learning Center');
    $row++;

    $col = 1;
    foreach ($compBranchHeaders as $c) {
        $sht->setCellValueByColumnAndRow($col, $row, $c);
        $col++;
    }

    $row++;


    foreach ($status['comp'] as $Id => $Cdet) {
        $col = 1;

        $sht->setCellValueByColumnAndRow($col, $row, $Id);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['aodFName']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['aodLName']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['Dept'])  ;
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['Equip']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['Cert']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['reCert']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['testStart']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['testEnd']);
        $col++;
        $sht->setCellValueByColumnAndRow($col, $row, $Cdet['testScore']);
        $row++;
    }

}

writeSheet($lrnCtrSsheet, '../output/LrnCtrOutput/neLCequipComp.xlsx');