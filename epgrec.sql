
CREATE TABLE IF NOT EXISTS Recorder_channelTbl (
	channel_disc VARCHAR(128) NOT NULL,
	type varchar(8) NOT NULL,
	channel SMALLINT UNSIGNED NOT NULL,
	name TEXT NOT NULL,
	sid varchar(64) not null default 'hd',
	PRIMARY KEY(channel_disc)
)ENGINE=InnoDB;

-- DROP TABLE Recorder_categoryTbl;
CREATE TABLE Recorder_categoryTbl(
	category_disc varchar(128) NOT NULL PRIMARY KEY,
	name_jp varchar(128) NOT NULL,
	name_en varchar(128) NOT NULL
)ENGINE=InnoDB;
