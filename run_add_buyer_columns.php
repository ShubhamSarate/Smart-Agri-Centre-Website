<?php
require 'db.php';

$cols = [
    "bcity" => "ALTER TABLE buyer ADD COLUMN bcity VARCHAR(255) DEFAULT ''",
    "bpincode" => "ALTER TABLE buyer ADD COLUMN bpincode VARCHAR(50) DEFAULT ''"
];

foreach ($cols as $col => $sql) {
    $res = @mysqli_query($conn, "SHOW COLUMNS FROM buyer LIKE '$col'");
    if (!$res || mysqli_num_rows($res) == 0) {
        $ok = @mysqli_query($conn, $sql);
        echo "$col: " . ($ok ? "created\n" : (mysqli_error($conn) . "\n"));
    } else {
        echo "$col: exists\n";
    }
}

?>