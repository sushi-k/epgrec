<?php
include_once('config.php');
include_once(INSTALL_PATH."/DBRecord.class.php");
include_once(INSTALL_PATH."/reclib.php");

if( !isset( $_POST['reserve_id'] ) ) {
	exit("Error: IDが指定されていません" );
}
$reserve_id = $_POST['reserve_id'];

$dbh = false;
if( defined("MEDIATOMB_UPDATE") ) {
	if( MEDIATOMB_UPDATE ) {
		$dbh = @mysql_connect( DB_HOST, DB_USER, DB_PASS );
		if( $dbh !== false ) {
			$sqlstr = "use ".DB_NAME;
			@mysql_query( $sqlstr );
			$sqlstr = "set NAME utf8";
			@mysql_query( $sqlstr );
		}
	}
}

try {
	$rec = new DBRecord(TBL_PREFIX.RESERVE_TBL, "id", $reserve_id );
	
	if( isset( $_POST['title'] ) ) {
		$rec->title = trim( $_POST['title'] );
		if( ($dbh !== false) && ($rec->complete == 1) ) {
			$title = trim( mysql_real_escape_string($_POST['title']));
			$title .= "(".date("Y/m/d", toTimestamp($rec->starttime)).")";
			$sqlstr = "update mt_cds_object set dc_title='".$title."' where metadata regexp 'epgrec:id=".$reserve_id."$'";
			mysql_query( $sqlstr );
		}
	}
	
	if( isset( $_POST['description'] ) ) {
		$rec->description = trim( $_POST['description'] );
		if( ($dbh !== false) && ($rec->complete == 1) ) {
			$desc = "dc:description=".trim( mysql_real_escape_string($_POST['description']));
			$desc .= "&epgrec:id=".$reserve_id;
			$sqlstr = "update mt_cds_object set metadata='".$desc."' where metadata regexp 'epgrec:id=".$reserve_id."$'";
			@mysql_query( $sqlstr );
		}
	}
}
catch( Exception $e ) {
	exit("Error: ". $e->getMessage());
}

exit("complete");

?>