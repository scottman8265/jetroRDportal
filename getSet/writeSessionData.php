<?php

session_start();

/*$_SESSION['fileType'] = $_POST['fileType'];
$_SESSION['count'] = $_POST['count'];
$_SESSION['fileCount'] = $_POST['fileCount'];*/

foreach ($_POST as $key => $value) {
    $_SESSION[$key] = $value;
}
