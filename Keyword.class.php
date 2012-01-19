<?php
require_once 'config.php';
require_once INSTALL_PATH . '/DBRecord.class.php';
require_once INSTALL_PATH . '/Reservation.class.php';

class Keyword extends DBRecord
{
    public function __construct($property = null, $value = null) {
        try {
            parent::__construct(KEYWORD_TBL, $property, $value);
        } catch (Exception $e) {
            throw $e;
        }
    }

    // 指定条件での検索結果を返す
    private function getPrograms()
    {
        // ちょっと先を検索する
        $options = " WHERE starttime > '".date("Y-m-d H:i:s", time() + $this->settings->padding_time + 120 )."'";

        if ($this->keyword != "") {
            if ($this->use_regexp) {
                $options .= " AND CONCAT(title,description) REGEXP '".mysql_real_escape_string($this->keyword)."'";
            } else {
                $options .= " AND CONCAT(title,description) like '%".mysql_real_escape_string($this->keyword)."%'";
            }
        }

        if( $this->type != "*" ) {
            $options .= " AND type = '".$this->type."'";
        }

        if( $this->category_id != 0 ) {
            $options .= " AND category_id = '".$this->category_id."'";
        }

        if( $this->channel_id != 0 ) {
            $options .= " AND channel_id = '".$this->channel_id."'";
        }

        if( $this->weekofday != 7 ) {
            $options .= " AND WEEKDAY(starttime) = '".$this->weekofday."'";
        }

        $options .= " ORDER BY starttime ASC";

        $recs = array();
        try {
            $recs = DBRecord::createRecords(PROGRAM_TBL, $options);
        } catch (Exception $e) {
            throw $e;
        }

        return $recs;
    }

    // 指定キーワードでの一括録画指定
    public function reservation()
    {
        if ($this->id == 0) {
           return;
        }

        $precs = array();
        try {
            $precs = $this->getPrograms();
        } catch (Exception $e) {
            throw $e;
        }

        if( count($precs) < 300 ) {
            // 一気に録画予約
            foreach ($precs as $rec) {
                if ($rec->autorec) {
                    Reservation::simple( $rec->id, $this->id, $this->autorec_mode );
                    usleep(100);		// あんまり時間を空けないのもどう?
                }
            }
        } else {
            throw new Exception( "300件以上の自動録画は実行できません" );
        }
    }

    public function delete() {
        if ($this->id == 0) return;

        $precs = $this->getPrograms();

        // 一気にキャンセル
        foreach( $precs as $rec ) {
            $reserve = new DBRecord( RESERVE_TBL, "program_id", $rec->id );
            // 自動予約されたもののみ削除
            if( $reserve->autorec ) {
                Reservation::cancel( $reserve->id );
                usleep( 100 );		// あんまり時間を空けないのもどう?
            }
        }
        parent::delete();
    }

    public function __destruct() {
        parent::__destruct();
    }
}

