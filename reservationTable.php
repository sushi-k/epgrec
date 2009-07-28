<?php
include_once('config.php');
include_once( INSTALL_PATH . '/DBRecord.class.php' );
include_once( INSTALL_PATH . '/Smarty/Smarty.class.php' );

try{
	$rvs = DBRecord::createRecords(RESERVE_TBL, "WHERE complete='0' ORDER BY starttime ASC" );
	
	$reservations = array();
	foreach( $rvs as $r ) {
		$cat = new DBRecord(CATEGORY_TBL, "id", $r->category_id );
		$arr = array();
		$arr['id'] = $r->id;
		$arr['type'] = $r->type;
		$arr['channel'] = $r->channel;
		$arr['starttime'] = $r->starttime;
		$arr['endtime'] = $r->endtime;
		$arr['mode'] = $RECORD_MODE[$r->mode]['name'];
		$arr['title'] = $r->title;
		$arr['description'] = $r->description;
		$arr['cat'] = $cat->name_en;
		$arr['autorec'] = $r->autorec;
		
		array_push( $reservations, $arr );
	}
	
	$smarty = new Smarty();
	$smarty->assign("sitetitle","録画予約一覧");
	$smarty->assign( "reservations", $reservations );
	$smarty->display("reservationTable.html");
}
catch( exception $e ) {
	exit( $e->getMessage() );
}
?>