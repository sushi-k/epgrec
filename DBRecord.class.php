<?php
include_once( 'config.php' );
include_once( 'Settings.class.php' );

class DBRecord {
	protected $table;
	protected $settings;
	
	protected $dbh;
	public $id;
	
    function __construct( $table, $property = null, $value = null ) {
		$this->settings = Settings::factory();
		
		$this->table = $this->settings->tbl_prefix.$table;
		
		$this->dbh = @mysql_connect( $this->settings->db_host , $this->settings->db_user, $this->settings->db_pass );
		if( $this->dbh === FALSE ) throw new exception( "construct:データベースに接続できない" );
		
		$sqlstr = "use ".$this->settings->db_name;
		$res = $this->__query($sqlstr);
		if( $res === false ) throw new exception("construct: " . $sqlstr );
		$sqlstr = "set NAMES utf8";
		$res = $this->__query($sqlstr);

		if( ($property == null) || ($value == null) ) {
			// レコードを特定する要素が指定されない場合はid=0として空のオブジェクトを作成する
			$this->id = 0;
		}
		else {
			$sqlstr = "SELECT * FROM ".$this->table.
			            " WHERE ".mysql_real_escape_string( $property ).
			              "='".mysql_real_escape_string( $value )."'";
			
			$res = $this->__query( $sqlstr );
			$arr = mysql_fetch_array( $res , MYSQL_ASSOC );
			if( $arr === FALSE ) throw new exception( "construct:無効な行" );
			// 最初にヒットした行のidを使用する
			$this->id = $arr['id'];
		}
		
		return;
	}
	
	function createTable( $tblstring ) {
		$sqlstr = "use ".$this->settings->db_name;
		$res = $this->__query($sqlstr);
		if( $res === false ) throw new exception("createTable: " . $sqlstr );
		$sqlstr = "CREATE TABLE IF NOT EXISTS ".$this->table." (" .$tblstring.") DEFAULT CHARACTER SET 'utf8'";
		$result = $this->__query( $sqlstr );
		if( $result === false ) throw new exception( "createTable:テーブル作成失敗" );
	}
	
	protected function __query( $sqlstr ) {
		$res = @mysql_query( $sqlstr, $this->dbh );
		if( $res === FALSE ) throw new exception( "__query:DBクエリ失敗:".$sqlstr );
		return $res;
	}
	
	function fetch_array( $property , $value, $options = null ) {
		$retval = array();
		
		$sqlstr = "SELECT * FROM ".$this->table.
		            " WHERE ".mysql_real_escape_string( $property ).
		              "='".mysql_real_escape_string( $value )."'";
		
		if( $options != null ) {
			$sqlstr .= "AND ".$options;
		}
		$res = $this->__query( $sqlstr );
		while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
			array_push( $retval, $row );
		}
		
		return $retval;
	}
	
	function __set( $property, $value ) {
		if( $property == "id" ) throw new exception( "set:idの変更は不可" );
		// id = 0なら空の新規レコード作成
		if( $this->id == 0 ) {
			$sqlstr = "INSERT INTO ".$this->table." VALUES ( )";
			$res = $this->__query( $sqlstr );
			$this->id = mysql_insert_id();
		}
		$sqlstr = "UPDATE ".$this->table." SET ".
		             mysql_real_escape_string($property)."='".
		             mysql_real_escape_string($value)."' WHERE id='".$this->id."'";
		$res = $this->__query( $sqlstr );
		if( $res == FALSE )  throw new exception("set:セット失敗" );
	}
	
	function __get($property) {
		if( $this->id == 0 ) throw new exception( "get:無効なid" );
		if( $property == "id" ) return $this->id;
		
		$sqlstr = "SELECT ".mysql_real_escape_string($property)." FROM ".$this->table." WHERE id='".$this->id."'";
		$res = $this->__query($sqlstr);
		$arr = mysql_fetch_row( $res );
		if( $arr === FALSE ) throw new exception( "get:".$property."は存在しない" );
		
		return stripslashes($arr[0]);
	}
	
	function delete() {
		if( $this->id == 0 ) throw new exception( "delete:無効なid" );
		
		$sqlstr = "DELETE FROM ".$this->table." WHERE id='".$this->id."'";
		$this->__query( $sqlstr );
		$this->id = 0;
	}
	
	// countを実行する
	static function countRecords( $table, $options = "" ) {
		try{
			$tbl = new self( $table );
			$sqlstr = "SELECT COUNT(*) FROM " . $tbl->table ." " . $options;
			$result = $tbl->__query( $sqlstr );
		}
		catch( Exception $e ) {
			throw $e;
		}
		if( $result === false ) throw new exception("COUNT失敗");
		$retval = mysql_fetch_row( $result );
		return $retval[0];
	}
	
	// DBRecordオブジェクトを返すstaticなメソッド
	static function createRecords( $table, $options = "" ) {
		$retval = array();
		$arr = array();
		try{
			$tbl = new self( $table );
			$sqlstr = "SELECT * FROM ".$tbl->table." " .$options;
			$result = $tbl->__query( $sqlstr );
		}
		catch( Exception $e ) {
			throw $e;
		}
		if( $result === false ) throw new exception("レコードが存在しません");
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			array_push( $retval, new self( $table,  'id', $row['id'] ) );
		}
		return $retval;
	}
	
	function __destruct() {
		$this->id = 0;
	}
}
?>
