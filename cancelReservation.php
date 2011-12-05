<?php
/**
 * キャンセルする場合は該当レコードをcrontabから削除する必要がある。
 */
require_once 'config.php';
require_once INSTALL_PATH . '/DBRecord.class.php';
require_once INSTALL_PATH . '/Reservation.class.php';

$program_id = false;
$reserve_id = false;

if (isset($_GET['program_id'])) {
    $program_id = $_GET['program_id'];
} else if (isset($_GET['reserve_id'])) {
    $reserve_id = $_GET['reserve_id'];
}

// 予約取り消し実行
try {
    // 手動取り消しのときには、その番組を自動録画対象から外す
    if ($program_id) {
        Program::disableAutorec($program_id);
    }

    // ２つの引数のうち、falseでないものを処理するようにできているらしい
    Reservation::cancel($reserve_id, $program_id);
} catch (Exception $e) {
    echo("Error" . $e->getMessage());
    exit;
}
