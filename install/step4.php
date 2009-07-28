<?php
include_once( "../config.php");
include_once( INSTALL_PATH."/Settings.class.php" );

// 設定の保存
$settings = Settings::factory();
$settings->post();
$settings->save();

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta http-equiv="Content-Style-Type" content="text/css">
<title>インストール最終ステップ</title>
</head>

<body>

<p>初期設定が完了しました。下のリンクをクリックするとEPGの初回受信が始まります。
EPGの受信には20～50分程度はかかります。初回受信が終了するまで番組表は表示できません。</p>

<p>EPG受信後、/etc/cron.d/以下にcronによるEPG受信の自動実行を設定する必要があります。
Debian/Ubuntu用の設定ファイルは<?php echo INSTALL_PATH; ?>/cron.d/getepgです。Debian/Ubuntuをお使いの方は<br>
<pre>
$ sudo cp <?php echo INSTALL_PATH; ?>/cron.d/getepg /etc/cron.d/ [Enter]
</pre>
<br>という具合にコピーするだけで動作するでしょう。それ以外のディストリビューションをお使いの方は
Debian/Ubuntu用の設定ファイルを参考に、適切にcronの設定を行ってください。</p>

<p>なお、設定ミスや受信データの異常によってEPGの初回受信に失敗すると番組表の表示はできません。
設定ミスが疑われる場合、<a href="<?php echo $settings->install_url; ?>/install/step1.php">インストール設定</a>を実行し直してください。
また、手動でEPGの受信を試みるのもひとつの方法です。コンソール上で、<br>
<pre>
$ <?php echo INSTALL_PATH; ?>/getepg.php [Enter]
</pre>
<br>
と実行してください。</p>

<a href="step5.php">このリンクをクリックするとEPGの初回受信が始まります</a>

</body>
</html>
