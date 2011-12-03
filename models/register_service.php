<?php
/**
 *
 *
 */
class RegisterService
{
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
            $starttime = str_replace(" +0900", '', $program['start'] );
            $endtime = str_replace( " +0900", '', $program['stop'] );
            $desc = (string)$program->desc;
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
                $is_batting = $db->row('SELECT * FROM Recorder_programTbl WHERE channel_disc = ? AND starttime < ? AND endtime > ?', array($channel_disc, $starttime, $endtime));
                if ($is_batting !== false) {
                    // 重複発生＝おそらく放映時間の変更
                    foreach ($is_batting as $rec) {
                        var_dump($is_batting);
                        exit;
                        // 自動録画予約された番組は放映時間変更と同時にいったん削除する
                        try {
                            $reserve = new DBRecord(RESERVE_TBL, "program_id", $rec->id );
                            if( $reserve->autorec ) {
                                Reservation::cancel( $reserve->id );
                            }
                        } catch (Exception $e) {
                            throw $e;
                        }
                        // 番組削除
                        $rec->delete();
                    }
                }
                $row = array(
                    'program_disc' => $program_disc,
                    'channel_disc' => $channel_disc,
                    'category_disc' => $category_disc,
                    'type' => $type,
                    'channel' => $channel['channel'],
                    'title' => (string)$program->title,
                    'description' => $desc,
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
        RegisterService::updateChannel($type, $xml);

        // programme 取得
        RegisterService::updateProgram($type, $xml);
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
