<?php

// ライブラリ

function toTimestamp( $string ) {
	sscanf( $string, "%4d-%2d-%2d %2d:%2d:%2d", $y, $mon, $day, $h, $min, $s );
	return mktime( $h, $min, $s, $mon, $day, $y );
}

function toDatetime( $timestamp ) {
	return date("Y-m-d H:i:s", $timestamp);
}


function jdialog( $message, $url = "index.php" ) {
    header( "Content-Type: text/html;charset=utf-8" );
    exit( "<script type=\"text/javascript\">\n" .
          "<!--\n".
         "alert(\"". $message . "\");\n".
         "window.open(\"".$url."\",\"_self\");".
         "// -->\n</script>" );
}

?>