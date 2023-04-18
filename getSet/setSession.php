<?php

session_start();

$_SESSION = [];

foreach($_POST as $item => $value) {
    $_SESSION[$item] = $value;
}

var_dump($_SESSION);