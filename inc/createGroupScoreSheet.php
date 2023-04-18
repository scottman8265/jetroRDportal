<?php

var_dump($branchArray);


	$writeGroupStart = microtime(true);

	$spreadSheet   = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$formatSheet   = new Format();
	$periodHeaders = getPeriodHeaders( $periods );
	$letters       = getColumnLetters();
	$quarCount   = 0;

	foreach ( $periodHeaders as $year => $quarter ) {
		$quarCount   += count( $quarter );
		$yearHeaders[] = [ 'year' => "20" . $year, 'count' => count( $quarter ) ];
		foreach ( $quarter as $q ) {
			$quarHeaders[ $year ][] = $q;
		}
	}

	$branchHeaders = [ 'Branch Number', 'Branch Name' ];

	foreach ( $writeArray as $name => $x ) {
		$names[] = $name;
	}

	var_dump($writeArray);

	foreach ( $deptArray as $lc => $long ) {

		$format = [];

		$newSheet = new \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet( $spreadSheet, $long );
		$spreadSheet->addSheet( $newSheet );
		$spreadSheet->setActiveSheetIndexByName( $long );
		$sheet = $spreadSheet->getActiveSheet();

		$title = $long . " CORP AUDIT SCORES BY " . strtoupper( $type );
		$row   = 1;
		$sheet->setCellValueByColumnAndRow( 1, $row, $title );
		$row ++;
		$row ++;

		$nameCount = count( $names );

		$count          = 0;
		$regionStartRow = [];
		$regionEndRow   = [];
		$mergeRow       = [];
		for ( $i = 0; $i < $nameCount; $i ++ ) {
			$count ++;
			$sheet->setCellValueByColumnAndRow( 1, $row, $names[ $i ] );
			$mergeRow[] = $row;

			$row ++;
			$col   = 1;
			$start = $col;
			foreach ( $branchHeaders as $head1 ) {
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
				$sheet->setCellValueByColumnAndRow( $col, $row, $years['year'] );
				/*$yearCol                = [
					'start' => $letters[ $col ],
					'end'   => $letters[ ( $col + $years['count'] - 1 ) ]
				];*/
				$range                  = $letters[ $col ] . $row . ":" . $letters[ ( $col + $years['count'] - 1 ) ] . $row;
				$format['merge'][]      = $range;
				$format['hCenter'][]    = $letters[ $col ] . $row;
				$format['size18'][]     = $letters[ $col ] . $row;
				$format['fillOrange'][] = $letters[ $col ] . $row;
				$format['outline'][]    = $range;
				$col                    += $years['count'];
			}
			$sheet->setCellValueByColumnAndRow( $col, $row, 'Average' );
			$range                  = $letters[ $col ] . $row . ":" . $letters[ $col ] . ( $row + 1 );
			$format['merge'][]      = $range;
			$format['vCenter'][]    = $letters[ $col ] . $row;
			$format['fillOrange'][] = $letters[ $col ] . $row;
			$format['outline'][]    = $range;
			$row ++;
			$col           = 3;
			$quarColStart = $col;
			$quarCounts    = [];
			foreach ( $quarHeaders as $year => $quarNames ) {
				$quarCounts[] = count( $quarNames );
				foreach ( $quarNames as $quarter ) {
					$sheet->setCellValueByColumnAndRow( $col, $row, $quarter );
					$quarColEnd = $col;
					$col ++;
				}

			}
			$format['fillDarkBlue'][] = $letters[ $quarColStart ] . $row . ":" . $letters[ $quarColEnd ] . $row;
			$row ++;
			$branchCount = 1;
			$rowStart    = $row;
			#var_dump($writeArray[$names]);
			foreach ( $writeArray[ $names[ $i ] ][ $lc ] as $branch => $data ) {
				$col = 1;
				$sheet->setCellValueByColumnAndRow( $col, $row, $branch );
				$col ++;
				$sheet->setCellValueByColumnAndRow( $col, $row, $branchArray[ $branch ] );
				$col ++;
				$avgStart = $col;

				#var_dump( $data );

				$sheet->setCellValueByColumnAndRow( $col, $row, $data );
				if ( $data === 'na' ) {
					$format['fillRed'][] = $letters[ $col ] . $row;
				}
				$avgEnd = $col;
				$col ++;

				$avgRange = $letters[ $avgStart ] . $row . ":" . $letters[ $avgEnd ] . $row;
				$sheet->setCellValueByColumnAndRow( 6, $row, '=IF(ISERROR(AVERAGE(' . $avgRange . ')), "NA", AVERAGE(' . $avgRange . '))' );
				$format['formatNum'][] = $letters[ $col ] . $row;
				$row ++;
			}
			$col = 1;
			$sheet->setCellValueByColumnAndRow( $col, $row, 'Period Average' );
			$col += 2;
			for ( $k = $col; $k < ( $quarCount + 1 + $col ); $k ++ ) {
				$range = $letters[ $k ] . $rowStart . ':' . $letters[ $k ] . ( $row - 1 );
				$sheet->setCellValueByColumnAndRow( $k, $row, '=IF(ISERROR(AVERAGE(' . $range . ')), "NA", AVERAGE(' . $range . '))' );
				$format['formatNum'][] = $letters[ $k ] . $row;
			}
			$regionEndRow[] = $row;
			$row ++;
			$row ++;
		}

		$highestCol = $sheet->getHighestColumn();
		$highestRow = $sheet->getHighestRow();

		foreach ( $mergeRow as $key => $z ) {
			$format['merge'][]          = "A" . $z . ":" . $highestCol . $z;
			$format['size18'][]         = "A" . $z . ":" . $highestCol . ( $z + 2 );
			$format['fillDarkerBlue'][] = "A" . $z . ":" . $highestCol . $z;
			$format['textWhite'][]      = "A" . $z . ":" . $highestCol . $z;
			$format['outline'][]        = "A" . $z . ":" . $highestCol . $regionEndRow[ $key ];
			$format['outline'][]        = "A" . ( $z + 3 ) . ":" . $highestCol . $regionEndRow[ $key ];
			$format['outline'][]        = "A" . ( $z + 2 ) . ":" . $highestCol . ( $regionEndRow[ $key ] - 1 );
			#$format['hCenter'][] = "A".$z;
			$format['allBorders'][]     = "A" . $z . ":" . $highestCol . $regionEndRow[ $key ];
			$format['merge'][]          = "A" . $regionEndRow[ $key ] . ":B" . $regionEndRow[ $key ];
			$format['fillDarkerBlue'][] = "A" . $regionEndRow[ $key ] . ":" . $highestCol . $regionEndRow[ $key ];
			$format['size18'][]         = "A" . $regionEndRow[ $key ] . ":" . $highestCol . $regionEndRow[ $key ];
			$format['textWhite'][]      = "A" . $regionEndRow[ $key ] . ":" . $highestCol . $regionEndRow[ $key ];
			$format['outline'][]        = "A" . $z . ":B" . $regionEndRow[ $key ];
			$format['size14'][]         = "A" . ( $z + 3 ) . ":B" . $regionEndRow[ $key ];
			$format['size12'][]         = "A" . ( $z + 3 ) . ":" . $highestCol . $regionEndRow[ $key ];
			$format['size14'][]         = $highestCol . ( $z + 3 ) . ":" . $highestCol . $regionEndRow[ $key ];
			$format['outline'][]        = $highestCol . $z . ":" . $highestCol . $regionEndRow[ $key ];
			$col                        = 3;

			foreach ( $quarCounts as $years ) {
				$format['outline'][] = $letters[ $col ] . ( $z + 2 ) . ":" . $letters[ $col + $years ] . $regionEndRow[ $key ];
				$col                 += $years;
			}


			for ( $o = ( $z + 3 ); $o < $regionEndRow[ $key ]; $o ++ ) {
				if ( $o % 2 === 0 && $sheet->getCell( "A" . $o )->getValue() !== "Period Average" ) {
					$format['fillLightBlue'][] = "A" . $o . ":" . $highestCol . $o;
				}
			}

		}

		$format['hcenter'][] = "A1:A" . $highestRow;
		$format['hcenter'][] = "C1:" . $highestCol . $highestRow;
		$format['merge'][]   = "A1:" . $highestCol . "1";
		$format['size22'][]  = "A1:" . $highestCol . "1";
		$format['zAutoSize'] = range( "A", $highestCol );

		#echo "<h1>" . $type . ": " . $lc . "</h1>";

		$formatSheet->formatSheet( $sheet, $format );
	}

	$spreadSheet->setActiveSheetIndexByName( 'Worksheet' );
	$remove = $spreadSheet->getActiveSheetIndex();
	$spreadSheet->removeSheetByIndex( $remove );
	$spreadSheet->setActiveSheetIndexByName( 'TOTAL' );

	$fileDate = new DateTime();
	$fileDate->modify( '-1 Month' );
	$date = $fileDate->format( 'F Y' );

	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadSheet, "Xlsx" );
	$file   = "../output/corpAuditAnalysis/" . $type . "CorpAuditScores - 2019 Q1-Q3.xlsx";
	$writer->save( $file );

	$writeGroupEnd = microtime(true);
	$writeGroupTime = $writeGroupEnd - $writeGroupStart;

	return $writeGroupTime;


