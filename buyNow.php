<?php
	session_start();
	require 'db.php';
    $pid = $_GET['pid'];
    $error_msg = '';
    
    // Fetch available stock for display/validation
    $available_qty = 0;
    $stockQuery = mysqli_query($conn, "SELECT quantity FROM fproduct WHERE pid = '" . mysqli_real_escape_string($conn, $pid) . "' LIMIT 1");
    if ($stockQuery && mysqli_num_rows($stockQuery) > 0) {
        $stockRow = mysqli_fetch_assoc($stockQuery);
        $available_qty = intval($stockRow['quantity']);
    }
    
    // Ensure buyer table has city and pincode columns
    $colCheckCity = @mysqli_query($conn, "SHOW COLUMNS FROM buyer LIKE 'bcity'");
    if (!$colCheckCity || mysqli_num_rows($colCheckCity) == 0) {
        @mysqli_query($conn, "ALTER TABLE buyer ADD COLUMN bcity VARCHAR(100)");
    }
    $colCheckPin = @mysqli_query($conn, "SHOW COLUMNS FROM buyer LIKE 'bpincode'");
    if (!$colCheckPin || mysqli_num_rows($colCheckPin) == 0) {
        @mysqli_query($conn, "ALTER TABLE buyer ADD COLUMN bpincode VARCHAR(20)");
    }
    
    // Fetch user profile data to pre-fill form
    $user_name = '';
    $user_email = '';
    $user_mobile = '';
    $user_city = '';
    $user_pincode = '';
    $user_addr = '';
    
    if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 1 && isset($_SESSION['id'])) {
        $bid = $_SESSION['id'];
        $userQuery = mysqli_query($conn, "SELECT bname, bemail, bmobile, bcity, bpincode, baddress FROM buyer WHERE bid = '" . mysqli_real_escape_string($conn, $bid) . "' LIMIT 1");
        if ($userQuery && mysqli_num_rows($userQuery) > 0) {
            $userData = mysqli_fetch_assoc($userQuery);
            $user_name = isset($userData['bname']) ? htmlspecialchars($userData['bname']) : '';
            $user_email = isset($userData['bemail']) ? htmlspecialchars($userData['bemail']) : '';
            $user_mobile = isset($userData['bmobile']) ? htmlspecialchars($userData['bmobile']) : '';
            $user_city = isset($userData['bcity']) ? htmlspecialchars($userData['bcity']) : '';
            $user_pincode = isset($userData['bpincode']) ? htmlspecialchars($userData['bpincode']) : '';
            $user_addr = isset($userData['baddress']) ? htmlspecialchars($userData['baddress']) : '';
        }
    }
    
    if($_SERVER['REQUEST_METHOD'] == "POST")
    {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $city = isset($_POST['city']) ? trim($_POST['city']) : '';
        $mobile = isset($_POST['mobile']) ? trim($_POST['mobile']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $pincode = isset($_POST['pincode']) ? trim($_POST['pincode']) : '';
        $addr = isset($_POST['addr']) ? trim($_POST['addr']) : '';
        $bid = $_SESSION['id'];
        $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;
        
        // Validate quantity is at least 1
        if ($quantity < 1) {
            $error_msg = "Quantity must be at least 1.";
        } else {
            // Check available stock
            $stockRes = mysqli_query($conn, "SELECT quantity FROM fproduct WHERE pid = '$pid' LIMIT 1");
            if ($stockRes && mysqli_num_rows($stockRes) > 0) {
                $stockRow = mysqli_fetch_assoc($stockRes);
                $available_qty = intval($stockRow['quantity']);
                
                if ($quantity > $available_qty) {
                    $error_msg = "Insufficient stock! Available: $available_qty units, Requested: $quantity units.";
                }
            } else {
                $error_msg = "Product not found or stock information unavailable.";
            }
        }
        
        // If no error, proceed with order
        if (empty($error_msg)) {

        // fetch product expiry_date and ensure quantity_purchased column exists
        $expiry_date = null;
        $r = mysqli_query($conn, "SELECT expiry_date FROM fproduct WHERE pid = '$pid' LIMIT 1");
        if($r){
            $rr = mysqli_fetch_assoc($r);
            if(isset($rr['expiry_date'])) $expiry_date = $rr['expiry_date'];
        }

        // Check if quantity_purchased column exists in transaction table
        $colCheckSql = "SHOW COLUMNS FROM transaction LIKE 'quantity_purchased'";
        $colRes = mysqli_query($conn, $colCheckSql);
        if (!$colRes || mysqli_num_rows($colRes) == 0) {
            @mysqli_query($conn, "ALTER TABLE transaction ADD COLUMN quantity_purchased INT DEFAULT 1");
        }

        // Check if created_at column exists in transaction table
        $colCheckSql = "SHOW COLUMNS FROM transaction LIKE 'created_at'";
        $colRes = mysqli_query($conn, $colCheckSql);
        if (!$colRes || mysqli_num_rows($colRes) == 0) {
            @mysqli_query($conn, "ALTER TABLE transaction ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP");
        }

        // If an expiry exists, ensure transaction table has expiry_date column to avoid fatal errors
        if ($expiry_date) {
            $colCheckSql = "SHOW COLUMNS FROM transaction LIKE 'expiry_date'";
            $colRes = mysqli_query($conn, $colCheckSql);
            if (!$colRes || mysqli_num_rows($colRes) == 0) {
                $alter = "ALTER TABLE transaction ADD COLUMN expiry_date DATE";
                @mysqli_query($conn, $alter);
            }
        }

        // Build insert SQL with quantity_purchased and created_at
        if($expiry_date){
            $sql = "INSERT INTO transaction (bid, pid, name, city, mobile, email, pincode, addr, expiry_date, quantity_purchased, created_at) VALUES ('$bid', '$pid', '$name', '$city', '$mobile', '$email', '$pincode', '$addr', '$expiry_date', '$quantity', NOW())";
        } else {
            $sql = "INSERT INTO transaction (bid, pid, name, city, mobile, email, pincode, addr, quantity_purchased, created_at) VALUES ('$bid', '$pid', '$name', '$city', '$mobile', '$email', '$pincode', '$addr', '$quantity', NOW())";
        }

        // Use an atomic update inside a DB transaction to avoid race conditions
        $success = false;
        // Start transaction (works if using InnoDB)
        mysqli_begin_transaction($conn);
        // Check if enough stock exists before placing order (but don't decrement yet - only on ship)
        $stockCheck = mysqli_query($conn, "SELECT quantity FROM fproduct WHERE pid = '$pid' FOR UPDATE");
        if ($stockCheck && $row = mysqli_fetch_assoc($stockCheck)) {
            $currentQty = intval($row['quantity']);
            if ($currentQty >= $quantity) {
                // Now insert the transaction record
                try {
                    $insRes = mysqli_query($conn, $sql);
                } catch (mysqli_sql_exception $e) {
                    $insRes = false;
                }

                if ($insRes) {
                    mysqli_commit($conn);
                    $success = true;
                } else {
                    // Insert failed - rollback
                    mysqli_rollback($conn);
                    $error_msg = "Could not place order. Please try again.";
                }
            } else {
                mysqli_rollback($conn);
                $error_msg = "Insufficient stock. Only $currentQty available.";
            }
        } else {
            mysqli_rollback($conn);
            $error_msg = "Could not check stock. Please try again.";
        }

        if ($success) {
            $_SESSION['message'] = "Order Successfully placed! <br /> Thanks for shopping with us!!!";
            header('Location: Login/success.php');
            exit();
        }
        } // End of if(empty($error_msg))
    }
?>


<!DOCTYPE html>
<html>
<head>
	<title>AgroCulture: Transaction</title>
	<meta lang="eng">
	<meta charset="UTF-8">
		<title>AgroCulture</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link href="bootstrap\css\bootstrap.min.css" rel="stylesheet">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="bootstrap\js\bootstrap.min.js"></script>
		<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
		<script src="js/jquery.min.js"></script>
		<script src="js/skel.min.js"></script>
		<script src="js/skel-layers.min.js"></script>
		<script src="js/init.js"></script>
		<link rel="stylesheet" href="Blog/commentBox.css" />
		<noscript>
			<link rel="stylesheet" href="css/skel.css" />
			<link rel="stylesheet" href="css/style.css" />
			<link rel="stylesheet" href="css/style-xlarge.css" />
		</noscript>
		<link rel="stylesheet" href="css/global-style.css" />
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<style>
			body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
			.wrapper { background: transparent !important; }
			.form-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-width: 600px; margin: 0 auto; }
			input[type="text"], input[type="email"], input[type="number"] { text-align: left !important; padding-left: 15px !important; padding: 14px 15px !important; font-size: 16px !important; height: 48px !important; line-height: 20px; vertical-align: middle; }
			input::placeholder { text-align: left !important; }
		</style>
</head>
<body>

    <?php
        require 'menu.php';
    ?>


    <section id="main" class="wrapper" >
        <div class="container">
            <div class="form-card">
                <h2><i class="fa fa-shopping-cart"></i> Confirm Order</h2>
                <?php if (!empty($error_msg)): ?>
                    <div style="background-color: #ffebee; border-left: 4px solid #f44336; color: #c62828; padding: 15px; margin-bottom: 20px; border-radius: 4px;">
                        <strong><i class="fa fa-exclamation-circle"></i> Error:</strong> <?php echo htmlspecialchars($error_msg); ?>
                    </div>
                <?php endif; ?>
        <form method="post" action="buyNow.php?pid=<?= $pid; ?>">
                    <div class="row uniform">
                        <input type="text" name="name" id="name" placeholder="Full Name" value="<?php echo $user_name; ?>" required/>
                        <input type="email" name="email" id="email" placeholder="Email" value="<?php echo $user_email; ?>" required/>
                        <input type="text" name="mobile" id="mobile" placeholder="Mobile Number" value="<?php echo $user_mobile; ?>" required/>
                        <input type="text" name="city" id="city" placeholder="City" value="<?php echo $user_city; ?>" required/>
                        <input type="text" name="pincode" id="pincode" placeholder="Pincode" value="<?php echo $user_pincode; ?>" required/>
                        <input type="text" name="addr" id="addr" placeholder="Full Address" value="<?php echo $user_addr; ?>" required/>
                        <input type="number" name="quantity" id="quantity" placeholder="Quantity" min="1" value="<?php echo !empty($error_msg) && isset($_POST['quantity']) ? intval($_POST['quantity']) : '1'; ?>" required data-available="<?php echo $available_qty; ?>" />
                        <div id="qty-alert" style="display:none; margin-top:8px; color:#c62828; font-weight:600;"></div>
                    </div>
                    <div style="display: flex; gap: 12px; margin-top: 25px; justify-content: center;">
                        <button type="submit" class="button" style="flex: 1; min-width: 150px; text-align: center; display: inline-flex; align-items: center; justify-content: center;"><i class="fa fa-check"></i> Confirm Order</button>
                        <a href="market.php" class="button secondary" style="flex: 1; min-width: 150px; text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center;"><i class="fa fa-times"></i> Cancel</a>
                    </div>
                </form>
            </div>
        </fieldset>

        <script>
            (function(){
                var qtyInput = document.getElementById('quantity');
                var form = qtyInput ? qtyInput.form : document.querySelector('form');
                var alertDiv = document.getElementById('qty-alert');
                var available = qtyInput ? parseInt(qtyInput.getAttribute('data-available') || '0', 10) : 0;

                function showAlert(msg) {
                    if(!alertDiv) return alert(msg);
                    alertDiv.textContent = msg;
                    alertDiv.style.display = 'block';
                    setTimeout(function(){ alertDiv.style.display = 'none'; }, 4000);
                }

                if(qtyInput) {
                    qtyInput.addEventListener('input', function(e){
                        var v = parseInt(this.value || '0', 10);
                        
                        // If out of stock entirely, clear the input
                        if (available <= 0) {
                            this.value = '';
                            showAlert('Product out of stock.');
                            return;
                        }
                        
                        // Allow any value freely — don't auto-clamp or auto-correct
                        // Just let user type what they want, we'll validate on submit
                    });
                }

                if(form) {
                    form.addEventListener('submit', function(e){
                        if(!qtyInput) return;
                        var v = parseInt(qtyInput.value || '0', 10);
                        
                        if (available <= 0) {
                            e.preventDefault();
                            showAlert('Product is out of stock.');
                            return false;
                        }
                        
                        if (isNaN(v) || v < 1) {
                            e.preventDefault();
                            showAlert('Quantity must be at least 1.');
                            return false;
                        }
                        
                        if (v > available) {
                            e.preventDefault();
                            showAlert('Quantity ' + v + ' exceeds available stock of ' + available + ' units. Please reduce quantity and try again.');
                            return false;
                        }
                    });
                }
            })();
        </script>
