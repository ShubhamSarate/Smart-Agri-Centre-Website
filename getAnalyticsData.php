<?php
/**
 * Analytics Data API
 * Returns JSON analytics data for the dashboard
 * Directly queries MySQL without Python dependencies
 */

header('Content-Type: application/json');
session_start();
require 'db.php';

// Admin-only
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 0) {
    echo json_encode(['error' => 'Admin access required']);
    exit();
}

$analytics = [];

// Revenue Summary
$summaryRes = mysqli_query($conn, "
    SELECT 
        COUNT(*) as total_orders,
        COALESCE(SUM(t.quantity_purchased * fp.price), 0) as total_revenue,
        CASE WHEN COUNT(*) > 0 THEN COALESCE(SUM(t.quantity_purchased * fp.price) / COUNT(*), 0) ELSE 0 END as avg_order_value,
        COUNT(DISTINCT t.pid) as unique_products
    FROM transaction t
    LEFT JOIN fproduct fp ON t.pid = fp.pid
");
$summary = mysqli_fetch_assoc($summaryRes);
$analytics['summary'] = [
    'total_orders' => intval($summary['total_orders']),
    'total_revenue' => floatval($summary['total_revenue']),
    'avg_order_value' => floatval($summary['avg_order_value']),
    'unique_products' => intval($summary['unique_products'])
];

// Top Products
$topRes = mysqli_query($conn, "
    SELECT 
        fp.product,
        fp.pid,
        COALESCE(SUM(t.quantity_purchased), 0) as sales_count,
        COALESCE(SUM(t.quantity_purchased * fp.price), 0) as total_revenue,
        COALESCE(AVG(fp.price), 0) as avg_price
    FROM transaction t
    JOIN fproduct fp ON t.pid = fp.pid
    GROUP BY fp.pid, fp.product
    ORDER BY sales_count DESC
    LIMIT 10
");
$analytics['top_products'] = [];
while ($row = mysqli_fetch_assoc($topRes)) {
    $analytics['top_products'][] = [
        'product' => $row['product'],
        'pid' => intval($row['pid']),
        'sales_count' => intval($row['sales_count']),
        'total_revenue' => floatval($row['total_revenue']),
        'avg_price' => floatval($row['avg_price'])
    ];
}

// Sales by Month (approximate using current data)
// Note: transaction table doesn't have timestamp, so we approximate
$salesRes = mysqli_query($conn, "
    SELECT COUNT(*) as order_count, COALESCE(SUM(t.quantity_purchased * fp.price), 0) as revenue
    FROM transaction t
    LEFT JOIN fproduct fp ON t.pid = fp.pid
");
$salesRow = mysqli_fetch_assoc($salesRes);
$analytics['sales_by_month'] = [
    [
        'month' => date('Y-m'),
        'orders' => intval($salesRow['order_count']),
        'revenue' => floatval($salesRow['revenue'] ?: 0)
    ]
];

// Expiry Alerts
$today = date('Y-m-d');
$soonDate = date('Y-m-d', strtotime('+30 days'));

$expireSoonRes = mysqli_query($conn, "
    SELECT 
        pid, product, pcat, price, expiry_date,
        DATEDIFF(expiry_date, '$today') as days_left
    FROM fproduct
    WHERE expiry_date IS NOT NULL
        AND expiry_date > '$today'
        AND expiry_date <= '$soonDate'
    ORDER BY expiry_date ASC
");

$expiredRes = mysqli_query($conn, "
    SELECT 
        pid, product, pcat, price, expiry_date,
        DATEDIFF('$today', expiry_date) as days_overdue
    FROM fproduct
    WHERE expiry_date IS NOT NULL AND expiry_date <= '$today'
    ORDER BY expiry_date DESC
");

$analytics['expiry_alerts'] = [
    'expire_soon' => [],
    'expired' => []
];

while ($row = mysqli_fetch_assoc($expireSoonRes)) {
    $analytics['expiry_alerts']['expire_soon'][] = [
        'pid' => intval($row['pid']),
        'product' => $row['product'],
        'category' => $row['pcat'],
        'price' => floatval($row['price']),
        'expiry_date' => $row['expiry_date'],
        'days_left' => intval($row['days_left'])
    ];
}

while ($row = mysqli_fetch_assoc($expiredRes)) {
    $analytics['expiry_alerts']['expired'][] = [
        'pid' => intval($row['pid']),
        'product' => $row['product'],
        'category' => $row['pcat'],
        'price' => floatval($row['price']),
        'expiry_date' => $row['expiry_date'],
        'days_overdue' => intval($row['days_overdue'])
    ];
}

// Low Stock / High Volume Items
$lowStockRes = mysqli_query($conn, "
    SELECT 
        fp.pid,
        fp.product,
        COALESCE(SUM(t.quantity_purchased), 0) as recent_sales,
        fp.price
    FROM fproduct fp
    LEFT JOIN transaction t ON fp.pid = t.pid
    GROUP BY fp.pid, fp.product
    HAVING recent_sales > 3
    ORDER BY recent_sales DESC
    LIMIT 5
);

$analytics['low_stock'] = [];
while ($row = mysqli_fetch_assoc($lowStockRes)) {
    $analytics['low_stock'][] = [
        'pid' => intval($row['pid']),
        'product' => $row['product'],
        'recent_sales' => intval($row['recent_sales']),
        'price' => floatval($row['price'])
    ];
}

$analytics['generated_at'] = date('c');

echo json_encode($analytics, JSON_UNESCAPED_SLASHES);
?>
