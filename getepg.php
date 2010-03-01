#!/usr/bin/php
<?php
  include_once('config.php');
  include_once( INSTALL_PATH . '/DBRecord.class.php' );
  include_once( INSTALL_PATH . '/Reservation.class.php' );
  include_once( INSTALL_PATH . '/Keyword.class.php' );
  include_once( INSTALL_PATH . '/Settings.class.php' );
  
  // 後方互換性
  if( ! defined( "BS_EPG_CHANNEL" )  ) define( "BS_EPG_CHANNEL",  "211"  );
  if( ! defined( "CS1_EPG_CHANNEL" ) ) define( "CS1_EPG_CHANNEL", "CS8"  );
  if( ! defined( "CS2_EPG_CHANNEL" ) ) define( "CS2_EPG_CHANNEL", "CS24" );
  
  
  function check_file( $file ) {
	// ファイルがないなら無問題
	if( ! file_exists( $file ) ) return true;
	
	// 1時間以上前のファイルなら削除してやり直す
	if( (time() - filemtime( $file )) > 3600 ) {
		@unlink( $file );
		return true;
	}
	
	return false;
  }
  
  $settings = Settings::factory();
  
  $temp_xml_bs  = $settings->temp_xml."_bs";
  $temp_xml_cs1 = $settings->temp_xml."_cs1";
  $temp_xml_cs2 = $settings->temp_xml."_cs2";
  $temp_xml_gr  = $settings->temp_xml."_gr";
  
  if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
  
  // BSを処理する
  if( $settings->bs_tuners != 0 ) {
	// 録画重複チェック
	$num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
	if( ($num == 0) && check_file($temp_xml_bs) ) {
	 	$cmdline = "CHANNEL=".BS_EPG_CHANNEL." DURATION=180 TYPE=BS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
  		exec( $cmdline );
  		$cmdline = $settings->epgdump." /BS ".$settings->temp_data." ".$temp_xml_bs;
  		exec( $cmdline );
		$cmdline = INSTALL_PATH."/storeProgram.php BS ".$temp_xml_bs;
		exec( $cmdline );
  		if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
	}

	// CS
	if ($settings->cs_rec_flg != 0) {
		$num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
		if( ($num == 0) && check_file($temp_xml_cs1) ) {
			$cmdline = "CHANNEL=".CS1_EPG_CHANNEL." DURATION=120 TYPE=CS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
			exec( $cmdline );
			$cmdline = $settings->epgdump." /CS ".$settings->temp_data." ".$temp_xml_cs1;
			exec( $cmdline );
			$cmdline = INSTALL_PATH."/storeProgram.php CS ".$temp_xml_cs1;
			exec( $cmdline );
			if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
		}
		$num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
		if( ($num == 0) && check_file($temp_xml_cs2) ) {
			$cmdline = "CHANNEL=".CS2_EPG_CHANNEL." DURATION=120 TYPE=CS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
			exec( $cmdline );
			$cmdline = $settings->epgdump." /CS ".$settings->temp_data." ".$temp_xml_cs2;
			exec( $cmdline );
			$cmdline = INSTALL_PATH."/storeProgram.php CS ".$temp_xml_cs2;
			exec( $cmdline );
			if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
	  	}
  	}
  }
  
  // 地上波を処理する
  if( $settings->gr_tuners != 0 ) {
	foreach( $GR_CHANNEL_MAP as $key=>$value ){
		// 録画重複チェック
		$num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND type = 'GR' AND endtime > now() AND starttime < addtime( now(), '00:01:10')" );
		if( ($num == 0) && check_file($temp_xml_gr.$value."") ) {
			$cmdline = "CHANNEL=".$value." DURATION=60 TYPE=GR TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
			exec( $cmdline );
			$cmdline = $settings->epgdump." ".$key." ".$settings->temp_data." ".$temp_xml_gr.$value."";
			exec( $cmdline );
			$cmdline = INSTALL_PATH."/storeProgram.php GR ".$temp_xml_gr.$value."";
			exec( $cmdline );
			if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
  		}
  	}
  }
  
?>