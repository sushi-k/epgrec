<?php
/**
 *
 *
 */
class Program extends Model {

    public static function get($program_disc)
    {
        $db = DB::conn();
        $row = $db->row('SELECT * FROM Recorder_programTbl WHERE program_disc = ?', array($program_disc));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }

    // @TODO 同一番組をすでに予約している場合警告
    // @TODO 同時間帯に別のチャンネルを予約している場合に警告
    public static function reserve($program_disc) {
        $db = DB::conn();
        $program = $db->row('SELECT * FROM Recorder_programTbl WHERE program_disc = ?', array($program_id));
        $row = array(
            'program_disc' => $program['program_disc'],
            'autorec' => 0,
            'mode' => 0,
            'job' => 0,
        );
        return $db->insert('Recorder_reserveTbl', $row);
    }

    public static function disableAutorec($program_disc)
    {
        $db = DB::conn();
        return $db->update(array('autorec' => 0), array('program_disc' => $program_disc));
    }
}
