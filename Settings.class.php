<?php
include_once('config.php');

class Settings extends SimpleXMLElement {
	
	const CONFIG_XML = "/settings/config.xml";
	
	public static function factory() {
		
		if( file_exists( INSTALL_PATH . self::CONFIG_XML ) ) {
			$xmlfile = file_get_contents(INSTALL_PATH . self::CONFIG_XML);
			return new self($xmlfile);
		}
		else {
			// 初回起動
			$xmlfile = '<?xml version="1.0" encoding="UTF-8" ?><epgrec></epgrec>';
			$xml = new self($xmlfile);
			
			// 旧config.phpを読み取って設定
			if(defined("SPOOL") ) $xml->spool = SPOOL;
			else $xml->spool = "/video";
			
			if(defined("THUMBS") ) $xml->thumbs = THUMBS;
			else $xml->thumbs = "/thumbs";
			
			if(defined("INSTALL_URL")) $xml->install_url = INSTALL_URL;
			else $xml->install_url = "http://localhost/epgrec";
			
			if(defined("BS_TUNERS")) $xml->bs_tuners = BS_TUNERS;
			else $xml->bs_tuners = 0;
			
			if(defined("GR_TUNERS")) $xml->gr_tuners = GR_TUNERS;
			else $xml->gr_tuners = 1;

			if(defined("CS_REC_FLG")) $xml->cs_rec_flg = CS_REC_FLG;
			else $xml->cs_rec_flg = 0;
			
			if(defined("USE_KUROBON")) $xml->use_kurobon = USE_KUROBON ? 1 : 0;
			else $xml->use_kurobon = 0;
			
			if(defined("FORMER_TIME")) $xml->former_time = FORMER_TIME;
			else $xml->former_time = 20;
			
			if(defined("EXTRA_TIME")) $xml->extra_time = EXTRA_TIME;
			else $xml->extra_time = 0;
			
			if(defined("FORCE_CONT_REC")) $xml->force_cont_rec = FORCE_CONT_REC ? 1 : 0;
			else $xml->force_cont_rec = 0;
			
			if(defined("REC_SWITCH_TIME")) $xml->rec_switch_time = REC_SWITCH_TIME;
			else $xml->rec_switch_time = 5;
			
			if(defined("USE_THUMBS")) $xml->use_thumbs = USE_THUMBS ? 1 : 0;
			else $xml->use_thumbs = 0;
			
			if(defined("MEDIATOMB_UPDATE")) $xml->mediatomb_update = MEDIATOMB_UPDATE ? 1 : 0;
			else $xml->mediatomb_update = 0;
			
			if(defined("FILENAME_FORMAT")) $xml->filename_format = FILENAME_FORMAT;
			else $xml->filename_format = "%TYPE%%CH%_%ST%_%ET%";
			
			if(defined("DB_HOST")) $xml->db_host = DB_HOST;
			else $xml->db_host = "localhost";
			
			if(defined("DB_NAME")) $xml->db_name = DB_NAME;
			else $xml->db_name = "yourdbname";
			
			if(defined("DB_USER")) $xml->db_user = DB_USER;
			else $xml->db_user = "yourname";
			
			if(defined("DB_PASS")) $xml->db_pass = DB_PASS;
			else $xml->db_pass = "yourpass";
			
			if(defined("TBL_PREFIX")) $xml->tbl_prefix = TBL_PREFIX;
			else $xml->tbl_prefix = "Recorder_";

			if(defined("EPGDUMP")) $xml->epgdump = EPGDUMP;
			else $xml->epgdump = "/usr/local/bin/epgdump";
			
			if(defined("AT")) $xml->at = AT;
			else $xml->at = "/usr/bin/at";
			
			if(defined( "ATRM" )) $xml->atrm = ATRM;
			else $xml->atrm = "/usr/bin/atrm";

			if(defined( "SLEEP" )) $xml->sleep = SLEEP;
			else $xml->sleep = "/bin/sleep";
			
			if(defined( "FFMPEG" )) $xml->ffmpeg = FFMPEG;
			else $xml->ffmpeg = "/usr/bin/ffmpeg";
			
			if(defined("TEMP_DATA" )) $xml->temp_data = TEMP_DATA;
			else $xml->temp_data = "/tmp/__temp.ts";
			
			if(defined("TEMP_XML")) $xml->temp_xml = TEMP_XML;
			else $xml->temp_xml = "/tmp/__temp.xml";
			
			// index.phpで使う設定値
			// 表示する番組表の長さ（時間）
			$xml->program_length = 8;
			// 1局の幅
			$xml->ch_set_width = 150;
			// 1分あたりの高さ
			$xml->height_per_hour = 120;
			
			$xml->save();
			
			return $xml;
		}
	}
	
	public function exists( $property ) {
		return (int)count( $this->{$property} );
	}
	
	public function post() {
		global $_POST;
		
		foreach( $_POST as $key => $value ) {
			
			if( $this->exists($key) ) {
				$this->{$key} = trim($value);
			}
		}
	}
	
	public function save() {
		$this->asXML(INSTALL_PATH . self::CONFIG_XML);
	}
}
?>
