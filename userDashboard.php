<?php
session_start();
require 'db.php';

// Only allow logged-in users (non-admins)
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 1) {
    $_SESSION['message'] = "You need to be logged in as a User to view this dashboard.";
    header('Location: Login/error.php');
    exit();
}

// Ensure transaction table has status column
$colCheckSql = "SHOW COLUMNS FROM transaction LIKE 'status'";
$colRes = mysqli_query($conn, $colCheckSql);
if (!$colRes || mysqli_num_rows($colRes) == 0) {
    $alter = "ALTER TABLE transaction ADD COLUMN status VARCHAR(50) DEFAULT 'pending'";
    @mysqli_query($conn, $alter);
}

// Ensure transaction table has created_at column for purchase date
$colCheckSql = "SHOW COLUMNS FROM transaction LIKE 'created_at'";
$colRes = mysqli_query($conn, $colCheckSql);
if (!$colRes || mysqli_num_rows($colRes) == 0) {
    $alter = "ALTER TABLE transaction ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
    @mysqli_query($conn, $alter);
}

// Ensure fproduct table has expiry_date column
$colCheckSql = "SHOW COLUMNS FROM fproduct LIKE 'expiry_date'";
$colRes = mysqli_query($conn, $colCheckSql);
if (!$colRes || mysqli_num_rows($colRes) == 0) {
    $alter = "ALTER TABLE fproduct ADD COLUMN expiry_date DATE";
    @mysqli_query($conn, $alter);
}

// Ensure transaction table has quantity_purchased column
$colCheckSql = "SHOW COLUMNS FROM transaction LIKE 'quantity_purchased'";
$colRes = mysqli_query($conn, $colCheckSql);
if (!$colRes || mysqli_num_rows($colRes) == 0) {
    $alter = "ALTER TABLE transaction ADD COLUMN quantity_purchased INT DEFAULT 1";
    @mysqli_query($conn, $alter);
}

// Get all products with ratings, sorted by expiry date
$sql = "SELECT 
            fp.pid, 
            fp.product, 
            fp.pcat, 
            fp.price, 
            fp.pimage, 
            fp.expiry_date,
            fp.quantity,
            AVG(r.rating) AS avg_rating,
            COUNT(r.rating) AS review_count,
            COALESCE(AVG(r.rating), 0) AS rating_score
        FROM fproduct fp
        LEFT JOIN review r ON fp.pid = r.pid
        GROUP BY fp.pid
        ORDER BY fp.expiry_date ASC, COALESCE(AVG(r.rating), 0) DESC";

$result = mysqli_query($conn, $sql);

// Get monthly product purchase statistics
$monthlySQL = "SELECT 
                DATE_FORMAT(FROM_UNIXTIME(UNIX_TIMESTAMP(CURDATE()) - INTERVAL DAY(CURDATE())-1 DAY - INTERVAL ROW_NUMBER() OVER() - 1 DAY), '%Y-%m') AS month,
                MONTH(CURDATE()) AS current_month,
                COUNT(DISTINCT t.tid) AS purchase_count
            FROM transaction t
            WHERE t.bid = " . intval($_SESSION['id']) . "
            GROUP BY MONTH(t.tid)
            ORDER BY MONTH(t.tid) DESC
            LIMIT 12";

// Simpler query for monthly purchases
$monthlySql = "SELECT 
                DATE_FORMAT(NOW() - INTERVAL n MONTH, '%Y-%m') AS month,
                (SELECT COUNT(*) FROM transaction WHERE bid = " . intval($_SESSION['id']) . " AND YEAR(CURDATE()) = YEAR(DATE_FORMAT(NOW() - INTERVAL n MONTH, '%Y-%m-01')) AND MONTH(CURDATE()) = MONTH(DATE_FORMAT(NOW() - INTERVAL n MONTH, '%Y-%m-01'))) AS purchases
            FROM (SELECT 0 AS n UNION SELECT 1 UNION SELECT 2 UNION SELECT 3 UNION SELECT 4 UNION SELECT 5) AS months
            ORDER BY month DESC";

// Get user's purchase history with product ratings and the purchased quantity
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

$purchasesResult = mysqli_query($conn, $userPurchasesSql);

// Get product categories for pie chart
// Normalize category values: trim whitespace and treat empty/NULL as 'Unknown'
$categorySql = "SELECT 
                    COALESCE(NULLIF(TRIM(fp.pcat), ''), 'Unknown') AS pcat,
                    COUNT(DISTINCT t.tid) AS purchase_count
                FROM transaction t
                LEFT JOIN fproduct fp ON t.pid = fp.pid
                WHERE t.bid = " . intval($_SESSION['id']) . "
                GROUP BY COALESCE(NULLIF(TRIM(fp.pcat), ''), 'Unknown')
                ORDER BY purchase_count DESC";

$categoryResult = mysqli_query($conn, $categorySql);
$categoryData = [];
$categoryLabels = [];
while($row = mysqli_fetch_assoc($categoryResult)) {
    $label = isset($row['pcat']) ? trim($row['pcat']) : '';
    // Rename placeholder 'Unknown' to 'Tool' per request
    if ($label === '' || strtolower($label) === 'unknown') {
        $label = 'Tool';
    }
    $categoryLabels[] = $label;
    $categoryData[] = (int)$row['purchase_count'];
}

// (No filtering here) Keep all categories returned by the query so
// categories like 'Tool' are displayed in the chart.

// Get average rating by category (for user's purchased products only)
// Show ratings only for products this user purchased
$categoryRatingSql = "SELECT 
                        COALESCE(NULLIF(TRIM(fp.pcat), ''), 'Unknown') AS pcat,
                        COALESCE(AVG(r.rating), 0) AS avg_rating,
                        COUNT(r.rating) AS review_count,
                        COUNT(DISTINCT t.pid) AS product_count
                    FROM transaction t
                    INNER JOIN fproduct fp ON t.pid = fp.pid
                    LEFT JOIN review r ON fp.pid = r.pid
                    WHERE t.bid = " . intval($_SESSION['id']) . "
                    GROUP BY COALESCE(NULLIF(TRIM(fp.pcat), ''), 'Unknown')
                    ORDER BY avg_rating DESC";

$categoryRatingResult = mysqli_query($conn, $categoryRatingSql);
$ratingLabels = [];
$ratingData = [];
while($row = mysqli_fetch_assoc($categoryRatingResult)) {
    $rlabel = isset($row['pcat']) ? trim($row['pcat']) : '';
    // Rename placeholder 'Unknown' to 'Tool' for ratings chart as well
    if ($rlabel === '' || strtolower($rlabel) === 'unknown') {
        $rlabel = 'Tool';
    }
    $ratingLabels[] = $rlabel;
    $ratingData[] = round((float)$row['avg_rating'], 2);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard - AgroCulture</title>
    <meta http-equiv="content-type" content="text/html; charset=utf-8" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
    <link rel="stylesheet" href="login.css"/>
    <script src="js/jquery.min.js"></script>
    <script src="js/skel.min.js"></script>
    <script src="js/skel-layers.min.js"></script>
    <script src="js/init.js"></script>
    <noscript>
        <link rel="stylesheet" href="css/skel.css" />
        <link rel="stylesheet" href="css/style.css" />
        <link rel="stylesheet" href="css/style-xlarge.css" />
    </noscript>
    <!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
	    <link rel="stylesheet" href="css/global-style.css" />
	    <link rel="stylesheet" href="css/font-awesome.min.css">
	    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .wrapper { background: transparent !important; }
        .container-card { background: white; border-radius: 12px; padding: 28px; box-shadow: 0 10px 40px rgba(0,0,0,0.12); }
        .dashboard-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #000;
            padding: 30px 0;
            margin-bottom: 30px;
            text-align: center;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .kpi-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        .kpi-value {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin: 10px 0;
        }
        .kpi-label {
            font-size: 1em;
            color: #666;
            text-transform: uppercase;
        }
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            color: #000;
        }
        .chart-container h4 {
            color: #000;
            font-weight: 600;
        }
        /* Charts Row - Side by side */
        .charts-row {
            display: flex;
            gap: 20px;
            flex-wrap: nowrap;
            justify-content: space-between;
        }
        .charts-row .col-md-6 {
            flex: 1;
            min-width: 0;
        }
        @media (max-width: 992px) {
            .charts-row {
                flex-wrap: wrap;
            }
            .charts-row .col-md-6 {
                flex: 0 0 100%;
            }
        }
        .product-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            border-left: 4px solid #667eea;
            transition: transform 0.3s ease;
        }
        .product-card:hover {
            transform: translateX(5px);
        }
        .product-image {
            max-width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 15px;
        }
        .rating-badge {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .expiry-badge {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .expiry-soon {
            background-color: #f8d7da;
            color: #721c24;
        }
        .expiry-far {
            background-color: #d4edda;
            color: #155724;
        }
        .section-title {
            font-size: 1.5em;
            color: #333;
            margin-bottom: 20px;
            font-weight: bold;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .no-data {
            text-align: center;
            color: #999;
            padding: 30px;
        }
        /* KPI Row - Force single line */
        .kpi-row {
            display: flex;
            gap: 15px;
            flex-wrap: nowrap;
            justify-content: space-between;
            align-items: stretch;
        }
        .kpi-row .col-md-3 {
            flex: 1;
            min-width: 0;
        }
        .kpi-card {
            height: 100%;
        }
        @media (max-width: 992px) {
            .kpi-row {
                flex-wrap: wrap;
            }
            .kpi-row .col-md-3 {
                flex: 0 0 calc(50% - 8px);
            }
        }
        @media (max-width: 576px) {
            .kpi-row .col-md-3 {
                flex: 0 0 100%;
            }
        }
        /* Products grid: 3 columns on desktop, 2 on tablet, 1 on mobile */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            align-items: start;
        }
        @media (max-width: 992px) {
            .products-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 576px) {
            .products-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
<?php require 'menu.php'; ?>

<section class="container container-card" style="margin-top:30px;">
    <div class="dashboard-header">
        <h2 style="color: #000;"><i class="fa fa-dashboard"></i> User Dashboard</h2>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION['Name']); ?>! Explore products and your purchase analytics.</p>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <!-- Debug block removed -->

    <!-- KPI Cards -->
    <div class="row kpi-row">
        <div class="col-md-3">
            <div class="kpi-card">
                <i class="fa fa-shopping-cart" style="font-size: 2.5em; color: #667eea;"></i>
                <div class="kpi-label">Total Purchases</div>
                <div class="kpi-value">
                    <?php 
                    $countRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM transaction WHERE bid = " . intval($_SESSION['id']));
                    $countRow = mysqli_fetch_assoc($countRes);
                    echo isset($countRow['cnt']) ? (int)$countRow['cnt'] : 0;
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <i class="fa fa-star" style="font-size: 2.5em; color: #f5576c;"></i>
                <div class="kpi-label">Avg Product Rating</div>
                <div class="kpi-value">
                    <?php 
                    $ratingRes = mysqli_query($conn, "SELECT AVG(r.rating) AS avg_rating FROM review r WHERE r.rating IS NOT NULL");
                    $ratingRow = mysqli_fetch_assoc($ratingRes);
                    echo isset($ratingRow['avg_rating']) ? round((float)$ratingRow['avg_rating'], 2) : 'N/A';
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <i class="fa fa-list" style="font-size: 2.5em; color: #f093fb;"></i>
                <div class="kpi-label">Available Products</div>
                <div class="kpi-value">
                    <?php 
                    $prodRes = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM fproduct");
                    $prodRow = mysqli_fetch_assoc($prodRes);
                    echo isset($prodRow['cnt']) ? (int)$prodRow['cnt'] : 0;
                    ?>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi-card">
                <i class="fa fa-check-circle" style="font-size: 2.5em; color: #4CAF50;"></i>
                <div class="kpi-label">Shipped Orders</div>
                <div class="kpi-value">
                    <?php 
                    // Be lenient: compare trimmed lowercase to avoid case/whitespace mismatches
                    $shippedSql = "SELECT COUNT(*) AS cnt FROM transaction WHERE bid = " . intval($_SESSION['id']) . " AND LOWER(TRIM(COALESCE(status, ''))) = 'shipped'";
                    $shippedRes = mysqli_query($conn, $shippedSql);
                        $shippedRow = mysqli_fetch_assoc($shippedRes);
                        echo isset($shippedRow['cnt']) ? (int)$shippedRow['cnt'] : 0;
                    ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row 1 -->
    <div class="row charts-row" style="margin-top: 30px;">
        <div class="col-md-6">
            <div class="chart-container">
                <h4 style="margin-bottom: 20px;"><i class="fa fa-pie-chart"></i> Purchases by Category</h4>
                <?php if(count($categoryData) > 0): ?>
                    <canvas id="categoryChart" height="200"></canvas>
                <?php else: ?>
                    <div class="no-data">No purchase data available</div>
                <?php endif; ?>
            </div>
        </div>
        <div class="col-md-6">
            <div class="chart-container">
                <h4 style="margin-bottom: 20px;"><i class="fa fa-bar-chart"></i> Product Ratings by Category</h4>
                <?php if(count($ratingData) > 0): ?>
                    <canvas id="ratingChart" height="200"></canvas>
                <?php else: ?>
                    <div class="no-data">No rating data available</div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- Products Sorted by Expiry Date -->
    <div style="margin-top: 30px;">
    <h3 class="section-title"><i class="fa fa-calendar"></i> All Products (Sorted by Expiry Date & Rating)</h3>
    <div class="products-grid">
            <?php 
            if(mysqli_num_rows($result) > 0):
                while($row = mysqli_fetch_assoc($result)):
                    $expiryDate = $row['expiry_date'];
                    $isExpirySoon = false;
                    $expiryText = 'N/A';
                    
                    if($expiryDate) {
                        $expiryTime = strtotime($expiryDate);
                        $todayTime = time();
                        $daysLeft = floor(($expiryTime - $todayTime) / (60 * 60 * 24));
                        
                        if($daysLeft <= 0) {
                            $expiryText = 'Expired';
                            $isExpirySoon = true;
                        } elseif($daysLeft <= 30) {
                            $expiryText = $daysLeft . ' days left';
                            $isExpirySoon = true;
                        } else {
                            $expiryText = $daysLeft . ' days left';
                        }
                    }
                    
                    $rating = $row['avg_rating'] ? round((float)$row['avg_rating'], 2) : 'N/A';
                    $reviewCount = (int)$row['review_count'];
            ?>
                <div class="product-grid-item">
                    <a href="review.php?pid=<?php echo $row['pid']; ?>" style="text-decoration: none; color: inherit; display: block;">
                    <div class="product-card">
                        <div style="display: flex; align-items: flex-start;">
                            <?php if(!empty($row['pimage'])): ?>
                                <img src="images/productImages/<?php echo htmlspecialchars($row['pimage']); ?>" class="product-image" alt="<?php echo htmlspecialchars($row['product']); ?>">
                            <?php else: ?>
                                <div class="product-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                    <i class="fa fa-image" style="font-size: 2em; color: #ccc;"></i>
                                </div>
                            <?php endif; ?>
                            <div style="flex: 1;">
                                <h5 style="margin: 0 0 5px 0; color: #333;"><?php echo htmlspecialchars($row['product']); ?></h5>
                                <small style="color: #999;">Category: <?php echo htmlspecialchars($row['pcat']); ?></small>
                                <div style="margin-top: 8px;">
                                    <span class="rating-badge">
                                        <i class="fa fa-star"></i> <?php echo $rating; ?> <?php if($reviewCount > 0) echo "(" . $reviewCount . ")"; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid #eee;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong style="color: #667eea;">₹<?php echo htmlspecialchars($row['price']); ?></strong>
                                </div>
                                <span class="expiry-badge <?php echo $isExpirySoon ? 'expiry-soon' : 'expiry-far'; ?>">
                                    <i class="fa fa-calendar"></i> <?php echo $expiryText; ?>
                                </span>
                            </div>
                            <div style="margin-top: 8px;">
                                <?php 
                                    $qty = isset($row['quantity']) ? (int)$row['quantity'] : 0;
                                    if ($qty < 5) {
                                        echo '<div style="color: #d9534f; font-weight: 600; font-size: 12px;"><i class="fa fa-exclamation-circle"></i> Low Stock: ' . $qty . ' left</div>';
                                    } elseif ($qty < 10) {
                                        echo '<div style="color: #f0ad4e; font-weight: 600; font-size: 12px;"><i class="fa fa-info-circle"></i> Limited Stock: ' . $qty . ' left</div>';
                                    } else {
                                        echo '<div style="color: #5cb85c; font-weight: 600; font-size: 12px;"><i class="fa fa-check-circle"></i> In Stock: ' . $qty . ' available</div>';
                                    }
                                ?>
                            </div>
                        </div>
                    </div>
                    </a>
                </div>
            <?php 
                endwhile;
            else:
            ?>
                <div class="col-md-12">
                    <div class="no-data">
                        <i class="fa fa-inbox" style="font-size: 3em; margin-bottom: 20px;"></i>
                        <p>No products available at the moment.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent Purchases -->
    <div style="margin-top: 40px; margin-bottom: 30px;">
        <h3 class="section-title"><i class="fa fa-history"></i> Your Recent Purchases</h3>
        <?php 
        if(mysqli_num_rows($purchasesResult) > 0):
        ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped" style="background-color: white;">
                    <thead style="background: #f8f9fa;">
                        <tr>
                            <th>S. No</th>
                            <th>Purchase Date</th>
                            <th>Product</th>
                            <th>Category</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Rating</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($purchasesResult, 0);
                        $sn = 1;
                        while($row = mysqli_fetch_assoc($purchasesResult)):
                            $status = $row['status'] ?? 'pending';
                            $statusBadge = '';
                            switch($status) {
                                case 'shipped': $statusBadge = '<span class="label label-success">Shipped</span>'; break;
                                case 'delivered': $statusBadge = '<span class="label label-info">Delivered</span>'; break;
                                default: $statusBadge = '<span class="label label-warning">Pending</span>'; break;
                            }
                            $rating = $row['product_rating'] ? round((float)$row['product_rating'], 2) : 'N/A';
                            $purchase_date = isset($row['purchase_date']) ? htmlspecialchars($row['purchase_date']) : 'N/A';
                        ?>
                            <tr>
                                <td><?php echo $sn++; ?></td>
                                <td><?php echo $purchase_date; ?></td>
                                <td><?php echo htmlspecialchars($row['product'] ?: 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($row['pcat'] ?: 'N/A'); ?></td>
                                <?php $purchased_qty = isset($row['quantity_purchased']) ? intval($row['quantity_purchased']) : (isset($row['quantity']) ? intval($row['quantity']) : 1); ?>
                                <td style="text-align: center;"><?php echo $purchased_qty; ?></td>
                                <td>₹<?php echo htmlspecialchars($row['price'] ?: 'N/A'); ?></td>
                                <td><?php echo $statusBadge; ?></td>
                                <td>
                                    <span style="color: #f5576c; font-weight: bold;">
                                        <?php if($rating !== 'N/A'): ?>
                                            <i class="fa fa-star"></i> <?php echo $rating; ?>
                                        <?php else: ?>
                                            No rating
                                        <?php endif; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="no-data">
                <i class="fa fa-shopping-bag" style="font-size: 3em; margin-bottom: 20px;"></i>
                <p>You haven't made any purchases yet.</p>
            </div>
        <?php endif; ?>
    </div>

</section>

<script>
<?php if(count($categoryData) > 0): ?>
    // Category Pie Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($categoryLabels); ?>,
            datasets: [{
                data: <?php echo json_encode($categoryData); ?>,
                backgroundColor: [
                    '#667eea',
                    '#764ba2',
                    '#f093fb',
                    '#f5576c',
                    '#4facfe',
                    '#00f2fe'
                ],
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
<?php endif; ?>

<?php if(count($ratingData) > 0): ?>
    // Rating Bar Chart
    const ratingCtx = document.getElementById('ratingChart').getContext('2d');
    new Chart(ratingCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($ratingLabels); ?>,
            datasets: [{
                label: 'Average Rating',
                data: <?php echo json_encode($ratingData); ?>,
                backgroundColor: 'rgba(245, 87, 108, 0.7)',
                borderColor: 'rgba(245, 87, 108, 1)',
                borderWidth: 2,
                borderRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            indexAxis: 'x',
            scales: {
                y: {
                    beginAtZero: true,
                    max: 10,
                    ticks: {
                        callback: function(value) {
                            return value.toFixed(1);
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: true
                }
            }
        }
    });
<?php endif; ?>
</script>

</body>
</html>