<?php

require_once '../inc/config.php';
require_once '../class/Process.php';

$process = new Process();

$result = $process->query('SELECT * FROM branchinfo');

json_encode($result);
