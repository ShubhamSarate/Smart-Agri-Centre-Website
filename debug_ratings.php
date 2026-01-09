<?php
session_start();
require 'db.php';

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 1) {
    echo "Access denied. User not logged in or not a buyer.";
    exit();
}

$bid = $_SESSION['id'];

echo "<h2>Debug: Rating Query Results for User ID: $bid</h2>";

// Get user's purchases
$purchaseSql = "SELECT DISTINCT t.pid, fp.product, fp.pcat FROM transaction t 
                JOIN fproduct fp ON t.pid = fp.pid 
                WHERE t.bid = $bid";
$purchaseRes = mysqli_query($conn, $purchaseSql);
echo "<h3>User's Purchased Products:</h3>";
while($p = mysqli_fetch_assoc($purchaseRes)) {
    echo "PID: {$p['pid']}, Product: {$p['product']}, Category: {$p['pcat']}<br>";
}

// Get all reviews for those products
$reviewSql = "SELECT r.pid, r.rating, r.name, fp.product, fp.pcat 
              FROM review r 
              JOIN fproduct fp ON r.pid = fp.pid
              WHERE fp.pcat IN (
                SELECT DISTINCT fp.pcat FROM transaction t
                JOIN fproduct fp ON t.pid = fp.pid
                WHERE t.bid = $bid
              )
              ORDER BY fp.pcat, r.rating DESC";
$reviewRes = mysqli_query($conn, $reviewSql);
echo "<h3>Reviews for User's Product Categories:</h3>";
while($r = mysqli_fetch_assoc($reviewRes)) {
    echo "Category: {$r['pcat']}, Product: {$r['product']}, Rating: {$r['rating']}/10 by {$r['name']}<br>";
}

// Get category ratings
$categoryRatingSql = "SELECT 
                        fp.pcat,
                        COALESCE(AVG(r.rating), 0) AS avg_rating,
                        COUNT(r.rating) AS review_count,
                        COUNT(DISTINCT t.pid) AS product_count
                    FROM transaction t
                    INNER JOIN fproduct fp ON t.pid = fp.pid
                    LEFT JOIN review r ON fp.pid = r.pid
                    WHERE t.bid = $bid
                    AND fp.pcat IS NOT NULL
                    GROUP BY fp.pcat
                    ORDER BY avg_rating DESC";
$categoryRatingResult = mysqli_query($conn, $categoryRatingSql);
echo "<h3>Category Average Ratings (Dashboard Query):</h3>";
while($row = mysqli_fetch_assoc($categoryRatingResult)) {
    echo "Category: {$row['pcat']}, Avg Rating: {$row['avg_rating']}, Review Count: {$row['review_count']}, Product Count: {$row['product_count']}<br>";
}

?>
