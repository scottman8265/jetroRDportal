<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/25/2019
 * Time: 6:18 AM
 */

function getAuditID($array)
{

    $lnk = new Process();

    foreach ($array as $field => $value) {
        $fields[] = $field;
        $values[] = $value;
    }

    $fList = implode(', ', $fields);
    $vList = implode(', ', $values);

    $sql = "INSERT INTO enteredaudits (" . $fList . ") VALUES (" . $vList . ")";
    $qry = $lnk->query($sql);



    if (!$qry) {
        echo "nothing to enter";
        return false;
           } else {
        echo $sql;
        return $lnk->getLastID();
            }
}

/**
 * @param $scores
 * @param int $auditID
 * @param $marker
 * @param $lnk  Process
 */
function writeScores($scores, $auditID = 0, $marker, $lnk)
{

    echo " inside write Scores *** ";

    $errors = null;
    $sql = [];

    $scores['rep'][] = $scores['base'][] = $auditID;
    $fields = ['totScore', 'freshScore', 'adScore', 'crScore', 'daScore', 'flScore', 'feScore', 'goScore',
        'icScore', 'meScore', 'pcScore', 'prScore', 'rvScore', 'rpScore', 'saScore', 'seScore', 'swScore',
        'lqScore', 'fsScore', 'deptFreshScore', 'deptFSafeScore', 'deptOpsScore', 'deptSafeScore', 'rep', 'auditID'];

    $repScoreStr = implode(', ', $scores['rep']);
    $baseScoreStr = implode(', ', $scores['base']);
    $fieldsStr = implode(', ', $fields);

    $sql[] = "INSERT INTO auditscores (" . $fieldsStr . ") VALUES (" . $repScoreStr . "); \n";
    $repSQL = "INSERT INTO auditscores (" . $fieldsStr . ") VALUES (" . $repScoreStr . ")";
    $sql[] = "INSERT INTO auditscores (" . $fieldsStr . ") VALUES (" . $baseScoreStr . "); \n";
    $baseSQL = "INSERT INTO auditscores (" . $fieldsStr . ") VALUES (" . $baseScoreStr . ")";
    file_put_contents('output/auditSQL/insertSQLs_' . $marker . '.sql', $sql, FILE_APPEND);

    $lnk->query($repSQL);
    $lnk->query($baseSQL);

}

/**
 * @param $findings
 * @param int $auditID
 * @param $marker
 * @param $lnk Process
 */
function writeFindings($findings, $auditID = 0, $marker, $lnk)
{

    echo " inside write Findings *** ";

    $sqlArray = [];
    foreach ($findings as $qCode => $info) {
        $sqlArray[] = "INSERT INTO auditfindings (auditID, qCode, qComm, rep) VALUES (" . $auditID . ", '" . $qCode . "', '" . $info['comm'] . "', " . $info['rep'] . "); \n";
        #$sql = "INSERT INTO auditfindings (auditID, qCode, qComm, rep) VALUES (" . $auditID . ", '" . $qCode . "', '" . $info['comm'] . "', " . $info['rep'] . "); \n";
        #$lnk->query($sql);
    }
    #file_put_contents('output/auditSQL/insertSQLs_' . $marker . '.sql', $sqlArray, FILE_APPEND);
}

/**
 * @param $people  array
 * @param int $auditID
 * @param $marker  string
 * @param $lnk  Process
 */
function writePeople($people, $auditID = 0, $marker, $lnk)
{

    echo " inside write People *** ";

    $people[] = $auditID;

    $fields = ['auditor', 'bm', 'abm1', 'abm2', 'ad', 'cr', 'da', 'fl', 'fe', 'go', 'ic', 'me', 'pc', 'pr', 'rv', 'rp', 'sa', 'se', 'sw',
        'lq', 'auditID'];

    $fieldStr = implode(', ', $fields);
    $peopleStr = implode(', ', $people);

    $sql = "INSERT INTO auditpeople (" . $fieldStr . ") VALUES (" . $peopleStr . "); \n";
    file_put_contents('output/auditSQL/insertSQLs_' . $marker . '.sql', $sql, FILE_APPEND);
    $lnk->query($sql);
}

echo "inside process file *** ";

$time = new DateTime();
$marker = $time->format('ymd');

$lnk = new Process();

$auditDates = "'" . substr($auditDates, 5) . "'";

$filePieces = explode(' ', $fileName);
$year = substr($filePieces[0], 17);
$quar = "'" . $filePieces[1] . "'";
$branch = $filePieces[3];

$idArray = ['year' => $year, 'period' => $quar, 'branch' => $branch, 'version' => $version, 'auditDate' => $auditDates];

$auditID = getAuditID($idArray);

if (!$auditID) {
      echo 'failure';
} else {
     echo $auditID . "\n";
}

writeScores($scores, $auditID, $marker, $lnk);
writeFindings($findings, $auditID, $marker, $lnk);
#writePeople($people, $auditID, $marker, $lnk);
