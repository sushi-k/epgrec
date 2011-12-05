<?php
header("Expires: Thu, 01 Dec 1994 16:00:00 GMT");
header("Last-Modified: ". gmdate("D, d M Y H:i:s"). " GMT");
header("Cache-Control: no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once("config.php");
require_once(INSTALL_PATH . "/DBRecord.class.php" );
require_once(INSTALL_PATH . "/reclib.php" );
require_once(INSTALL_PATH . "/Settings.class.php" );

$settings = Settings::factory();

if (!isset($_GET['reserve_id'])) {
    throw RuntimeException("予約番号が指定されていません");
}

$reserve_id = $_GET['reserve_id'];

try{
    $rrec = new DBRecord( RESERVE_TBL, "id", $reserve_id );

    $start_time = strtotime($rrec->starttime);
    $end_time = strtotime($rrec->endtime );
    $duration = $end_time - $start_time;

    $size = 3 * 1024 * 1024 * $duration;	// 1秒あたり3MBと仮定

    header('Content-type: video/mpeg');
    header('Content-Disposition: inline; filename="'.$rrec->path.'"');
    header('Content-Length: ' . $size );

    ob_clean();
    flush();

    $fp = @fopen( INSTALL_PATH.$settings->spool."/".$rrec->path, "r" );
    if( $fp !== false ) {
        do {
            $start = microtime(true);
            if( feof( $fp ) ) break;
            echo fread( $fp, 6292 );
            @usleep( 2000 - (int)((microtime(true) - $start) * 1000 * 1000));
        }
        while( connection_aborted() == 0 );
    }
    fclose($fp);
} catch (Exception $e) {
    throw $e;
}
