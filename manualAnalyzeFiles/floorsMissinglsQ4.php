<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/24/2019
 * Time: 4:09 PM
 */

require '../class/Process.php';

$lnk = new Process();

$branchArray = [114, 523, 542, 547, 548, 559, 564, 565, 576, 583, 584, 586, 401,
    413, 415, 418, 419, 422, 424, 425, 426, 427, 430, 431, 433, 461];

$finding = 'FL14.2';

foreach ($branchArray as $branch) {

    $auditSql = "SELECT id from enteredaudits WHERE period = 'Q4' and branch = " .$branch;
    $auditQry = $lnk->query($auditSql);

    echo $auditQry[0]['id'] . "</br>";
    var_dump($auditQry);

    $findingSql = "SELECT findID FROM auditfindings WHERE auditID = ? AND qCode = ?";
    $findingParams = [$auditQry[0]['id'], $finding];
    $findingQry = $lnk->query($findingSql, $findingParams);

    if ($findingQry) {
        $response['failed'][] = $branch;
    } else {
        $response['passed'][] = $branch;
    }
}

foreach ($response as $pf => $x) {
    echo "</br>". $pf . "</br>";
    foreach ($x as $branch) {
        echo $branch . "</br>";
    }
}