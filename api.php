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

    public function reservationForm()
    {
        require_once INSTALL_PATH . '/DBRecord.class.php';
        require_once INSTALL_PATH . '/Smarty/Smarty.class.php';

        if (!isset($_GET['program_id'])) {
           exit('Error: 番組IDが指定されていません');
        }
        $program_id = $_GET['program_id'];

        try {
            $prec = new DBRecord(PROGRAM_TBL, "id", $program_id);

            sscanf($prec->starttime, "%4d-%2d-%2d %2d:%2d:%2d", $syear, $smonth, $sday, $shour, $smin, $ssec);
            sscanf($prec->endtime, "%4d-%2d-%2d %2d:%2d:%2d", $eyear, $emonth, $eday, $ehour, $emin, $esec);

            $crecs = DBRecord::createRecords(CATEGORY_TBL);
            $cats = array();
            foreach ($crecs as $crec) {
                $cat = array();
                $cat['id'] = $crec->id;
                $cat['name'] = $crec->name_jp;
                $cat['selected'] = $prec->category_id == $cat['id'] ? "selected" : "";
                array_push($cats, $cat);
            }

            $smarty = new Smarty();
            $smarty->assign( "syear", $syear );
            $smarty->assign( "smonth", $smonth );
            $smarty->assign( "sday", $sday );
            $smarty->assign( "shour", $shour );
            $smarty->assign( "smin" ,$smin );
            $smarty->assign( "eyear", $eyear );
            $smarty->assign( "emonth", $emonth );
            $smarty->assign( "eday", $eday );
            $smarty->assign( "ehour", $ehour );
            $smarty->assign( "emin" ,$emin );
            $smarty->assign( "type", $prec->type );
            $smarty->assign( "channel", $prec->channel );
            $smarty->assign( "channel_id", $prec->channel_id );
            $smarty->assign( "record_mode" , $RECORD_MODE );
            $smarty->assign( "title", $prec->title );
            $smarty->assign( "description", $prec->description );
            $smarty->assign( "cats" , $cats );
            $smarty->assign( "program_id", $prec->id );
            $smarty->display("reservationform.html");
        } catch (Exception $e) {
            exit("Error:". $e->getMessage());
        }
    }
}

// dispatch
$controller = new API_Controller();
if (in_array($_REQUEST['method'], array('simpleReservation', 'saveSettings', 'channelInfo', 'deleteKeyword'))) {
    $controller->$_REQUEST['method']();
}

