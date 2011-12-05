#!/usr/bin/php
<?php

include_once('config.php');
include_once(INSTALL_PATH.'/DBRecord.class.php');
include_once(INSTALL_PATH.'/reclib.php');
include_once(INSTALL_PATH.'/Settings.class.php');

$settings = Settings::factory();

try {

  $recs = DBRecord::createRecords(RESERVE_TBL );

// DB接続
  $dbh = mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );
  if( $dbh === false ) exit( "mysql connection fail" );
  $sqlstr = "use ".$settings->db_name;
  mysql_query( $sqlstr );
  $sqlstr = "set NAME utf8";
  mysql_query( $sqlstr );

  foreach( $recs as $rec ) {
	  $title = mysql_real_escape_string($rec->title)."(".date("Y/m/d", strtotime($rec->starttime)).")";
      $sqlstr = "update mt_cds_object set metadata='dc:description=".mysql_real_escape_string($rec->description)."&epgrec:id=".$rec->id."' where dc_title='".$rec->path."'";
      mysql_query( $sqlstr );
      $sqlstr = "update mt_cds_object set dc_title='".$title."' where dc_title='".$rec->path."'";
      mysql_query( $sqlstr );
  }
}
catch( Exception $e ) {
    exit( $e->getMessage() );
}
?>
