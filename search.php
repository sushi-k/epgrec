<?php
require_once 'config.php';
require_once INSTALL_PATH . '/DBRecord.class.php';
require_once INSTALL_PATH . '/Smarty/Smarty.class.php';
require_once INSTALL_PATH . '/Settings.class.php';

$settings = Settings::factory();

$options = " WHERE starttime > '".date("Y-m-d H:i:s", time() + 300 )."'";

// 曜日
$weekofdays = array(
    array( "name" => "月", "id" => 0, "selected" => "" ),
    array( "name" => "火", "id" => 1, "selected" => "" ),
    array( "name" => "水", "id" => 2, "selected" => "" ),
    array( "name" => "木", "id" => 3, "selected" => "" ),
    array( "name" => "金", "id" => 4, "selected" => "" ),
    array( "name" => "土", "id" => 5, "selected" => "" ),
    array( "name" => "日", "id" => 6, "selected" => "" ),
    array( "name" => "なし", "id" => 7, "selected" => "" ),
);

$autorec_modes = $RECORD_MODE;
$autorec_modes[(int)($settings->autorec_mode)]['selected'] = "selected";

$weekofday = 7;
$search = "";
$use_regexp = 0;
$type = "*";
$category_id = 0;
$station = 0;

// mysql_real_escape_stringより先に接続しておく必要がある
$dbh = @mysql_connect($settings->db_host, $settings->db_user, $settings->db_pass );

// パラメータの処理
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
    if( isset($_POST['weekofday']) ) {
        $weekofday = $_POST['weekofday'];
        if( $weekofday != 7 ) {
            $options .= " AND WEEKDAY(starttime) = '".$weekofday."'";
        }
    }
}
$options .= " ORDER BY starttime ASC LIMIT 300";
$do_keyword = 0;
if( ($search != "") || ($type != "*") || ($category_id != 0) || ($station != 0) ) {
    $do_keyword = 1;
}

try {
    $db = DB::conn();
    $programs = $db->rows("SELECT * FROM Recorder_programTbl LEFT JOIN Recorder_categoryTbl ON Recorder_programTbl.category_disc = Recorder_categoryTbl.category_disc {$options}");
    foreach ($programs as $key => $program) {
        $channel = Channel::get($program['channel_disc']);
        $category = new DBRecord(CATEGORY_TBL, "category_disc", $program['category_disc']);
        $programs[$key]['station_name'] = $channel->name;
        $programs[$key]['cat'] = $program['name_en'];
        //$programs[$key]['rec'] = DBRecord::countRecords(RESERVE_TBL, "WHERE program_id='".$p->id."'");
        $programs[$key]['rec'] = 0;
    }

    $k_category_name = "";
    $crecs = DBRecord::createRecords(CATEGORY_TBL);
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
    if( $settings->gr_tuners != 0 ) {
        $arr = array();
        $arr['name'] = "GR";
        $arr['value'] = "GR";
        $arr['selected'] = $type == "GR" ? "selected" : "";
        array_push( $types, $arr );
    }
    if( $settings->bs_tuners != 0 ) {
        $arr = array();
        $arr['name'] = "BS";
        $arr['value'] = "BS";
        $arr['selected'] = $type == "BS" ? "selected" : "";
        array_push( $types, $arr );

        // CS
        if ($settings->cs_rec_flg != 0) {
            $arr = array();
            $arr['name'] = "CS";
            $arr['value'] = "CS";
            $arr['selected'] = $type == "CS" ? "selected" : "";
            array_push( $types, $arr );
        }
    }

    $k_station_name = "";
    $crecs = DBRecord::createRecords(CHANNEL_TBL);
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
    $weekofdays["$weekofday"]["selected"] = "selected" ;
} catch( exception $e ) {
    throw $e;
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
$smarty->assign( "weekofday", $weekofday );
$smarty->assign( "k_weekofday", $weekofdays["$weekofday"]["name"] );
$smarty->assign( "weekofday", $weekofday );
$smarty->assign( "weekofdays", $weekofdays );
$smarty->assign( "autorec_modes", $autorec_modes );
$smarty->display("programTable.html");

