<?php
/**
 *
 *
 */
class Reserve {
    public function isReserved($program_disc) {
        $db = DB::conn();
        $result = $db->row('SELECT * FROM Recorder_reserveTbl WHERE program_disc = ?', array($program_disc));
        if ($result !== false) {
            return true;
        } else {
            return false;
        }
    }
}
