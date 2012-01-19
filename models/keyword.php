<?php
/**
 *
 *
 */
class Keyword extends Model
{
    const TABLE = 'Recorder_keywordTbl';

    public static function get($id)
    {
        $db = DB::conn();
        $row = $db->row('SELECT * FROM ' . self::TABLE . ' WHERE id = ?', array($id));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }

    // 指定条件での検索結果を返す
    private function getPrograms()
    {
        $padding_time = 3600 * 3;
        $endtime = date("Y-m-d H:i:s", time() + $padding_time);

        $params = array();

        // ちょっと先を検索する
        $sql = "SELECT * FROM Recorder_programTbl WHERE starttime > ?";
        $params[] = $endtime;

        if ($this->keyword !== "") {
            if ($this->use_regexp) {
                $sql .= " AND CONCAT(title,description) REGEXP ?";
                $params[] = $this->keyword;
            } else {
                $sql .= " AND CONCAT(title,description) LIKE ?";
                $params[] = '%' . $this->keyword . '%';
            }
        }

        if( $this->type != "*" ) {
            $sql .= " AND type = '".$this->type."'";
        }

        if( $this->category_id != 0 ) {
            $sql .= " AND category_id = '".$this->category_id."'";
        }

        if( $this->channel_id != 0 ) {
            $sql .= " AND channel_id = '".$this->channel_id."'";
        }

        if( $this->weekofday != 7 ) {
            $sql .= " AND WEEKDAY(starttime) = '".$this->weekofday."'";
        }

        $sql .= " ORDER BY starttime ASC";

        $db = DB::conn();
        $result = $db->rows($sql, $params);

        return $result;
    }

    public function reserve()
    {
        $programs = $this->getPrograms();
        foreach ($programs as $program) {
            Reserve::simpleReserve($program['program_disc']);
        }
    }

    // cronで呼ぶ
    public static function reserveAll()
    {
        $db = DB::conn();
        $rows = $db->rows('SELECT id FROM Recorder_reserveTbl');
        foreach ($rows as $row) {
            $keyword = self::get($row['id']);
            $keyword->reserve();
        }
    }

    public static function add(array $record)
    {
        $db = DB::conn();
        return $db->insert(self::TABLE, $record);
    }

    // @TODO 録画予約も取り消す
    public static function delete($id)
    {
        $db = DB::conn();
        return $db->query('DELETE FROM ' . self::TABLE . ' WHERE id = ?', array($id));
    }
}
