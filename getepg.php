#!/usr/bin/php
<?php
/**
 * 番組表を取得する。
 *
 * @TODO キーワード自動録画を実装する
 */
require_once 'config.php';
require_once INSTALL_PATH . '/Settings.class.php';

$settings = Settings::factory();

$temp_xml_bs  = $settings->temp_xml."_bs";
$temp_xml_cs1 = $settings->temp_xml."_cs1";
$temp_xml_cs2 = $settings->temp_xml."_cs2";

if (file_exists($settings->temp_data)) {
    unlink($settings->temp_data);
}

// BSを処理する
if( $settings->bs_tuners != 0 ) {
    // 録画重複チェック
    $num = DBRecord::countRecords(RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
    if($num == 0) {
        if (!RecorderService::isDumped($temp_xml_bs)) {
            $cmdline = "CHANNEL=".BS_EPG_CHANNEL." DURATION=180 TYPE=BS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
            exec( $cmdline );
        }
        $cmdline = $settings->epgdump." /BS ".$settings->temp_data." ".$temp_xml_bs;
        exec( $cmdline );
        $cmdline = INSTALL_PATH."/storeProgram.php BS ".$temp_xml_bs." >/dev/null 2>&1 &";
        exec( $cmdline );
        if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
    }

    // CS
    if ($settings->cs_rec_flg != 0) {
        $num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
        if ($num == 0) {
            if (!RecorderService::isDumped($temp_xml_cs1)) {
                $cmdline = "CHANNEL=".CS1_EPG_CHANNEL." DURATION=120 TYPE=CS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
                exec( $cmdline );
            }
            $cmdline = $settings->epgdump." /CS ".$settings->temp_data." ".$temp_xml_cs1;
            exec( $cmdline );
            $cmdline = INSTALL_PATH."/storeProgram.php CS ".$temp_xml_cs1." >/dev/null 2>&1 &";
            exec( $cmdline );
            if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
        }
        $num = DBRecord::countRecords(  RESERVE_TBL, "WHERE complete = '0' AND (type = 'BS' OR type = 'CS') AND endtime > now() AND starttime < addtime( now(), '00:03:05')" );
        if($num == 0) {
            if (!RecorderService::isDumped($temp_xml_cs2)) {
                $cmdline = "CHANNEL=".CS2_EPG_CHANNEL." DURATION=120 TYPE=CS TUNER=0 MODE=0 OUTPUT=".$settings->temp_data." ".DO_RECORD . " >/dev/null 2>&1";
                exec( $cmdline );
            }
            $cmdline = $settings->epgdump." /CS ".$settings->temp_data." ".$temp_xml_cs2;
            exec( $cmdline );
            $cmdline = INSTALL_PATH."/storeProgram.php CS ".$temp_xml_cs2." >/dev/null 2>&1 &";
            exec( $cmdline );
            if( file_exists( $settings->temp_data ) ) @unlink( $settings->temp_data );
        }
    }
}

// 地上波を処理する
if ($settings->gr_tuners != 0) {
    foreach (ChannelMaster::$GR as $key => $channel_no) {
        // 録画重複チェック
        $db = DB::conn();
        $row = $db->row("SELECT COUNT(*) FROM Recorder_reserveTbl LEFT JOIN Recorder_programTbl ON Recorder_reserveTbl.program_disc = Recorder_programTbl.program_disc WHERE complete = '0' AND type = 'GR' AND endtime > NOW() AND starttime < addtime( NOW(), '00:01:10')");
        if (is_array($row) && current($row) == 0) {
            $temp_filename = str_replace('.ts', "_GR{$channel_no}.ts", $settings->temp_data);

            // 直近1時間のtsない時だけ作る
            if (!RecorderService::isDumped($temp_filename)) {
                $options = array(
                    'CHANNEL' => $channel_no,
                    'DURATION' => 30,
                    'TYPE' => 'GR',
                    'TUNER' => 0,
                    'MODE' => 0,
                    'OUTPUT' => $temp_filename,
                );
                RecorderService::doRecord($options);
            }

            // dump
            $xml = str_replace('.xml', "_GR{$channel_no}.xml", $settings->temp_xml);
            $cmdline = "{$settings->epgdump} {$key} {$temp_filename} {$xml}";
            exec($cmdline);

            // parse
            if (file_exists($xml)) {
                RecorderService::storeProgram('GR', $xml);
                RecorderService::cleanup();
                // unlink($xml);
            }
        }
    }
}
