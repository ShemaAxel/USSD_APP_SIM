<?php

session_name('MOBILE_EMULATOR');
session_start();

@session_unset();
@session_destroy();
//mclub
$m = 0;
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // date in the past
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // always modified
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1 
header("Cache-Control: post-check=0, pre-check=0", false);
header("Cache-Control: private");
header("Pragma: no-cache"); // HTTP/1.0 

header("Location:createSession.php");
exit();
?>
