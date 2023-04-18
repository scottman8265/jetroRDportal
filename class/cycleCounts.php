<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 2/3/2019
 * Time: 6:58 PM
 */

namespace cycleCounts;

require_once '../class/Process.php';


class cycleCounts
{

    private $lnk;
    private $week;
    public  $branchArray; #array of branches only
    public  $deptArray;
    public  $freqArray;
    public  $dataQry;
    public  $zeroArray; #used to itialize arrays to zero
    public  $typeArray; #used to quickly write type titles from abbrevieation
    public  $branchCounts;
    public  $freqCounts;
    public  $weekCounts;
    public  $deptCounts;
    public  $nsArray;
    public  $dwArray;
    public  $ncArray;
    public  $invArray;
    public  $bigArray;
    public  $mainArray;
    public $branchAmount;
    public $deptAmount;
    public $freqAmount;
    public $weekAmount;

    public function __construct($week) {
        $this->lnk = new \Process();
        $this->week = $week;
        $this->setZeroArray();
        $this->setTypeArray();
        $this->setBranchArray();
        $this->setDataQry();
        $this->setDeptArray();
        $this->setFreqArray();
        $this->initializeArrays();
        $this->processDataQry();
    }

    private function setDataQry() {
        $dataSql = "SELECT * FROM cyclecounts.enteredcounts WHERE wkNum = " . $this->week;
        $this->dataQry = $this->lnk->query($dataSql);
    }

    private function setBranchArray() {
        $colSql = "SHOW COLUMNS FROM cyclecounts.enteredcounts";
        $colQry = $this->lnk->colNames($colSql);

        foreach ($colQry as $fields) {
            $field = $fields['Field'];
            if (substr($field, 0, 1) == '_') {
                $array[] = $field;
            }
        }

        asort($array);

        $this->branchArray = $array;
    }

    private function setZeroArray() {
        $this->zeroArray = ['ncCount' => 0, 'dwCount' => 0, 'nsCount' => 0, 'invCount' => 0,
                            'countedCount' => 0, 'totalCount' => 0, 'amount' => 0];
    }

    private function setTypeArray() {
        $this->typeArray = ['nc' => 'Not Counted', 'dw' => 'Dollar Wash', 'ns' => 'Not Sold', 'inv' => 'Inventory',
                            'counted' => 'Counted', 'total' => 'Total'];
    }

    private function setDeptArray() {

        foreach ($this->dataQry as $data) {

            $this->deptArray[] = "'" . $data['deptID'] . "'";

        }

    }

    private function setFreqArray() {
        foreach ($this->deptArray as $dept) {
            $sql = "SELECT countFreq, deptName FROM cyclecounts.deptinfo WHERE deptID = " . $dept;
            $qry = $this->lnk->query($sql);

            $this->freqArray[$dept] = ['freq' => $qry[0]['countFreq'], 'deptName' => $qry[0]['deptName']];
        }
    }

    private function initializeArrays() {

        foreach ($this->branchArray as $branch) {
            $this->branchCounts[$branch] = $this->zeroArray;
        }

        foreach ($this->deptArray as $dept) {
            $this->deptCounts[$dept] = $this->zeroArray;
            $freq = $this->freqArray[$dept]['freq'];
            $this->freqCounts[$freq] = $this->zeroArray;
        }

        $this->weekCounts[$this->week] = $this->zeroArray;
    }

    public function processDataQry() {

        foreach ($this->dataQry as $data) {
            $deptID = "'" . $data['deptID'] . "'";
            $freq = $this->freqArray[$deptID]['freq'];
            $week = $this->week;

            foreach ($this->branchArray as $branch) {
                $this->bigArray[$week][$freq][$deptID][] = $branch;

                $entry = $data[$branch];

                switch ($entry) {
                    case 'NC':
                        $this->ncArray[$week][$freq][$deptID][] = $branch;
                        $this->branchCounts[$branch]['ncCount']++;
                        $this->branchCounts[$branch]['totalCount']++;
                        $this->deptCounts[$deptID]['ncCount']++;
                        $this->deptCounts[$deptID]['totalCount']++;
                        $this->freqCounts[$freq]['ncCount']++;
                        $this->freqCounts[$freq]['totalCount']++;
                        $this->weekCounts[$week]['ncCount']++;
                        $this->weekCounts[$week]['totalCount']++;
                        #$bigArray[$week][$freq][$deptID][$branch]['ncCount']++;
                        #$countArray[$week][$freq][$deptID][$branch]['totalCount']++;
                        break;
                    case 'DW':
                        $this->dwArray[$week][$freq][$deptID][] = $branch;
                        $this->branchCounts[$branch]['dwCount']++;
                        $this->branchCounts[$branch]['totalCount']++;
                        $this->deptCounts[$deptID]['dwCount']++;
                        $this->deptCounts[$deptID]['totalCount']++;
                        $this->freqCounts[$freq]['dwCount']++;
                        $this->freqCounts[$freq]['totalCount']++;
                        $this->weekCounts[$week]['dwCount']++;
                        $this->weekCounts[$week]['totalCount']++;
                        #$bigArray[$week][$freq][$deptID][$branch]['dwCount']++;
                        #$countArray[$week][$freq][$deptID][$branch]['totalCount']++;
                        break;
                    case 'NS':
                        $this->nsArray[$week][$freq][$deptID][] = $branch;
                        $this->branchCounts[$branch]['nsCount']++;
                        $this->branchCounts[$branch]['totalCount']++;
                        $this->deptCounts[$deptID]['nsCount']++;
                        $this->deptCounts[$deptID]['totalCount']++;
                        $this->freqCounts[$freq]['nsCount']++;
                        $this->freqCounts[$freq]['totalCount']++;
                        $this->weekCounts[$week]['nsCount']++;
                        $this->weekCounts[$week]['totalCount']++;
                        #$bigArray[$week][$freq][$deptID][$branch]['nsCount']++;
                        #$countArray[$week][$freq][$deptID][$branch]['totalCount']++;
                        break;
                    case 'INV':
                        $this->invArray[$week][$freq][$deptID][] = $branch;
                        $this->branchCounts[$branch]['invCount']++;
                        $this->branchCounts[$branch]['totalCount']++;
                        $this->deptCounts[$deptID]['invCount']++;
                        $this->deptCounts[$deptID]['totalCount']++;
                        $this->freqCounts[$freq]['invCount']++;
                        $this->freqCounts[$freq]['totalCount']++;
                        $this->weekCounts[$week]['invCount']++;
                        $this->weekCounts[$week]['totalCount']++;
                        #$bigArray[$week][$freq][$deptID][$branch]['invCount']++;
                        #$countArray[$week][$freq][$deptID][$branch]['totalCount']++;
                        break;
                    default:
                        #$entry = number_format($entry, 2, '.', ',');
                        $entry = floatval($entry);
                        $this->branchAmount[$week][$freq][$deptID][$branch][] = $entry;
                        $this->deptAmount[$week][$freq][$deptID][] = $entry;
                        $this->freqAmount[$week][$freq][] = $entry;
                        $this->weekAmount[$week][] = $entry;
                        $this->branchCounts[$branch]['countedCount']++;
                        $this->branchCounts[$branch]['totalCount']++;
                        $this->deptCounts[$deptID]['countedCount']++;
                        $this->deptCounts[$deptID]['totalCount']++;
                        $this->freqCounts[$freq]['countedCount']++;
                        $this->freqCounts[$freq]['totalCount']++;
                        $this->weekCounts[$week]['countedCount']++;
                        $this->weekCounts[$week]['totalCount']++;
                        #$bigArray[$week][$freq][$deptID][$branch]['amountCount']++;
                        #$countArray[$week][$freq][$deptID][$branch]['totalCount']++;
                        break;
                }

                $this->mainArray[$week][$freq][$deptID][$branch][] = $entry;
            }
        }


    }

    /**
     * @return mixed
     */
    public function getBranchArray() {
        return $this->branchArray;
    }

    /**
     * @return mixed
     */
    public function getDeptArray() {
        return $this->deptArray;
    }

    /**
     * @return mixed
     */
    public function getFreqArray() {
        return $this->freqArray;
    }

    /**
     * @return mixed
     */
    public function getTypeArray() {
        return $this->typeArray;
    }

    /**
     * @return mixed
     */
    public function getBranchCounts() {
        return $this->branchCounts;
    }

    /**
     * @return mixed
     */
    public function getFreqCounts() {
        return $this->freqCounts;
    }

    /**
     * @return mixed
     */
    public function getWeekCounts() {
        return $this->weekCounts;
    }

    /**
     * @return mixed
     */
    public function getDeptCounts() {
        return $this->deptCounts;
    }

    /**
     * @return mixed
     */
    public function getNsArray() {
        return $this->nsArray;
    }

    /**
     * @return mixed
     */
    public function getDwArray() {
        return $this->dwArray;
    }

    /**
     * @return mixed
     */
    public function getNcArray() {
        return $this->ncArray;
    }

    /**
     * @return mixed
     */
    public function getInvArray() {
        return $this->invArray;
    }

    /**
     * @return mixed
     */
    public function getBigArray() {
        return $this->bigArray;
    }

    /**
     * @return mixed
     */
    public function getMainArray() {
        return $this->mainArray;
    }

    /**
     * @return mixed
     */
    public function getBranchAmount() {
        return $this->branchAmount;
    }

    /**
     * @return mixed
     */
    public function getDeptAmount() {
        return $this->deptAmount;
    }

    /**
     * @return mixed
     */
    public function getFreqAmount() {
        return $this->freqAmount;
    }

    /**
     * @return mixed
     */
    public function getWeekAmount() {
        return $this->weekAmount;
    }




}