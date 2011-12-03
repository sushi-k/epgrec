#!/usr/bin/php
<?php
// xmlファイルをパースして番組情報を更新する
require_once('config.php');
require_once( INSTALL_PATH . '/DBRecord.class.php' );
require_once( INSTALL_PATH . '/Reservation.class.php' );
require_once( INSTALL_PATH . '/Keyword.class.php' );
  
$type = $argv[1];	// BS CS GR
$xml = $argv[2];	// XMLファイル

//$type = 'GR';
//$xml = '/tmp/__temp_GR23.xml';

if (file_exists($xml)) {
    RegisterService::storeProgram($type, $xml);
    RegisterService::cleanup();
    // unlink($xml);
}
