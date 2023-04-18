<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/20/2019
 * Time: 6:44 AM
 */

ini_set("log_errors", 1);
ini_set("error_log", "../logs/php-error.log");

require_once '../inc/readFileFunc.php';

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\
 */
function cycleCounts($spreadSheet) {

    $insert = [];
    $timed = null;

    include '../parse/masterCounts.php';

    return ['insert'=>$insert, 'time'=>$timed];
}

#use PhpOffice\PhpSpreadsheet\Reader\Exception;

isset($_FILES) ? $file = $_FILES['file']['tmp_name'] : $file = "error";
isset($_POST['fileType']) ? $fileType = $_POST['fileType'] : $fileType = 'error';

$spreadSheet = readFileData($file);

$test =$fileType($spreadSheet);

#$test = $spreadSheet->getActiveSheet()->getCell("A2")->getValue();
#$test = $fileType;
#$test = $fileType;
#$test = 'WTF is going on?!?';

echo json_encode(['test'=>$test]);