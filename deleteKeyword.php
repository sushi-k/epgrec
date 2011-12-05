<?php
require_once 'config.php';
require_once INSTALL_PATH . '/Keyword.class.php';

if (!isset($_GET['keyword_id'])) {
    exit("Error:キーワードIDが指定されていません");
}

try {
    $rec = new Keyword( "id", $_GET['keyword_id']);
    $rec->delete();
} catch(Exception $e ) {
    exit("Error:" . $e->getMessage());
}
