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

    public function saveSettings()
    {
        require_once INSTALL_PATH . "/Smarty/Smarty.class.php";
        require_once INSTALL_PATH."/Settings.class.php";

        $settings = Settings::factory();
        $settings->post();
        $settings->save();

        $smarty = new Smarty();
        $smarty->assign('message', '設定が保存されました');
        $smarty->assign('url', 'index.php');
        $smarty->display("dialog.html");
    }
}

// dispatch
$controller = new API_Controller();
if (in_array($_REQUEST['method'], array('simpleReservation', 'saveSettings'))) {
    $controller->$_REQUEST['method']();
}

