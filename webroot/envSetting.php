<?php
require_once dirname(dirname(__FILE__) ) . "/config.php";
include_once(INSTALL_PATH."/Settings.class.php");

$settings = Settings::factory();
$smarty = new Smarty();
$smarty->template_dir = dirname(dirname(__FILE__)) . '/templates/'; 
$smarty->compile_dir = dirname(dirname(__FILE__)) . '/templates_c/'; 

$smarty->assign("settings", $settings);
$smarty->assign("record_mode", $RECORD_MODE);
$smarty->assign("sitetitle", "環境設定設定");

$smarty->display("envSetting.html");

