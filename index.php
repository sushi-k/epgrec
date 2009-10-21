<?php

include_once("config.php");
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/Smarty/Smarty.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );
include_once( INSTALL_PATH . "/Settings.class.php" );

// 設定ファイルの有無を検査する
if( ! file_exists( INSTALL_PATH."/settings/config.xml") ) {
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
 for( $i = 0 ; $i < $program_length; $i++ ) {
	$tvtimes[$i] = date("H", $top_time + 3600 * $i );
 }
 
 
 // 番組表
 $programs = array();
 if( $type == "BS" ) $channel_map = $BS_CHANNEL_MAP;
 else if( $type == "GR" ) $channel_map = $GR_CHANNEL_MAP;
 else if( $type == "CS" ) $channel_map = $CS_CHANNEL_MAP;
 $st = 0;
 $prec = new DBRecord(PROGRAM_TBL);
 foreach( $channel_map as $channel_disc => $channel ) {
	$prev_end = $top_time;
 	try {
		$crec = new DBRecord( CHANNEL_TBL, "channel_disc", $channel_disc );
		$programs[$st]["station_name"]  = $crec->name;
		
		$reca = $prec->fetch_array( "channel_disc", $channel_disc,
		                                  "endtime > '".toDatetime($top_time)."' ".
		                                  "AND starttime < '". toDatetime($last_time)."' ".
		                                  "ORDER BY starttime ASC "
		                               );
		$programs[$st]['list'] = array();
		$num = 0;
		foreach( $reca as $prg ) {
			// 前プログラムとの空きを調べる
			$start = toTimestamp( $prg['starttime'] );
			if( ($start - $prev_end) > 0 ) {
				$height = ($start-$prev_end) * $settings->height_per_hour / 3600;
				$height = $height;
				$programs[$st]['list'][$num]['category_none'] = "none";
				$programs[$st]['list'][$num]['height'] = $height;
				$programs[$st]['list'][$num]['title'] = "";
				$programs[$st]['list'][$num]['starttime'] = "";
				$programs[$st]['list'][$num]['description'] = "";
				$num++;
			}
			$prev_end = toTimestamp( $prg['endtime'] );
			
			$height = ((toTimestamp($prg['endtime']) - toTimestamp($prg['starttime'])) * $settings->height_per_hour / 3600);
			// $top_time より早く始まっている番組
			if( toTimestamp($prg['starttime']) <$top_time ) {
				$height = ((toTimestamp($prg['endtime']) - $top_time ) * $settings->height_per_hour / 3600);
			}
			// $last_time より遅く終わる番組
			if( toTimestamp($prg['endtime']) > $last_time ) {
				$height = (($last_time - toTimestamp($prg['starttime'])) * $settings->height_per_hour / 3600);
			}
			
			// プログラムを埋める
			$cat = new DBRecord( CATEGORY_TBL, "id", $prg['category_id'] );
			$programs[$st]['list'][$num]['category_name'] = $cat->name_en;
			$programs[$st]['list'][$num]['height'] = $height;
			$programs[$st]['list'][$num]['title'] = $prg['title'];
			$programs[$st]['list'][$num]['starttime'] = date("H:i", $start )."" ;
			$programs[$st]['list'][$num]['description'] = $prg['description'];
			$programs[$st]['list'][$num]['prg_start'] = str_replace( "-", "/", $prg['starttime']);
			$programs[$st]['list'][$num]['duration'] = "" . (toTimestamp($prg['endtime']) - toTimestamp($prg['starttime']));
			$programs[$st]['list'][$num]['channel'] = ($prg['type'] == "GR" ? "地上D" : "BS" ) . ":". $prg['channel'] . "ch";
			$programs[$st]['list'][$num]['id'] = "" . ($prg['id']);
			$programs[$st]['list'][$num]['rec'] = DBRecord::countRecords(RESERVE_TBL, "WHERE complete = '0' AND program_id = '".$prg['id']."'" );
			$num++;
		}
	}
	 catch( exception $e ) {
		exit( $e->getMessage() );
 	}
 	// 空きを埋める
	if( ($last_time - $prev_end) > 0 ) {
		$height = ($last_time - $prev_end) * $settings->height_per_hour / 3600;
		$height = $height;
		$programs[$st]['list'][$num]['category_name'] = "none";
		$programs[$st]['list'][$num]['height'] = $height;
		$programs[$st]['list'][$num]['title'] = "";
		$programs[$st]['list'][$num]['starttime'] = "";
		$programs[$st]['list'][$num]['description'] = "";
		$num++;
 	}
	$st++;
 }
 $prec = null;
 
 // 局の幅
 $ch_set_width = $settings->ch_set_width;
 // 全体の幅
 $chs_width = $ch_set_width * count( $channel_map );
 
 // GETパラメタ
  $get_param = $_SERVER['SCRIPT_NAME'] . "?type=".$type."&length=".$program_length."";
 
 $smarty = new Smarty();
 
 // カテゴリ一覧
 $crec = DBRecord::createRecords( CATEGORY_TBL );
 $cats = array();
 $num = 0;
 foreach( $crec as $val ) {
	$cats[$num]['name_en'] = $val->name_en;
	$cats[$num]['name_jp'] = $val->name_jp;
	$num++;
 }
 $smarty->assign( "cats", $cats );
 


 // タイプ選択
 $types = array();
 $i = 0;
 if( $settings->bs_tuners != 0 ) {
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
 if( $settings->gr_tuners != 0 ) {
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

// date("Y-m-d H:i:s", $timestamp);
 
 $sitetitle = date( "Y", $top_time ) . "年" . date( "m", $top_time ) . "月" . date( "d", $top_time ) . "日". date( "H", $top_time ) .
              "時～".( $type == "GR" ? "地上デジタル" : "BSデジタル" )."番組表";
 
 $smarty->assign("sitetitle", $sitetitle );
 
 $smarty->assign("top_time", str_replace( "-", "/" ,toDatetime($top_time)) );
 $smarty->assign("last_time", str_replace( "-", "/" ,toDatetime($last_time)) );
 
 
 $smarty->display("index.html");
?>