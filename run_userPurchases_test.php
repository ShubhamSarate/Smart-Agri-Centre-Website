<?php
session_start();
$_SESSION['id'] = 3; // test user id present in the sample DB
require 'db.php';
$colCheckSql = "SHOW COLUMNS FROM transaction LIKE 'quantity_purchased'";
$colRes = mysqli_query($conn, $colCheckSql);
if (!$colRes || mysqli_num_rows($colRes) == 0) {
    $alter = "ALTER TABLE transaction ADD COLUMN quantity_purchased INT DEFAULT 1";
    @mysqli_query($conn, $alter);
}

$userPurchasesSql = "SELECT 
                        t.tid,
                        t.pid,
                        t.quantity_purchased,
                        fp.product,
                        fp.pcat,
                        fp.price,
                        t.status,
                        COALESCE(DATE_FORMAT(t.created_at, '%Y-%m-%d %H:%i'), DATE_FORMAT(NOW(), '%Y-%m-%d')) AS purchase_date,
                        AVG(r.rating) AS product_rating,
                        COUNT(r.rating) AS review_count
                    FROM transaction t
                    LEFT JOIN fproduct fp ON t.pid = fp.pid
                    LEFT JOIN review r ON fp.pid = r.pid
                    WHERE t.bid = " . intval($_SESSION['id']) . "
                    GROUP BY t.tid
                    ORDER BY t.tid DESC
                    LIMIT 10";

$result = mysqli_query($conn, $userPurchasesSql);
if (!$result) {
    echo "ERROR: " . mysqli_error($conn) . "\n";
    exit(1);
}

echo "Query OK, rows: " . mysqli_num_rows($result) . "\n";
while ($r = mysqli_fetch_assoc($result)) {
    print_r($r);
}
?>