
CREATE TABLE IF NOT EXISTS Recorder_channelTbl (
	channel_disc VARCHAR(128) NOT NULL,
	type VARCHAR(8) NOT NULL,
	channel SMALLINT UNSIGNED NOT NULL,
	name TEXT NOT NULL,
	sid VARCHAR(64) NOT NULL DEFAULT 'hd',
	PRIMARY KEY(channel_disc)
)ENGINE=InnoDB;

-- DROP TABLE Recorder_categoryTbl;
CREATE TABLE IF NOT EXISTS Recorder_categoryTbl(
	category_disc VARCHAR(128) NOT NULL PRIMARY KEY,
	name_jp VARCHAR(128) NOT NULL,
	name_en VARCHAR(128) NOT NULL
)ENGINE=InnoDB;

-- DROP TABLE Recorder_programTbl;
CREATE TABLE IF NOT EXISTS Recorder_programTbl (
	program_disc VARCHAR(128) NOT NULL,				-- 識別用hash
	channel_disc VARCHAR(32) NOT NULL,				-- channel disc
	type VARCHAR(8) NOT NULL DEFAULT 'GR',				-- 種別（GR/BS/CS）
	channel VARCHAR(10) NOT NULL DEFAULT '0',			-- チャンネル
	title VARCHAR(512) NOT NULL DEFAULT 'none',			-- タイトル
	description VARCHAR(512) NOT NULL DEFAULT 'none',		-- 説明 text->VARCHAR
	category_disc VARCHAR(128) NOT NULL DEFAULT 'none',		-- カテゴリID
	starttime datetime NOT NULL DEFAULT '1970-01-01 00:00:00',	-- 開始時刻
	endtime datetime NOT NULL DEFAULT '1970-01-01 00:00:00',	-- 終了時刻
	autorec boolean NOT NULL DEFAULT '1',				-- 自動録画有効無効
	PRIMARY KEY(program_disc)
)ENGINE=InnoDB;

-- DROP TABLE IF EXISTS Recorder_reserveTbl;
CREATE TABLE IF NOT EXISTS Recorder_reserveTbl (
	program_disc VARCHAR(128) NOT NULL,		-- Program ID
	job INT UNSIGNED NOT NULL DEFAULT '0',		-- job番号
	complete boolean NOT NULL DEFAULT '0',		-- 完了フラグ
	autorec INT UNSIGNED NOT NULL DEFAULT '0',	-- キーワードID
	mode INT UNSIGNED NOT NULL DEFAULT '0',		-- 録画モード
	PRIMARY KEY(program_disc)
)ENGINE=InnoDB;

-- DROP TABLE IF EXISTS Recorder_keywordTbl;
CREATE TABLE IF NOT EXISTS Recorder_keywordTbl (
	id integer not null auto_increment primary key,
	keyword varchar(512) not null default '*',
	type varchar(8) not null default '*',
	channel_disc integer not null default '0',
	category_disc integer not null default '0',
	use_regexp boolean not null default '0',
	autorec_mode integer not null default '0',
	weekofday enum ('0','1','2','3','4','5','6','7' ) default '7'
)ENGINE=InnoDB;
