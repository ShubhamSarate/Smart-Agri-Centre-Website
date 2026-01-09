<?php
// Run this once to decode any HTML entities stored in blogfeedback.comment
// Usage: open in browser: http://localhost/Shubham/fix_decode_comments.php
require 'db.php';

set_time_limit(0);

$res = mysqli_query($conn, "SELECT blogId, comment, commentTime FROM blogfeedback");
$updated = 0;
if($res){
    while($row = mysqli_fetch_assoc($res)){
        $orig = $row['comment'];
        $decoded = html_entity_decode($orig, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        if($decoded !== $orig){
            $blogId = intval($row['blogId']);
            $time = mysqli_real_escape_string($conn, $row['commentTime']);
            $new = mysqli_real_escape_string($conn, $decoded);
            $u = "UPDATE blogfeedback SET comment = '$new' WHERE blogId = $blogId AND commentTime = '$time' LIMIT 1";
            if(mysqli_query($conn, $u)){
                $updated++;
            }
        }
    }
}

echo "Decoded and updated $updated comment(s).";

?>