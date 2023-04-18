<?php
/**
 * Created by PhpStorm.
 * User: Robert Brandt
 * Date: 12/26/2018
 * Time: 9:10 AM
 */

$scriptStart = microtime( true );

set_time_limit( 300 );
ini_set( "log_errors", 1 );
ini_set( "error_log", "logs/php-error.log" . date( 'ymd' ) );
ini_set( 'memory_limit', '1G' );
date_default_timezone_set( 'America/Chicago' );

require( '../vendor/autoload.php' );
require( '../class/Process.php' );
require( '../class/Format.php' );

use PhpOffice\PhpSpreadsheet\Reader\Exception;

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 */
function createTotalSheet( $deptArray, $branchArray, $writeArray, $periods ) {

	include_once '../inc/createTotalScoreSheet.php';

}

/**
 * @param $spreadSheet \PhpOffice\PhpSpreadsheet\Spreadsheet
 * @param $deptArray
 * @param $branchArray
 * @param $writeArray
 * @param $type
 *
 * @throws \PhpOffice\PhpSpreadsheet\Exception
 */
function createGroupSheet( $deptArray, $branchArray, $writeArray, $type, $periods ) {

	include_once '../inc/createGroupScoreSheet.php';

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

function getSelectedAudits( $periods, $lnk ) {

	$lnk = new Process();

	foreach ( $periods as $period ) {

		$pb      = explode( ":", $period );
		$year    = '20' . $pb[0];
		$quarter = $pb[1];

		$auditSql    = "SELECT id, branch, version, year, period, auditStatus FROM auditAnalysis.enteredaudits WHERE year = ? AND period = ?";
		$auditParams = [ $year, $quarter ];
		$auditQry    = $lnk->query( $auditSql, $auditParams );
		#var_dump($auditQry);
		foreach ( $auditQry as $value ) {

			$auditArray[ $value['id'] ] = [
				'branch'  => $value['branch'],
				'version' => $value['version'],
				'year'    => $year,
				'quarter' => $quarter,
				'status'  => $value['auditStatus']
			];
		}
	}

	asort( $auditArray );

	return $auditArray;
}

function getLookupArrays( $lnk ) {
	#$lnk = new Process();

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

	return [
		'branch'   => $branchArray,
		'auditor'  => $auditorArray,
		'regional' => $regionalArray,
		'director' => $directorArray
	];

}

function setScoreDeptArrays( $lnk ) {
	#$lnk      = new Process();
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

function getScoreArrays( $audits, $depts, $lnk ) {

	$getScoresStart = microtime( true );

	#var_dump($scoreSql);

	#$lnk   = new Process();
	$scoreSql    = "SELECT * FROM auditAnalysis.auditscores where auditID = ? && rep = 1";
	$countAudits = 0;
	echo "selected audit count: " . count( $audits );
	foreach ( $audits as $id => $x ) {
		$version = $x['version'];
		#$id      = $x['id'];
		$branch  = $x['branch'];
		$year    = $x['year'];
		$quarter = $x['quarter'];
		$status  = $x['status'];

		#echo "status: " . $status . "</br>";

		#var_dump($status);

		if ( $status === "1" ) {
			$scoreParams = [ $id ];
			$scoreQry    = $lnk->query( $scoreSql, $scoreParams );
			#var_dump($scoreQry);

			foreach ( $depts as $field ) {

				if ( isset( $scoreQry[0] ) ) {
					$deptScore = $scoreQry[0][ $field ];
				} else {
					$deptScore = 'na';
				}

				#echo "fields line 204: " . $field . PHP_EOL;

				#echo "dept score [line 584] " . $deptScore . ":" . $id. "</br>";

				if ( $deptScore > 0 ) {
					$score = number_format( $deptScore * 100, 2 );
					#$scoreArray[$year][$month][$branch][$field][] = $score;
					#$branchScoreArray[ $branch ][ $field ][ $year ][ $quarter ] = $score;
					$branchScoreArray[ $id ][] = [
						'branch'  => $branch,
						'depts'   => [ 'dept' => $field, 'score' => $score ],
						'year'    => $year,
						'quarter' => $quarter
					];
				} else {
					#$scoreArray[$year][$month][$branch][$field][] = 'na';
					$branchScoreArray[ $id ][] = [
						'branch'  => $branch,
						'depts'   => [ 'dept' => $field, 'score' => 'na' ],
						'year'    => $year,
						'quarter' => $quarter
					];
				}
			}
		}

		if ( $status === "2" ) {
			foreach ( $depts as $field ) {
				#$scoreArray[$year][$month][$branch][$field][] = 'na';
				$branchScoreArray[ $id ][] = [
					'branch'  => $branch,
					'depts'   => [ 'dept' => $field, 'score' => 'na' ],
					'year'    => $year,
					'quarter' => $quarter
				];
			}
		}
		$countAudits ++;
		#echo "audit # line 567: " . $auditCount . "</br>";
	}

	#return ['scoreArray' => $scoreArray, 'branchScoreArray' => $branchScoreArray];

	#var_dump($branchScoreArray);

	$getScoresEnd = microtime( true );
	$getScoreTime = $getScoresEnd - $getScoresEnd;

	return [ 'array' => $branchScoreArray, 'time' => $getScoreTime ];
}

function getWriteAuditor( $audits, $lnk, $branchScores ) {

	$auditCount = count( $audits );

	foreach ( $audits as $id => $auditInfo ) {

		$branch           = $auditInfo['branch'];
		$getAuditorSql    = "SELECT auditor FROM auditAnalysis.auditpeople WHERE auditID = ?";
		$getAuditorParams = [ $id ];
		$getAuditorQry    = $lnk->query( $getAuditorSql, $getAuditorParams );
		$deptCount = count($branchScores[$id]);
		echo "deptCount line 266: " . $deptCount. ":".$id. "</br>";
		for ( $i = 0; $i < $deptCount; $i ++ ) {
			if ( ! $getAuditorQry ) {
				$auditor[ 'SKIPPED' ][ $branchScores[ $id ][ $i ]['depts']['dept'] ][ $branch ] = $branchScores[ $id ][ $i ]['depts']['score'];

			} else {

				$name = strtoupper( $getAuditorQry[0]['auditor'] );

				#$auditor[ $name ][] = [ 'id' => $id, 'branch' => $branch ];


				$auditor[ $name ][ $branchScores[ $id ][ $i ]['depts']['dept'] ][ $branch ] = $branchScores[ $id ][ $i ]['depts']['score'];
			}
		}
	}

	return $auditor;
}

function sortArrays( $array ) {
	foreach ( $array as $x => $y ) {
		asort( $array[ $x ] );
	}

	return $array;
}

#$periods = isset($_SESSION['periods']) ? $_SESSION['periods'] : ['19:2', '19:3'];
$periods = [ '19:Q1', '19:Q2', '19:Q3' ];

$lnk = new Process();

$auditorSql  = "SELECT auditorFName, auditorLName FROM branchInfo.auditors WHERE auditorID = ?";
$regionalSql = "SELECT fName, lName FROM branchInfo.regionals WHERE regionID = ?";
$directorSql = "SELECT fName, lName FROM branchInfo.regionals WHERE regionID = ?";

$auditArray = getSelectedAudits( $periods, $lnk );


$lua         = getLookupArrays( $lnk );
$branchArray = $lua['branch'];
#$auditorArray  = $lua['auditor'];
$regionalArray = $lua['regional'];
$directorArray = $lua['director'];

$sd         = setScoreDeptArrays( $lnk );
$scoreField = $sd['scoreField'];
$deptArray  = $sd['deptArray'];

$branchScoreArrays = getScoreArrays( $auditArray, $scoreField, $lnk );

$branchScoreArray = $branchScoreArrays['array'];
$scoreTime        = $branchScoreArrays['time'];

$auditorWriteArray = getWriteAuditor( $auditArray, $lnk, $branchScoreArray );


#asort($auditorArray);

var_dump($auditorWriteArray);
#var_dump( $branchScoreArray[1370] );


/*foreach ( $auditorArray as $auditor => $y ) {
	var_dump( $y );
	$branchCount = count( $y );
	echo "</br>" . $branchCount . "</br></br>";
	$auditorParams = [ $auditor ];
	$auditorQry    = $lnk->query( $auditorSql, $auditorParams );
	$name          = $auditorQry[0]['auditorFName'] . " " . $auditorQry[0]['auditorLName'];
	$name = $auditor;
	for ( $i = 0; $i < $branchCount; $i ++ ) {
		#echo "y->i->branch line 679: " . $y[$i]['branch'] . "</br>";
		if ( isset( $y[ $i ]['branch'] ) ) {
			$auditorScoreArray[ $name ][ $y[$i]['branch'] ][] = $branchScoreArray[ $y[$i]['branch'] ];
		} else {
			$auditorScoreArray['error'][] = $name;
		}
		#var_dump($branchScoreArray[$y[$i]['branch']]);
	}
}*/
#var_dump($auditorScoreArray);

/*foreach ( $regionalArray as $regional => $branch ) {
	$branchCount    = count( $branch );
	$regionalParams = [ $regional ];
	$regionalQry    = $lnk->query( $regionalSql, $regionalParams );
	$name           = $regionalQry[0]['fName'] . " " . $regionalQry[0]['lName'];
	for ( $i = 0; $i < $branchCount; $i ++ ) {
		$regionalScoreArray[ $name ][ $branch[ $i ] ][] = $branchScoreArray[ $branch[ $i ] ];
	}
}
unset( $regionalArray );*/

/*foreach ( $directorArray as $director => $branch ) {
	$branchCount    = count( $branch );
	$directorParams = [ $director ];
	$directorQry    = $lnk->query( $directorSql, $directorParams );
	$name           = $directorQry[0]['fName'] . " " . $directorQry[0]['lName'];
	for ( $i = 0; $i < $branchCount; $i ++ ) {
		$directorScoreArray[ $name ][ $branch[ $i ] ][] = $branchScoreArray[ $branch[ $i ] ];
	}
}
unset( $directorArray );*/

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

/*foreach ( $regionalScoreArray as $regional => $f ) {
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
unset( $regionalScoreArray );*/

/*foreach ( $directorScoreArray as $director => $f ) {
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
unset( $directorScoreArray );*/

/*foreach ( $auditorScoreArray as $auditor => $r ) {
	foreach ( $r as $branch => $t ) {
		foreach ( $t[0] as $field => $u ) {
			foreach ( $u as $year => $month ) {
				echo "</br>" . $branch . "</br></br>";
				#var_dump( $month );
				foreach ( $month as $m ) {
					$auditorWriteArray[ $auditor ][ $field ][ $branch ][] = $m;
					#var_dump($m);
				}
			}
		}
	}
	#var_dump($m);
}*/
#var_dump( $auditorWriteArray );

#$totalWriteTime = createTotalSheet( $deptArray, $branchArray, $mwWriteArray, $periods );
#$regionalWriteTime = createGroupSheet($deptArray, $branchArray, $regionalWriteArray, 'Regional', $periods);
$auditorWriteTime = createGroupSheet( $deptArray, $branchArray, $auditorWriteArray, 'Auditor', $periods );
#$directorWriteTime = createGroupSheet($deptArray, $branchArray, $directorWriteArray, 'Director', $periods);

$scriptEndTime = microtime( true );
$scriptTime    = $scriptEndTime - $scriptStart;

echo "total script time: " . $scriptTime . "</br>";
echo "total score time: " . $scoreTime . "</br>";
if ( isset( $totalWriteTime ) ) {
	echo 'total write time: ' . $totalWriteTime . "</br>";
}
if ( isset( $regionalWriteTime ) ) {
	echo 'total regional write time: ' . $regionalWriteTime . "</br>";
}
if ( isset( $auditorWriteTime ) ) {
	echo 'total auditor write time: ' . $auditorWriteTime . "</br>";
}
if ( isset( $directorWriteTime ) ) {
	echo 'total director write time: ' . $directorWriteTime . "</br>";
}

#var_dump($auditorScoreArray['error']);

$auditCount = 0;

/*foreach ($auditorScoreArray['error'] as $branch => $data) {

	$auditCount += count($data);

	print_r($data);

}*/

echo "</br></br>audit count line 833: " . $auditCount . "</br></br>";











