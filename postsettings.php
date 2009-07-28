<?php
include_once("config.php");
include_once(INSTALL_PATH."/Settings.class.php");
include_once(INSTALL_PATH."/reclib.php" );

$settings = Settings::factory();
$settings->post();
$settings->save();

jdialog("設定が保存されました", "index.php" );
?>