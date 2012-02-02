#!/usr/bin/php
<?php
// 録画が完了したことを知らせるフラグ
// @TODO gen-thumbnailを実行する

require_once "config.php";
require_once INSTALL_PATH . "/DBRecord.class.php";

$settings = Settings::factory();

$reserve_id = $argv[1];

try {
    $rrec = new DBRecord( RESERVE_TBL, "id" , $reserve_id );
    if ( file_exists( INSTALL_PATH .$settings->spool . "/". $rrec->path ) ) {
        // 予約完了
        $rrec->complete = '1';
    } else {
        // 予約失敗
        $rrec->delete();
    }
} catch (Exception $e) {
    exit($e->getMessage());
}
