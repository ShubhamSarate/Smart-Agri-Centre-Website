<?php
session_start();
require 'db.php';

echo "<h2>Product Quantity Debug</h2>";

// Get all products and their quantities
$sql = "SELECT pid, product, pcat, quantity FROM fproduct ORDER BY product";
$result = mysqli_query($conn, $sql);

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>PID</th><th>Product</th><th>Category</th><th>Quantity</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>{$row['pid']}</td>";
    echo "<td>{$row['product']}</td>";
    echo "<td>{$row['pcat']}</td>";
    echo "<td><strong>{$row['quantity']}</strong></td>";
    echo "</tr>";
}
echo "</table>";

// Show recent transactions
echo "<h3>Recent Transactions (Last 10)</h3>";
$txnSql = "SELECT t.tid, t.bid, t.pid, t.quantity_purchased, fp.product, fp.quantity as current_qty, t.created_at FROM transaction t LEFT JOIN fproduct fp ON t.pid = fp.pid ORDER BY t.tid DESC LIMIT 10";
$txnResult = mysqli_query($conn, $txnSql);

echo "<table border='1' cellpadding='8'>";
echo "<tr><th>TID</th><th>Buyer ID</th><th>PID</th><th>Product</th><th>Qty Purchased</th><th>Current Stock</th><th>Date</th></tr>";
while($row = mysqli_fetch_assoc($txnResult)) {
    echo "<tr>";
    echo "<td>{$row['tid']}</td>";
    echo "<td>{$row['bid']}</td>";
    echo "<td>{$row['pid']}</td>";
    echo "<td>{$row['product']}</td>";
    echo "<td>{$row['quantity_purchased']}</td>";
    echo "<td>{$row['current_qty']}</td>";
    echo "<td>{$row['created_at']}</td>";
    echo "</tr>";
}
echo "</table>";

?>
