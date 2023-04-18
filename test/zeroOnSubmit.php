<?php

require ('../class/Process.php');

$lnk = new Process();

$selectSql = "SELECT submitteddate, id from auditanalysis.selfaudits";
$selectQry = $lnk->query($selectSql);

foreach ($selectQry as $x) {
    $new = '0' . $x['submitteddate'];

    $update = "UPDATE auditanalysis.selfaudits SET submitteddate = '" . $new . "' WHERE id = " . $x['id'];

    echo $update . "<br>";

    $lnk->query($update);
}