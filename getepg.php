#!/usr/bin/php
<?php
/**
 * 番組表を取得する。crontabに登録しておく。
 */
require_once 'config.php';
require_once INSTALL_PATH . '/DBRecord.class.php';
require_once INSTALL_PATH . '/Reservation.class.php';
require_once INSTALL_PATH . '/Keyword.class.php';
require_once INSTALL_PATH . '/Settings.class.php';

/**
 * is_dumped
 *
 * １時間以内のepgdumpデータがあるかどうか
 */
function is_dumped($xml_file) {
    if(!file_exists($xml_file)) {
       return false;
    }

    // 1時間以上前のファイルなら削除してやり直す
    if( (time() - filemtime( $xml_file )) > 3600 ) {
        unlink($xml_file);
        return false;
    }

    // ファイルサイズ0の場合friioに問題がある可能性
    if (filesize($xml_file) <= 0) {
        unlink($xml_file);
        throw new RuntimeException("invalid dump log: {$xml_file} is empty.");
    }

    return true;
}

function do_record($options = array())
{
    $command = '';
    foreach ($options as $key => $value) {
        if (!is_numeric($value)) {
            $value = '"' . $value . '"';
        }
        $command .= strtoupper($key) . "={$value} ";
    }
    $command .= INSTALL_PATH . "/do-record.sh >/dev/null 2>&1";
    return exec($command);
}

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
        if (!is_dumped($temp_xml_bs)) {
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
            if (!is_dumped($temp_xml_cs1)) {
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
            if (!is_dumped($temp_xml_cs2)) {
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
    foreach ($GR_CHANNEL_MAP as $key => $channel_no) {
        // 録画重複チェック
        $num = DBRecord::countRecords(
            RESERVE_TBL,
            "WHERE complete = '0' AND type = 'GR' AND endtime > now() AND starttime < addtime( now(), '00:01:10')"
        );

        if ($num == 0) {
            $temp_filename = str_replace('.ts', "_GR{$channel_no}.ts", $settings->temp_data);

            // 直近1時間のtsない時だけ作る
            if (!is_dumped($temp_filename)) {
                $options = array(
                    'CHANNEL' => $channel_no,
                    'DURATION' => 60,
                    'TYPE' => 'GR',
                    'TUNER' => 0,
                    'MODE' => 0,
                    'OUTPUT' => $temp_filename,
                );
                do_record($options);
            }

            // dump
            $xml = str_replace('.xml', "_GR{$channel_no}.xml", $settings->temp_xml);
            $cmdline = "{$settings->epgdump} {$key} {$temp_filename} {$xml}";
            exec($cmdline);

            // parse
            $cmdline = INSTALL_PATH."/storeProgram.php GR {$xml} >/dev/null 2>&1 &";
            exec($cmdline);
        }
    }
}
?>
