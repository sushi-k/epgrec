<?php
// 簡易予約
require_once 'config.php';

if (!isset($_GET['program_id'])) {
    exit("Error: 番組が指定されていません");
}

Program::reserve($_GET['program_id']);
