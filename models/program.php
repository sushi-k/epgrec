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

    // @TODO 同一番組をすでに予約している場合警告
    // @TODO 同時間帯に別のチャンネルを予約している場合に警告
    public static function reserve($program_disc) {
        $db = DB::conn();
        $table = self::TABLE;
        $program = $db->row("SELECT * FROM {$table} WHERE program_disc = ?", array($program_disc));
        $row = array(
            'program_disc' => $program['program_disc'],
            'autorec' => 0,
            'mode' => 0,
            'job' => 0,
        );
        return $db->insert($table, $row);
    }

    public static function disableAutorec($program_disc)
    {
        $db = DB::conn();
        return $db->update(self::TABLE, array('autorec' => 0), array('program_disc' => $program_disc));
    }
}
