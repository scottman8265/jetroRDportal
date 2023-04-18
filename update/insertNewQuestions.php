<?php
    /**
     * Created by PhpStorm.
     * User: Scott
     * Date: 6/24/2018
     * Time: 11:05 AM
     */

use PhpOffice\PhpSpreadsheet\Reader\Exception;

require ('../vendor/autoload.php');
require ('../class/Process.php');

$lnk = new Process();

$file = "C:\Users\Robert Brandt\OneDrive\OneDrive - Jetro Holdings LLC\Self Audits\\0219 Feb\\449 PITTSBURGH 030619 0219.xls";

$reader = \PhpOffice\PhpSpreadsheet\IOFactory ::createReader('Xls');
$reader->setReadDataOnly(true);

$spreadSheet = $reader->load($file);
$spreadSheet->setActiveSheetIndexByName('OPS AUDIT RECAP');
$sheet = $spreadSheet->getActiveSheet();

$auditSql = "SELECT auditName, auditCode from auditanalysis.auditlookup";
$auditQry = $lnk->query($auditSql);

foreach ($auditQry as $x) {
    $audits[$x['auditName']] = $x['auditCode'];
}

#var_dump($audits);

$rowStart = 2;
$rowEnd = 531;
$version = 4;

$colArray = [6, 7, 9, 10];

for ($i = $rowStart; $i<$rowEnd; $i++) {
    $qNum = (string)$sheet->getCellByColumnAndRow(6, $i)->getValue();
    $qPoints = $sheet->getCellByColumnAndRow(7, $i)->getOldCalculatedValue();
    $qAudit = $sheet->getCellByColumnAndRow(9, $i)->getValue();
    $qTitle = $sheet->getCellByColumnAndRow(10, $i)->getValue();
    $qCode = $audits[$qAudit] . $qNum . "." . $version;

    if ($audits[$qAudit] != 'WC') {
        $insertSql = "INSERT INTO auditanalysis.auditquestions (qCode, qTitle, qPoints) VALUES (?, ?, ?)";
        $insertParams = [$qCode, $qTitle, $qPoints];
        $insertQry = $lnk->query($insertSql, $insertParams);
    }

}


/*if ($data["F"] != NULL) {
    $qNum = (string)$data["F"];
    $qPoints = $data["G"];
    $qAudit = $data["I"];
    $qTitle = trim($data["J"]);
    $qCode = $audits[$qAudit] . $qNum;

    $sql = "INSERT INTO auditquestions (qCode, qTitle, qPoints) VALUES (?, ?, ?)";
    $params = [$qCode, $qTitle, $qPoints];

    $auditLnk->query($sql, $params);

}*/