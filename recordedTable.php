<?php
require_once 'config.php';
require_once INSTALL_PATH . '/Smarty/Smarty.class.php';
require_once INSTALL_PATH . '/Settings.class.php';

$settings = Settings::factory();

$order = "";
$search = "";
$category_id = 0;
$station = 0;

// $options = "WHERE complete='1'";
$options = "WHERE starttime < '". date("Y-m-d H:i:s")."'";  // ながら再生は無理っぽい？

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

try {
    $db = DB::conn();
    $channels = $db->rows('SELECT * FROM Recorder_channelTbl');

    $smarty = new Smarty();
    $smarty->assign('sitetitle','録画済一覧');
    $smarty->assign('records', Reserve::getRecordedItems($options));
    $smarty->assign('search', $search);
    $smarty->assign('channels', $channels);
    $smarty->assign('categories', Category::getAll());
    $smarty->assign('use_thumbs', $settings->use_thumbs );

    $smarty->display("recordedTable.html");
} catch (exception $e) {
    throw $e;
}
