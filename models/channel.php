<?php
/**
 *
 *
 */
class Channel extends Model
{
    public static function get($channel_disc)
    {
        $db = DB::conn();
        $row = $db->row('SELECT * FROM Recorder_channelTbl WHERE channel_disc = ?', array($channel_disc));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }
}
