<?php
/**
 *
 *
 */
class Keyword
{
    const TABLE = 'Recorder_keywordTbl';

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
