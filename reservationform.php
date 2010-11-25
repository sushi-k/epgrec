<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/Smarty/Smarty.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );

if( ! isset( $_GET['program_id'] ) ) exit("Error: 番組IDが指定されていません" );
$program_id = $_GET['program_id'];

try {
  $prec = new DBRecord( PROGRAM_TBL, "id", $program_id );
  
  sscanf( $prec->starttime, "%4d-%2d-%2d %2d:%2d:%2d", $syear, $smonth, $sday, $shour, $smin, $ssec );
  sscanf( $prec->endtime, "%4d-%2d-%2d %2d:%2d:%2d", $eyear, $emonth, $eday, $ehour, $emin, $esec );
  
  $crecs = DBRecord::createRecords( CATEGORY_TBL );
  $cats = array();
  foreach( $crecs as $crec ) {
	$cat = array();
	$cat['id'] = $crec->id;
	$cat['name'] = $crec->name_jp;
	$cat['selected'] = $prec->category_id == $cat['id'] ? "selected" : "";
	
	array_push( $cats , $cat );
  }
  
  $smarty = new Smarty();
  
  $smarty->assign( "syear", $syear );
  $smarty->assign( "smonth", $smonth );
  $smarty->assign( "sday", $sday );
  $smarty->assign( "shour", $shour );
  $smarty->assign( "smin" ,$smin );
  $smarty->assign( "eyear", $eyear );
  $smarty->assign( "emonth", $emonth );
  $smarty->assign( "eday", $eday );
  $smarty->assign( "ehour", $ehour );
  $smarty->assign( "emin" ,$emin );
  
  $smarty->assign( "type", $prec->type );
  $smarty->assign( "channel", $prec->channel );
  $smarty->assign( "channel_id", $prec->channel_id );
  $smarty->assign( "record_mode" , $RECORD_MODE );
  
  $smarty->assign( "title", $prec->title );
  $smarty->assign( "description", $prec->description );
  
  $smarty->assign( "cats" , $cats );
  
  $smarty->assign( "program_id", $prec->id );
  
  $smarty->display("reservationform.html");
}
catch( exception $e ) {
	exit( "Error:". $e->getMessage() );
}
?>