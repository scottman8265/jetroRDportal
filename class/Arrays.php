<?php

/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/7/2019
 * Time: 5:25 AM
 */

require_once '../class/Process.php';
require_once '../inc/config.php';

class Arrays extends Process
{

    private $branchNums = [];
    private $activeBranches = [];
    public $locationRequest = [];
    public $xDock;
    public $seafood;
    public $branchName;
    public $region;
    public $regional;
    public $director;
    public $locationType;
    public $smwares;
    public $liquor;
    public $auditLU;
    public $weekDates;
    public $periodDates;
    public $deptInfo;
    private $sql = null;


    public function __construct($x = null)
    {
        parent::__construct();

        $this->setBranchNums($x);
    }

    /**
     * Summary of getBranchInfo
     * @param int $x branch number
     * @param string $y field to query
     * @return mixed
     */
    private function getBranchInfo($x, $y)
    {
        $result = null;

        $sql = "SELECT ? FROM jrdStuff.branchInfo WHERE branchId = ?";
        $params = [$y, $x];
        $result = Process::query($sql, $params);

        return $result;
    }

    private function setBranchNums($x = null)
    {
        $this->branchNums = $x == null ? $this->getActiveBranches() : $x;
    }

    public function getBranchNums()
    {
        if (empty($this->branchNums)) {
            $this->setBranchNums();
        }
        return $this->branchNums;
    }

    private function setActiveBranches()
    {
        $arr = [];

        $sql = "SELECT branchId AS branchNum FROM jrdStuff.branchInfo WHERE active = 1 SORT BY branchNum ASC";

        $qry = parent::query($sql);

        foreach ($qry as $row) {
            $arr[] = $row['branchNum'];
        }

        $this->activeBranches = $arr;
    }

    public function getActiveBranches()
    {
        if (empty($this->activeBranches)) {
            $this->setActiveBranches();
        }
        return $this->activeBranches;
    }


    private function setBranchName($x)
    {
        $sql = "SELECT branchName FROM jrdStuff.branchNames WHERE branchNameId = " . $this->getBranchInfo($x, 'branchName');
        $this->branchName = Process::query($sql);
    }

    public function getBranchName($x)
    {
        if (!$this->branchName) {
            $this->setBranchName($x);
        }
        return $this->branchName;
    }

    private function setRegion($x)
    {
        $sql = "Select region FROM jrdStuff.regions WHERE regionId = " . $this->getBranchInfo($x, 'region');
        $this->region = Process::query($sql);
    }

    public function getRegion($x)
    {
        if (!$this->region) {
            $this->setRegion($x);
        }
        return $this->region;
    }

    private function setRegional($x)
    {
        $sql = "Select regional FROM jrdStuff.fieldTeamMembers WHERE tmId = " . $this->getBranchInfo($x, 'regional');
        $this->regional = Process::query($sql);
    }

    public function getRegional($x)
    {
        if (!$this->regional) {
            $this->setRegional($x);
        }
        return $this->regional;
    }

    private function setDirector($x)
    {
        $sql = "Select director FROM jrdStuff.fieldTeamMembers WHERE tmId = " . $this->getBranchInfo($x, 'director');
        $this->director = Process::query($sql);
    }

    public function getDirector($x)
    {
        if (!$this->director) {
            $this->setDirector($x);
        }
        return $this->director;
    }

    private function setXDock($x)
    {
        $sql = "Select xDock FROM jrdStuff.xDocks WHERE xDockId = " . $this->getBranchInfo($x, 'xDock');
        $this->xDock = Process::query("Select xDock FROM jrdStuff.xDocks WHERE xDockId = " . $x);
    }

    public function getxDock($x)
    {
        if (!$this->xDock) {
            $this->setxDock($x);
        }
        return $this->xDock;
    }

    private function setSeafood($x)
    {
        $this->seafood = $this->getBranchInfo($x, 'seafood');
    }

    public function getSeafood($x)
    {
        if (!$this->seafood) {
            $this->setSeafood($x);
        }
        return $this->seafood;
    }

    private function setSmwares($x)
    {
        $this->smwares = $this->getBranchInfo($x, 'smwares');
    }

    public function getSmwares($x)
    {
        if (!$this->smwares) {
            $this->setSmwares($x);
        }
        return $this->smwares;
    }

    private function setLocationType($x)
    {
        $sql = "Select locationType FROM jrdStuff.locationTypes WHERE locationTypeId = " . $this->getBranchInfo($x, 'locationType');
        $this->locationType = Process::query($sql);
    }

    public function getLocationType($x)
    {
        if (!$this->locationType) {
            $this->setLocationType($x);
        }
        return $this->locationType;
    }

    private function setLiquor($x)
    {
        $this->liquor = $this->getBranchInfo($x, 'liquor');
    }

    public function getLiquor($x)
    {
        if (!$this->liquor) {
            $this->setLiquor($x);
        }
        return $this->liquor;
    }

    private function setLocationRequest()
    {

        $array = [];

        foreach ($this->branchNums as $x) {
            if ($x['active']) {
                $array[$x['x']] = ['branchName' => $x['branchName'], 'regional' => $x['regional'], 'director' => $x['director'], 'xDock' => $x['xDock'], 'region' => $x['region'], 'locationType' => $x['locationType'], 'seafood' => $x['seafood'], 'smwares' => $x['smwares'], 'liquor' => $x['liquor']];
            }
        }
        $this->locationRequest = $array;
    }

    private function getLocationRequest()
    {
        if (empty($this->locationRequest)) {
            $this->setLocationRequest();
        }

        return $this->locationRequest;
    }


    #array[auditorID][fName, lName, fullName]
    public function auditorArray($sql)
    {

        $array = [];

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {

            $array = [];

            $array[$x['auditorID']] = [
                'fName' => $x['auditorFName'], "lName" => $x['auditorLName'],
                "fullName" => $x['auditorFName'] . " " . $x['auditorLName']
            ];
        }
        $this->auditorArray = $array;
    }

    #array[regionID][fName, lName, fullName]
    public function regionalArray($sql)
    {

        $regArray = [];
        $dopArray = [];

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            if ($x['position'] == 'reg') {
                $regArray[$x['regionID']] = [
                    'fName' => $x['fName'], "lName" => $x['lName'],
                    "fullName" => $x['fName'] . " " . $x['lName']
                ];
            } elseif ($x['position'] == 'dop') {
                $dopArray[$x['regionID']] = [
                    'fName' => $x['fName'], "lName" => $x['lName'],
                    "fullName" => $x['fName'] . " " . $x['lName']
                ];
            }
        }

        $this->regionalArray = $regArray;
        $this->directorArray = $dopArray;
    }

    #array[year][period][id][x, version]
    public function auditArray($sql)
    {

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $array[$x['year']][$x['period']][$x['id']] = ['x' => $x['branch'], 'version' => $x['version']];
        }

        $this->auditArray = $array;
    }

    #array[auditID][base/repeat][scores]
    public function scoreArray($sql)
    {

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {

            #echo $x['rep'] . "</br>";

            $scoreArray = [
                'totScore' => number_format($x['totScore'] * 100, 2),
                'freshScore' => number_format($x['freshScore'] * 100, 2),
                'adScore' => number_format($x['adScore'] * 100, 2),
                'crScore' => number_format($x['crScore'] * 100, 2),
                'daScore' => number_format($x['daScore'] * 100, 2),
                'feScore' => number_format($x['feScore'] * 100, 2),
                'flScore' => number_format($x['flScore'] * 100, 2),
                'goScore' => number_format($x['goScore'] * 100, 2),
                'icScore' => number_format($x['icScore'] * 100, 2),
                'meScore' => number_format($x['meScore'] * 100, 2),
                'pcScore' => number_format($x['pcScore'] * 100, 2),
                'prScore' => number_format($x['prScore'] * 100, 2),
                'rvScore' => number_format($x['rvScore'] * 100, 2),
                'rpScore' => number_format($x['rpScore'] * 100, 2),
                'saScore' => number_format($x['saScore'] * 100, 2),
                'seScore' => number_format($x['seScore'] * 100, 2),
                'swScore' => number_format($x['swScore'] * 100, 2),
                'lqScore' => number_format($x['lqScore'] * 100, 2),
                'fsScore' => number_format($x['fsScore'] * 100, 2),
                'deptFreshScore' => number_format($x['deptFreshScore'] * 100, 2),
                'deptFSafeScore' => number_format($x['deptFSafeScore'] * 100, 2),
                'deptOpsScore' => number_format($x['deptOpsScore'] * 100, 2),
                'deptSafeScore' => number_format($x['deptSafeScore'] * 100, 2)
            ];
            if (!$x['rep']) {
                $array[$x['auditID']]['base'] = $scoreArray;
            } else {
                $array[$x['auditID']]['repeat'] = $scoreArray;
            }
        }

        $this->scoreArray = $array;
    }

    #array[locID][locCode, locName]
    public function locationArray($sql)
    {

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $array[$x['locID']] = ['locCode' => $x['locCode'], 'locName' => $x['locName']];
        }

        $this->locationArray = $array;
    }

    #array[auditID][question, comment, id, rep]
    public function findingArray($sql)
    {

        #echo "</br>" . $sql . "</br>";

        $array = [];

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $array[$x['auditID']][] = ['question' => $x['qCode'], 'comment' => $x['qComm'], 'id' => $x['findID'], 'rep' => $x['rep']];
        }

        $this->findingArray = $array;
    }

    #array[auditCode][version][qNum, title, points]
    public function questionArray($sql)
    {
        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $codeArray = explode('.', $x['qCode']);
            $version = $codeArray[1];
            $auditCode = substr($codeArray[0], 0, 2);
            $qNum = substr($codeArray[0], 2);

            #echo "</br>" . $version . " " . $auditCode . " " . $qNum . "</br>";

            $array[$auditCode][$version][] = ['qNum' => $qNum, 'title' => $x['qTitle'], 'points' => $x['qPoints']];
        }

        $this->questionArray = $array;
    }

    #array[auditCode][auditName, corpID]
    public function auditLU($sql)
    {
        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $array[$x['auditCode']] = ['auditName' => $x['auditName'], 'corpID' => $x['corpID']];
        }

        $this->auditLU = $array;
    }

    #array[wkNum][wkStart, wkEnd]
    public function weekDates($sql)
    {
        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $array[$x['wkNum']] = ['wkStart' => $x['wkStart'], 'wkEnd' => $x['wkEnd']];
        }

        $this->weekDates = $array;
    }

    #array[monthNum][periodName, wkStart, wkEnd, perStart, PerEnd, julStart, julEnd]
    public function periodDates($sql)
    {
        # echo $sql . "</br></br>";

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $array[$x['monthNum']] = [
                'periodName' => $x['periodName'], 'wkStart' => $x['weekStart'], 'wkEnd' => $x['weekEnd'],
                'perStart' => $x['perStart'], 'perEnd' => $x['perEnd'], 'julStart' => $x['julStart'], 'julEnd' => $x['julEnd']
            ];
        }

        $this->periodDates = $array;
    }

    #array[groupNum][deptNum, deptName, freq, countWeeks]
    public function deptInfo($sql)
    {
        # echo $sql . "</br></br>";

        $qry = $this->lnk->query($sql);

        foreach ($qry as $x) {
            $array[$x['groupNum']] = [
                'deptNum' => $x['deptNum'], 'deptName' => $x['deptName'], 'freq' => $x['countFreq'],
                'countWeeks' => $x['countWeeks']
            ];
        }

        $this->deptInfo = $array;
    }

    public function getBranchArray()
    {
        return $this->branchArray;
    }

    public function getTwoDigitArray()
    {
        return $this->twoDigitArray;
    }

    public function getRepServerNumArray()
    {
        return $this->repServerNumArray;
    }

    public function getAuditorArray()
    {
        return $this->auditorArray;
    }

    public function getRegionalArray()
    {
        return $this->regionalArray;
    }

    public function getAuditArray()
    {
        return $this->auditArray;
    }

    private function getAuditOpts()
    {

        #var_dump($this->auditArray);
        foreach ($this->auditArray as $year => $periodArray) {
            foreach ($periodArray as $period => $auditArray) {
                foreach ($auditArray as $audit => $x) {
                    $opts[] = ['field' => 'auditID', 'value' => $audit, 'operand' => 'or'];
                }
            }
        }

        #var_dump($opts);

        return $opts;
    }

    public function getScoreArray()
    {
        return $this->scoreArray;
    }

    public function getLocationArray()
    {
        return $this->locationArray;
    }

    public function getFindingArray()
    {
        return $this->findingArray;
    }

    public function getQuestionArray()
    {
        return $this->questionArray;
    }

    public function getAuditLU()
    {
        return $this->auditLU;
    }

    public function getWeekDates()
    {
        return $this->weekDates;
    }

    public function getPeriodDates()
    {
        return $this->periodDates;
    }

    public function getDeptInfo()
    {
        return $this->deptInfo;
    }

    public function getDirectorArray()
    {
        return $this->directorArray;
    }
}
