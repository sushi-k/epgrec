<?php
require_once 'config.php';
require_once INSTALL_PATH . '/Smarty/Smarty.class.php';
require_once INSTALL_PATH . '/DBRecord.class.php';
require_once INSTALL_PATH . '/Reservation.class.php';
require_once INSTALL_PATH . '/Keyword.class.php';

$weekofdays = array( "月", "火", "水", "木", "金", "土", "日", "なし" );

// 新規キーワードがポストされた
if( isset($_POST["add_keyword"]) ) {
    if( $_POST["add_keyword"] == 1 ) {
        try {
            $rec = new Keyword();
            $rec->keyword = $_POST['k_search'];
            $rec->type = $_POST['k_type'];
            $rec->category_id = $_POST['k_category'];
            $rec->channel_id = $_POST['k_station'];
            $rec->use_regexp = $_POST['k_use_regexp'];
            $rec->weekofday = $_POST['k_weekofday'];
            $rec->autorec_mode = $_POST['autorec_mode'];

            // 録画予約実行
            $rec->reservation();
        } catch( Exception $e ) {
            exit( $e->getMessage() );
        }
    }
}

try {
    $db = DB::conn();
    $keywords = $db->rows("SELECT * FROM Recorder_keywordTbl");
    foreach ($keywords as $key => $keyword) {
        $keywords[$key]['type'] = $keyword['type'] == "*" ? "すべて" : $keyword['type'];

        $keywords[$key]['channel'] = 'すべて';
        if ($keyword['channel_disc']) {
            $crec = new DBRecord(CHANNEL_TBL, "id", $keyword['channel_disc']);
            $keywords[$key]['channel'] = $crec->name;
        }

        $keywords[$key]['category'] = 'すべて';
        if ($keyword['category_disc']) {
            $crec = new DBRecord(CATEGORY_TBL, "id", $keyword['category_disc']);
            $keywords[$key]['category'] = $crec->name_jp;
        }

        $keywords[$key]['weekofday'] = $weekofdays[$keyword['weekofday']];

        $keywords[$key]['autorec_mode'] = $RECORD_MODE[(int)$keyword['autorec_mode']]['name'];
    }
} catch( Exception $e ) {
    exit( $e->getMessage() );
}

$smarty = new Smarty();
$smarty->assign('keywords', $keywords);
$smarty->assign('sitetitle', '自動録画キーワードの管理');
$smarty->display('keywordTable.html');

