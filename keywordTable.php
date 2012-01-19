<?php
require_once 'config.php';
require_once INSTALL_PATH . '/Smarty/Smarty.class.php';
require_once INSTALL_PATH . '/DBRecord.class.php';

$weekofdays = array( "月", "火", "水", "木", "金", "土", "日", "なし" );

// 新規キーワードがポストされた
if (isset($_POST["add_keyword"]) && $_POST['add_keyword'] == 1) {
    try {
        $record = array(
            'keyword' => $_POST['k_search'],
            'type' => $_POST['k_type'],
            'category' => $_POST['k_category'],
            'channel' => $_POST['k_station'],
            'use_regexp' => $_POST['k_use_regexp'],
            'weekofday' => $_POST['k_weekofday'],
            'autorec_mode' => $_POST['autorec_mode'],
        );
        Keyword::add($record);

        // @TODO 追加したキーワードを元に予約処理を走らせる
    } catch (Exception $e) {
        exit($e->getMessage());
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

