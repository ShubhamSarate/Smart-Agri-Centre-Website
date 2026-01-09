<?php
// Temporary debug page - remove after use
session_start();
header('Content-Type: text/plain; charset=utf-8');
echo "SESSION DUMP:\n\n";
var_export($_SESSION);
echo "\n\nSERVER INFO:\n\n";
// show current script and cookies
echo 'SCRIPT: ' . $_SERVER['SCRIPT_NAME'] . "\n";
echo 'REMOTE_ADDR: ' . ($_SERVER['REMOTE_ADDR'] ?? 'n/a') . "\n";
echo "\nCookies:\n";
var_export($_COOKIE);

?>
