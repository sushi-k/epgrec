<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );
include_once( INSTALL_PATH . "/Reservation.class.php" );

$program_id = 0;
if( isset( $_POST['program_id'] ) ) $program_id = $_POST['program_id'];

$start_time = @mktime( $_POST['shour'], $_POST['smin'], 0, $_POST['smonth'], $_POST['sday'], $_POST['syear'] );
if( ($start_time < 0) || ($start_time === false) ) {
	if( $program_id ) jdialog( "開始時間が不正です" , "reservation.php?program_id=".$program_id );
	else jdialog("開始時間が不正です" );
}

$end_time = @mktime( $_POST['ehour'], $_POST['emin'], 0, $_POST['emonth'], $_POST['eday'], $_POST['eyear'] );
if( ($end_time < 0) || ($end_time === false) ) {
	if( $program_id ) jdialog( "終了時間が不正です" , "reservation.php?program_id=".$program_id );
	else jdialog("終了時間が不正です" );
}

$channel_id = $_POST['channel_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$category_id = $_POST['category_id'];
$mode = $_POST['record_mode'];


$rval = 0;
try{
	$rval = Reservation::custom(
		toDatetime($start_time),
		toDatetime($end_time),
		$channel_id,
		$title,
		$description,
		$category_id,
		$program_id,
		0,		// 自動録画
		$mode	// 録画モード
	);
}
catch( Exception $e ) {
	if( $progarm_id ) jdialog( $e->getMessage(), "reservation.php?program_id=".$program_id );
	else jdialog( $e->getMessage() );
}

jdialog("予約しました:job番号".$rval);

?>