<?php
require_once 'config.php';

try{
    $smarty = new Smarty();
    $smarty->assign('sitetitle', '録画予約一覧');
    $smarty->assign('reservations', Reserve::getReservations());
    $smarty->display('reservationTable.html');
} catch (Exception $e) {
    exit($e->getMessage());
}
