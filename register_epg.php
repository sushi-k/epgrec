<?php
/**
 * register_epg.php
 *
 */
mb_language("ja");
require_once dirname(__FILE__) . '/config.php';

function cleanTmp()
{
    if( file_exists( TEMP_DATA ) ) unlink( TEMP_DATA );
    if( file_exists( TEMP_XML ) ) unlink( TEMP_XML );
}

function convert_cron($row)
{
    $delay = 60;

    $start_date = strtotime($row['starttime']);
    $start_date -= $delay;

    $end_date = strtotime($row['endtime']);

    $i = (int)date('i', $start_date);
    $h = (int)date('H', $start_date);
    $d = (int)date('d', $start_date);
    $m = (int)date('m', $start_date);

    $cmd = '/home/ha1t/bin/recfriio --b25 --strip';
    $save_path = "/home/ha1t/tv/{$row['title']}_" . date("Ymd_His", $start_date) . '.ts';

    $time = $end_date - $start_date;
    $cron = "{$i} {$h} {$d} {$m} * {$cmd} {$row['channel']} {$time} \"{$save_path}\"";

    return $cron;
}

function debug($msg)
{
    echo $msg . PHP_EOL;
}

$db = DB::conn();
$rows = $db->rows('SELECT * FROM Recorder_reserveTbl LEFT JOIN Recorder_programTbl ON Recorder_programTbl.program_disc = Recorder_reserveTbl.program_disc WHERE complete = 0 AND starttime > NOW()');

$list = array();
foreach ($rows as $row) {
    $list[] = convert_cron($row);
}

$re = shell_exec('crontab -l');

foreach ($list as $key => $line) {
    if (strpos($re, $line) !== false) {
        unset($list[$key]);
    } else {
        $re .= $line . PHP_EOL;
    }
}

file_put_contents('/home/ha1t/tv/crontab.txt', $re);
exec('crontab /home/ha1t/tv/crontab.txt');

