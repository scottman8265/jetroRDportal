<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/26/2018
 * Time: 9:10 AM
 */

set_time_limit( 600 );
ini_set( "log_errors", 1 );
ini_set( "error_log", "logs/php-error.log" . date( 'ymd' ) );
ini_set( 'memory_limit', '1G' );
date_default_timezone_set( 'America/Chicago' );

require( '../vendor/autoload.php' );
require( '../class/Process.php' );
require( '../class/Format.php' );

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 * @param $periods
 *
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
 */
function createMWSheet( $deptArray, $branchArray, $writeArray, $periods ) {

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
	var_dump( $writeArray );

	foreach ( $writeArray as $dept => $a ) {
		echo "</br></br>" . $dept . "</br>";

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

			$sheet->setCellValueByColumnAndRow( $col, $row, '=IF(ISERROR(AVERAGE(' . $avgRange . ')), "NA", AVERAGE(' . $avgRange . '))' );
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

	$fileDate = new DateTime();
	$fileDate->modify( '-1 Month' );
	$date = $fileDate->format( 'F Y' );

	$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter( $spreadSheet, "Xlsx" );
	$writer->save( "../output/corpAuditAnalysis/corpScores - 2019 Q1-Q3 Test.xlsx" );
}

/**
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 * @param $type
 * @param $periods
 *
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
 */
function createarSheet( $deptArray, $branchArray, $writeArray, $type, $periods ) {

	$spreadSheet   = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
	$formatSheet   = new Format();
	$periodHeaders = getPeriodHeaders( $periods );
	$letters       = getColumnLetters();
	$periodCount   = 0;

	foreach ( $periodHeaders as $year => $quarter ) {
		$periodCount   += count( $quarter );
		$yearHeaders[] = [ 'year' => "20" . $year, 'count' => count( $quarter ) ];
		foreach ( $quarter as $q ) {
			$monthHeaders[ $year ][] = $q;
		}
	}

	$headers1 = [ 'Branch Number', 'Branch Name' ];

	foreach ( $writeArray as $name => $x ) {
		$names[] = $name;
	}

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
				$sheet->setCellValueByColumnAndRow( $col, $row, $years['year'] );
				$yearCol                = [
					'start' => $letters[ $col ],
					'end'   => $letters[ ( $col + $years['count'] - 1 ) ]
				];
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
			$monthColStart = $col;
			$yearCounts    = [];
			foreach ( $monthHeaders as $year => $monthNames ) {
				$yearCounts[] = count( $monthNames );
				foreach ( $monthNames as $quarter ) {
					$sheet->setCellValueByColumnAndRow( $col, $row, $quarter );
					$monthColEnd = $col;
					$col ++;
				}

			}
			$format['fillDarkBlue'][] = $letters[ $monthColStart ] . $row . ":" . $letters[ $monthColEnd ] . $row;
			$row ++;
			$branchCount = 1;
			$rowStart    = $row;
			foreach ( $writeArray[ $names[ $i ] ][ $lc ] as $branch => $data ) {
				$col = 1;
				$sheet->setCellValueByColumnAndRow( $col, $row, $branch );
				$col ++;
				$sheet->setCellValueByColumnAndRow( $col, $row, $branchArray[ $branch ] );
				$col ++;
				$avgStart = $col;

				foreach ( $data as $q ) {
					$sheet->setCellValueByColumnAndRow( $col, $row, $q );
					if ( $q === 'na' ) {
						$format['fillRed'][] = $letters[ $col ] . $row;
					}
					$avgEnd = $col;
					$col ++;
				}

				$avgRange = $letters[ $avgStart ] . $row . ":" . $letters[ $avgEnd ] . $row;
				$sheet->setCellValueByColumnAndRow( $col, $row, '=IF(ISERROR(AVERAGE(' . $avgRange . ')), "NA", AVERAGE(' . $avgRange . '))' );
				$format['formatNum'][] = $letters[ $col ] . $row;
				$row ++;
			}
			$col = 1;
			$sheet->setCellValueByColumnAndRow( $col, $row, 'Period Average' );
			$col += 2;
			for ( $k = $col; $k < ( $periodCount + 1 + $col ); $k ++ ) {
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

			foreach ( $yearCounts as $years ) {
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

		echo "<h1>" . $type . ": " . $lc . "</h1>";

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
	$file   = "../output/corpAuditAnalysis/" . $type . "CorpAuditScores - 2019 Q1-Q3 Test.xlsx";
	$writer->save( $file );
}

function getPeriodHeaders( $periods ) {
	foreach ( $periods as $period ) {
		$p                        = explode( ":", $period );
		$year                     = $p[0];
		$quarter                  = $p[1];
		$periodHeaders[ $year ][] = $quarter;
	}

	return $periodHeaders;
}

function getColumnLetters() {
	$letters = [
		1  => 'A',
		2  => 'B',
		3  => 'C',
		4  => 'D',
		5  => 'E',
		6  => 'F',
		7  => 'G',
		8  => 'H',
		9  => 'I',
		10 => 'J',
		11 => 'K',
		12 => 'L',
		13 => 'M',
		14 => 'N',
		15 => 'O',
		16 => 'P',
		17 => 'Q',
		18 => 'R',
		19 => 'S',
		20 => 'T',
		21 => 'U',
		22 => 'V',
		23 => 'W',
		24 => 'X',
		25 => 'Y',
		26 => 'Z'
	];

	return $letters;
}

/**
 * @param $periods
 * @param $lnk Process
 *
 * @return array
 */
function getSelectedAudits( $periods, $lnk ) {


	foreach ( $periods as $period ) {

		$pb      = explode( ":", $period );
		$year    = '20' . $pb[0];
		$quarter = $pb[1];

		$auditSql    = "SELECT id, branch, version, year, period FROM auditAnalysis.enteredaudits WHERE year = ? AND period = ?";
		$auditParams = [ $year, $quarter ];
		$auditQry    = $lnk->query( $auditSql, $auditParams );
		foreach ( $auditQry as $value ) {
			$branch       = $value['branch'];
			$id           = $value['id'];
			$version      = $value['version'];
			$auditArray[] = [ 'branch'  => $branch,
			                  'id'      => $id,
			                  'version' => $version,
			                  'year'    => $year,
			                  'quarter' => $quarter
			];
		}
	}

	return $auditArray;
}

/**
 * @param $lnk Process
 *
 * @return array
 */
function getLookupArrays( $lnk ) {
	$branchSql = "SELECT branchNum, branchName, regional, director, auditor FROM branchInfo.branches where active = 1 && location = 'MW'";
	$branchQry = $lnk->query( $branchSql );//sets branch, auditor, regional, director branch lookup arrays
	foreach ( $branchQry as $a ) {
		$branchArray[ $a['branchNum'] ] = $a['branchName'];

		$auditorArray[ $a['auditor'] ][]   = $a['branchNum'];
		$regionalArray[ $a['regional'] ][] = $a['branchNum'];
		$directorArray[ $a['director'] ][] = $a['branchNum'];
	}
	ksort( $branchArray );
	$auditorArray  = sortArrays( $auditorArray );
	$regionalArray = sortArrays( $regionalArray );
	$directorArray = sortArrays( $directorArray );

	return [ 'branch'   => $branchArray,
	         'auditor'  => $auditorArray,
	         'regional' => $regionalArray,
	         'director' => $directorArray
	];

}

/**
 * @param $lnk Process
 *
 * @return array
 */
function setScoreDeptArrays( $lnk ) {
	$deptSql      = "SELECT lcAuditCode, auditName FROM auditAnalysis.auditlookup WHERE active = TRUE";
	$deptQry      = $lnk->query( $deptSql );
	$scoreField[] = 'totScore';
	$scoreField[] = 'freshScore';
	foreach ( $deptQry as $dept ) {
		$scoreField[] = $dept['lcAuditCode'] . "Score";
	}//sets lookup array for audit names
	$deptArray['totScore']   = 'OVERALL';
	$deptArray['freshScore'] = 'FRESH';
	foreach ( $deptQry as $b ) {
		$deptArray[ $b['lcAuditCode'] . "Score" ] = $b['auditName'];
	}

	return [ 'scoreField' => $scoreField, 'deptArray' => $deptArray ];
}

/**
 * @param $audits
 * @param $scores
 * @param $lnk Process
 *
 * @return mixed
 */
function getScoreArrays( $audits, $scores, $lnk ) {

	#var_dump($scores);
	$scoreSql = "SELECT * FROM auditAnalysis.auditscores where auditID = ? && rep = 1";

	foreach ( $audits as $x ) {
		$version = $x['version'];
		$id      = $x['id'];
		$branch  = $x['branch'];
		$year    = $x['year'];
		$month   = $x['quarter'];

		if ( ! is_null( $version ) ) {
			$scoreParams = [ $id ];
			$scoreQry    = $lnk->query( $scoreSql, $scoreParams );

			if ( isset( $scoreQry[0] ) ) {
				foreach ( $scores as $field ) {

					if ( $scoreQry[0][ $field ] > 0 ) {
						$score = number_format( $scoreQry[0][ $field ] * 100, 2 );
						#$scoreArray[$year][$month][$branch][$field][] = $score;
						$branchScoreArray[ $branch ][ $field ][ $year ][ $month ] = $score;
					} else {
						#$scoreArray[$year][$month][$branch][$field][] = 'na';
						$branchScoreArray[ $branch ][ $field ][ $year ][ $month ] = 'na';
					}
				}
			} else {
				$branchScoreArray['error'][] = 'Error in ' . $branch . " " . $year . " " . $month . "</br>";
			}
		} else {
			foreach ( $scores as $field ) {
				#$scoreArray[$year][$month][$branch][$field][] = 'na';
				$branchScoreArray[ $branch ][ $field ][ $year ][ $month ] = 'na';

			}
		}
	}

	return $branchScoreArray;
}

function sortArrays( $array ) {
	foreach ( $array as $x => $y ) {
		asort( $array[ $x ] );
	}

	return $array;
}

/**
 * @param $audits
 * @param $lnk Process
 *
 * @return mixed
 */
function getAuditorArray( $audits, $lnk ) {

	foreach ( $audits as $key => $auditInfo ) {

		$branch           = $auditInfo['branch'];
		$getAuditorSql    = "SELECT auditor FROM auditAnalysis.auditpeople WHERE auditID = ?";
		$getAuditorParams = [ $auditInfo['id'] ];
		$getAuditorQry    = $lnk->query( $getAuditorSql, $getAuditorParams );

		if ( ! $getAuditorQry ) {
			$name = 'NO PEOPLE INFO OR SKIPPED';
		} else {
			$name = strtoupper( $getAuditorQry[0]['auditor'] );
		}

		$auditor[ $name ][] = $branch;
	}

	return $auditor;
}

#$periods = isset($_SESSION['periods']) ? $_SESSION['periods'] : ['19:2', '19:3'];
$periods = [ '19:Q1', '19:Q2', '19:Q3' ];

$lnk = new Process();

$auditorSql  = "SELECT auditorFName, auditorLName FROM branchInfo.auditors WHERE auditorID = ? AND auditorFT = 1";
$regionalSql = "SELECT fName, lName FROM branchInfo.regionals WHERE regionID = ?";
$directorSql = "SELECT fName, lName FROM branchInfo.regionals WHERE regionID = ?";

$lua           = getLookupArrays( $lnk );
$branchArray   = $lua['branch'];
$regionalArray = $lua['regional'];
$directorArray = $lua['director'];

$sd         = setScoreDeptArrays( $lnk );
$scoreField = $sd['scoreField'];
$deptArray  = $sd['deptArray'];

$auditArray = getSelectedAudits( $periods, $lnk );

#$auditorArray = $lua['auditor'];
$auditorArray = getAuditorArray( $auditArray, $lnk );

$branchScoreArray = getScoreArrays( $auditArray, $scoreField, $lnk );

//sets auditorScoreArray **possible delete
foreach ( $auditorArray as $auditor => $branch ) {
	$branchCount = count( $branch );
	/*$auditorParams = [$auditor];
	$auditorQry = $lnk->query($auditorSql, $auditorParams);
	$name = $auditorQry[0]['auditorFName'] . " " . $auditorQry[0]['auditorLName'];*/
	for ( $i = 0; $i < $branchCount; $i ++ ) {
		$auditorScoreArray[ $auditor ][ $branch[ $i ] ][] = $branchScoreArray[ $branch[ $i ] ];
	}
}
unset( $auditorArray );

//sets regionalScoreArray **possible delete
foreach ( $regionalArray as $regional => $branch ) {
	$branchCount    = count( $branch );
	$regionalParams = [ $regional ];
	$regionalQry    = $lnk->query( $regionalSql, $regionalParams );
	$name           = $regionalQry[0]['fName'] . " " . $regionalQry[0]['lName'];
	for ( $i = 0; $i < $branchCount; $i ++ ) {
		$regionalScoreArray[ $name ][ $branch[ $i ] ][] = $branchScoreArray[ $branch[ $i ] ];
	}
}
unset( $regionalArray );

//sets diretorScoreArray **possible Delete
foreach ( $directorArray as $director => $branch ) {
	$branchCount    = count( $branch );
	$directorParams = [ $director ];
	$directorQry    = $lnk->query( $directorSql, $directorParams );
	$name           = $directorQry[0]['fName'] . " " . $directorQry[0]['lName'];
	for ( $i = 0; $i < $branchCount; $i ++ ) {
		$directorScoreArray[ $name ][ $branch[ $i ] ][] = $branchScoreArray[ $branch[ $i ] ];
	}
}
unset( $directorArray );

var_dump( $branchScoreArray );

//sets mwWriteArray **possible consolidation
foreach ( $branchScoreArray as $branch => $a ) {
	foreach ( $a as $field => $b ) {
		foreach ( $b as $c => $d ) {
			foreach ( $d as $e ) {
				$mwWriteArray[ $field ][ $branch ][] = $e;
			}
		}

	}
}
unset( $branchScoreArray );

//sets regionalWriteArray **possible consolidation
foreach ( $regionalScoreArray as $regional => $f ) {
	foreach ( $f as $branch => $g ) {
		foreach ( $g[0] as $field => $h ) {
			foreach ( $h as $year => $month ) {
				foreach ( $month as $m ) {
					$regionalWriteArray[ $regional ][ $field ][ $branch ][] = $m;
				}
			}
		}
	}
}
unset( $regionalScoreArray );

//set directorWriteArray **possible consolidation
foreach ( $directorScoreArray as $director => $f ) {
	foreach ( $f as $branch => $g ) {
		foreach ( $g[0] as $field => $h ) {
			foreach ( $h as $year => $month ) {
				foreach ( $month as $m ) {
					$directorWriteArray[ $director ][ $field ][ $branch ][] = $m;
				}
			}
		}
	}
}
unset( $directorScoreArray );

//sets auditorWriteArray **possible consolidation
foreach ( $auditorScoreArray as $auditor => $r ) {
	asort( $r );
	foreach ( $r as $branch => $t ) {
		foreach ( $t[0] as $field => $u ) {
			foreach ( $u as $year => $month ) {
				foreach ( $month as $m ) {
					$auditorWriteArray[ $auditor ][ $field ][ $branch ][] = $m;
				}
			}
		}
	}
}
#unset($auditorScoreArray);

var_dump( $auditorScoreArray );

#createMWSheet($deptArray, $branchArray, $mwWriteArray, $periods);
#createarSheet($deptArray, $branchArray, $regionalWriteArray, 'Regional', $periods);
createarSheet( $deptArray, $branchArray, $auditorWriteArray, 'Auditor', $periods );
#createarSheet($deptArray, $branchArray, $directorWriteArray, 'Director', $periods);








