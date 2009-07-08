#!/usr/bin/php
<?php
include_once( "config.php" );
include_once( INSTALL_PATH . "/DBRecord.class.php" );

$reserve_id = $argv[1];

try{
	$rrec = new DBRecord( TBL_PREFIX.RESERVE_TBL, "id" , $reserve_id );
	
	if( file_exists( INSTALL_PATH . SPOOL . "/". $rrec->path ) ) {
		// 予約完了
		$rrec->complete = '1';
	}
	else {
		// 予約失敗
		$rrec->delete();
	}
}
catch( exception $e ) {
	exit( $e->getMessage() );
}

?>
