<?php
require_once dirname(dirname(__FILE__) ) . "/config.php";

try{
    $smarty = new Smarty();
    $smarty->template_dir = dirname(dirname(__FILE__)) . '/templates/'; 
    $smarty->compile_dir = dirname(dirname(__FILE__)) . '/templates_c/'; 
    $smarty->assign('sitetitle', '録画予約一覧');
    $smarty->assign('reservations', Reserve::getReservations());
    $smarty->display('reservationTable.html');
} catch (Exception $e) {
    exit($e->getMessage());
}
