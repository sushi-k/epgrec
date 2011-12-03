#!/usr/bin/php
<?php
// xmlファイルをパースして番組情報を更新する
// キーワードを指定して自動録画する機能は未実装
require_once 'config.php';
  
$type = $argv[1];	// BS CS GR
$xml = $argv[2];	// XMLファイル

//$type = 'GR';
//$xml = '/tmp/__temp_GR23.xml';

if (file_exists($xml)) {
    RegisterService::storeProgram($type, $xml);
    RegisterService::cleanup();
    // unlink($xml);
}
