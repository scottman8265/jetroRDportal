<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/7/2019
 * Time: 5:25 AM
 */

#require ('Process.php');

class Arrays
{
    var $branchArray;
    var $twoDigitArray;
    var $repServerNumArray;
    var $auditorArray;
    var $regionalArray;
    var $directorArray;
    var $locationArray;
    var $auditArray;
    var $scoreArray;
    var $findingArray;
    var $peopleArray;
    var $xDockArray;
    var $corpScoreArray;
    var $corpBranchArray;
    var $questionArray;
    var $auditLU;
    var $weekDates;
    var $periodDates;
    var $deptInfo;
    var $lnk;
    var $sql;
    var $qry;
    var $params;

    public function __construct($array) {

        $this->lnk = new Process();

        foreach ($array as $func => $opts) {

            if ($func == 'scoreArray' || $func == 'findingArray') {
                $opts[3] = $this->getAuditOpts();
            }

            $sql = $this->createSql($opts);

            #echo $sql . "</br></br>";

            $this->$func($sql);

            #echo $sql. "</br>";

        }

    }

    private function createSql($opts) {

        $schema = $opts[1];
        $db = $opts[2];

        isset($opts[0]) ? $selectStr = implode(", ", $opts[0]) : $selectStr = null;
        isset($opts[3]) && !empty($opts[3]) ? $params = true : $params = false;

        $this->sql = "SELECT " . $selectStr . " FROM " . $schema . "." . $db;

        if ($params) {

            $this->sql .= " WHERE";

            $count = count($opts[3]);

            #var_dump($opts[3]);

            for ($i = 0; $i < $count; $i++) {

                $field = $opts[3][$i]['field'];
                $value = $opts[3][$i]['value'];
                $operand = $opts[3][$i]['operand'];

                if (!is_numeric($value)) {
                    $value = "'" . $value . "'";
                }

                $this->sql .= " " . $field . " = " . $value;

                if ($i < $count - 1 && $operand == 'and') {
                    $this->sql .= " AND";
                } elseif ($i < $count - 1 && $operand == 'or') {
                    $this->sql .= " OR";
                }

            }

        }

        return $this->sql;
    }

    #branchArray[branchNum][branchName, regional, auditor, location, twoDigit]
    #twoDigitArray[twoDigitNum][branchNum]
    #repServerNumArray[repServerNum][branchNum]
    public function branchArray($sql) {

        $array = [];

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['branchNum']] = ['branchName' => $info['branchName'], 'regional' => $info['regional'],
                                          'auditor' => $info['auditor'], 'location' => $info['location']];
            if(!is_null($info['_2DigNum'])) {
                $array2[$info['_2DigNum']] = $info['branchNum'];
            }
            if(!is_null($info['repServerNum'])) {
                $array3[$info['repServerNum']] = $info['branchNum'];
            }
        }

        $this->branchArray = $array;
        $this->twoDigitArray = $array2;
        $this->repServerNumArray;

    }

    #array[auditorID][fName, lName, fullName]
    public function auditorArray($sql) {

        $array = [];

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {

            $array = [];

            $array[$info['auditorID']] = ['fName' => $info['auditorFName'], "lName" => $info['auditorLName'],
                                          "fullName" => $info['auditorFName'] . " " . $info['auditorLName']];
        }
        $this->auditorArray = $array;

    }

    #array[regionID][fName, lName, fullName]
    public function regionalArray($sql) {

        $regArray = [];
        $dopArray = [];

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            if ($info['position'] == 'reg') {
                $regArray[$info['regionID']] = ['fName' => $info['fName'], "lName" => $info['lName'],
                                                "fullName" => $info['fName'] . " " . $info['lName']];
            } elseif ($info['position'] == 'dop') {
                $dopArray[$info['regionID']] = ['fName' => $info['fName'], "lName" => $info['lName'],
                                                "fullName" => $info['fName'] . " " . $info['lName']];
            }
        }

        $this->regionalArray = $regArray;
        $this->directorArray = $dopArray;
    }

    #array[year][period][id][branchNum, version]
    public function auditArray($sql) {

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['year']][$info['period']][$info['id']] = ['branchNum' => $info['branch'], 'version' => $info['version']];
        }

        $this->auditArray = $array;

    }

    #array[auditID][base/repeat][scores]
    public function scoreArray($sql) {

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {

            #echo $info['rep'] . "</br>";

            $scoreArray = ['totScore' => number_format($info['totScore'] * 100, 2),
                           'freshScore' => number_format($info['freshScore'] * 100, 2),
                           'adScore' => number_format($info['adScore'] * 100, 2),
                           'crScore' => number_format($info['crScore'] * 100, 2),
                           'daScore' => number_format($info['daScore'] * 100, 2),
                           'feScore' => number_format($info['feScore'] * 100, 2),
                           'flScore' => number_format($info['flScore'] * 100, 2),
                           'goScore' => number_format($info['goScore'] * 100, 2),
                           'icScore' => number_format($info['icScore'] * 100, 2),
                           'meScore' => number_format($info['meScore'] * 100, 2),
                           'pcScore' => number_format($info['pcScore'] * 100, 2),
                           'prScore' => number_format($info['prScore'] * 100, 2),
                           'rvScore' => number_format($info['rvScore'] * 100, 2),
                           'rpScore' => number_format($info['rpScore'] * 100, 2),
                           'saScore' => number_format($info['saScore'] * 100, 2),
                           'seScore' => number_format($info['seScore'] * 100, 2),
                           'swScore' => number_format($info['swScore'] * 100, 2),
                           'lqScore' => number_format($info['lqScore'] * 100, 2),
                           'fsScore' => number_format($info['fsScore'] * 100, 2),
                           'deptFreshScore' => number_format($info['deptFreshScore'] * 100, 2),
                           'deptFSafeScore' => number_format($info['deptFSafeScore'] * 100, 2),
                           'deptOpsScore' => number_format($info['deptOpsScore'] * 100, 2),
                           'deptSafeScore' => number_format($info['deptSafeScore'] * 100, 2)];
            if (!$info['rep']) {
                $array[$info['auditID']]['base'] = $scoreArray;
            } else {
                $array[$info['auditID']]['repeat'] = $scoreArray;
            }
        }

        $this->scoreArray = $array;

    }

    #array[locID][locCode, locName]
    public function locationArray($sql) {

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['locID']] = ['locCode' => $info['locCode'], 'locName' => $info['locName']];
        }

        $this->locationArray = $array;

    }

    #array[auditID][question, comment, id, rep]
    public function findingArray($sql) {

        #echo "</br>" . $sql . "</br>";

        $array = [];

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['auditID']][] = ['question' => $info['qCode'], 'comment' => $info['qComm'], 'id' => $info['findID'], 'rep'=>$info['rep']];
        }

        $this->findingArray = $array;

    }

    #array[auditCode][version][qNum, title, points]
    public function questionArray($sql) {
        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $codeArray = explode('.', $info['qCode']);
            $version = $codeArray[1];
            $auditCode = substr($codeArray[0], 0, 2);
            $qNum = substr($codeArray[0], 2);

            #echo "</br>" . $version . " " . $auditCode . " " . $qNum . "</br>";

            $array[$auditCode][$version][] = ['qNum' => $qNum, 'title' => $info['qTitle'], 'points' => $info['qPoints']];

        }

        $this->questionArray = $array;
    }

    #array[auditCode][auditName, corpID]
    public function auditLU($sql) {
        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['auditCode']] = ['auditName' => $info['auditName'], 'corpID' => $info['corpID']];
        }

        $this->auditLU = $array;

    }

    #array[wkNum][wkStart, wkEnd]
    public function weekDates($sql) {
        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['wkNum']] = ['wkStart' => $info['wkStart'], 'wkEnd' => $info['wkEnd']];
        }

        $this->weekDates = $array;

    }

    #array[monthNum][periodName, wkStart, wkEnd, perStart, PerEnd, julStart, julEnd]
    public function periodDates($sql) {
        # echo $sql . "</br></br>";

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['monthNum']] = ['periodName' => $info['periodName'], 'wkStart' => $info['weekStart'], 'wkEnd'=>$info['weekEnd'],
                                         'perStart'=>$info['perStart'], 'perEnd'=>$info['perEnd'], 'julStart'=>$info['julStart'], 'julEnd'=>$info['julEnd']];
        }

        $this->periodDates = $array;

    }

    #array[groupNum][deptNum, deptName, freq, countWeeks]
    public function deptInfo($sql) {
        # echo $sql . "</br></br>";

        $qry = $this->lnk->query($sql);

        foreach ($qry as $info) {
            $array[$info['groupNum']] = ['deptNum' => $info['deptNum'], 'deptName' => $info['deptName'], 'freq'=>$info['countFreq'],
                                         'countWeeks'=>$info['countWeeks']];
        }

        $this->deptInfo = $array;

    }

    public function getBranchArray() {
        return $this->branchArray;
    }

    public function getTwoDigitArray() {
        return $this->twoDigitArray;
    }

    public function getRepServerNumArray() {
        return $this->repServerNumArray;
    }

    public function getAuditorArray() {
        return $this->auditorArray;
    }

    public function getRegionalArray() {
        return $this->regionalArray;
    }

    public function getAuditArray() {
        return $this->auditArray;
    }

    private function getAuditOpts() {

        #var_dump($this->auditArray);
        foreach ($this->auditArray as $year => $periodArray) {
            foreach ($periodArray as $period => $auditArray) {
                foreach ($auditArray as $audit => $info) {
                    $opts[] = ['field' => 'auditID', 'value' => $audit, 'operand' => 'or'];
                }
            }
        }

        #var_dump($opts);

        return $opts;
    }

    public function getScoreArray() {
        return $this->scoreArray;
    }

    public function getLocationArray() {
        return $this->locationArray;
    }

    public function getFindingArray() {
        return $this->findingArray;
    }

    public function getQuestionArray() {
        return $this->questionArray;
    }

    public function getAuditLU() {
        return $this->auditLU;
    }

    public function getWeekDates() {
        return $this->weekDates;
    }

    public function getPeriodDates() {
        return $this->periodDates;
    }

    public function getDeptInfo() {
        return $this->deptInfo;
    }

    public function getDirectorArray() {
        return $this->directorArray;
    }
}