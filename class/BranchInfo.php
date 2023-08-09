<?php

/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 1/7/2019
 * Time: 5:25 AM
 */

//TODO replace all hardcoded database names with constants from config.php
//TODO replace all hardcoded table names with constants from config.php
//TODO add params to all sql statements that can take them
if (file_exists('../class/Process.php')) {
    require_once '../class/Process.php';
} else {
    require_once 'class/Process.php';
}
if (file_exists('../inc/config.php')) {
    require_once '../inc/config.php';
} else {
    require_once 'inc/config.php';
}

class BranchInfo extends Process
{

    /**
     * Summary of ABS
     * @var array active branches
     */
    private $ABS = [];
    /**
     * Summary of LR
     * @var array location request
     */
    private $LR = [];
    /**
     * Summary of XD
     * @var string xDock
     */
    private $XD;
    /**
     * Summary of SF
     * @var bool seafood
     */
    private $SF;
    /**
     * Summary of BN
     * @var string branchName
     */
    private $BN;
    /**
     * Summary of RGN
     * @var string region
     */
    private $RGN;
    /**
     * Summary of REG
     * @var string regional
     */
    private $REG;
    /**
     * Summary of DIR
     * @var string director
     */
    private $DIR;
    /**
     * Summary of LT
     * @var string location type
     */
    private $LT;
    /**
     * Summary of SMW
     * @var bool smwares
     */
    private $SMW;
    /**
     * Summary of WAS
     * @var bool wine and spirits
     */
    private $WAS;

    /**
     * Summary of RIS
     * @var array
     */
    private $RIS = [];
    /**
     * Summary of BNS
     * @var array branch numbers
     */
    private $BNS = [];
    /**
     * Summary of ARS
     * @var array active request items
     */
    private $ARS = [];

    private $DBLU = ["BI" => ["db" => "jrdStuff.BRINF", "w" => 'BID'], "SF" => ["db" => 'jrdStuff.BRINF', "w" => "SF"], "XD" => ["db" => 'jrdStuff.XDS', "w" => "XDID"], "BN" => ["db" => 'jrdStuff.BNS', "w" => "BNID"], "RGN" => ["db" => 'jrdStuff.RGNS', "w" => "RGNID"], "REG" => ["db" => 'jrdStuff.FTMS', "w" => "TMID"], "DIR" => ["db" => 'jrdStuff.FTMS', "w" => "TMID"], "LT" => ["db" => 'jrdStuff.LTS', "w" => "LTID"], "SMW" => ["db" => 'jrdStuff.BRINF', "w" => "SMW"], "WAS" => ["db" => 'jrdStuff.BRINF', "w" => "WAS"]];

    /**
     * Summary of __construct
     * @param array $BNS branch numbers
     * @param array $RIS request items
     */
    public function __construct($BNS = null, $RIS = null)
    {
        parent::__construct();

        $this->setupLR([$BNS, $RIS]);
    }

    /**
     * Summary of getBi
     * @param int $bn branch number
     * @param string $ri field to query
     * @return mixed $r result
     */
    private function getBi($bn, $ri)
    {
        $r = null;

        $s = "SELECT ? FROM jrdStuff.branchInfo WHERE branchId = ?";
        $p = [$ri, $bn];
        $r = Process::query($s, $p);

        return $r;
    }

    private function queryRI($ri)
    {
        $r = null;

        $s = "SELECT ? FROM ? WHERE ? = ?";
        $p = [$ri[0], $ri[1], $ri[2], $ri[3]];
        $r = Process::query($s, $p);

        return $r;
    }



    /**
     * Summary of requestBranchInfo
     * @param array $x array of [branch numbers, request items]
     * @return void
     */
    public function requestBranchInfo($x)
    {
        $this->setupLR($x);

        return $this->answerLR();
    }
    private function setupLR($z)
    {
        $this->setBNS($z[0]);
        $this->setRIS($z[1]);
        $this->setLR();
    }

    private function setDBLU($ri)
    {
        $d = [];

        $d = $this->DBLU[$ri];

        $this->DBLU = $d;
    }

    private function answerLR()
    {
        $d = [];
        foreach ($this->getLR() as $bn => $ris) {
            foreach ($ris as $ri => $riid) {
                $d[$bn][$ri] = $this->queryRI([$ri, $this->DBLU[$ri][0], $this->DBLU[$ri][1], $riid]);
            }
        }
        $this->LR = $d;
    }

    /**
     * Summary of setLR
     * @param array $dblu [database.table, w clause]
     * @var int $o branch number
     * @var string $g request item
     * @var array $d array[branch number][request item][request item lookup value]
     * @return void
     */
    private function setLR()
    {
        $d = [];

        foreach ($this->getBNS() as $o) {
            foreach ($this->getRIS() as $g) {
                $dblu = $this->DBLU[$g];
                $d[$o][$g] = $this->queryRI([$g, $dblu[0], $dblu[1], $o]);
            }
        }
        $this->LR = $d;
    }

    private function getLR()
    {
        if (empty($this->LR)) {
            $this->setLR();
        }

        return $this->LR;
    }

    /**
     * Summary of setBNS
     * @param array $BNS branch numbers
     * @return void
     */
    private function setBNS($BNS = null)
    {
        $this->BNS = $BNS == null ? $this->getABS() : $BNS;
    }

    /**
     * Summary of getBNS
     * @return array $BNS branch numbers
     */
    public function getBNS()
    {
        if (empty($this->BNS)) {
            $this->setBNS();
        }
        return $this->BNS;
    }

    /**
     * Summary of setABS
     * @var array $a active branches
     * @var string $s s
     * @var Process::query $q query
     * @var array $r result
     * @return void ABS active branches
     */
    private function setABS()
    {
        $a = [];

        $s = "SELECT branchId AS branchNum FROM jrdStuff.branchInfo WHERE active = 1 SORT BY branchNum ASC";

        $q = parent::query($s);

        foreach ($q as $r) {
            $a[] = $r['branchNum'];
        }

        $this->ABS = $a;
    }

    /**
     * Summary of getABS
     * @return array $ABS active branches
     */
    public function getABS()
    {
        if (empty($this->ABS)) {
            $this->setABS();
        }
        return $this->ABS;
    }

    /**
     * Summary of setRIS
     * @param mixed $RIS request items
     * @return void
     */
    private function setRIS($RIS = null)
    {
        $this->RIS = $RIS == null ? $this->getARS() : $RIS;
    }

    public function getRIS()
    {
        if (empty($this->RIS)) {
            $this->setRIS();
        }
        return $this->RIS;
    }

    public function setARS()
    {

        //TODO take out the hard coded a and replace with dynamic a from database
        $this->ARS = ['BN', 'RGN', 'REG', 'DIR', 'LT', 'SF', 'SMW', 'WAS', 'XD'];
    }

    private function getARS()
    {
        if (empty($this->ARS)) {
            $this->setARS();
        }
        return $this->ARS;
    }

    private function setBN($x)
    {
        $s = "SELECT branchName FROM jrdStuff.branchNames WHERE branchNameId = " . $this->getBi($x, 'branchName');
        $this->BN = Process::query($s);
    }

    public function getBN($x)
    {
        if (!$this->BN) {
            $this->setBN($x);
        }
        return $this->BN;
    }

    private function setRGN($x)
    {
        $s = "Select region FROM jrdStuff.regions WHERE regionId = " . $this->getBi($x, 'region');
        $this->RGN = Process::query($s);
    }

    public function getRGN($x)
    {
        if (!$this->RGN) {
            $this->setRGN($x);
        }
        return $this->RGN;
    }

    private function setREG($x)
    {
        $s = "Select ftmName FROM jrdStuff.fieldTeamMembers WHERE tmId = " . $this->getBi($x, 'regional');
        $this->REG = Process::query($s);
    }

    public function getREG($x)
    {
        if (!$this->REG) {
            $this->setREG($x);
        }
        return $this->REG;
    }

    private function setDIR($x)
    {
        $s = "Select DIR FROM jrdStuff.fieldTeamMembers WHERE tmId = " . $this->getBi($x, 'DIR');
        $this->DIR = Process::query($s);
    }

    public function getDIR($x)
    {
        if (!$this->DIR) {
            $this->setDIR($x);
        }
        return $this->DIR;
    }

    private function setXD($x)
    {
        $s = "Select XD FROM jrdStuff.xDocks WHERE xDockId = " . $this->getBi($x, 'XD');
        $this->XD = Process::query("Select XD FROM jrdStuff.xDocks WHERE xDockId = " . $x);
    }

    public function getXD($x)
    {
        if (!$this->XD) {
            $this->setXD($x);
        }
        return $this->XD;
    }

    private function setSF($x)
    {
        $this->SF = $this->getBi($x, 'SF');
    }

    public function getSF($x)
    {
        if (!$this->SF) {
            $this->setSF($x);
        }
        return $this->SF;
    }

    private function setSMW($x)
    {
        $this->SMW = $this->getBi($x, 'SMW');
    }

    public function getSMW($x)
    {
        if (!$this->SMW) {
            $this->setSMW($x);
        }
        return $this->SMW;
    }

    private function setLT($x)
    {
        $s = "Select LT FROM jrdStuff.locationTypes WHERE locationTypeId = " . $this->getBi($x, 'LT');
        $this->LT = Process::query($s);
    }

    public function getLT($x)
    {
        if (!$this->LT) {
            $this->setLT($x);
        }
        return $this->LT;
    }

    private function setWAS($x)
    {
        $this->WAS = $this->getBi($x, 'WAS');
    }

    public function getWAS($x)
    {
        if (!$this->WAS) {
            $this->setWAS($x);
        }
        return $this->WAS;
    }
}
