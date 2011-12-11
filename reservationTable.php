<?php
require_once 'config.php';
require_once INSTALL_PATH . '/Smarty/Smarty.class.php';

try{
    $db = DB::conn();
    $sql = <<<EOD
SELECT * FROM Recorder_reserveTbl
  LEFT JOIN Recorder_programTbl ON Recorder_reserveTbl.program_disc = Recorder_programTbl.program_disc
  LEFT JOIN Recorder_categoryTbl ON Recorder_programTbl.category_disc = Recorder_categoryTbl.category_disc
WHERE complete=0
ORDER BY starttime ASC
EOD;
    $rows = $db->rows($sql);

    $smarty = new Smarty();
    $smarty->assign('sitetitle', '録画予約一覧');
    $smarty->assign('reservations', $rows);
    $smarty->display('reservationTable.html');
} catch (Exception $e) {
    exit($e->getMessage());
}
