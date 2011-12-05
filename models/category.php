<?php
/**
 *
 *
 */
class Category extends Model
{
    public static function get($category_disc)
    {
        $db = DB::conn();
        $row = $db->row('SELECT * FROM Recorder_categoryTbl WHERE category_disc = ?', array($category_disc));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }
}
