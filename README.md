# epgrec friio #
epgrecをforkして、friioを接続してあるLinuxサーバ上で動作させる事に特化したバージョンを作りました。
従来atコマンドを渡していた部分をcrontabに変更したのが一番特徴的な変更部分ですが、
全体的にクエリリクエストが多かったり、無駄な処理が多くなっている部分を修正してあります。
かなりの量のXSSとSQL Injectionができるポイントがあるので、そのあたりも修正していく予定。

## v0.1 ##
とりあえず最低限の動作。
friioをcrontab経由で動かせるようになった。
DBレベルで変更を始めたのでインストーラーもまともに動作しない。

## TODO ##
- recomplete.phpを実行させる
- keyword登録システムを有効にする
- DBRecordからの完全な脱出

## DBRecordクラス ##
epgrecは簡易O/Rマッピングを行うDBRecordクラスを足回りとして利用しています。

### オブジェクトの作成 ###
 $record = new DBRecord( PROGRAM_TBL|CATEGORY_TBL|CHANNEL_TBL|KEYWORD_TBL|RESERVE_TBL
                        [,フィールド名 ,検索語句]
);

DBレコードに関連づけられたDBRecordオブジェクトを生成します。フィールド名と検索語句を指定すると、DBテーブルを検索して最初にヒットしたレコードと関連づけられたオブジェクトを返します。フィールド名と検索語句を省略すると新規レコードを作成して、そのオブジェクトを返します。

### レコードの読み書き ###
-プロパティに対するリード/ライトの形でレコードの読み書きを行います。

$record->フィールド名 = "foobar";	//書き込み
echo $record->フィールド名;			// 読み出し

・一括読みだし
$arr = $record->fetch_array("フィールド名", "検索語句"[,options] );

-検索語句がヒットしたレコードを配列に読み出します。

・レコードの削除
$record->delete();

・静的メソッド
$arr = createRecords( PROGRAM_TBL|CATEGORY_TBL|CHANNEL_TBL|KEYWORD_TBL|RESERVE_TBL
					 [,options] );
-テーブルの全レコードをDBRecordオブジェクト配列として返します（低速）。optionsにSELECT文のWHERE節を追加して絞り込むことが出来ます。optionsは"WHERE ..."と記述してください。

■ファイル群

DBRecord.class.php
-DBRecordクラス

Keyword.class.php
-キーワードレコードクラス（親：DBRecord）

Reservation.class.php
-予約クラス。静的メソッドsimple()、静的メソッドcustom()。

Settings.class.php
-設定の読み出し/保存を行うクラス（親：SimpleXML）

cancelReservation.php
-JavaScriptから呼ばれる予約取り消し

changeReservation.php
-JavaScriptから呼ばれる予約内容の更新

channelSetSID.php
-チャンネルに対応するSIDを更新する（JavaScriptから呼ばれる）

config.php.sample
-config.phpのサンプルファイル

customReservation.php
-詳細予約実行（JavaScriptから呼ばれる）

envSetting.php
-環境設定

getepg.php
-EPG取得スクリプト

index.php
-トップページ（番組表）

keywordTable.php
-キーワードの管理ページ

api.php
-saveSettings 設定の更新（設定ページから呼ばれる）
-channelInfo チャンネル情報を返す
-deleteKeyword キーワードの削除実行（keywordTable.phpから呼ばれる）

search.php
-番組検索ページ

recomplete.php
-録画終了フラグを立てるスクリプト

recordedTable.php
-録画済み一覧ページ

reservationTable.php
-予約一覧ページ

reservationform.php
-詳細予約のフォームを返す（JavaScriptから呼ばれる）

systemSetting.php
-システム設定ページ

install/grscan.php
-インストール：地上デジタルチャンネルスキャン（grscanが存在するときのみ）

install/step1.php
-インストール：ステップ1

install/step2.php
-インストール：ステップ2

install/step3.php
-インストール：ステップ3

install/step4.php
-インストール：ステップ4

install/step5.php
-インストール：ステップ5

