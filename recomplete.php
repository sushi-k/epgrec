#!/usr/bin/php
<?php
include_once( "config.php" );
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/Settings.class.php" );

$settings = Settings::factory();


$reserve_id = $argv[1];

try{
	$rrec = new DBRecord( RESERVE_TBL, "id" , $reserve_id );
	
	if( file_exists( INSTALL_PATH .$settings->spool . "/". $rrec->path ) ) {
		// 予約完了
		$rrec->complete = '1';
		if( $settings->mediatomb_update ) {
			// ちょっと待った方が確実っぽい
			@exec("sync");
			sleep(15);
			$dbh = mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );
			if( $dbh !== false ) {
				$sqlstr = "use ".$settings->db_name;
				@mysql_query( $sqlstr );
				// 別にやらなくてもいいが
				$sqlstr = "set NAME utf8";
				@mysql_query( $sqlstr );
				$sqlstr = "update mt_cds_object set metadata='dc:description=".mysql_real_escape_string($rrec->description)."&epgrec:id=".$reserve_id."' where dc_title='".$rrec->path."'";
				@mysql_query( $sqlstr );
				$sqlstr = "update mt_cds_object set dc_title='".mysql_real_escape_string($rrec->title)."(".date("Y/m/d").")' where dc_title='".$rrec->path."'";
				@mysql_query( $sqlstr );
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
