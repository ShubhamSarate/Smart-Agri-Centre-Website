<?php
// Post a new comment (called by React)
require 'db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 1) {
    echo json_encode(['ok' => false, 'error' => 'not_logged_in']);
    exit;
}

$blogId = isset($_POST['blogId']) ? intval($_POST['blogId']) : 0;
$comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

if ($blogId <= 0 || $comment === '') {
    echo json_encode(['ok' => false, 'error' => 'invalid_input']);
    exit;
}

// Filter: strip tags but do not double-encode
$comment = strip_tags($comment);
$commentUser = $_SESSION['Username'] ?? 'Anonymous';
$pic = $_SESSION['picName'] ?? 'profile0.png';

// Ensure commentTime column exists
$colCheckSql = "SHOW COLUMNS FROM blogfeedback LIKE 'commentTime'";
$colRes = mysqli_query($conn, $colCheckSql);
if (!$colRes || mysqli_num_rows($colRes) == 0) {
    $alter = "ALTER TABLE blogfeedback ADD COLUMN commentTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    mysqli_query($conn, $alter);
}

// Insert comment
$stmt = mysqli_prepare($conn, "INSERT INTO blogfeedback (blogId, comment, commentUser, commentPic, commentTime) VALUES (?, ?, ?, ?, NOW())");
mysqli_stmt_bind_param($stmt, 'isss', $blogId, $comment, $commentUser, $pic);
$res = mysqli_stmt_execute($stmt);

if (!$res) {
    echo json_encode(['ok' => false, 'error' => mysqli_error($conn)]);
    mysqli_stmt_close($stmt);
    exit;
}

$insertId = mysqli_insert_id($conn);
mysqli_stmt_close($stmt);

// Get comment count
$countRes = mysqli_query($conn, "SELECT COUNT(*) as cnt FROM blogfeedback WHERE blogId = $blogId");
$cnt = 0;
if ($countRes) {
    $r = mysqli_fetch_assoc($countRes);
    $cnt = (int)$r['cnt'];
}

// Return the new comment object
echo json_encode([
    'ok' => true,
    'comment' => [
        'id' => md5($blogId . date('Y-m-d H:i:s')),
        'blogId' => $blogId,
        'comment' => htmlspecialchars($comment),
        'commentUser' => htmlspecialchars($commentUser),
        'commentPic' => htmlspecialchars($pic),
        'commentTime' => date('g:i a')
    ],
    'count' => $cnt
]);
exit;
?>
