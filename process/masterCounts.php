<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/20/2019
 * Time: 3:43 PM
 */

session_start();

require_once '../class/Process.php';

$insert = $_SESSION['writeData'][0]['sentData'];

$start = microtime(true);

$lnk = new Process();

#echo "insert array</br></br>";

#var_dump($insert);

foreach ($insert as $dept) {
    $qry = $lnk->query($dept);

    if (!$qry) {
        $errors[] = 'ERROR processing ' . $dept;
    }
}

if (!isset($errors)) {
    echo "Cycle Counts Processed";
} else {
    foreach($errors as $error) {
        echo $error . "</br>";
    }
}

#echo '<a href="' . $getFile . '" download><button class="ui-corner-all ui-button">Output File</button></a>';
