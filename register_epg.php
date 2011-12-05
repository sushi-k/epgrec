<?php
/**
 * register_epg.php
 *
 */
mb_language("ja");
require_once dirname(__FILE__) . '/config.php';

// 一旦TMPに出す。
file_put_contents('/home/ha1t/tv/crontab.txt', RecorderService::generateCrontab());
exec('crontab /home/ha1t/tv/crontab.txt');
