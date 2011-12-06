<?php
require_once("config.php");
require_once(INSTALL_PATH."/Smarty/Smarty.class.php");
require_once(INSTALL_PATH."/Settings.class.php");

$settings = Settings::factory();
$smarty = new Smarty();
$smarty->assign( "settings", $settings );
$smarty->assign( "install_path", INSTALL_PATH );
$smarty->assign( "sitetitle", "システム設定" );
$smarty->assign( "message", '<a href="index.php">設定せずに番組表に戻る</a>' );
$smarty->display("systemSetting.html");
