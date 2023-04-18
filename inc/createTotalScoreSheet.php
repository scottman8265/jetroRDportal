<?php

$writeTotalStart = microtime(true);

$spreadSheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

$periodHeaders = getPeriodHeaders( $periods );
$letters       = getColumnLetters();
$periodCount   = 0;

foreach ( $periodHeaders as $year => $quarters ) {
	$periodCount   += count( $quarters );
	$yearHeaders[] = [ 'year' => "20" . $year, 'count' => count( $quarters ) ];
	foreach ( $quarters as $quarter ) {
		$monthHeaders[ $year ][] = $quarter;
	}
}

$headers1 = [ 'Branch Number', 'Branch Name' ];
#var_dump($writeArray);

foreach ( $writeArray as $dept => $a ) {
	#echo "</br></br>Dept (line 47): " . $dept . "</br>";

	ksort( $a );

	$deptName = $deptArray[ $dept ];

	$format = [];

	$newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet( $spreadSheet, $deptName );
	$spreadSheet->addSheet( $newSheet );
	$spreadSheet->setActiveSheetIndexByName( $deptName );
	$sheet = $spreadSheet->getActiveSheet();

	$title = $deptName . " CORP AUDIT SCORES";

	$row = 1;

	$sheet->setCellValueByColumnAndRow( 1, $row, $title );
	$row ++;
	$col   = 1;
	$start = $col;
	foreach ( $headers1 as $head1 ) {
		$sheet->setCellValueByColumnAndRow( $col, $row, $head1 );
		$range                  = $letters[ $col ] . $row . ":" . $letters[ $col ] . ( $row + 1 );
		$format['merge'][]      = $range;
		$format['hCenter'][]    = $letters[ $col ] . $row;
		$format['vCenter'][]    = $letters[ $col ] . $row;
		$format['size18'][]     = $letters[ $col ] . $row;
		$format['wrapText'][]   = $letters[ $col ] . $row;
		$format['fillOrange'][] = $letters[ $col ] . $row;
		$end                    = $col;
		$col ++;
	}

	$format['outline'][] = $letters[ $start ] . $row . ":" . $letters[ $end ] . ( $row + 1 );
	foreach ( $yearHeaders as $years ) {

		var_dump( $years );
		$sheet->setCellValueByColumnAndRow( $col, $row, $years['year'] );
		$yearCol           = [ 'start' => $letters[ $col ], 'end' => $letters[ ( $col + $years['count'] - 1 ) ] ];
		$range             = $letters[ $col ] . $row . ":" . $letters[ ( $col + $years['count'] - 1 ) ] . $row;
		$format['merge'][] = $range;
		/*$format['hCenter'][] = $letters[$col] . $row;
		$format['size18'][] = $letters[$col] . $row;*/
		$format['fillOrange'][] = $letters[ $col ] . $row;
		$format['outline'][]    = $range;
		$col                    += $years['count'];
	}

	var_dump( $yearCol );

	$sheet->setCellValueByColumnAndRow( $col, $row, 'Average' );
	$range                  = $letters[ $col ] . $row . ":" . $letters[ $col ] . ( $row + 1 );
	$format['merge'][]      = $range;
	$format['vCenter'][]    = $letters[ $col ] . $row;
	$format['fillOrange'][] = $letters[ $col ] . $row;
	$format['outline'][]    = $range;
	$row ++;
	$col = 3;
	foreach ( $monthHeaders as $year => $monthNames ) {
		$start = $col;

		foreach ( $monthNames as $quarters ) {
			$sheet->setCellValueByColumnAndRow( $col, $row, $quarters );
			#$format['hCenter'][] = $letters[$col] . $row;
			#$format['size18'][] = $letters[$col] . $row;
			$format['fillDarkBlue'][] = $letters[ $col ] . $row;
			$end                      = $col;
			$col ++;
		}

		$format['outline'][] = $letters[ $start ] . "3:" . $letters[ $end ] . "3";
	}
	$row ++;
	$branchCount = 1;

	foreach ( $a as $branch => $x ) {
		$col        = 1;
		$branchName = $branchArray[ $branch ];
		$sheet->setCellValueByColumnAndRow( $col, $row, $branch );
		$col ++;
		$sheet->setCellValueByColumnAndRow( $col, $row, $branchName );
		$col ++;
		$colAvgStart = $col;
		foreach ( $x as $y ) {
			$sheet->setCellValueByColumnAndRow( $col, $row, $y );

			if ( $y == 'na' ) {
				$format['fillRed'][] = $letters[ $col ] . $row;
			}

			$colAvgEnd = $col;
			$col ++;
		}
		$avgRange = $letters[ $colAvgStart ] . $row . ':' . $letters[ $colAvgEnd ] . $row;

		$sheet->setCellValueByColumnAndRow( 6, $row, '=IF(ISERROR(AVERAGE(' . $avgRange . ')), "NA", AVERAGE(' . $avgRange . '))' );
		$format['formatNum'][] = $letters[ $col - 1 ] . $row;
		$row ++;
	}
	$col = 1;
	$sheet->setCellValueByColumnAndRow( $col, $row, 'Period Average' );
	$format['merge'][]  = $letters[ $col ] . $row . ":" . $letters[ $col + 1 ] . $row;
	$format['size14'][] = $letters[ $col ] . $row;
	$col                += 2;
	$colStart           = $col;
	for ( $i = $col; $i < ( $periodCount + 1 + $col ); $i ++ ) {
		$range = $letters[ $i ] . '4:' . $letters[ $i ] . ( $row - 1 );
		$sheet->setCellValueByColumnAndRow( $i, $row, '=IF(ISERROR(AVERAGE(' . $range . ')), "NA", AVERAGE(' . $range . '))' );
		$format['formatNum'][] = $letters[ $i - 1 ] . $row;
		$colEnd                = $i;
	}

	$highestCol = $sheet->getHighestColumn();
	$highestRow = $sheet->getHighestRow();

	foreach ( $yearCol as $y ) {

		#echo "This is y line 165: </br>";
		#var_dump( $y );

		$range               = $y['start'] . '4:' . $y['end'] . $highestRow;
		$format['outline'][] = $range;
	}

	$format['outline'][] = "A4:B" . $highestRow;
	$format['outline'][] = "B1:" . $highestCol . $highestRow;
	$format['outline'][] = "A" . $highestRow . ":B" . $highestRow;
	$format['outline'][] = "A" . $highestRow . ":" . $highestCol . $highestRow;

	$format['hCenter'][] = "A4:A" . $highestRow;
	$format['hCenter'][] = "C4:" . $highestCol . $highestRow;
	$format['hCenter'][] = "A1:" . $highestCol . '3';

	$format['size12'][] = "A4:A" . $highestRow;
	$format['size12'][] = "C4:" . $highestCol . $highestRow;

	$format['size14'][] = "A4:B" . $highestRow;

	$format['size18'][] = "A1:" . $highestCol . '3';

	$format['allBorders'][] = "A2:" . $highestCol . $highestRow;

	$format['freezePane'][] = "A4";
	$format['wrapText']     = [ 'A2', 'B2' ];
	$format['zAutoSize']    = range( 'A', $highestCol );

	$range              = "A1:" . $highestCol . "1";
	$format['merge'][]  = $range;
	$format['size22'][] = 'A1';

	$range                 = $letters[ $colStart ] . $row . ":" . $letters[ $colEnd ] . $row;
	$format['formatNum'][] = $range;

	for ( $i = 4; $i < $highestRow + 1; $i ++ ) {
		if ( $i % 2 === 0 && $sheet->getCell( "A" . $i )->getValue() !== "Period Average" ) {
			$format['fillLightBlue'][] = "A" . $i . ":" . $highestCol . $i;
		}
	}

	$format['size18'][]         = "A" . $highestRow . ":" . $highestCol . $highestRow;
	$format['fillDarkerBlue'][] = "A" . $highestRow . ":" . $highestCol . $highestRow;
	$format['textWhite'][]      = "A" . $highestRow . ":" . $highestCol . $highestRow;

	$formatPage = new Format();
	$formatPage->formatSheet( $sheet, $format );
}

$spreadSheet->setActiveSheetIndexByName( 'Worksheet' );
$remove = $spreadSheet->getActiveSheetIndex();
$spreadSheet->removeSheetByIndex( $remove );
$spreadSheet->setActiveSheetIndexByName( 'TOTAL' );

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadSheet, "Xlsx" );
$writer->save( "../output/corpAuditAnalysis/corpScores - 2019 Q1-Q3.xlsx" );

$writeTotalEnd = microtime(true);
$writeTotalTime = $writeTotalEnd - $writeTotalStart;

return $writeTotalTime;
