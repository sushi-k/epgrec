#!/usr/bin/php
<?php
  include_once('config.php');
  include_once( INSTALL_PATH . '/DBRecord.class.php' );
  include_once( INSTALL_PATH . '/Reservation.class.php' );
  include_once( INSTALL_PATH . '/Keyword.class.php' );
  include_once( INSTALL_PATH . '/Settings.class.php' );
  
  $settings = Settings::factory();

  if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
  
  // BSを処理する
  if( $settings->bs_tuners != 0 ) {
	// 録画重複チェック
	$num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
	if( ($num == 0) && !file_exists($settings->temp_xml."_bs") ) {
	 	$cmdline = "CHANNEL=211 DURATION=180 TYPE=BS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
  		exec( $cmdline );
  		$cmdline = $settings->epgdump." /BS ".$settings->temp_data." ".$settings->temp_xml."_bs";
  		exec( $cmdline );
		$cmdline = INSTALL_PATH."/storeProgram.php BS ".$settings->temp_xml."_bs";
		exec( $cmdline );
  		if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
	}

	// CS
	if ($settings->cs_rec_flg != 0) {
		$num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
		if( ($num == 0) && !file_exists($settings->temp_xml."_cs01") ) {
			$cmdline = "CHANNEL=CS8 DURATION=120 TYPE=CS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
			exec( $cmdline );
			$cmdline = $settings->epgdump." /CS ".$settings->temp_data." ".$settings->temp_xml."_cs01";
			exec( $cmdline );
			$cmdline = INSTALL_PATH."/storeProgram.php CS ".$settings->temp_xml."_cs01";
			exec( $cmdline );
			if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
		}
		$num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
		if( ($num == 0) && !file_exists($settings->temp_xml."_cs02") ) {
			$cmdline = "CHANNEL=CS24 DURATION=120 TYPE=CS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
			exec( $cmdline );
			$cmdline = $settings->epgdump." /CS ".$settings->temp_data." ".$settings->temp_xml."_cs02";
			exec( $cmdline );
			$cmdline = INSTALL_PATH."/storeProgram.php CS ".$settings->temp_xml."_cs02";
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
		if( ($num == 0) && !file_exists($settings->temp_xml."_".$value."") ) {
			$cmdline = "CHANNEL=".$value." DURATION=60 TYPE=GR TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
			exec( $cmdline );
			$cmdline = $settings->epgdump." ".$key." ".$settings->temp_data." ".$settings->temp_xml."_".$value."";
			exec( $cmdline );
			$cmdline = INSTALL_PATH."/storeProgram.php GR ".$settings->temp_xml."_".$value."";
			exec( $cmdline );
			if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
  		}
  	}
  }
  
?>