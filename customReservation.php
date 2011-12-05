<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );
include_once( INSTALL_PATH . "/Reservation.class.php" );
include_once( INSTALL_PATH . "/Settings.class.php" );

$settings = Settings::factory();

$program_id = 0;
if( isset( $_POST['program_id'] ) ) $program_id = $_POST['program_id'];


if(!(
   isset($_POST['shour'])       && 
   isset($_POST['smin'])        &&
   isset($_POST['smonth'])      &&
   isset($_POST['sday'])        &&
   isset($_POST['syear'])       &&
   isset($_POST['ehour'])       &&
   isset($_POST['emin'])        &&
   isset($_POST['emonth'])      &&
   isset($_POST['eday'])        &&
   isset($_POST['eyear'])       &&
   isset($_POST['channel_id'])  &&
   isset($_POST['title'])       &&
   isset($_POST['description']) &&
   isset($_POST['category_id']) &&
   isset($_POST['record_mode']))
) {
	exit("Error:予約に必要な値がセットされていません");
}


$start_time = @mktime( $_POST['shour'], $_POST['smin'], 0, $_POST['smonth'], $_POST['sday'], $_POST['syear'] );
if( ($start_time < 0) || ($start_time === false) ) {
	exit("Error:開始時間が不正です" );
}

$end_time = @mktime( $_POST['ehour'], $_POST['emin'], 0, $_POST['emonth'], $_POST['eday'], $_POST['eyear'] );
if( ($end_time < 0) || ($end_time === false) ) {
	exit("Error:終了時間が不正です" );
}

$channel_id = $_POST['channel_id'];
$title = $_POST['title'];
$description = $_POST['description'];
$category_id = $_POST['category_id'];
$mode = $_POST['record_mode'];


$rval = 0;
try{
	$rval = Reservation::custom(
		date('Y-m-d H:i:s', $start_time),
		date('Y-m-d H:i:s', $end_time),
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
	exit( "Error:".$e->getMessage() );
}
exit( "".$program_id );
?>
