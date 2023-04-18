<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 11/24/2018
 * Time: 1:06 PM
 */


class Format
{
    /**
     * @var \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     */
    var $sheet;
    var $outline      = ['borders' => ['outline' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK],],];
    var $allBorders   = ['borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],],];
    var $bottomBorder = ['borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],],];
    var $makeBold     = ['font' => ['bold' => true]];
    var $size18       = ['font' => ['size' => 18]];
    var $size14       = ['font' => ['size' => 14]];
    var $size16       = ['font' => ['size' => 16]];
    var $size12       = ['font' => ['size' => 12]];
    var $size22       = ['font' => ['size' => 22]];
    var $rightAlign   = ['alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT]];
    var $letters;

    /**
     * @param $sheet
     * @param $formats
     * @return \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     */
    public function formatSheet($sheet, $formats, $output = true) {

        $this->sheet = $sheet;

        ksort($formats);

        /*if ($output) {
            echo "<h1>Start Format Array</h1>";
        }*/

        foreach ($formats as $function => $range) {

            /*if ($output) {
                echo $function . "</br>";
                var_dump($range);
                echo "</br></br>";
            }*/

            $this->$function($range);
        }

        return $this->sheet;
    }

    private function merge($range) {
        foreach ($range as $cells) {
            $this->sheet->mergeCells($cells);
        }
    }

    private function vCenter($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)
                ->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        }
    }

    private function hCenter($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }
    }

    private function right($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->rightAlign);
        }
    }

    private function size18($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->size18);
        }
    }

    private function size14($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->size14);
        }
    }

    private function size16($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->size16);
        }
    }

    private function size12($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->size12);
        }
    }

    private function size22($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->size22);
        }
    }

    private function bottomBorder($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->bottomBorder);
        }
    }

    private function allBorders($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->allBorders);
        }
    }

    private function outline($range) {
        foreach ($range as $cells) {

            $this->sheet->getStyle($cells)->applyFromArray($this->outline);
        }
    }

    private function bold($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->applyFromArray($this->makeBold);
        }
    }

    private function fillRed($range) {

        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFff0000');
        }

    }

    private function fillLightBlue($range) {

        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFD9E1F2');
        }

    }

    private function fillOrange($range) {

        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFf7911d');
        }

    }

    private function fillDarkBlue($range) {

        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF8DB4E2');
        }

    }

    private function fillDarkerBlue($range) {

        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FF254B79');
        }

    }

    private function textWhite($range) {

        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        }

    }

    private function zAutoSize($range) {

        foreach ($range as $cells) {

            $this->sheet->getColumnDimension($cells)->setAutoSize(true);
        }

    }

    private function fitToPage($bool) {
        if ($bool) {
            $this->sheet->getPageSetup()->setFitToWidth(1);
            $this->sheet->getPageSetup()->setFitToHeight(0);
        }
    }

    private function orientation($bool) {
        $bool ? $this->sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE)
            : $this->sheet->getPageSetup()
            ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);

    }

    private function gridLines($bool) {
        !$bool ? $this->sheet->setShowGridlines(false) : $this->sheet->setShowGridlines(true);
    }

    private function wrapText($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)
                ->getAlignment()->setWrapText(true);
        }
    }

    private function zSetWidth($range) {
        foreach ($range as $cells) {
            #echo "column width info" . $cells['col'] . " " . $cells['width'] . "</br>";
            $this->sheet->getColumnDimension($cells['col'])->setWidth($cells['width']);
        }
    }

    private function freezePane($range) {
        foreach ($range as $cells) {
            $this->sheet->freezePane($cells);
        }
    }

    private function formatNum($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getNumberFormat()
                ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        }
    }

    private function formatPercent($range) {
        foreach ($range as $cells) {
            $this->sheet->getStyle($cells)->getNumberFormat()->setFormatCode('0.00%');
        }
    }

    public function setLetters() {
        $this->letters = [1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H', 9 => 'I', 10 => 'J',
                          11 => 'K', 12 => 'L', 13 => 'M', 14 => 'N', 15 => 'O', 16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S',
                          20 => 'T', 21 => 'U', 22 => 'V', 23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z'];
    }

    public function getLetters() {
        $this->setLetters();
        return $this->letters;
    }


}