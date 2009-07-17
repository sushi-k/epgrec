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
		if( defined("MEDIATOMB_UPDATE") ) {
			if( MEDIATOMB_UPDATE ) {
				// ちょっと待った方が確実っぽい
				@exec("sync");
				sleep(15);
				$dbh = mysql_connect( DB_HOST, DB_USER, DB_PASS );
				if( $dbh !== false ) {
					$sqlstr = "use ".DB_NAME;
					mysql_query( $sqlstr );
					// 別にやらなくてもいいが
					$sqlstr = "set NAME utf8";
					mysql_query( $sqlstr );
					$sqlstr = "update mt_cds_object set metadata='dc:description=".mysql_real_escape_string($rrec->description)."' where dc_title='".$rrec->path."'";
					mysql_query( $sqlstr );
					$sqlstr = "update mt_cds_object set dc_title='".mysql_real_escape_string($rrec->title)."(".date("Y/m/d").")' where dc_title='".$rrec->path."'";
					mysql_query( $sqlstr );
				}
			}
		}
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
