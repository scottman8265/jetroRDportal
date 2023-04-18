<?php

require_once ('../class/Process.php');

$wkNum = $_POST['wkNum'];

$lnk = new Process();

$sql = "SELECT wkEnd FROM branchInfo.weekdates WHERE wkNum = " . $wkNum;
$qry = $lnk->query($sql);

$wkEnd = $qry[0]['wkEnd'];

echo $wkEnd;
