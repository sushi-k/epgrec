<?php
include_once("config.php");
include_once(INSTALL_PATH."/Smarty/Smarty.class.php");
include_once(INSTALL_PATH."/Settings.class.php");

$settings = Settings::factory();
$smarty = new Smarty();

$smarty->assign( "settings", $settings );
$smarty->assign( "record_mode", $RECORD_MODE );
$smarty->assign( "install_path", INSTALL_PATH );
$smarty->assign( "sitetitle", "環境設定設定" );
$smarty->assign( "message", '<a href="index.php">設定せずに番組表に戻る</a>/<a href="systemSetting.php">システム設定へ</a>' );

$smarty->display("envSetting.html");
?>
