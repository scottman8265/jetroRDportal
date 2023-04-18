<?php

require_once( '../class/Process.php' );

$lnk      = new Process();
$inputSet = true;
$tracking = $identifier = $outlier = $date = null;

foreach ( $_POST as $key => $val ) {
	$arr[ $key ] = $val;

	switch ( $key ) {
		case 'regAud':
		case 'branch':
			$identifier = $val;
			break;
		case 'outlier':
			$outlier = $val;
			break;
		case 'date':
			$date = $val;
			break;
		case 'tracking':
			$tracking = $val;
			break;
		default:
			$inputSet = false;
	}
}

$sql    = "INSERT INTO trackers.trackers (tracking, date, identifier, outlier) VALUES (?, ?, ?, ?)";
$params = [ $tracking, $date, $identifier, $outlier ];
$qry    = $lnk->query( $sql, $params );

if ( $qry ) {
	print_r($params);
} else {
	echo "You Fucked Up";
}

#echo json_encode( $arr );