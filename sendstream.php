<?php
header("Expires: Thu, 01 Dec 1994 16:00:00 GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s"). " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");


include_once("config.php");
include_once(INSTALL_PATH . "/DBRecord.class.php" );
include_once(INSTALL_PATH . "/reclib.php" );
include_once(INSTALL_PATH . "/Settings.class.php" );

$settings = Settings::factory();

if( ! isset( $_GET['reserve_id'] )) jdialog("予約番号が指定されていません", "recordedTable.php");
$reserve_id = $_GET['reserve_id'];


try{
	$rrec = new DBRecord( RESERVE_TBL, "id", $reserve_id );

	$start_time = toTimestamp($rrec->starttime);
	$end_time = toTimestamp($rrec->endtime );
	$duration = $end_time - $start_time;
	
	$size = 3 * 1024 * 1024 * $duration;	// 1秒あたり3MBと仮定

	header('Content-type: video/mpeg');
	header('Content-Disposition: inline; filename="'.$rrec->path.'"');
	header('Content-Length: ' . $size );
	
	ob_clean();
	flush();
	
	$fp = @fopen( INSTALL_PATH.$settings->spool."/".$rrec->path, "r" );
	if( $fp !== false ) {
		ob_start(null,4096);
		$status = array();
		do {
			echo fread( $fp, 4096 );
			if( feof( $fp ) ) break;
			
			$status = ob_get_status();
		}
		while( $status['status'] != PHP_OUTPUT_HANDLER_END );
	}
	fclose($fp);
}
catch(exception $e ) {
	exit( $e->getMessage() );
}
?>