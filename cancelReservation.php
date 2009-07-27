<?php
include_once('config.php');
include_once( INSTALL_PATH . '/DBRecord.class.php' );
include_once( INSTALL_PATH . '/Reservation.class.php' );
include_once( INSTALL_PATH . '/reclib.php' );
include_once( INSTALL_PATH . '/Settings.class.php' );

$program_id = 0;
$reserve_id = 0;
$settings = Settings::factory();

if( isset($_GET['program_id'])) {
	$program_id = $_GET['program_id'];
}
else if(isset($_GET['reserve_id'])) {
	$reserve_id = $_GET['reserve_id'];
	try {
		$rec = new DBRecord( RESERVE_TBL, "id" , $reserve_id );
		$program_id = $rec->program_id;
		
		if( isset( $_GET['delete_file'] ) ) {
			if( $_GET['delete_file'] == 1 ) {
				// ファイルを削除
				if( file_exists( INSTALL_PATH."/".$settings->spool."/".$rec->path ) ) {
					@unlink(INSTALL_PATH."/".$settings->spool."/".$rec->path);
				}
			}
		}
	}
	catch( Exception $e ) {
		// 無視
	}
}



// 手動取り消しのときには、その番組を自動録画対象から外す
if( $program_id ) {
	try {
		$rec = new DBRecord(PROGRAM_TBL, "id", $program_id );
		$rec->autorec = 0;
	}
	catch( Exception $e ) {
		// 無視
	}
}

// 予約取り消し実行
try {
	Reservation::cancel( $reserve_id, $program_id );
}
catch( Exception $e ) {
	exit( "Error" . $e->getMessage() );
}
exit();
?>