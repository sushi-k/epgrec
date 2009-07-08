<?php
include_once('config.php');
include_once( INSTALL_PATH . "/DBRecord.class.php" );
include_once( INSTALL_PATH . "/reclib.php" );
include_once( INSTALL_PATH . "/Reservation.class.php" );

class Keyword extends DBRecord {
	
	public function __construct($property = null, $value = null ) {
		try {
			parent::__construct(TBL_PREFIX.KEYWORD_TBL, $property, $value );
		}
		catch( Exception $e ) {
			throw $e;
		}
	}
	
	private function getPrograms() {
		if( $this->id == 0 ) return false;
		
		// ちょっと先を検索する
		$options = " WHERE starttime > '".date("Y-m-d H:i:s", time() + PADDING_TIME + 120 )."'";
		
		if( $this->keyword != "" ) {
			if( $this->use_regexp ) {
				$options .= " AND CONCAT(title,description) REGEXP '".mysql_real_escape_string($this->keyword)."'";
			}
			else {
				$options .= " AND CONCAT(title,description) like '%".mysql_real_escape_string($this->keyword)."%'";
			}
		}
		
		if( $this->type != "*" ) {
			$options .= " AND type = '".$this->type."'";
		}
		
		if( $this->category_id != 0 ) {
			$options .= " AND category_id = '".$this->category_id."'";
		}
		
		if( $this->channel_id != 0 ) {
			$options .= " AND channel_id = '".$this->channel_id."'";
		}
		
		$options .= " ORDER BY starttime ASC";
		
		$recs = array();
		try {
			$recs = DBRecord::createRecords( TBL_PREFIX.PROGRAM_TBL, $options );
		}
		catch( Exception $e ) {
			throw $e;
		}
		
		return $recs;
	}
	
	
	public function reservation() {
		if( $this->id == 0 ) return;
		
		$precs = array();
		try {
			$precs = $this->getPrograms();
		}
		catch( Exception $e ) {
			throw $e;
		}
		if( count($precs) < 300 ) {
			// 一気に録画予約
			foreach( $precs as $rec ) {
				try {
					if( $rec->autorec ) {
						Reservation::simple( $rec->id, $this->id );
						usleep( 100 );		// あんまり時間を空けないのもどう?
					}
				}
				catch( Exception $e ) {
					// 無視
				}
			}
		}
		else {
			throw new Exception( "300件以上の自動録画は実行できません" );
		}
	}
	
	public function delete() {
		if( $this->id == 0 ) return;
		
		$precs = array();
		try {
			$precs = $this->getPrograms();
		}
		catch( Exception $e ) {
			throw $e;
		}
		// 一気にキャンセル
		foreach( $precs as $rec ) {
			try {
				$reserve = new DBRecord( TBL_PREFIX.RESERVE_TBL, "program_id", $rec->id );
				// 自動予約されたもののみ削除
				if( $reserve->autorec ) {
					Reservation::cancel( $reserve->id );
					usleep( 100 );		// あんまり時間を空けないのもどう?
				}
			}
			catch( Exception $e ) {
				// 無視
			}
		}
		try {
			parent::delete();
		}
		catch( Exception $e ) {
			throw $e;
		}
	}

	// staticなファンクションはオーバーライドできない
	static function createKeywords( $options = "" ) {
		$retval = array();
		$arr = array();
		try{
			$tbl = new self();
			$sqlstr = "SELECT * FROM ".$tbl->table." " .$options;
			$result = $tbl->__query( $sqlstr );
		}
		catch( Exception $e ) {
			throw $e;
		}
		if( $result === false ) throw new exception("レコードが存在しません");
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			array_push( $retval, new self('id', $row['id']) );
		}
		return $retval;
	}
	
	public function __destruct() {
		parent::__destruct();
	}
}
?>