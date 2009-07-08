<?php
include_once('config.php');
include_once( INSTALL_PATH . '/DBRecord.class.php' );
include_once( INSTALL_PATH . '/Reservation.class.php' );
include_once( INSTALL_PATH . '/reclib.php' );
include_once( INSTALL_PATH . '/Keyword.class.php' );

if( isset($_GET['keyword_id'])) {
	try {
		$rec = new Keyword( "id", $_GET['keyword_id'] );
		$rec->delete();
	}
	catch( Exception $e ) {
		exit( "Error:" . $e->getMessage() );
	}
}
else exit( "Error:キーワードIDが指定されていません" );
?>