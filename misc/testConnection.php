<?php

require_once '../inc/config.php';
require_once '../class/Process.php';

$process = new Process();

$lnk = $process->connect();

$result = $lnk->query('SELECT * FROM branchinfo');

json_encode($result);
