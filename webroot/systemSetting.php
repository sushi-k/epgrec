<?php
require_once dirname(dirname(__FILE__) ) . "/config.php";
require_once(INSTALL_PATH."/Settings.class.php");

$settings = Settings::factory();
$smarty = new Smarty();
$smarty->template_dir = dirname(dirname(__FILE__)) . '/templates/'; 
$smarty->compile_dir = dirname(dirname(__FILE__)) . '/templates_c/'; 
$smarty->assign( "settings", $settings );
$smarty->assign( "install_path", INSTALL_PATH );
$smarty->assign( "sitetitle", "システム設定" );
$smarty->assign( "message", '<a href="index.php">設定せずに番組表に戻る</a>' );
$smarty->display("systemSetting.html");
