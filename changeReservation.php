<?php
include_once('config.php');
include_once(INSTALL_PATH."/DBRecord.class.php");

if( !isset( $_POST['reserve_id'] ) ) {
	exit("Error: IDが指定されていません" );
}
$reserve_id = $_POST['reserve_id'];

try {
	$rec = new DBRecord(TBL_PREFIX.RESERVE_TBL, "id", $reserve_id );
	
	if( isset( $_POST['title'] ) ) {
		$rec->title = trim( $_POST['title'] );
	}
	
	if( isset( $_POST['description'] ) ) {
		$rec->description = trim( $_POST['description'] );
	}
}
catch( Exception $e ) {
	exit("Error: ". $e->getMessage());
}

exit("complete");y

?>