<?php
/**
 *
 *
 */
require_once dirname(dirname(__FILE__) ) . "/config.php";

class API_Controller
{
    // 簡易予約
    public function simpleReservation()
    {
        if (!isset($_GET['program_id']) || $_GET['program_id'] == '') {
            exit('Error: 番組idが指定されていません');
        }

        $result = Reserve::simpleReserve($_GET['program_id']);
        if ($result === false) {
            exit('Error: 指定された番組idは存在しません');
        }
    }

    // reserve_id,title,descriptionを受け取り更新する
    public function editReservation()
    {
        if (!isset($_POST['reserve_id'])) {
            exit('Error: 予約idが指定されていません');
        }

        $reserve_id = $_POST['reserve_id'];
        $program = Program::get($reserve_id); 
        if ($program === false) {
            exit('Error: 指定された番組idは存在しません');
        }

        $program->update(
            array('title' => $_POST['title'], 'description' => $_POST['description'])
        ); 
    }

    public function saveSettings()
    {
        require_once INSTALL_PATH."/Settings.class.php";

        $settings = Settings::factory();
        $settings->post();
        $settings->save();

        $smarty = new Smarty();
        $smarty->template_dir = dirname(dirname(__FILE__)) . '/templates/'; 
        $smarty->compile_dir = dirname(dirname(__FILE__)) . '/templates_c/'; 
        $smarty->assign('message', '設定が保存されました');
        $smarty->assign('url', 'index.php');
        $smarty->display("dialog.html");
    }

    public function channelInfo()
    {
        if (isset($_GET['channel_disc'])) {

            try {
                $channel = Channel::get($_GET['channel_disc']);

                $view = <<<EOD
<div class="prg_rec_cfg ui-corner-all">
<div class="prg_title">{$channel->name}</div>

<table>
<tr>
  <td>種別</td><td>{$channel->type}</td>
</tr>
<tr>
  <td>物理チャンネル</td><td>{$channel->channel}</td>
</tr>
<tr>
  <td>サービスID</td>
  <td>
<form method="post" action="channelSetSID.php">
<input type="text" name="n_sid" size="20" id="id_sid" value="{$channel->sid}" />
<input type="hidden" name="n_channel_disc" id="id_disc" value="{$channel->channel_disc}" />
</form>
  </td>
</tr>
</table>
</div>
EOD;
                echo $view;
            } catch (Exception $e) {
                echo 'Error:チャンネル情報の取得に失敗';
            }
        }
    }

    public function deleteKeyword()
    {
        if (!isset($_GET['keyword_id'])) {
            exit("Error:キーワードIDが指定されていません");
        }

        try {
            Keyword::delete((int)$_GET['keyword_id']);
        } catch (Exception $e) {
            exit("Error:" . $e->getMessage());
        }
    }

    public function reservationForm()
    {
        require_once INSTALL_PATH . '/DBRecord.class.php';

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
            $smarty->template_dir = dirname(dirname(__FILE__)) . '/templates/'; 
            $smarty->compile_dir = dirname(dirname(__FILE__)) . '/templates_c/'; 
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
if (in_array($_REQUEST['method'], array('simpleReservation', 'saveSettings', 'channelInfo', 'deleteKeyword', 'editReservation'))) {
    $controller->$_REQUEST['method']();
}

