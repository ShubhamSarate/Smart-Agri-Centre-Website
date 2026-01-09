<?php
session_start();
require 'db.php';

// only admin
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 0) {
    $_SESSION['message'] = "You need admin access to view orders.";
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

// Ensure transaction table has is_seen column (tracks whether admin has viewed the order)
$colCheckSeen = "SHOW COLUMNS FROM transaction LIKE 'is_seen'";
$colResSeen = mysqli_query($conn, $colCheckSeen);
if (!$colResSeen || mysqli_num_rows($colResSeen) == 0) {
    $alterSeen = "ALTER TABLE transaction ADD COLUMN is_seen TINYINT(1) DEFAULT 0";
    @mysqli_query($conn, $alterSeen);
}

// Mark all unseen orders as seen when admin opens this page so the orders badge resets
@mysqli_query($conn, "UPDATE transaction SET is_seen = 1 WHERE is_seen = 0");

// fetch transactions with product and buyer info
$sql = "SELECT t.tid, t.bid, t.pid, t.quantity_purchased,
                    t.name AS buyer_name, t.city, t.mobile, t.email, t.pincode, t.addr, t.tid AS order_id, fp.product AS product_name, fp.pimage, fp.expiry_date, b.bname, t.status
    FROM transaction t
    LEFT JOIN fproduct fp ON t.pid = fp.pid
    LEFT JOIN buyer b ON t.bid = b.bid
    ORDER BY t.tid DESC";
$result = mysqli_query($conn, $sql);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin Orders - AgroCulture</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="bootstrap/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="css/skel.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/style-xlarge.css" />
    <link rel="stylesheet" href="css/global-style.css" />
    <link rel="stylesheet" href="css/font-awesome.min.css">
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
        .container-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 1200px; margin: 0 auto; }
        .container-card h2 { color: #333; font-weight: 700; margin-bottom: 25px; font-size: 28px; display: flex; gap: 10px; align-items: center; }
        
        /* Alert styling */
        .alert { border-radius: 8px; border: none; }
        .alert-info { background: #e7f3ff; color: #004085; }
        
        /* Table styling */
        .table-responsive { border-radius: 8px; overflow: hidden; }
        .table { margin-bottom: 0; }
        table th { 
            background: #667eea; 
            color: white; 
            text-align: center; 
            vertical-align: middle;
            font-weight: 600;
            padding: 15px 10px;
        }
        table td { 
            color: #333; 
            text-align: center; 
            vertical-align: middle;
            padding: 12px 10px;
        }
        .thumb { max-height: 50px; display: block; margin: 0 auto; border-radius: 4px; }
        
        /* Badge styling */
        .label { 
            padding: 6px 12px; 
            border-radius: 6px; 
            font-weight: 600;
            display: inline-block;
        }
        .label-warning { background: #ffc107; color: #000; }
        .label-success { background: #28a745; color: white; }
        .label-info { background: #17a2b8; color: white; }
        
        /* Button styling */
        .btn-sm { 
            padding: 6px 12px !important; 
            border-radius: 6px;
            font-weight: 600;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none !important;
        }
        .btn-primary { 
            background: #007bff; 
            color: white;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
        }
        .btn-primary:hover { 
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
        }
        .btn-danger { 
            background: #dc3545; 
            color: white;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
        .btn-danger:hover { 
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220, 53, 69, 0.4);
        }
        
        @media (max-width: 768px) {
            .container-card { padding: 20px; }
            table { font-size: 12px; }
            .btn-sm { font-size: 12px; padding: 4px 8px !important; }
        }
    </style>
</head>
<body>
<?php require 'menu.php'; ?>

<section class="wrapper">
    <div class="container-card">
        <h2><i class="fa fa-shopping-cart"></i> Orders</h2>
    <?php if(isset($_SESSION['logged_in']) && $_SESSION['Category'] == 0): ?>
        <!-- Analysis feature removed -->
    <?php endif; ?>
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-info"><?= $_SESSION['message']; unset($_SESSION['message']); ?></div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped" style="background-color: white;">
            <thead style="background: white;">
                <tr>
                    <th style="text-align:center;">Order ID</th>
                    <th style="text-align:center;">Product</th>
                    <th style="text-align:center;">Quantity</th>
                    <th style="text-align:center;">Image</th>
                    <th style="text-align:center;">Expiry</th>
                    <th style="text-align:center;">Buyer</th>
                    <th style="text-align:center;">Contact</th>
                    <th style="text-align:center;">Address</th>
                    <th style="text-align:center;">Status</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name'] ?: 'N/A'); ?></td>
                        <?php $order_qty = isset($row['quantity_purchased']) ? intval($row['quantity_purchased']) : (isset($row['quantity']) ? intval($row['quantity']) : 1); ?>
                        <td style="text-align:center;"><?php echo $order_qty; ?></td>
                        <td><?php if(!empty($row['pimage'])): ?><img src="images/productImages/<?php echo htmlspecialchars($row['pimage']); ?>" class="thumb" alt="<?php echo htmlspecialchars($row['product_name']); ?>"><?php else: echo '—'; endif; ?></td>
                        <td><?php echo (!empty($row['expiry_date']) ? htmlspecialchars($row['expiry_date']) : '—'); ?></td>
                        <td><?php echo htmlspecialchars($row['bname'] ?: $row['buyer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['mobile']); ?><br><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['addr']).'<br>'.htmlspecialchars($row['city']).' - '.htmlspecialchars($row['pincode']); ?></td>
                        <td>
                            <?php
                            $status = $row['status'] ?? 'pending';
                            $badge_class = '';
                            switch($status) {
                                case 'shipped': $badge_class = 'label-success'; break;
                                case 'delivered': $badge_class = 'label-info'; break;
                                default: $badge_class = 'label-warning'; break;
                            }
                            ?>
                            <span class="label <?php echo $badge_class; ?>"><?php echo ucfirst(htmlspecialchars($status)); ?></span>
                        </td>
                        <td>
                            <form method="post" action="adminOrderAction.php" style="display:inline;">
                                <input type="hidden" name="tid" value="<?php echo $row['tid']; ?>">
                                <?php if($status !== 'shipped' && $status !== 'delivered'): ?>
                                    <button type="submit" name="action" value="shipped" class="btn btn-primary btn-sm" title="Mark as Shipped"><i class="fa fa-truck"></i> Shipped</button>
                                <?php endif; ?>
                                <button type="submit" name="action" value="delete" class="btn btn-danger btn-sm" onclick="return confirm('Delete this order?');"><i class="fa fa-trash"></i> Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        </div>
    </div>
</section>
</body>
</html>