<?php
/**
 *
 *
 */
class Program {
    public static function reserve($program_disc) {
        try {
            // @TODO 同一番組をすでに予約している場合警告
            // @TODO 同時間帯に別のチャンネルを予約している場合に警告
            $db = DB::conn();
            $program = $db->row('SELECT * FROM Recorder_programTbl WHERE program_disc = ?', array($program_id));
            $row = array(
                'program_disc' => $program['program_disc'],
                'autorec' => 0,
                'mode' => 0,
                'job' => 0,
            );
            return $db->insert('Recorder_reserveTbl', $row);
        } catch( Exception $e ) {
            throw $e;
        }
    }
}
