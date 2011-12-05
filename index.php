<?php
require_once "config.php";
require_once  INSTALL_PATH . "/Smarty/Smarty.class.php";
require_once  INSTALL_PATH . "/Settings.class.php";

// 設定ファイルの有無を検査する
if (!file_exists(INSTALL_PATH . "/settings/config.xml")) {
    header( "Content-Type: text/html;charset=utf-8" );
    exit( "<script type=\"text/javascript\">\n" .
          "<!--\n".
         "window.open(\"install/step1.php\",\"_self\");".
         "// -->\n</script>" );
}

$settings = Settings::factory();

$DAY_OF_WEEK = array( "(日)","(月)","(火)","(水)","(木)","(金)","(土)" );

// パラメータの処理
// 表示する長さ（時間）
$program_length = $settings->program_length;
if( isset( $_GET['length']) ) $program_length = (int) $_GET['length'];
// 地上=GR/BS=BS
$type = "GR";
if( isset( $_GET['type'] ) ) $type = $_GET['type'];
// 現在の時間
$top_time = mktime( date("H"), 0 , 0 );
if( isset( $_GET['time'] ) ) {
	if( sscanf( $_GET['time'] , "%04d%2d%2d%2d", $y, $mon, $day, $h ) == 4 ) {
		$tmp_time = mktime( $h, 0, 0, $mon, $day, $y );
		if( ($tmp_time < ($top_time + 3600 * 24 * 8)) && ($tmp_time > ($top_time - 3600 * 24 * 8)) )
			$top_time = $tmp_time;
	}
}
$last_time = $top_time + 3600 * $program_length;

// 時刻欄
for ($i = 0; $i < $program_length; $i++) {
  $tvtimes[$i] = date("H", $top_time + 3600 * $i);
}
 
// 番組表
if ($type == "BS") {
    $channel_map = $BS_CHANNEL_MAP;
} else if ($type == "GR") {
    $channel_map = ChannelMaster::$GR;
} else if ($type == "CS") {
    $channel_map = $CS_CHANNEL_MAP;
}

$db = DB::conn();
$st = 0;
$programs = array();
foreach ($channel_map as $channel_disc => $channel) {
    $prev_end = $top_time;
    try {
        $channel = $db->row("SELECT * FROM Recorder_channelTbl WHERE channel_disc = ?", array($channel_disc));
        $items = $db->rows('SELECT * FROM Recorder_programTbl WHERE channel_disc = ? AND endtime > ? AND starttime < ? ORDER BY starttime ASC', array($channel_disc, date('Y-m-d H:i:s', $top_time), date('Y-m-d H:i:s', $last_time)));
        $programs[$st]["station_name"]  = $channel['name'];
        $programs[$st]["channel_disc"]  = $channel['channel_disc'];
        $programs[$st]['list'] = array();
        $num = 0;
        foreach ($items as $program) {
            // 前プログラムとの空きを調べる
            $start = strtotime($program['starttime']);
            if (($start - $prev_end) > 0) {
                $height = ($start-$prev_end) * $settings->height_per_hour / 3600;
                $programs[$st]['list'][$num]['category_none'] = "none";
                $programs[$st]['list'][$num]['height'] = $height;
                $programs[$st]['list'][$num]['title'] = "";
                $programs[$st]['list'][$num]['starttime'] = "";
                $programs[$st]['list'][$num]['description'] = "";
                $num++;
            }
            $prev_end = strtotime( $program['endtime'] );

            $height = ((strtotime($program['endtime']) - strtotime($program['starttime'])) * $settings->height_per_hour / 3600);
            // $top_time より早く始まっている番組
            if (strtotime($program['starttime']) < $top_time) {
                $height = ((strtotime($program['endtime']) - $top_time ) * $settings->height_per_hour / 3600);
            }
            // $last_time より遅く終わる番組
            if (strtotime($program['endtime']) > $last_time) {
                $height = (($last_time - strtotime($program['starttime'])) * $settings->height_per_hour / 3600);
            }

            // プログラムを埋める
            $category = $db->row('SELECT * FROM Recorder_categoryTbl WHERE category_disc = ?', array($program['category_disc']));
            if ($category === false) {
                $category_name = 'none';
            } else {
                $category_name = $category['name_en'];
            }
            $programs[$st]['list'][$num]['category_name'] = $category_name;
            $programs[$st]['list'][$num]['program_disc'] = $program['program_disc'];
            $programs[$st]['list'][$num]['height'] = $height;
            $programs[$st]['list'][$num]['title'] = $program['title'];
            $programs[$st]['list'][$num]['starttime'] = date("H:i", $start )."" ;
            $programs[$st]['list'][$num]['description'] = $program['description'];
            $programs[$st]['list'][$num]['prg_start'] = str_replace( "-", "/", $program['starttime']);
            $programs[$st]['list'][$num]['duration'] = "" . (strtotime($program['endtime']) - strtotime($program['starttime']));
            $programs[$st]['list'][$num]['channel'] = ($program['type'] == "GR" ? "地上D" : "BS" ) . ":". $program['channel'] . "ch";
            if (Reserve::isReserved($program['program_disc'])) {
                $programs[$st]['list'][$num]['rec'] = 1;
            } else {
                $programs[$st]['list'][$num]['rec'] = 0;
            }
            $num++;
        }
    } catch( exception $e ) {
        throw $e;
    }

    // 空きを埋める
    if (($last_time - $prev_end) > 0) {
        $height = ($last_time - $prev_end) * $settings->height_per_hour / 3600;
        $programs[$st]['list'][$num]['category_name'] = "none";
        $programs[$st]['list'][$num]['height'] = $height;
        $programs[$st]['list'][$num]['title'] = "";
        $programs[$st]['list'][$num]['starttime'] = "";
        $programs[$st]['list'][$num]['description'] = "";
        $num++;
    }
    $st++;
}

// 局の幅
$ch_set_width = $settings->ch_set_width;
// 全体の幅
$chs_width = $ch_set_width * count($channel_map);
 
// GETパラメタ
$get_param = $_SERVER['SCRIPT_NAME'] . "?type=".$type."&length=".$program_length."";
 
$smarty = new Smarty();
 
// カテゴリ一覧
$db = DB::conn();
$smarty->assign("cats", $db->rows("SELECT * FROM Recorder_categoryTbl"));
 
// タイプ選択
$types = array();
$i = 0;
if ($settings->bs_tuners != 0) {
    $types[$i]['selected'] = $type == "BS" ? 'class="selected"' : "";
    $types[$i]['link'] = $_SERVER['SCRIPT_NAME'] . "?type=BS&length=".$program_length."&time=".date( "YmdH", $top_time);
    $types[$i]['name'] = "BS";
    $i++;

    // CS
    if ($settings->cs_rec_flg != 0) {
        $types[$i]['selected'] = $type == "CS" ? 'class="selected"' : "";
        $types[$i]['link'] = $_SERVER['SCRIPT_NAME'] . "?type=CS&length=".$program_length."&time=".date( "YmdH", $top_time);
        $types[$i]['name'] = "CS";
        $i++;
    }
}

if ($settings->gr_tuners != 0) {
    $types[$i]['selected'] = $type == "GR" ? 'class="selected"' : "";
    $types[$i]['link'] = $_SERVER['SCRIPT_NAME'] . "?type=GR&length=".$program_length."&time=".date( "YmdH", $top_time);
    $types[$i]['name'] = "地上デジタル";
    $i++;
}
$smarty->assign( "types", $types );
 
 // 日付選択
 $days = array();
 $day = array();
 $day['d'] = "昨日";
 $day['link'] = $get_param . "&time=". date( "YmdH", time() - 3600 *24 );
 $day['ofweek'] = "";
 $day['selected'] = $top_time < mktime( 0, 0 , 0) ? 'class="selected"' : '';
 
 array_push( $days , $day );
 $day['d'] = "現在";
 $day['link'] = $get_param;
 $day['ofweek'] = "";
 $day['selected'] = "";
 array_push( $days, $day );
 for( $i = 0 ; $i < 8 ; $i++ ) {
	$day['d'] = "".date("d", time() + 24 * 3600 * $i ) . "日";
	$day['link'] = $get_param . "&time=".date( "Ymd", time() + 24 * 3600 * $i) . date("H" , $top_time );
	$day['ofweek'] = $DAY_OF_WEEK[(int)date( "w", time() + 24 * 3600 * $i )];
	$day['selected'] = date("d", $top_time) == date("d", time() + 24 * 3600 * $i ) ? 'class="selected"' : '';
	array_push( $days, $day );
 }
 $smarty->assign( "days" , $days );
 
 // 時間選択
 $toptimes = array();
 for( $i = 0 ; $i < 24; $i+=4 ) {
	$tmp = array();
	$tmp['hour'] = sprintf( "%02d:00", $i );
	$tmp['link'] = $get_param . "&time=".date("Ymd", $top_time ) . sprintf("%02d", $i );
	array_push( $toptimes, $tmp );
 }
$smarty->assign( "toptimes" , $toptimes );
$smarty->assign( "tvtimes", $tvtimes );
$smarty->assign( "programs", $programs );
$smarty->assign( "ch_set_width", $settings->ch_set_width );
$smarty->assign( "chs_width", $chs_width );
$smarty->assign( "height_per_hour", $settings->height_per_hour );
$smarty->assign( "height_per_min", $settings->height_per_hour / 60 );
$sitetitle = date( "Y", $top_time ) . "年" . date( "m", $top_time ) . "月" . date( "d", $top_time ) . "日". date( "H", $top_time ) .
              "時～".( $type == "GR" ? "地上デジタル" : "BSデジタル" )."番組表";
$smarty->assign("sitetitle", $sitetitle );
$smarty->assign("top_time", str_replace( "-", "/" ,date('Y-m-d H:i:s', $top_time)) );
$smarty->assign("last_time", str_replace( "-", "/" ,date('Y-m-d H:i:s', $last_time)) );
$smarty->display("index.html");

