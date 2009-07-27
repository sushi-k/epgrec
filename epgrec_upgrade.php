#!/usr/bin/php
<?php
include_once('config.php');
include_once(INSTALL_PATH . '/Settings.class.php' );

$settings = Settings::factory();

 $dbh = mysql_connect( $settings->db_host, $settings->db_user, $settings->db_pass );
 if( $dbh !== false ) {
	
	$sqlstr = "use ".$settings->db_name;
	mysql_query( $sqlstr );
	
	$sqlstr = "set NAMES 'utf8'";
	mysql_query( $sqlstr );
	
	// RESERVE_TBL
	// description -> text
	$sqlstr = "alter table ".RESERVE_TBL." modify description text default null;";
	mysql_query( $sqlstr );
	// path -> blob
	$sqlstr = "alter table ".RESERVE_TBL." modify path blob default null;";
	mysql_query( $sqlstr );
	
	// PROGRAM_TBL
	// descripton -> text
	$sqlstr = "alter table ".PROGRAM_TBL." modify description text default null;";
	mysql_query( $sqlstr );
 }
 else exit( "Can't connect DB\n");

?>