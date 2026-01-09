<?php
require 'db.php';
if ($conn) {
    echo "OK: connected to database\n";
} else {
    echo "ERROR: " . mysqli_connect_error() . "\n";
}
?>