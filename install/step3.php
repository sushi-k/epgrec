<?php
require_once("../config.php");
require_once("../Smarty/Smarty.class.php");
require_once("../DBRecord.class.php");
require_once("../Settings.class.php");

$settings = Settings::factory();
$settings->post();	// いったん保存する
$settings->save();

$smarty = new Smarty();
$smarty->template_dir = "../templates/";
$smarty->compile_dir = "../templates_c/";
$smarty->cache_dir = "../cache/";

try {
    // データベース接続チェック
    $dbh = @mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );
    if ($dbh == false) {
        throw new RuntimeException('MySQLに接続できません。ホスト名/ユーザー名/パスワードを再チェックしてください');
    }

    $res = @mysql_query("USE {$settings->db_name}");
    if ($res == false) {
        throw new RuntimeException('データベース名が異なるようです');
    }

    $rec = new DBRecord( RESERVE_TBL );
    $rec->createTable( RESERVE_STRUCT );

    $rec = new DBRecord( PROGRAM_TBL );
    $rec->createTable( PROGRAM_STRUCT );

    $rec = new DBRecord( CHANNEL_TBL );
    $rec->createTable( CHANNEL_STRUCT );

    $rec = new DBRecord( CATEGORY_TBL );
    $rec->createTable( CATEGORY_STRUCT );

    $rec = new DBRecord( KEYWORD_TBL );
    $rec->createTable( KEYWORD_STRUCT );
} catch (Exception $e) {
    $smarty->assign('message', $e->getMessage());
    $smarty->assign('url', 'step2.php');
    $smarty->display("dialog.html");
    exit;
}

$smarty->assign( "record_mode", $RECORD_MODE );
$smarty->assign( "settings", $settings );
$smarty->assign( "install_path", INSTALL_PATH );
$smarty->assign( "post_to", "step4.php" );
$smarty->assign( "sitetitle", "インストールステップ3" );
$smarty->assign( "message" , "環境設定を行います。これらの設定はデフォルトのままでも制限付きながら動作します。" );

$smarty->display("envSetting.html");

