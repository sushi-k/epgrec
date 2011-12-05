<?php
/**
 * 番組表作成のための、録画、録画データからのxml dump, xmlの解析登録を行う
 *
 */
class RecorderService
{
    // 予約番組付きのcrontabを作る
    // 同一行があったら追加しない。
    public static function generateCrontab()
    {
        $db = DB::conn();
        $rows = $db->rows('SELECT * FROM Recorder_reserveTbl LEFT JOIN Recorder_programTbl ON Recorder_programTbl.program_disc = Recorder_reserveTbl.program_disc WHERE complete = 0 AND starttime > NOW()');

        $list = array();
        foreach ($rows as $row) {
            $list[] = RecorderService::convertCron($row);
        }

        $crontab = shell_exec('crontab -l');

        foreach ($list as $key => $line) {
            if (strpos($crontab, $line) !== false) {
                unset($list[$key]);
            } else {
                $crontab .= $line . PHP_EOL;
            }
        }

        return $crontab;
    }

    // レコードをcronのコマンドに変換する
    public static function convertCron($row)
    {
        $delay = 60;

        $start_date = strtotime($row['starttime']);
        $start_date -= $delay;

        $end_date = strtotime($row['endtime']);

        $i = (int)date('i', $start_date);
        $h = (int)date('H', $start_date);
        $d = (int)date('d', $start_date);
        $m = (int)date('m', $start_date);

        $cmd = '/home/ha1t/bin/recfriio --b25 --strip';
        $save_path = "/home/ha1t/tv/{$row['title']}_" . date("Ymd_His", $start_date) . '.ts';

        $time = $end_date - $start_date;
        $cron = "{$i} {$h} {$d} {$m} * {$cmd} {$row['channel']} {$time} \"{$save_path}\"";

        return $cron;
    }

    public static function doRecord($options = array())
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

    /**
     * isDumped
     *
     * １時間以内のepgdumpデータがあるかどうか
     */
    public static function isDumped($xml_file) {
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

    private static function updateChannel($type, $xml) {
        try {
            $db = DB::conn();
            $db->begin();
            foreach ($xml->channel as $ch) {
                $channel_no = (string)$ch['id'];
                $display_name = (string)$ch->{'display-name'};
                $row = array(
                    'type' => $type,
                    'channel' => ChannelMaster::$GR[$channel_no],
                    'channel_disc' => $channel_no,
                    'name' => $display_name,
                );
                $db->replace('Recorder_channelTbl', $row);
            }
            $db->commit();
        } catch(Exception $e) {
            $db->rollback();
            throw $e;
        }
    }

    private static function updateProgram($type, $xml)
    {
        $db = DB::conn();
        foreach ($xml->programme as $program) {
            $channel_disc = (string)$program['channel']; 
            $channel = $db->row('SELECT * FROM Recorder_channelTbl WHERE channel_disc = ?', array($channel_disc));
            $starttime = str_replace(" +0900", '', $program['start']);
            $endtime = str_replace(" +0900", '', $program['stop']);
            $cat_ja = '';
            $cat_en = '';
            foreach ($program->category as $cat) {
                if ((string)$cat['lang'] === "ja_JP") {
                    $cat_ja = (string)$cat;
                } else if ((string)$cat['lang'] === "en") {
                    $cat_en = (string)$cat;
                }
            }
            $program_disc = md5($channel_disc . $starttime . $endtime);
            $category_disc = md5($cat_ja . $cat_en);

            try {
                $db->begin();
                $row = array(
                    'name_jp' => $cat_ja,
                    'name_en' => $cat_en,
                    'category_disc' => $category_disc,
                );
                $db->replace('Recorder_categoryTbl', $row);

                // 重複チェック 同時間帯にある番組
                $is_batting = $db->rows('SELECT * FROM Recorder_programTbl WHERE channel_disc = ? AND starttime < ? AND endtime > ?', array($channel_disc, $starttime, $endtime));
                if ($is_batting !== false) {
                    // 重複発生＝おそらく放映時間の変更
                    foreach ($is_batting as $batting_program) {
                        $db->query('DELETE FROM Recorder_programTbl WHERE program_disc = ?', array($batting_program['program_disc']));
                    }
                }
                $row = array(
                    'program_disc' => $program_disc,
                    'channel_disc' => $channel_disc,
                    'category_disc' => $category_disc,
                    'type' => $type,
                    'channel' => $channel['channel'],
                    'title' => (string)$program->title,
                    'description' => (string)$program->desc,
                    'starttime' => $starttime,
                    'endtime' => $endtime,
                );
                $db->replace('Recorder_programTbl', $row);
                $db->commit();
            } catch(Exception $e) {
                $db->rollback();
                throw $e;
            }
        }
    }

    public static function storeProgram($type, $xmlfile) {
        global $BS_CHANNEL_MAP;
        global $CS_CHANNEL_MAP;

        // チャンネルマップファイルの準備
        $map = array();
        if ($type === "BS") {
            $map = $BS_CHANNEL_MAP;
        } else if ($type === "GR") {
            $map = ChannelMaster::$GR;
        } else if ($type === "CS") {
            $map = $CS_CHANNEL_MAP;
        }

        // XML parse
        $xml = simplexml_load_file($xmlfile);
        if ($xml === false) {
            throw new RuntimeException('simplexml parse error');
        }

        // channel抽出
        RecorderService::updateChannel($type, $xml);

        // programme 取得
        RecorderService::updateProgram($type, $xml);
    }

    public static function cleanup()
    {
        // 不要なプログラムの削除
        // 8日以上前のプログラムを消す
        $db = DB::conn();
        $db->query("DELETE FROM Recorder_programTbl WHERE endtime < SUBDATE(NOW(), 8)");

        // 8日以上先のデータがあれば消す
        $db->query("DELETE FROM Recorder_programTbl WHERE endtime > adddate(NOW(), 8)");
    }
}
