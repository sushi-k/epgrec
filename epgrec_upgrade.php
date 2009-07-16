#!/usr/bin/php
<?php
include_once('config.php');

 $dbh = mysql_connect( DB_HOST, DB_USER, DB_PASS );
 if( $dbh !== false ) {
	
	$sqlstr = "use ".DB_NAME;
	mysql_query( $sqlstr );
	
	$sqlstr = "set NAMES 'utf8'";
	mysql_query( $sqlstr );
	
	// RESERVE_TBL
	// description -> text
	$sqlstr = "alter table ".TBL_PREFIX.RESERVE_TBL." modify description text default null;";
	mysql_query( $sqlstr );
	// path -> blob
	$sqlstr = "alter table ".TBL_PREFIX.RESERVE_TBL." modify path blob default null;";
	mysql_query( $sqlstr );
	
	// PROGRAM_TBL
	// descripton -> text
	$sqlstr = "alter table ".TBL_PREFIX.PROGRAM_TBL." modify description text default null;";
	mysql_query( $sqlstr );
 }
 else exit( "Can't connect DB\n");

?>