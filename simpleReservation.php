<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/Reservation.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );

if( ! isset( $_GET['program_id'] ) ) exit("Error: 番組が指定されていません" );
$program_id = $_GET['program_id'];

try {
	Reservation::simple( $program_id );
}
catch( Exception $e ) {
	exit( "Error:". $e->getMessage() );
}
?>