<?php
session_start();
require 'db.php';

// Admin-only access
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 0) {
    $_SESSION['message'] = "Admin access required for dashboard.";
    header('Location: Login/error.php');
    exit();
}

// Fetch analytics data directly (no HTTP call needed)
$analyticsData = null;
$error = null;

try {
    // Revenue Summary
    // Summary: use quantity_purchased to compute revenue and average order value
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
    
    // Top Products
    // Top products aggregated by total quantity sold (quantity_purchased)
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
    $topProducts = [];
    while ($row = mysqli_fetch_assoc($topRes)) {
        $topProducts[] = $row;
    }
    
    // Daily Sales Revenue
    // Get daily revenue breakdown for current month
    $currentMonth = date('Y-m');
    $dailySalesRes = mysqli_query($conn, "
        SELECT 
            COALESCE(DATE_FORMAT(t.created_at, '%d'), '01') AS day,
            COUNT(t.tid) AS order_count,
            COALESCE(SUM(t.quantity_purchased * fp.price), 0) as revenue
        FROM transaction t
        LEFT JOIN fproduct fp ON t.pid = fp.pid
        WHERE (t.created_at IS NULL OR DATE_FORMAT(t.created_at, '%Y-%m') = '$currentMonth')
        GROUP BY COALESCE(DATE_FORMAT(t.created_at, '%d'), '01')
        ORDER BY day ASC
    ");
    
    $dailyRevenueMap = [];
    while ($row = mysqli_fetch_assoc($dailySalesRes)) {
        $dayNum = intval($row['day']);
        $dailyRevenueMap[$dayNum] = round(floatval($row['revenue'] ?: 0), 2);
    }
    
    $salesByMonth = [];
    for($day = 1; $day <= 31; $day++) {
        $salesByMonth[] = [
            'day' => 'Day ' . sprintf('%02d', $day),
            'orders' => 0,
            'revenue' => isset($dailyRevenueMap[$day]) ? $dailyRevenueMap[$day] : 0
        ];
    }
    
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
    
    $expireSoon = [];
    $expired = [];
    
    while ($row = mysqli_fetch_assoc($expireSoonRes)) {
        $expireSoon[] = $row;
    }
    
    while ($row = mysqli_fetch_assoc($expiredRes)) {
        $expired[] = $row;
    }
    
    // Low Stock Items
    // Show products with low quantity from menu (quantity < 10)
    $lowStockRes = mysqli_query($conn, "
        SELECT 
            fp.pid,
            fp.product,
            fp.quantity,
            fp.price,
            COALESCE(SUM(t.quantity_purchased), 0) as recent_sales
        FROM fproduct fp
        LEFT JOIN transaction t ON fp.pid = t.pid
        WHERE fp.quantity < 10
        GROUP BY fp.pid, fp.product, fp.quantity, fp.price
        ORDER BY fp.quantity ASC
        LIMIT 5
    ");
    
    $lowStock = [];
    while ($row = mysqli_fetch_assoc($lowStockRes)) {
        $lowStock[] = $row;
    }
    
    // Build analytics array
    $analyticsData = [
        'summary' => [
            'total_orders' => intval($summary['total_orders']),
            'total_revenue' => floatval($summary['total_revenue']),
            'avg_order_value' => floatval($summary['avg_order_value']),
            'unique_products' => intval($summary['unique_products'])
        ],
        'top_products' => $topProducts,
        'sales_by_month' => $salesByMonth,
        'expiry_alerts' => [
            'expire_soon' => $expireSoon,
            'expired' => $expired
        ],
        'low_stock' => $lowStock,
        'generated_at' => date('c')
    ];
    
} catch (Exception $e) {
    $error = "Error loading analytics: " . htmlspecialchars($e->getMessage());
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Dashboard - AgroCulture</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/skel.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/style-xlarge.css" />
    <link rel="stylesheet" href="css/global-style.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <style>
        #header { background-color: #202222; color: #ffffff; cursor: default; height: 4.75em; line-height: 4.75em; width: 100%; z-index: 10000; }
        #header h1 { color: #ffffff; height: inherit; left: 2.5em; line-height: inherit; margin: 0; padding: 0; position: absolute; top: 0; }
        #header h1 a { font-size: 1.25em; }
        #header nav { height: inherit; line-height: inherit; position: absolute; right: 2.75em; top: 0; vertical-align: middle; }
        #header nav > ul { list-style: none; margin: 0; padding-left: 0; }
        #header nav > ul > li { border-radius: 4px; display: inline-block; margin-left: 1.5em; padding-left: 0; }
        #header nav > ul > li a { color: #cee8d8; display: inline-block; text-decoration: none; transition: color 0.2s ease-in-out; }
        #header nav > ul > li a:hover { color: #ffffff; }
        #header nav > ul > li:first-child { margin-left: 0; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px 0; }
        .wrapper { background: transparent !important; padding: 40px 0; }
        .container-fluid { max-width: 1300px; margin: 0 auto; padding: 0 20px; }
        .dashboard-card { background: white; border-radius: 15px; padding: 25px; margin-bottom: 25px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .dashboard-card h4 { color: #333; font-weight: 700; margin-bottom: 20px; display: flex; gap: 8px; align-items: center; }
        .metric-box { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-radius: 12px; padding: 25px; text-align: center; margin-bottom: 15px; box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3); }
        .metric-box h3 { margin: 0; font-size: 32px; font-weight: bold; }
        .metric-box p { margin: 10px 0 0 0; font-size: 14px; opacity: 0.95; }
        .metrics-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; margin-bottom: 30px; }
        @media (max-width: 1024px) {
            .metrics-row { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 768px) {
            .metrics-row { grid-template-columns: 1fr; }
        }
        .alert-item { padding: 15px; margin-bottom: 12px; border-left: 5px solid; border-radius: 4px; }
        .alert-expire-soon { border-left-color: #ff9800; background-color: #fff3e0; }
        .alert-expired { border-left-color: #f44336; background-color: #ffebee; }
        .chart-container { position: relative; height: 300px; margin-bottom: 30px; }
        table th { background: #667eea; color: white; font-weight: 600; text-align: center; }
        table td { color: #333; text-align: center; }
        .header-title { color: white; margin-bottom: 30px; font-size: 32px; font-weight: 700; display: flex; gap: 10px; align-items: center; }
        .row { margin: 0 -20px; }
        .col-md-3, .col-md-6, .col-md-12 { padding: 0 20px; margin-bottom: 20px; }
        
        @media (max-width: 768px) {
            .container-fluid { padding: 0 15px; }
            .row { margin: 0 -15px; }
            .col-md-3, .col-md-6, .col-md-12 { padding: 0 15px; }
            .dashboard-card { padding: 15px; }
            .metric-box h3 { font-size: 24px; }
        }
    </style>
</head>
<body>
<?php require 'menu.php'; ?>

<section class="wrapper">
    <div class="container-fluid">
        <h1 class="header-title"><i class="fa fa-bar-chart"></i> Admin Dashboard</h1>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php // (Reverted: removed sales prediction integration) ?>
    <?php if ($analyticsData): ?>

        <!-- Key Metrics Row - Single Line -->
        <div class="metrics-row">
            <div class="metric-box">
                <h3><?php echo intval($analyticsData['summary']['total_orders']); ?></h3>
                <p>Total Orders</p>
            </div>
            <div class="metric-box" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>₹<?php echo number_format($analyticsData['summary']['total_revenue'], 2); ?></h3>
                <p>Total Revenue</p>
            </div>
            <div class="metric-box" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>₹<?php echo number_format($analyticsData['summary']['avg_order_value'], 2); ?></h3>
                <p>Avg Order Value</p>
            </div>
            <div class="metric-box" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <h3><?php echo intval($analyticsData['summary']['unique_products']); ?></h3>
                <p>Products Sold</p>
            </div>
        </div>

        <!-- Charts Row - Single Line -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div class="dashboard-card">
                <h4>Daily Sales Revenue</h4>
                <div class="chart-container">
                    <canvas id="monthlySalesChart"></canvas>
                </div>
            </div>
            <div class="dashboard-card">
                <h4>Top Products by Sales</h4>
                <div class="chart-container">
                    <canvas id="topProductsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Alerts Section - Single Line -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
            <div class="dashboard-card">
                <h4>🚨 Expiry Alerts</h4>
                <?php if (empty($analyticsData['expiry_alerts']['expire_soon']) && empty($analyticsData['expiry_alerts']['expired'])): ?>
                    <p style="color: #28a745;">✓ All products are fresh!</p>
                <?php else: ?>
                    <?php foreach ($analyticsData['expiry_alerts']['expire_soon'] as $item): ?>
                        <div class="alert-item alert-expire-soon">
                            <strong><?php echo htmlspecialchars($item['product']); ?></strong>
                            expires in <?php echo intval($item['days_left']); ?> days
                            (<?php echo htmlspecialchars($item['expiry_date']); ?>)
                        </div>
                    <?php endforeach; ?>
                    <?php foreach ($analyticsData['expiry_alerts']['expired'] as $item): ?>
                        <div class="alert-item alert-expired">
                            <strong><?php echo htmlspecialchars($item['product']); ?></strong>
                            EXPIRED <?php echo intval($item['days_overdue']); ?> days ago
                            (<?php echo htmlspecialchars($item['expiry_date']); ?>)
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <div class="dashboard-card">
                <h4>📦 Low Stock Items</h4>
                <?php if (empty($analyticsData['low_stock'])): ?>
                    <p style="color: #28a745;">No low stock products.</p>
                <?php else: ?>
                    <table class="table table-sm">
                        <thead>
                            <tr><th>Product</th><th>Quantity</th><th>Price</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($analyticsData['low_stock'], 0, 5) as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product']); ?></td>
                                    <td><strong style="color: <?php echo $item['quantity'] < 5 ? '#d32f2f' : '#ff9800'; ?>;"><?php echo intval($item['quantity']); ?></strong></td>
                                    <td>₹<?php echo number_format($item['price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products Table -->
        <div class="row">
            <div class="col-md-12">
                <div class="dashboard-card">
                    <h4>Top 10 Products</h4>
                    <table class="table table-striped table-hover">
                        <thead style="background-color: #f9f9f9;">
                            <tr>
                                <th>Product Name</th>
                                <th>Units Sold</th>
                                <th>Total Revenue</th>
                                <th>Avg Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($analyticsData['top_products'] as $product): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($product['product']); ?></td>
                                    <td><?php echo intval($product['sales_count']); ?></td>
                                    <td>₹<?php echo number_format($product['total_revenue'], 2); ?></td>
                                    <td>₹<?php echo number_format($product['avg_price'], 2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Charts JavaScript -->
        <script>
            // Daily Sales Chart
            const dailySalesData = <?php echo json_encode(array_map(function($d) { 
                return ['day' => $d['day'], 'revenue' => $d['revenue']]; 
            }, $analyticsData['sales_by_month']), JSON_UNESCAPED_SLASHES); ?>;

            const monthlyCtx = document.getElementById('monthlySalesChart').getContext('2d');
            new Chart(monthlyCtx, {
                type: 'line',
                data: {
                    labels: dailySalesData.map(d => d.day),
                    datasets: [{
                        label: 'Daily Revenue (₹)',
                        data: dailySalesData.map(d => d.revenue),
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        tension: 0.3,
                        fill: true,
                        pointRadius: 5,
                        pointBackgroundColor: '#667eea'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            ticks: {
                                callback: function(value) {
                                    return '₹' + value.toLocaleString('en-IN', {maximumFractionDigits: 0});
                                }
                            }
                        }
                    },
                    plugins: { legend: { display: true } }
                }
            });

            // Top Products Chart
            const topProductsData = <?php echo json_encode(array_slice($analyticsData['top_products'], 0, 8), JSON_UNESCAPED_SLASHES); ?>;

            const topCtx = document.getElementById('topProductsChart').getContext('2d');
            new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels: topProductsData.map(p => p.product.substring(0, 15)),
                        datasets: [{
                        label: 'Units Sold',
                        data: topProductsData.map(p => p.sales_count),
                        backgroundColor: [
                            '#667eea', '#764ba2', '#f093fb', '#f5576c',
                            '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: { legend: { display: false } }
                }
            });
        </script>

    <?php else: ?>
        <div class="alert alert-warning">No data available. Please check that the database connection is working.</div>
    <?php endif; ?>

    <hr style="margin-top: 40px;">
    <a href="adminOrders.php" class="button" style="display: inline-flex !important; align-items: center; justify-content: center; gap: 8px;"><i class="fa fa-arrow-left"></i> Back to Orders</a>
    </div>
</section>

</body>
</html>
