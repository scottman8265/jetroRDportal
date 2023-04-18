<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/25/2019
 * Time: 6:18 AM
 */

session_start();

#$session = var_export($_SESSION, true);

#echo json_encode($_SESSION);

$count = count($_SESSION['writeData']);

echo $count;

var_dump($_SESSION);

echo "hello susan";

#echo count($_SESSION['writeData']);
