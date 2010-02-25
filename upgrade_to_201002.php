#!/usr/bin/php
<?php
include_once('config.php');
include_once(INSTALL_PATH . '/Settings.class.php' );


// mysqli::multi_queryは動作がいまいちなので使わない

function multi_query( $sqlstrs, $dbh ) {
	$error = false;
	
	foreach( $sqlstrs as $sqlstr ) {
		$res = mysql_query( $sqlstr );
		if( $res === FALSE ) {
			echo "failed: ". $sqlstr . "\n";
			$error = true;
		}
	}
	return $error;
}


$settings = Settings::factory();
$dbh = mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );
if( $dbh !== FALSE ) {

	$sqlstr = "use ".$settings->db_name;
	mysql_query( $sqlstr );

	$sqlstr = "set NAMES 'utf8'";
	mysql_query( $sqlstr );
	
	// RESERVE_TBL

	$sqlstrs = array (
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  channel_disc varchar(128) not null default 'none';",	// channel disc
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  channel_id integer not null  default '0';",			// channel ID
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  program_id integer not null default '0';",				// Program ID
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  type varchar(8) not null default 'GR';",				// 種別（GR/BS/CS）
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  channel varchar(10) not null default '0';",			// チャンネル
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  title varchar(512) not null default 'none';",			// タイトル
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  description varchar(512) not null default 'none';",		// 説明 text->varchar
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  category_id integer not null default '0';",			// カテゴリID
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  starttime datetime not null default '1970-01-01 00:00:00';",	// 開始時刻
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  endtime datetime not null default '1970-01-01 00:00:00';",		// 終了時刻
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  job integer not null default '0';",					// job番号
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  path blob default null;",								// 録画ファイルパス
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  complete boolean not null default '0';",				// 完了フラグ
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  reserve_disc varchar(128) not null default 'none';",	// 識別用hash
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  autorec integer not null default '0';",				// キーワードID
	 "alter table ".$settings->tbl_prefix.RESERVE_TBL." modify  mode integer not null default '0';",					//録画モード
	);
	
	if( multi_query( $sqlstrs, $dbh ) ) {
		echo "予約テーブルのアップデートに失敗\n";
	}
	
	$sqlstrs = array(
	 "create index reserve_ch_idx on ".$settings->tbl_prefix.RESERVE_TBL."  (channel_disc);",
	 "create index reserve_st_idx on ".$settings->tbl_prefix.RESERVE_TBL."  (starttime);",
	);
	
	if( multi_query( $sqlstrs, $dbh ) ) {
		echo "予約テーブルにインデックスが作成できません\n";
	}
	
	// PROGRAM_TBL
	
	$sqlstrs = array (
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify channel_disc varchar(128) not null default 'none';",	// channel disc
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify channel_id integer not null default '0';",				// channel ID
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify type varchar(8) not null default 'GR';",				// 種別（GR/BS/CS）
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify channel varchar(10) not null default '0';",			// チャンネル
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify title varchar(512) not null default 'none';",			// タイトル
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify description varchar(512) not null default 'none';",	// 説明 text->varchar
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify category_id integer not null default '0';",			// カテゴリID
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify starttime datetime not null default '1970-01-01 00:00:00';",	// 開始時刻
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify endtime datetime not null default '1970-01-01 00:00:00';",		// 終了時刻
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify program_disc varchar(128) not null default 'none';",	 		// 識別用hash
		"alter table ".$settings->tbl_prefix.PROGRAM_TBL." modify autorec boolean not null default '1';",					// 自動録画有効無効
	);
	
	if( multi_query( $sqlstrs, $dbh ) ) {
		echo "番組テーブルのアップデートに失敗\n";
	}
	
	$sqlstrs = array(
	 "create index program_ch_idx on ".$settings->tbl_prefix.PROGRAM_TBL." (channel_disc);",		// インデックス
	 "create index program_st_idx on ".$settings->tbl_prefix.PROGRAM_TBL." (starttime);",			// インデックス
	);
	
	if( multi_query( $sqlstrs, $dbh ) ) {
		echo "番組テーブルにインデックスが作成できません\n";
	}
	
	// CHANNEL_TBL
	
	$sqlstrs = array(
	"alter table ".$settings->tbl_prefix.CHANNEL_TBL." modify type varchar(8) not null default 'GR';",				// 種別
	"alter table ".$settings->tbl_prefix.CHANNEL_TBL." modify channel varchar(10) not null default '0';",			// channel
	"alter table ".$settings->tbl_prefix.CHANNEL_TBL." modify name varchar(512) not null default 'none';",			// 表示名
	"alter table ".$settings->tbl_prefix.CHANNEL_TBL." modify channel_disc varchar(128) not null default 'none';",	// 識別用hash
	"alter table ".$settings->tbl_prefix.CHANNEL_TBL." add sid varchar(64) not null default 'hd'",				// サービスID用02/23/2010追加
	);
	
	if( multi_query( $sqlstrs, $dbh ) ) {
		echo "チャンネルテーブルのアップデートに失敗\n";
	}
	
	// CATEGORY_TBL
	
	$sqlstrs  = array(
	"alter table ".$settings->tbl_prefix.CATEGORY_TBL." modify name_jp varchar(512) not null default 'none';",		// 表示名
	"alter table ".$settings->tbl_prefix.CATEGORY_TBL." modify name_en varchar(512) not null default 'none';",		// 同上
	"alter table ".$settings->tbl_prefix.CATEGORY_TBL." modify category_disc varchar(128) not null default 'none'",	// 識別用hash
	);
	if( multi_query( $sqlstrs, $dbh ) ) {
		echo "カテゴリテーブルのアップデートに失敗\n";
	}
	
	// KEYWORD_TBL
	
	$sqlstrs = array(
	 "alter table ".$settings->tbl_prefix.KEYWORD_TBL." modify keyword varchar(512) not null default '*';",			// 表示名
	 "alter table ".$settings->tbl_prefix.KEYWORD_TBL." modify type varchar(8) not null default '*';",				// 種別
	 "alter table ".$settings->tbl_prefix.KEYWORD_TBL." modify channel_id integer not null default '0';",				// channel ID
	 "alter table ".$settings->tbl_prefix.KEYWORD_TBL." modify category_id integer not null default '0';",			// カテゴリID
	 "alter table ".$settings->tbl_prefix.KEYWORD_TBL." modify use_regexp boolean not null default '0';",				// 正規表現を使用するなら1
	 "alter table ".$settings->tbl_prefix.KEYWORD_TBL." add autorec_mode integer not null default '0';",						// 自動録画のモード02/23/2010追加
	 "alter table ".$settings->tbl_prefix.KEYWORD_TBL." add weekofday enum ('0','1','2','3','4','5','6','7' ) default '7'",		// 曜日、同追加
	);
	if( multi_query( $sqlstrs, $dbh ) ) {
		echo "キーワードテーブルのアップデートに失敗\n";
	}
}
else
	exit( "DBの接続に失敗\n" );
?>