<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/16/2019
 * Time: 6:11 AM
 */

/**
 * @param $fileName
 * @param $ext
 * @param bool $dataOnly
 * @return \PhpOffice\PhpSpreadsheet\Spreadsheet|string|array
 */

ini_set('memory_limit', '1024M');
function readFileData($fileName, $count = null) {

    try {
       $spreadSheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileName);
    }
    catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
        return $e->getMessage() .' ?????';
    }
    return $spreadSheet ;
}