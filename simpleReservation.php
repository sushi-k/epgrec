<?php
// 簡易予約
require_once 'config.php';

if (!isset($_GET['program_id'])) {
    exit("Error: 番組が指定されていません");
}

$program_id = $_GET['program_id'];
try {
    // 同一番組をすでに予約している場合警告
    // 同時間帯に別のチャンネルを予約している場合に警告
    $db = DB::conn();
    $program = $db->row('SELECT * FROM Recorder_programTbl WHERE program_disc = ?', array($program_id));
    $row = array(
        'program_disc' => $program['program_disc'],
        'autorec' => 0,
        'mode' => 0,
        'job' => 0,
    );
    $db->insert('Recorder_reserveTbl', $row);
} catch( Exception $e ) {
    throw $e;
}

?>
