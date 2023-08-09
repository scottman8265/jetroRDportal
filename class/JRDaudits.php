<?php

//TODO write scores, findings & people to database


require_once('..\inc\config.php');
require_once('..\class\Process.php');

/**
 * Class JRDaudits
 * Handles audit processing with various operations related to corporate and self audits.
 */
class JRDaudits extends Process
{

    private $sheet;
    private $totalScoreCell = array();
    /**
     * Summary of version
     * @var
     */
    public $version = null;
    /**
     * Summary of positions
     * @var
     */
    private $positions = array();
    /**
     * Summary of findings
     * @var
     */
    public $findings = array();
    /**
     * Summary of auditLookup

     * @var
     */
    public $auditLookup = array();

    /**
     * Summary of findingStart
     * @var int
     */
    public $findingStart = 0;

    /**
     * Summary of figuresStartRow
     * @var int
     */
    private $figuresStartRow = 0;

    /**
     * Summary of people
     * @var array
     */
    public $people = array();

    /**
     * Summary of scores
     * @var array
     */
    public $scores = array();
    /**
     * Summary of __construct
     * @param string $function
     * @param \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet
     */
    public function __construct($function = null, $sheet)
    {
        parent::__construct();

        if ($function != null) {
            $function == 'newCorpAudit' ? $this->processNewCorpAudit($sheet) : null;
        }
    }


    /**
     * Summary of processNewCorpAudit
     * @param PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $this->sheet
     * @return void
     */
    public function processNewCorpAudit($sheet)
    {
        $initializeErrors = [];

        $this->sheet = $sheet;
        $this->sheet == null ? $initializeErrors[] = "There was an error in getting your spreadsheet. Make sure the file is in the correct format and name correctly ([YEAR] [QUARTER] 'CORP' [BRANCH NUMBER] [BRANCH NAME])" : null;

        $this->initializeTotalScoreCell();
        $this->getFiguresStartRow() == null ? $initializeErrors[] = "There was an error in reading the information from your spreadsheet. Make sure you are using a recent audit and it has not been altered from it's original structure for the used version.)" : null;

        $this->initializeVersion();
        $this->getVersion() == null ? $initializeErrors[] = "There was an error in eading the version from your spreadsheet. Make sure you are using a recent audit and it has not been altered from it's original structure for the used version.)" : null;

        $this->initializeAuditLookup();
        $this->getAuditLookup() == null ? $initializeErrors[] = "There was an error in getting the audits from the database. This is an internal error and if it continues please utilize this sites help function for assistance." : null;

        $this->initializePositions();
        $this->getPeople() == null ? $initializeErrors[] = "There was an error in getting the people from the database. Please verify that you do not have any unauthorized characters in any of the fields listing the department heads at the top of the Audit Recap (only A-Z, 0-9, '-', '_', '.' is allowed)." : null;

        $this->initializeFindings();
        $this->initializeScores();
    }

    /**
     * Initializes totalScoreCell by finding "Total Score" and breaking down the cell into parts.
     */
    private function initializeTotalScoreCell()
    {
        // Search in column H for "Total Score"
        $highestRow = $this->sheet->getHighestRow();
        for ($row = 1; $row <= $highestRow; $row++) {
            if ($this->sheet->getCellByColumnAndRow(8, $row)->getValue() === "Total Score") {
                $this->totalScoreCell = [
                    'row' => $row,
                    'col' => 8,
                ];
                break;
            }
        }
        $this->setFiguresStartRow();
    }

    /**
     * Initializes figuresStartRow based on the totalScoreCell.
     */
    private function setFiguresStartRow()
    {
        $this->figuresStartRow = $this->totalScoreCell['row'] + 1; // Modify as needed
    }

    /**
     * Gets the figuresStartRow.
     * @return int
     */
    private function getFiguresStartRow()
    {
        return $this->figuresStartRow;
    }

    /**
     * Sets positions with an optional receivedPositions array.
     * @return void
     */
    private function initializePositions()
    {
        $this->positions = array(
            'Branch Manager' => [4, 14],
            'auditor' => [3, 14],
            'Sr ABM' => [5, 14],
            'Jr ABM' => [6, 14],
            'Hradmin' => [0, 27],
            'Cashroom' => [1, 27],
            'Deli Mgr' => [2, 27],
            'Floor Mgr' => [3, 27],
            'Front End Mgr' => [4, 27],
            'Gen Ops (NABM)' => [5, 27],
            'Inv Control' => [6, 27],
            'Meat Mgr' => [0, 38],
            'Pest (NABM)' => [1, 38],
            'Produce Mgr' => [2, 38],
            'Receiving Mgr' => [3, 38],
            'Receptionist (TOA)' => [4, 38],
            'Safety Mgr (NABM)' => [5, 38],
            'Seafood Mgr' => [6, 38],
            'Smallwares Mgr' => [0, 51],
            'Wine Steward' => [1, 51],
            'Branch Manager #2' => [2, 51],
            'ABM #3' => [3, 51]
        );
        $this->setPeople();
    }

    /**
     * Summary of getPositions
     * @return array
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * Summary of updatePositions
     * @param array $updatedPositions
     * @return void
     */
    public function updatePositions($updatedPositions)
    {
        foreach ($updatedPositions as $positionName => $offset) {
            $this->positions[$positionName] = $offset;
        }
    }

    /**
     * Summary of setPeople
     * @return void
     */
    private function setPeople()
    {
        $people = array();
        foreach ($this->getPositions() as $positionName => $offset) {
            $row = $this->totalScoreCell['row'] + $offset['row'];
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($this->totalScoreCell['column']) + $offset['col'];

            $tmInPosition = $this->sheet->getCellByColumnAndRow($col, $row)->getValue();

            $people[$positionName] = $tmInPosition;
        }
        $this->people = $people;
    }

    /**
     * Summary of getPeople
     * @return array
     */
    public function getPeople()
    {
        return $this->people;
    }

    /**
     * Sets the version by offsetting the totalScoreCell.
     */
    public function initializeVersion()
    {
        // Assuming $this->totalScoreCell has been initialized as an array with 'row' and 'col'
        $row = $this->totalScoreCell['row'] + 4;
        $col = $this->totalScoreCell['col'] + 50;

        // Retrieve the value from the sheet
        $this->version = $this->sheet->getCellByColumnAndRow($col, $row)->getValue();
    }

    /**
     * Summary of getVersion
     * @return mixed
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Summary of setQuesArray
     * @return void
     */
    public function initializeAuditLookup()
    {
        if ($this->version == null) {
            $this->initializeVersion();
        }

        $array = [];

        $sql = "SELECT auditCode, auditName FROM jrd_stuff.auditlookup ORDER BY auditCode ASC";
        $qry = Process::query($sql);

        foreach ($qry as $info) {
            $array[$info['auditName']] = $info['auditCode'];
        }

        $this->auditLookup = $array;
    }

    /**
     * Summary of getQuesArray
     * @return array
     */
    public function getAuditLookup()
    {
        return $this->auditLookup;
    }

    /**
     * Sets findings with an optional getRepeats boolean.
     * @param bool $getRepeats
     */
    function initializeFindings($getRepeats = true)
    {
        $audits = $this->getAuditLookup();
        $findings = array();

        $rowMax = $this->totalScoreCell['row'] - 2;
        $findingStart = $this->totalScoreCell['row'] + 27;

        // Processing findings code...

        if ($rowMax != null) {
            for ($i = 2; $i < $rowMax; $i++) {
                $qNum = $this->sheet->getCellByColumnAndRow(6, $i)->getValue();
                if ($qNum != null) {
                    $qAudit = $this->sheet->getCellByColumnAndRow(9, $i)->getValue();
                    $qComm = $this->sheet->getCellByColumnAndRow(11, $i)->getOldCalculatedValue();
                    $response = $this->sheet->getCellByColumnAndRow(3, $i)->getOldCalculatedValue();
                    $code = $audits[$qAudit] . (string) $qNum . "." . $this->version;

                    if ($response) {
                        $findings[$code]['comm'] = $qComm;
                    } elseif ($audits[$qAudit] == "FL") {
                        $varArray = [1, 2, 3, 4, 5, 8];
                        if (in_array($qNum, $varArray) == true && strlen($qComm) > 1) {
                            $findings[$code]['comm'] = trim($qComm);
                        }
                    }
                }
            }
        }


        if ($getRepeats) {
            $this->findRepeats($findings);
        }

        $this->findings = $findings; // Assigning to the class property
    }


    /**
     * Summary of findRepeats
     * @param mixed $findings
     * @return void
     */
    private function findRepeats(&$findings)
    {
        $count    = 0;
        $testCode = 'FFFFFFFF';

        foreach ($findings as $code => $value) {
            $commentCell = "H" . ($this->findingStart + ($count * 8) + 2);
            $hashCode = $this->sheet->getCell($commentCell)->getStyle()->getFill()->getStartColor()->getARGB();

            if ($hashCode != $testCode) {
                $findings[$code]['rep'] = 1;
            } else {
                $findings[$code]['rep'] = 0;
            }

            $count++;
        }
    }


    /**
     * Sets scores based on the provided details.
     */
    public function initializeScores()
    {
        $baseStartRow = $this->getFiguresStartRow();
        $array = array();

        // Define cell locations
        $r_totScoreLoc = "L" . $baseStartRow;
        $b_totScoreLoc = "H" . $baseStartRow;
        $r_freshScoreLoc = "L" . ($baseStartRow + 4);
        $b_freshScoreLoc = "H" . ($baseStartRow + 4);
        $deptScoreStart = $baseStartRow + 8;
        $deptScoreEnd = $baseStartRow + 14;
        $foodSafety = $baseStartRow + 15;
        $totArray = ['Z' . ($baseStartRow + 15), 'AH' . ($baseStartRow + 15), 'AP' . ($baseStartRow + 15), 'AX' . ($baseStartRow + 15)];
        $repCol = "N";
        $baseCol = "L";

        // Extract and process scores
        try {
            $r_array[] = $this->sheet->getCell($r_totScoreLoc)->getOldCalculatedValue();
            $b_array[] = $this->sheet->getCell($b_totScoreLoc)->getOldCalculatedValue();
            $r_array[] = $this->sheet->getCell($r_freshScoreLoc)->getOldCalculatedValue();
            $b_array[] = $this->sheet->getCell($b_freshScoreLoc)->getOldCalculatedValue();

            for ($i = $deptScoreStart; $i < $deptScoreEnd; $i++) {
                $r_cell = $repCol . $i;
                $b_cell = $baseCol . $i;
                $b_score = $this->sheet->getCell($b_cell)->getOldCalculatedValue();
                $r_score = $this->sheet->getCell($r_cell)->getOldCalculatedValue();
                $baseScore = $b_score === 'N/A' ? -1 : $b_score;
                $repScore = $r_score === 'N/A' ? -1 : $r_score;
                $r_array[] = $repScore;
                $b_array[] = $baseScore;
            }

            $r_fsCell = $repCol . $foodSafety;
            $b_fsCell = $baseCol . $foodSafety;
            $r_array[] = $this->sheet->getCell($r_fsCell)->getOldCalculatedValue();
            $b_array[] = $this->sheet->getCell($b_fsCell)->getOldCalculatedValue();

            foreach ($totArray as $tot) {
                $r_array[] = $this->sheet->getCell($tot)->getOldCalculatedValue();
                $b_array[] = $this->sheet->getCell($tot)->getOldCalculatedValue();
            }
        } catch (\PhpOffice\PhpSpreadsheet\Exception $e) {
            echo $e->getMessage();
        }

        $r_array[] = 1;
        $b_array[] = 0;

        $array = ['rep' => $r_array, 'base' => $b_array];

        $this->scores = $array;
    }



    // Additional methods and logic as needed
}
