<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/20/2019
 * Time: 6:44 AM
 */

session_start();

set_time_limit(300);
ini_set("log_errors", 1);
ini_set("error_log", "logs/php-error.log" . date('ymd'));
ini_set('memory_limit', '1G');
date_default_timezone_set('America/Chicago');

use PhpOffice\PhpSpreadsheet\Reader\Exception;

if(file_exists('../inc/readFileFunc.php')) {require_once '../inc/readFileFunc.php';} else {require_once 'inc/readFileFunc.php';}
#if(file_exists('class/Arrays.php')) {require_once '../class/Arrays.php';} else {require_once 'class/Arrays.php';}
if(file_exists('../class/Process.php')) {require_once '../class/Process.php';} else {require_once 'class/Process.php';}
if(file_exists('../vendor/autoload.php')) {require_once '../vendor/autoload.php';} else {require_once 'vendor/autoload.php';}
if(file_exists('../inc/getJCMSArrays.php')) {require_once '../inc/getJCMSArrays.php';} else {require_once 'inc/getJCMSArrays.php';}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 */
function cycleCounts($spreadSheet, $wkNum)
{

    $start = microtime(true);
    $insert = [];
    $timed = null;
    $output = null;
    $ccBranches = null;
    $errors = [];

    include 'parse/masterCounts.php';
    #include 'process/cycleCounts.php';
    $end = microtime(true);

    $timed = timed($start, $end, 'Read Cycle Counts');

    return ['sentData' => $insert, 'timed' => $timed, 'output' => $ccBranches];
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 */
function branchCounts($spreadSheet, $wkNum, $name, $bcp)
{

    $branchNums = substr($name, 0, 3);

    $data = null;
    $output = null;
    $branchNum = null;

    include 'parse/branchCounts.php';

    if (!is_null($bcp)) {
        $_SESSION['bcp'] = $bcp;
    }

    $end = microtime(true);

    return false;
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 */
function jcmsTesting($spreadSheet)
{
    $return = [];
    $output = null;
    $parsedFailed = 0;
    $parsedNotTaken = 0;
    $processedFailed = 0;
    $processedNotTaken = 0;

    include 'parse/jcmsTesting.php';
    include 'process/jcmsTesting.php';

    $sendData = ['parsedNotTaken' => $parsedNotTaken, 'processedNotTaken' => $processedNotTaken, 'parsedFailed' => $parsedFailed, 'processedFailed' => $processedFailed];

    return ['sentData' => $return];
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 */
function audits($spreadSheet, $count = null, $fileName)
{

    $testReturn = [];
    $return = [];

    $sheet = $spreadSheet->getSheet(0);

    if(file_exists('../parse/audits.php')) {include_once '../parse/audits.php';} else {include_once 'parse/audits.php';}
    if(file_exists('../process/audits.php')) {include_once '../process/audits.php';} else {include_once 'process/audits.php';}

    return $return;
}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 */
function self($spreadSheet, $count)
{
    $timed = null;

    #$start = microtime(true);

    $testReturn = [];

    $spreadSheet->setActiveSheetIndexByName("OPS AUDIT RECAP");
    $sheet = $spreadSheet->getActiveSheet();

    include 'parse/self.php';

    return ['sentData' => $testReturn, 'output' => 'test', 'timed' => $timed];
}

function verifyAudits($name)
{
    $lnk = new Process();

    $piece = explode(" ", $name);
    $auditYear = $piece[0];
    $auditQuarter = $piece[1];
    $auditBranch = $piece[2];
    $searchSQL = "SELECT * FROM enteredaudits WHERE year = ? AND period = ? AND branch = ?";
    $searchParams = [$auditYear, $auditQuarter, $auditBranch];
    $searchQRY = $lnk->query($searchSQL, $searchParams);

    if (!$searchQRY) {
        return false;
    }
    return true;
}

function verifySelf($name)
{
    $lnk = new Process();

    $piece = explode(" ", $name);
    $branchNum = $piece[0];
    $selfMonth = $piece[3];
    $year = substr($selfMonth, 2, 2);
    $month = substr($selfMonth, 0, 2);


    $searchSQL = "SELECT * FROM selfaudits WHERE year = ? AND month = ? AND branch = ?";
    $searchParams = [$year, $month, $branchNum];
    $searchQRY = $lnk->query($search);

    if (!$searchQRY) {
        return false;
    }
    return true;
}
console.log('inside upload.php');
$file = isset($_POST['files']) ? $_POST['files'] : null;
$fileType = isset($_POST['fileType']) ? $_POST['fileType'] : null;
$wkNum = isset($_POST['wkNum']) ? $_POST['wkNum'] : null;
$response = [];

#sets directory variable
switch ($fileType) {
    case 'self':
        $dir = 'input/auditsSelf';
        break;
    case 'audits':
        $dir = 'input/auditsCorp';
        break;
    case 'branchCounts':
        $dir = 'input/branchCounts';
        break;
    case 'cycleCounts':
        $dir = 'input/masterCounts';
        break;
    case 'jcmsTesting':
        $dir = 'input/jcms';
        break;
    default:
        $dir = null;
        break;
}

switch ($fileType) {
    case 'audits':
        $verified = verifyAudits($file);
        break;
    case 'self':
        $verified = verifySelf($file);
        break;
}

//!verified means audit has not been processes or this is a different fileType to be processed
#if (!$verified) {

    #$spreadSheet = readFileData($file, $count);
    try {
        $reader      = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file);
        $reader->setReadDataOnly(TRUE);
        $spreadSheet = $reader->load($file);
    } catch (Exception $e) {
        echo $e->getMessage();
    }
    $response = [];

    switch ($fileType) {
        case 'self':
        case 'audits':
            $response = $fileType($spreadSheet, $count, $file);
            break;
        case 'branchCounts':
            $response = $fileType($spreadSheet, $wkNum, $file);
            break;
        case 'cycleCounts':
            $response = $fileType($spreadSheet, $wkNum);
            break;
        case 'jcmsTesting':
            $response = $fileType($spreadSheet);
            break;
    }
    $count++;
#}

if ($fileType === 'audits' || $fileType === 'self') {

    $name = explode(' - ', $file);

    if (!$verified) {
        $response['output'] = "<div style='margin-top:15px'>" . $name[0] . ' processed</div>';
    }
}

echo json_encode($response);


