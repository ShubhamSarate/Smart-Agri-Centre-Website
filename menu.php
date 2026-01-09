<?php
	if(isset($_SESSION['logged_in']) AND $_SESSION['logged_in'] == 1)
	{
		$loginProfile = "My Profile: ". $_SESSION['Username'];
		$logo = "glyphicon glyphicon-user";
		if($_SESSION['Category']!= 1)
		{
			$link = "Login/profile.php";
		}
		else {
				$link = "profileView.php";
		}
	} // Admin for Category 0, User for Category 1
	else
	{
		$loginProfile = "Login";
		$link = "index.php";
		$logo = "glyphicon glyphicon-log-in";
	}
	// compute admin orders count if possible
	$orderCount = 0;
	if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] == 1 && isset($_SESSION['Category']) && $_SESSION['Category'] == 0) {
		// ensure DB connection available
		if (!isset($conn)) {
			@include_once 'db.php';
		}
		if (isset($conn)) {
			// If the 'is_seen' column exists, count only unseen orders; otherwise fall back to total
			$colRes = @mysqli_query($conn, "SHOW COLUMNS FROM transaction LIKE 'is_seen'");
			if ($colRes && mysqli_num_rows($colRes) > 0) {
				$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM `transaction` WHERE is_seen = 0");
			} else {
				$res = mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM `transaction`");
			}
			if ($res) {
				$r = mysqli_fetch_assoc($res);
				$orderCount = (int)$r['cnt'];
			}
		}
	}
?>

				<header id="header">
					<h1><a href="/Shubham/index.php">Krishna Sheti Seva Kendra</a></h1>
				<nav id="nav">
					<ul>
						<li><a href="/Shubham/index.php"><span class="glyphicon glyphicon-home"></span> Home</a></li>
						<?php if(!(isset($_SESSION['logged_in']) && $_SESSION['Category'] == 0)): ?>
						<li><a href="/Shubham/myCart.php"><span class="glyphicon glyphicon-shopping-cart"></span> Cart</a></li>
						<?php endif; ?>
						<?php if(isset($_SESSION['logged_in']) && $_SESSION['Category'] == 1): ?>
						<li><a href="/Shubham/userDashboard.php"><span class="glyphicon glyphicon-dashboard"></span> Dashboard</a></li>
						<?php endif; ?>
						<li><a href="<?= (strpos($link, '/') === 0 ? $link : '/Shubham/'.$link); ?>"><span class="<?php echo $logo; ?>"></span> <?php echo $loginProfile; ?></a></li>
						<li><a href="/Shubham/market.php"><span class="glyphicon glyphicon-grain"></span> Market</a></li>
						<?php if(isset($_SESSION['logged_in']) && $_SESSION['Category'] == 0): ?>
						<li><a href="/Shubham/uploadProduct.php"><span class="glyphicon glyphicon-upload"></span> Upload</a></li>
						<li><a href="/Shubham/adminDashboard.php"><span class="glyphicon glyphicon-stats"></span> Dashboard</a></li>
						<?php endif; ?>
						<?php if(isset($_SESSION['logged_in']) && $_SESSION['Category'] == 0): ?>
						<li><a href="/Shubham/adminOrders.php"><span class="glyphicon glyphicon-list-alt"></span> Orders<?php if($orderCount>0) echo ' <span class="badge" style="background:#d9534f;color:#fff;margin-left:6px;">'. $orderCount .'</span>'; ?></a></li>
						<?php endif; ?>
						<li><a href="/Shubham/blogView.php"><span class="glyphicon glyphicon-comment"></span> Blog</a></li>
					</ul>
				</nav>
			</header>
