<?php
include_once("../config.php");
include_once("../Smarty/Smarty.class.php");
include_once("../DBRecord.class.php");
include_once("../Settings.class.php");
include_once("../reclib.php" );

$settings = Settings::factory();
$settings->post();	// いったん保存する
$settings->save();

// データベース接続チェック
$dbh = @mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );
if( $dbh == false ) {
	jdialog( "MySQLに接続できません。ホスト名/ユーザー名/パスワードを再チェックしてください", "step2.php" );
	exit();
}

$sqlstr = "use ".$settings->db_name;
$res = @mysql_query( $sqlstr );
if( $res == false ) {
	jdialog( "データベース名が異なるようです", "step2.php" );
	exit();
}

// DBテーブルの作成

try {
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
}
catch( Exception $e ) {
	jdialog("テーブルの作成に失敗しました。データベースに権限がない等の理由が考えられます。", "step2.php" );
	exit();
}

$smarty = new Smarty();
$smarty->template_dir = "../templates/";
$smarty->compile_dir = "../templates_c/";
$smarty->cache_dir = "../cache/";

$smarty->assign( "record_mode", $RECORD_MODE );
$smarty->assign( "settings", $settings );
$smarty->assign( "install_path", INSTALL_PATH );
$smarty->assign( "post_to", "step4.php" );
$smarty->assign( "sitetitle", "インストールステップ3" );
$smarty->assign( "message" , "環境設定を行います。これらの設定はデフォルトのままでも制限付きながら動作します。" );

$smarty->display("envSetting.html");
?>
