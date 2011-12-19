<?php
// 指定のチャンネルのsidを変更する
require_once 'config.php';

if (isset($_POST['sid']) && isset($_POST['channel_disc'])) {
    try {
        $channel = Channel::get($_POST['channel_disc']);
        $channel->setSID($_POST['sid']);
    } catch(Exception $e) {
        throw $e;
    }
}

