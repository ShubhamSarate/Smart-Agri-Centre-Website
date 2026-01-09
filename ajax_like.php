<?php
// Post/increment a like (called by React or form)
require 'db.php';
session_start();

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] != 1) {
    echo json_encode(['ok' => false, 'error' => 'not_logged_in']);
    exit;
}

$blogId = isset($_POST['blogId']) ? intval($_POST['blogId']) : 0;

if ($blogId <= 0) {
    echo json_encode(['ok' => false, 'error' => 'invalid_blog_id']);
    exit;
}

$userId = $_SESSION['id'] ?? 0;

// Check if user already liked this blog
$checkSql = "SELECT * FROM likedata WHERE blogId = $blogId AND blogUserId = $userId";
$checkRes = mysqli_query($conn, $checkSql);
$alreadyLiked = mysqli_num_rows($checkRes) > 0;

if (!$alreadyLiked) {
    // Insert like
    $insertSql = "INSERT INTO likedata (blogId, blogUserId) VALUES ($blogId, $userId)";
    mysqli_query($conn, $insertSql);

    // Increment likes count on blog
    $updateSql = "UPDATE blogdata SET likes = likes + 1 WHERE blogId = $blogId";
    mysqli_query($conn, $updateSql);
}

// Get current likes count
$likeRes = mysqli_query($conn, "SELECT likes FROM blogdata WHERE blogId = $blogId");
$likes = 0;
if ($likeRes) {
    $r = mysqli_fetch_assoc($likeRes);
    $likes = (int)($r['likes'] ?? 0);
}

echo json_encode(['ok' => true, 'likes' => $likes]);
exit;
?>
