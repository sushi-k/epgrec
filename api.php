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

    public function channelInfo()
    {
        require_once( INSTALL_PATH . "/DBRecord.class.php" );
        if( isset($_GET['channel_disc']) ) {

            try {
                $crec = new DBRecord( CHANNEL_TBL, "channel_disc", $_GET['channel_disc'] );

                echo '<div class="prg_title">';
                echo $crec->name . "</div>";

                // 種別
                echo '<div class="prg_channel"><span class="labelLeft">種別：</span><span class="bold">';
                echo $crec->type;
                echo '</span></div>';

                // チャンネル
                echo '<div class="prg_channel"><span class="labelLeft">物理チャンネル：</span><span class="bold">';
                echo $crec->channel;
                echo '</span></div>';

                // フォーム
                echo '<form method="post" action="channelSetSID.php">';
                echo '<div class="prg_channel"><span class="labelLeft">サービスID：</span>';
                echo '<span><input type="text" name="n_sid" size="20" id="id_sid" value="'. $crec->sid .'" /></span>';
                echo '<input type="hidden" name="n_channel_disc" id="id_disc" value="'. $crec->channel_disc .'" />';
                echo '</div>';
                echo '</form>';
            } catch( Exception $e ) {
                echo "error:チャンネル情報の取得に失敗";
            }
        }
    }

    public function deleteKeyword()
    {
        require_once INSTALL_PATH . '/Keyword.class.php';

        if (!isset($_GET['keyword_id'])) {
            exit("Error:キーワードIDが指定されていません");
        }

        try {
            $rec = new Keyword( "id", $_GET['keyword_id']);
            $rec->delete();
        } catch(Exception $e ) {
            exit("Error:" . $e->getMessage());
        }
    }
}

// dispatch
$controller = new API_Controller();
if (in_array($_REQUEST['method'], array('simpleReservation', 'saveSettings', 'channelInfo', 'deleteKeyword'))) {
    $controller->$_REQUEST['method']();
}

