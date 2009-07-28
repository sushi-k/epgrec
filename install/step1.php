<?php

// パーミッションを返す
function getPerm( $file ) {
	
	$ss = @stat( $file );
	return sprintf("%o", ($ss['mode'] & 000777));
}

echo "<p><b>epgrecのインストール状態をチェックします</b></p>";

// config.phpの存在確認

if(! file_exists( "../config.php" ) ) {
	@copy( "../config.php.sample", "../config.php" );
	if( ! file_exists( "../config.php" ) ) {
		exit("config.phpが存在しません<br>config.php.sampleをリネームし地上デジタルチャンネルマップを編集してください<br>");
	}
}

include_once("../config.php");
include_once(INSTALL_PATH."/reclib.php");

// do-record.shの存在チェック

if(! file_exists( DO_RECORD ) ) {
	exit("do-record.shが存在しません<br>do-record.sh.pt1やdo-record.sh.friioを参考に作成してください<br>" );
}


// パーミッションチェック

$rw_dirs = array( 
	INSTALL_PATH."/templates_c",
	INSTALL_PATH."/video",
	INSTALL_PATH."/thumbs",
	INSTALL_PATH."/settings",
	INSTALL_PATH."/cache",
);


$exec_files = array(
	DO_RECORD,
	COMPLETE_CMD,
	INSTALL_PATH."/getepg.php",
	GEN_THUMBNAIL,
);

echo "<p><b>ディレクトリのパーミッションチェック（777）</b></p>";
echo "<div>";
foreach($rw_dirs as $value ) {
	echo $value;
	
	$perm = getPerm( $value );
	if( $perm != "777" ) {
		exit('<font color="red">...'.$perm.'... missing</font><br>このディレクトリを書き込み許可にしてください（ex. chmod 777 '.$value.'）</div>' );
	}
	echo "...".$perm."...ok<br>";
}
echo "</div>";


echo "<p><b>ファイルのパーミッションチェック（755）</b></p>";
echo "<div>";
foreach($exec_files as $value ) {
	echo $value;
	
	$perm = getPerm( $value );
	if( !($perm == "755" || $perm == "775" || $perm == "777") ) {
		exit('<font color="red">...'.$perm.'... missing</font><br>このファイルを実行可にしてください（ex. chmod 755 '.$value.'）</div>');
	}
	echo "...".$perm."...ok<br>";
}
echo "</div>";

echo "<p><b>地上デジタルチャンネルの設定確認</b></p>";

echo "<div>現在、config.phpでは以下のチャンネルの受信が設定されています。受信不可能なチャンネルが混ざっていると番組表が表示できません。</div>";

echo "<ul>";
foreach( $GR_CHANNEL_MAP as $key => $value ) {
	echo "<li>物理チャンネル".$value."</li>";
}
echo "</ul>";


echo '<p><a href="step2.php">以上を確認し次の設定に進む</a></p>';

?>
