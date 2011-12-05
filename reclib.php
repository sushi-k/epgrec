<?php
// ライブラリ
function toDatetime($timestamp) {
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
