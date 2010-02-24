<?php
  include_once( "../config.php" );

  $catv = "";

  if( isset($_POST['catv']) ) {
	if( $_POST['catv'] == 1) {
		$catv = "catv";
	}
  }
  echo '<p>チャンネルスキャン実行中...</p>';
  flush();
  ob_flush();

  system("/usr/local/bin/grscan ".$catv." >".INSTALL_PATH."/settings/gr_channel.php" );

  if( ! file_exists( INSTALL_PATH."/settings/gr_channel.php") ) {
	exit("地上デジタルのスキャンに失敗したようです。自身で".INSTALL_PATH."/settings/gr_channel.phpを作成し、http://localhost/epgrec/install/step2.phpからインストールを再開させてください");
  }
  include_once( INSTALL_PATH."/settings/gr_channel.php" );

echo "<p><b>地上デジタルチャンネルの設定確認</b></p>";

echo "<div>現在、config.phpでは以下のチャンネルの受信が設定されています。受信不可能なチャ
ンネルが混ざっていると番組表が表示できません。</div>";

echo "<ul>";
foreach( $GR_CHANNEL_MAP as $key => $value ) {
        echo "<li>物理チャンネル".$value."</li>";
}
echo "</ul>";

echo '<p><a href="step2.php">以上を確認し次の設定に進む</a></p>';

?>
