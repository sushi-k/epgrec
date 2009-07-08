#!/usr/bin/php
<?php

  require_once( "config.php" );
  require_once( INSTALL_PATH. "/DBRecord.class.php" );
 
/* 
  $dbh = @mysql_connect( DB_HOST, DB_USER, DB_PASS );
  if( $dbh === false ) {
	exit("Can't connect DB\n" );
  }
  
  $sqlstr = "CREATE DATABASE IF NOT EXISTS ".DB_NAME;
  $result = mysql_query( $sqlstr );
  if( $result === false ) {
	exit( "Quary error\n" );
  }
*/ 
  try {
    $rec = new DBRecord( TBL_PREFIX . RESERVE_TBL );
    $rec->createTable( RESERVE_STRUCT );

    $rec = new DBRecord( TBL_PREFIX . PROGRAM_TBL );
    $rec->createTable( PROGRAM_STRUCT );

    $rec = new DBRecord( TBL_PREFIX . CHANNEL_TBL );
    $rec->createTable( CHANNEL_STRUCT );

    $rec = new DBRecord( TBL_PREFIX . CATEGORY_TBL );
    $rec->createTable( CATEGORY_STRUCT );
    
    $rec = new DBRecord( TBL_PREFIX . KEYWORD_TBL );
    $rec->createTable( KEYWORD_STRUCT );
  }
  catch( Exception $e ) {
    exit( $e->getMessage() );
  }
  exit( "Complete!\n");
?>
