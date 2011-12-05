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

// $options = "WHERE complete='1'";
$options = "WHERE starttime < '". date("Y-m-d H:i:s")."'";	// ながら再生は無理っぽい？

$row = array();
$where = '';
if (isset($_GET['key'])) {
    $where = 'autorec = ?';
    $row['autorec'] = $_GET['key'];
}

if (isset($_POST['do_search'])) {
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

$db = DB::conn();
try{
    $rows = $db->rows('SELECT * FROM Recorder_reserveTbl LEFT JOIN Recorder_programTbl ON Recorder_reserveTbl.program_disc = Recorder_programTbl.program_disc ORDER BY starttime DESC');
    //$rvs = DBRecord::createRecords(RESERVE_TBL, $options );
    $records = array();
    foreach ($rows as $key => $r) {
        $cat = new DBRecord(CATEGORY_TBL, "category_disc", $r['category_disc']);
        $ch  = new DBRecord(CHANNEL_TBL,  "channel_disc", $r['channel_disc']);
        //$rows[$key]['asf'] = "".$settings->install_url."/viewer.php?reserve_id=".$r->id;
        //$rows[$key]['thumb'] = "<img src=\"".$settings->install_url.$settings->thumbs."/".$r->path.".jpg\" />";
        //$rows[$key]['cat'] = $cat->name_en;
    }

    $categories = $db->rows('SELECT * FROM Recorder_categoryTbl');
    $channels = $db->rows('SELECT * FROM Recorder_channelTbl');

    $smarty = new Smarty();
    $smarty->assign("sitetitle","録画済一覧");
    $smarty->assign("records", $rows);
    $smarty->assign("search", $search);
    $smarty->assign("channels", $channels);
    $smarty->assign("categories", $categories);
    $smarty->assign("use_thumbs", $settings->use_thumbs );

    $smarty->display("recordedTable.html");
} catch (exception $e) {
    throw $e;
}
