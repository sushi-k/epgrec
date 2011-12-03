#!/usr/bin/php
<?php
// xmlファイルをパースして番組情報を更新する

require_once('config.php');
require_once( INSTALL_PATH . '/DBRecord.class.php' );
require_once( INSTALL_PATH . '/Reservation.class.php' );
require_once( INSTALL_PATH . '/Keyword.class.php' );
require_once( INSTALL_PATH . '/Settings.class.php' );
  
$settings = Settings::factory();
  
function updateProgram($type, $xmlfile) {
    global $BS_CHANNEL_MAP, $GR_CHANNEL_MAP, $CS_CHANNEL_MAP;
    // チャンネルマップファイルの準備
    $map = array();
    if ($type === "BS") {
       $map = $BS_CHANNEL_MAP;
    } else if ($type === "GR") {
       $map = $GR_CHANNEL_MAP;
    } else if ($type === "CS") {
       $map = $CS_CHANNEL_MAP;
    }

    // XML parse
    $xml = simplexml_load_file($xmlfile);
    if ($xml === false) {
        throw new RuntimeException('simplexml parse error');
    }

    // channel抽出
    foreach ($xml->channel as $ch) {
        $disc = $ch['id'];
        try {
            // チャンネルデータを探す
            $num = DBRecord::countRecords( CHANNEL_TBL , "WHERE channel_disc = '" . $disc ."'" );
            if ($num == 0) {
                // チャンネルデータがないなら新規作成
                $rec = new DBRecord(CHANNEL_TBL);
                $rec->type = $type;
                $rec->channel = $map["$disc"];
                $rec->channel_disc = $disc;
                $rec->name = $ch->{'display-name'};
            } else {
                // 存在した場合も、とりあえずチャンネル名は更新する
                $rec = new DBRecord(CHANNEL_TBL, "channel_disc", $disc );
                $rec->name = $ch->{'display-name'};
            }
        } catch(Exception $e) {
            throw $e;
        }
    }

    // programme 取得
    foreach( $xml->programme as $program ) {
        $channel_disc = $program['channel']; 
        $channel = $map["$channel_disc"];
        $starttime = str_replace(" +0900", '', $program['start'] );
        $endtime = str_replace( " +0900", '', $program['stop'] );
        $title = $program->title;
        $desc = $program->desc;
        $cat_ja = "";
        $cat_en = "";
        foreach( $program->category as $cat ) {
            if( $cat['lang'] == "ja_JP" ) $cat_ja = $cat;
            if( $cat['lang'] == "en" ) $cat_en = $cat;
        }
        $program_disc = md5( $channel_disc . $starttime . $endtime );
        // printf( "%s %s %s %s %s %s %s \n", $program_disc, $channel, $starttime, $endtime, $title, $desc, $cat_ja );
        try {
            // カテゴリを処理する
            $category_disc = md5( $cat_ja . $cat_en );
            $num = DBRecord::countRecords(CATEGORY_TBL, "WHERE category_disc = '".$category_disc."'" );
            $cat_rec = null;
            if( $num == 0 ) {
                // 新規カテゴリの追加
                $cat_rec = new DBRecord( CATEGORY_TBL );
                $cat_rec->name_jp = $cat_ja;
                $cat_rec->name_en = $cat_en;
                $cat_rec->category_disc = $category_disc;
            }
            else
                $cat_rec = new DBRecord(CATEGORY_TBL, "category_disc" , $category_disc );
            //
            $channel_rec = new DBRecord(CHANNEL_TBL, "channel_disc", $channel_disc );
            $num = DBRecord::countRecords(PROGRAM_TBL, "WHERE program_disc = '".$program_disc."'" );
            if( $num == 0 ) {
                // 新規番組
                // 重複チェック 同時間帯にある番組
                $options = "WHERE channel_disc = '".$channel_disc."' ".
                    "AND starttime < '". $endtime ."' AND endtime > '".$starttime."'";
                $battings = DBRecord::countRecords(PROGRAM_TBL, $options );
                if( $battings > 0 ) {
                    // 重複発生＝おそらく放映時間の変更
                    $records = DBRecord::createRecords(PROGRAM_TBL, $options );
                    foreach( $records as $rec ) {
                        // 自動録画予約された番組は放映時間変更と同時にいったん削除する
                        try {
                            $reserve = new DBRecord(RESERVE_TBL, "program_id", $rec->id );
                            if( $reserve->autorec ) {
                                Reservation::cancel( $reserve->id );
                            }
                        }
                        catch( Exception $e ) {
                            //無視
                        }
                        // 番組削除
                        $rec->delete();
                    }
                }
                // //
                $rec = new DBRecord( PROGRAM_TBL );
                $rec->channel_disc = $channel_disc;
                $rec->channel_id = $channel_rec->id;
                $rec->type = $type;
                $rec->channel = $channel_rec->channel;
                $rec->title = $title;
                $rec->description = $desc;
                $rec->category_id = $cat_rec->id;
                $rec->starttime = $starttime;
                $rec->endtime = $endtime;
                $rec->program_disc = $program_disc;
            }
            else {
                // 番組内容更新
                $rec = new DBRecord( PROGRAM_TBL, "program_disc", $program_disc );
                $rec->title = $title;
                $rec->description = $desc;
                $rec->category_id = $cat_rec->id;
            }
        }
        catch(Exception $e) {
            exit( $e->getMessage() );
        }
    }
}

function cleanup()
{
    // 不要なプログラムの削除
    // 8日以上前のプログラムを消す
    $arr = DBRecord::createRecords(PROGRAM_TBL, "WHERE endtime < subdate( now(), 8 )");
    foreach( $arr as $val ) $val->delete();

    // 8日以上先のデータがあれば消す
    $arr = DBRecord::createRecords(PROGRAM_TBL, "WHERE starttime  > adddate( now(), 8 )");
    foreach( $arr as $val ) $val->delete();

    // キーワード自動録画予約
    $arr = Keyword::createKeywords();
    foreach($arr as $val) {
        try {
            $val->reservation();
        } catch(Exception $e) {
            // 無視
        }
    }
}

$type = $argv[1];	// BS CS GR
$xml = $argv[2];	// XMLファイル
  
if (file_exists($xml)) {
    updateProgram($type, $xml);
    unlink($xml);
}

cleanup();

