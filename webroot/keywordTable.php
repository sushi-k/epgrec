<?php
// キーワード登録、キーワード一覽
require_once dirname(dirname(__FILE__) ) . "/config.php";

// 新規キーワードがポストされた
if (isset($_POST["add_keyword"]) && $_POST['add_keyword'] == 1) {
    try {
        $record = array(
            'keyword' => $_POST['k_search'],
            'type' => $_POST['k_type'],
            'category_id' => $_POST['k_category'],
            'channel_id' => $_POST['k_station'],
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

$sql = <<<EOD
SELECT * FROM Recorder_keywordTbl
  LEFT JOIN Recorder_channelTbl ON Recorder_keywordTbl.channel_disc = Recorder_channelTbl.channel_disc
  LEFT JOIN Recorder_categoryTbl ON Recorder_keywordTbl.category_disc = Recorder_categoryTbl.category_disc
EOD;

$db = DB::conn();
$keywords = $db->rows($sql);

$smarty = new Smarty();
$smarty->template_dir = dirname(dirname(__FILE__)) . '/templates/'; 
$smarty->compile_dir = dirname(dirname(__FILE__)) . '/templates_c/'; 
$smarty->assign('keywords', $keywords);
$smarty->assign('sitetitle', '自動録画キーワードの管理');
$smarty->display('keywordTable.html');

