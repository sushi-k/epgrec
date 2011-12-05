<?php
require_once("config.php");
require_once  INSTALL_PATH . "/Smarty/Smarty.class.php";
require_once(INSTALL_PATH."/Settings.class.php");

$settings = Settings::factory();
$settings->post();
$settings->save();

$smarty = new Smarty();
$smarty->assign('message', '設定が保存されました');
$smarty->assign('url', 'index.php');
$smarty->display("dialog.html");
