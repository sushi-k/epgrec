<?php
include_once('config.php');
include_once( INSTALL_PATH . '/DBRecord.class.php' );
include_once( INSTALL_PATH . '/Smarty/Smarty.class.php' );
include_once( INSTALL_PATH . '/Settings.class.php' );

$settings = Settings::factory();


$order = "";
$search = "";
$category_id = 0;
$station = 0;

// mysql_real_escape_stringより先に接続しておく必要がある
$dbh = @mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );

// $options = "WHERE complete='1'";
$options = "WHERE starttime < '". date("Y-m-d H:i:s")."'";	// ながら再生は無理っぽい？

if(isset( $_GET['key']) ) {
	$options .= " AND autorec ='".mysql_real_escape_string(trim($_GET['key']))."'";
}

if(isset( $_POST['do_search'] )) {
	if( isset($_POST['search'])){
		if( $_POST['search'] != "" ) {
			$search = $_POST['search'];
			 $options .= " AND CONCAT(title,description) like '%".mysql_real_escape_string($_POST['search'])."%'";
		}
	}
	if( isset($_POST['category_id'])) {
		if( $_POST['category_id'] != 0 ) {
			$category_id = $_POST['category_id'];
			$options .= " AND category_id = '".$_POST['category_id']."'";
		}
	}
	if( isset($_POST['station'])) {
		if( $_POST['station'] != 0 ) {
			$station = $_POST['station'];
			$options .= " AND channel_id = '".$_POST['station']."'";
		}
	}
}


$options .= " ORDER BY starttime DESC";

try{
	$rvs = DBRecord::createRecords(RESERVE_TBL, $options );
	$records = array();
	foreach( $rvs as $r ) {
		$cat = new DBRecord(CATEGORY_TBL, "id", $r->category_id );
		$ch  = new DBRecord(CHANNEL_TBL,  "id", $r->channel_id );
		$arr = array();
		$arr['id'] = $r->id;
		$arr['station_name'] = $ch->name;
		$arr['starttime'] = $r->starttime;
		$arr['endtime'] = $r->endtime;
		$arr['asf'] = "".$settings->install_url."/viewer.php?reserve_id=".$r->id;
		$arr['title'] = htmlspecialchars($r->title,ENT_QUOTES);
		$arr['description'] = htmlspecialchars($r->description,ENT_QUOTES);
		$arr['thumb'] = "<img src=\"".$settings->install_url.$settings->thumbs."/".$r->path.".jpg\" />";
		$arr['cat'] = $cat->name_en;
		$arr['mode'] = $RECORD_MODE[$r->mode]['name'];
		
		array_push( $records, $arr );
	}
	
	$crecs = DBRecord::createRecords(CATEGORY_TBL );
	$cats = array();
	$cats[0]['id'] = 0;
	$cats[0]['name'] = "すべて";
	$cats[0]['selected'] = $category_id == 0 ? "selected" : "";
	foreach( $crecs as $c ) {
		$arr = array();
		$arr['id'] = $c->id;
		$arr['name'] = $c->name_jp;
		$arr['selected'] = $c->id == $category_id ? "selected" : "";
		array_push( $cats, $arr );
	}
	
	$crecs = DBRecord::createRecords(CHANNEL_TBL );
	$stations = array();
	$stations[0]['id'] = 0;
	$stations[0]['name'] = "すべて";
	$stations[0]['selected'] = (! $station) ? "selected" : "";
	foreach( $crecs as $c ) {
		$arr = array();
		$arr['id'] = $c->id;
		$arr['name'] = $c->name;
		$arr['selected'] = $station == $c->id ? "selected" : "";
		array_push( $stations, $arr );
	}
	
	
	$smarty = new Smarty();
	$smarty->assign("sitetitle","録画済一覧");
	$smarty->assign( "records", $records );
	$smarty->assign( "search", $search );
	$smarty->assign( "stations", $stations );
	$smarty->assign( "cats", $cats );
	$smarty->assign( "use_thumbs", $settings->use_thumbs );
	
	$smarty->display("recordedTable.html");
	
	
}
catch( exception $e ) {
	exit( $e->getMessage() );
}
?>