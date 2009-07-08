<?php
include_once('config.php');
include_once( INSTALL_PATH . '/DBRecord.class.php' );
include_once( INSTALL_PATH . '/Smarty/Smarty.class.php' );

$options = " WHERE starttime > '".date("Y-m-d H:i:s", time() + 300 )."'";

$search = "";
$use_regexp = 0;
$type = "*";
$category_id = 0;
$station = 0;


if(isset( $_POST['do_search'] )) {
	if( isset($_POST['search'])){
		if( $_POST['search'] != "" ) {
			$search = $_POST['search'];
			if( isset($_POST['use_regexp']) && ($_POST['use_regexp']) ) {
				$use_regexp = $_POST['use_regexp'];
				$options .= " AND CONCAT(title,description) REGEXP '".mysql_real_escape_string($search)."'";
			}
			else {
				$options .= " AND CONCAT(title,description) like '%".mysql_real_escape_string($search)."%'";
			}
		}
	}
	if( isset($_POST['type'])){
		if( $_POST['type'] != "*" ) {
			$type = $_POST['type'];
			$options .= " AND type = '".$_POST['type']."'";
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

$options .= " ORDER BY starttime ASC LIMIT 300";

$do_keyword = 0;
if( ($search != "") || ($type != "*") || ($category_id != 0) || ($station != 0) )
	$do_keyword = 1;

try{
	$precs = DBRecord::createRecords(TBL_PREFIX.PROGRAM_TBL, $options );
	
	$programs = array();
	foreach( $precs as $p ) {
		$ch  = new DBRecord(TBL_PREFIX.CHANNEL_TBL, "id", $p->channel_id );
		$cat = new DBRecord(TBL_PREFIX.CATEGORY_TBL, "id", $p->category_id );
		$arr = array();
		$arr['type'] = $p->type;
		$arr['station_name'] = $ch->name;
		$arr['starttime'] = $p->starttime;
		$arr['endtime'] = $p->endtime;
		$arr['title'] = $p->title;
		$arr['description'] = $p->description;
		$arr['id'] = $p->id;
		$arr['cat'] = $cat->name_en;
		$arr['rec'] = DBRecord::countRecords(TBL_PREFIX.RESERVE_TBL, "WHERE program_id='".$p->id."'");
		
		array_push( $programs, $arr );
	}
	
	$k_category_name = "";
	$crecs = DBRecord::createRecords(TBL_PREFIX.CATEGORY_TBL );
	$cats = array();
	$cats[0]['id'] = 0;
	$cats[0]['name'] = "すべて";
	$cats[0]['selected'] = $category_id == 0 ? "selected" : "";
	foreach( $crecs as $c ) {
		$arr = array();
		$arr['id'] = $c->id;
		$arr['name'] = $c->name_jp;
		$arr['selected'] = $c->id == $category_id ? "selected" : "";
		if( $c->id == $category_id ) $k_category_name = $c->name_jp;
		array_push( $cats, $arr );
	}
	
	$types = array();
	$types[0]['name'] = "すべて";
	$types[0]['value'] = "*";
	$types[0]['selected'] = $type == "*" ? "selected" : "";
	if( GR_TUNERS ) {
		$arr = array();
		$arr['name'] = "GR";
		$arr['value'] = "GR";
		$arr['selected'] = $type == "GR" ? "selected" : "";
		array_push( $types, $arr );
	}
	if( BS_TUNERS ) {
		$arr = array();
		$arr['name'] = "BS";
		$arr['value'] = "BS";
		$arr['selected'] = $type == "BS" ? "selected" : "";
		array_push( $types, $arr );
	}
	
	$k_station_name = "";
	$crecs = DBRecord::createRecords(TBL_PREFIX.CHANNEL_TBL );
	$stations = array();
	$stations[0]['id'] = 0;
	$stations[0]['name'] = "すべて";
	$stations[0]['selected'] = (! $station) ? "selected" : "";
	foreach( $crecs as $c ) {
		$arr = array();
		$arr['id'] = $c->id;
		$arr['name'] = $c->name;
		$arr['selected'] = $station == $c->id ? "selected" : "";
		if( $station == $c->id ) $k_station_name = $c->name;
		array_push( $stations, $arr );
	}

	$smarty = new Smarty();
	$smarty->assign("sitetitle","番組検索");
	$smarty->assign("do_keyword", $do_keyword );
	$smarty->assign( "programs", $programs );
	$smarty->assign( "cats", $cats );
	$smarty->assign( "k_category", $category_id );
	$smarty->assign( "k_category_name", $k_category_name );
	$smarty->assign( "types", $types );
	$smarty->assign( "k_type", $type );
	$smarty->assign( "search" , $search );
	$smarty->assign( "use_regexp", $use_regexp );
	$smarty->assign( "stations", $stations );
	$smarty->assign( "k_station", $station );
	$smarty->assign( "k_station_name", $k_station_name );
	$smarty->display("programTable.html");
}
catch( exception $e ) {
	exit( $e->getMessage() );
}
?>