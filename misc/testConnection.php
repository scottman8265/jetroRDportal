<?php

require_once('../class/Process.php');

// Instantiate a new Process object
$process = new Process();

// Query the database
$result = $process->query('SELECT * FROM branches');

// Dump the results
var_dump($result);