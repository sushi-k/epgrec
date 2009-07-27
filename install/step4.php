<?php
include_once( "../config.php");
include_once( INSTALL_PATH."/Settings.class.php" );

// 設定の保存
$settings = Settings::factory();
$settings->post();
$settings->save();

echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"';
echo '"http://www.w3.org/TR/html4/loose.dtd">';
echo '<html>';
echo '<head>';
echo '<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
echo '<meta http-equiv="Content-Style-Type" content="text/css">';
echo '<title>インストール最終ステップ</title>';
echo '</head>';

echo '<body>';

echo 'これからEPGデータの初回受信を実行します。サーバーの速度や受信可能な局の数によって異なりますが、';
echo 'EPGの受信には20～50分程度はかかります。初回受信が終了するまで番組表は表示できません。<br>';
echo 'また、設定ミスや受信データの異常によってEPGの初回受信に失敗すると番組表の表示はできません。<br>';
echo '設定ミスが疑われる場合、<a href="'.$settings->install_url.'./install/step1.php">インストール設定</a>を実行し直してください。<br>';
echo 'また、手動でEPGの受信を試みるのもひとつの方法です。コンソール上で、<br>';
echo '<br>';
echo '$ '.INSTALL_PATH.'/getepg.php [Enter]<br>';
echo '<br>';
echo 'と実行してください。<br><br>';
echo 'EPGの受信を設定後、/etc/cron.d/以下にEPG受信の自動実行を設定する必要があります。<br>';
echo 'Debian/Ubuntu用の設定ファイルは'.INSTALL_PATH.'/cron.d/getepgです。Debian/Ubuntuの方は<br><br>';
echo '$ sudo cp '.INSTALL_PATH.'/cron.d/getepg /etc/cron.d/ [Enter]<br>';
echo '<br>という具合にコピーするだけで動作するでしょう。それ以外のディストリビューションをお使いの方は';
echo 'Debian/Ubuntu用の設定ファイルを参考に、適切に設定を行ってください<br>';
echo '<br>';
echo 'EPGの初回受信をこのスクリプトから実行します。20～50分程度後に<a href="'.$settings->install_url.'">epgrecのトップページ</a>を開いてください。';

@system( INSTALL_PATH."/getepg.php &" );

exit();
?>
