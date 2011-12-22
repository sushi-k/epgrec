<?php
/**
 *
 *
 */
class Program extends Model
{
    const TABLE = 'Recorder_programTbl';

    public static function get($program_disc)
    {
        $db = DB::conn();
        $table = self::TABLE;
        $row = $db->row("SELECT * FROM {$table} WHERE program_disc = ?", array($program_disc));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }

    public static function search($options, $args)
    {
        $db = DB::conn();
        $table = self::TABLE;
        $category_table = Category::TABLE;
        $sql = <<<EOD
SELECT * FROM {$table}
  LEFT JOIN {$category_table} ON {$table}.category_disc = {$category_table}.category_disc
  {$options}
EOD;
        return $db->rows($sql, $args);
    }

    public static function disableAutorec($program_disc)
    {
        $db = DB::conn();
        return $db->update(self::TABLE, array('autorec' => 0), array('program_disc' => $program_disc));
    }
}
