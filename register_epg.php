<?php
/**
 * register_epg.php
 *
 */

mb_language("ja");

require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/DBRecord.class.php';

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

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
    DB_USER,
    DB_PASS,
    array(
        'PDO::MYSQL_ATTR_INIT_COMMAND' => 'SET NAMES utf8',
    )
);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$now = $pdo->quote(date('Y-m-d H:i:s'));
$sql = <<<EOD
SELECT * FROM epgrec_reserveTbl WHERE complete = 0 AND starttime > {$now}
EOD;

$list = array();
foreach ($pdo->query($sql) as $row) {
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

