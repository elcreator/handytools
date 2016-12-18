<?php
// === Config ===
$db_host = 'localhost';
$db_user = '';
$db_pass = '';
$db_name = '';

$db_charset = 'utf8';
// ==============


// === Main logic ===
mysql_connect($db_host, $db_user, $db_pass);
mysql_select_db($db_name);
mysql_query('SET CHARACTER SET ' . $db_charset);
if (mysql_errno()) die ('DB connection error.');

$NoCache = '';
// ==================
?>