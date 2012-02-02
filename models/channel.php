<?php
/**
 *
 *
 */
class Channel extends Model
{
    const TABLE = 'Recorder_channelTbl';

    public static function get($channel_disc)
    {
        $db = DB::conn();
        $table = self::TABLE;
        $row = $db->row("SELECT * FROM {$table} WHERE channel_disc = ?", array($channel_disc));
        if ($row === false) {
            return false;
        }
        return new self($row);
    }

    public function setSID($sid)
    {
        $db = DB::conn();
        $db->update(self::TABLE, array('sid' => $sid), array('channel_disc' => $this->channel_disc));
    }
}
