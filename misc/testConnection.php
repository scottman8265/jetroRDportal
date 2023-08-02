<?php

// Instantiate a new Process object
$process = new Process();

// Query the database
$result = $process->query('SELECT * FROM branchinfo');

// Dump the results
var_dump($result);