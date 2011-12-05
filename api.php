<?php
/**
 *
 *
 */
require_once 'config.php';

class API_Controller
{
    // 簡易予約
    public function simpleReservation()
    {
        if (!isset($_GET['program_id'])) {
            exit("Error: 番組が指定されていません");
        }

        Program::reserve($_GET['program_id']);
    }
}

// dispatch
$controller = new API_Controller();
if (in_array($_GET['method'], array('simpleReservation'))) {
    $controller->$_GET['method']();
}

