<?php
/**
 *
 *
 */
class Category extends Model
{
    const TABLE = 'Recorder_categoryTbl';

    public static function get($category_disc)
    {
        $db = DB::conn();
        $row = $db->row('SELECT * FROM ' . self::TABLE . ' WHERE category_disc = ?', array($category_disc));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }

    public static function getAll()
    {
        $items = array();
        $db = DB::conn();
        $rows = $db->rows('SELECT * FROM ' . self::TABLE . ' ORDER BY name_jp');
        foreach ($rows as $row) {
            $items[] = new self($row);
        }

        return $items;
    }
}
