<?php
	session_start();
	require 'db.php';

	// Handle quantity update
	if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_quantity'])) {
		$pid = (int)$_POST['pid'];
		$new_quantity = (int)$_POST['new_quantity'];
		
		$update_sql = "UPDATE fproduct SET quantity = '$new_quantity' WHERE pid = '$pid'";
		if (mysqli_query($conn, $update_sql)) {
			$_SESSION['message'] = "Quantity updated successfully!";
		} else {
			$_SESSION['message'] = "Error updating quantity: " . mysqli_error($conn);
		}
	}
?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>AgroCulture</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<link href="bootstrap\css\bootstrap.min.css" rel="stylesheet">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="bootstrap\js\bootstrap.min.js"></script>
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
		<!-- Product-focused styles (lightweight, non-invasive) -->
		<link rel="stylesheet" href="css/products-inspired.css" />
		<link rel="stylesheet" href="css/global-style.css" />
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<style>
			body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
			.wrapper { background: transparent !important; }
			.container-card { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); max-width: 1200px; margin: 20px auto; }
			.select-wrapper { width: auto; margin: 0 auto; max-width: 400px; text-align: center; }
			.select-wrapper select { 
				width: 100%;
				padding: 14px 16px;
				border: 2px solid #ddd;
				border-radius: 8px;
				font-size: 16px;
				color: black;
				background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E") no-repeat right 10px center;
				background-size: 12px;
				appearance: none;
				-webkit-appearance: none;
				-moz-appearance: none;
				cursor: pointer;
			}
			.select-wrapper select option { font-size: 16px; color: black; }
			.select-wrapper select:focus { border-color: #667eea; outline: none; box-shadow: 0 0 5px rgba(102, 126, 234, 0.2); }
			.table-responsive .table { background: white; }
			.table th, .table td { text-align: center; vertical-align: middle; }
			.title { color: #333; font-size: 1.8em; margin-bottom: 12px; font-weight: 700; }
			.filter-heading { color: #ffffff; font-weight: 700; margin-top: 20px; margin-bottom: 12px; text-align: center; }
			.image.fit { width: 100%; height: auto; border-radius: 8px; object-fit: cover; }
			.product-card { background: white; padding: 18px; border-radius: 12px; box-shadow: 0 6px 20px rgba(0,0,0,0.08); margin-bottom: 20px; }
			blockquote { background: #f8f9fa; padding: 12px; border-radius: 8px; font-size: 1.1em; font-weight: 500; color: #333; line-height: 1.6; }
			@media (max-width: 768px) {
				.col-md-4 { width: 100%; }
			}

		</style>
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
	</head>
	<body class>

		<?php
			require 'menu.php';
			function dataFilter($data)
			{
				$data = trim($data);
				$data = stripslashes($data);
				$data = htmlspecialchars($data);
				return $data;
			}
		?>

		<!-- One -->
			<section id="main" class="wrapper style1 align-center" >
				<div class="container">
						<a href="market.php" class="button" style="display:inline-flex;align-items:center;gap:8px;margin-bottom:12px;"><i class="fa fa-arrow-left"></i> Back</a>
						<h2>Welcome to digital market</h2>

				<?php
					if(isset($_GET['n']) AND $_GET['n'] == 1):
				?>
					<h3 class="filter-heading">Select Filter</h3>
					<form method="GET" action="productMenu.php?">
						<input type="text" value="1" name="n" style="display: none;"/>
						<center>
							<div class="row">
							<div class="col-sm-4"></div>
							<div class="col-sm-4">
								<div class="select-wrapper" style="width: auto" >
									<?php $selType = isset($_GET['type']) ? $_GET['type'] : 'all'; ?>
									<select name="type" id="type" required style="background-color:white;color: black;" onchange="this.form.submit();">
										<option value="all" style="color: black;" <?php echo ($selType=='all')? 'selected':''; ?>>List All</option>
										<option value="fertilizer" style="color: black;" <?php echo ($selType=='fertilizer')? 'selected':''; ?>>Fertilizers</option>
										<option value="seed" style="color: black;" <?php echo ($selType=='seed')? 'selected':''; ?>>Seeds</option>
										<option value="tool" style="color: black;" <?php echo ($selType=='tool')? 'selected':''; ?>>Tools & Equipment</option>
										<option value="pesticide" style="color: black;" <?php echo ($selType=='pesticide')? 'selected':''; ?>>Pesticides</option>
										<option value="other" style="color: black;" <?php echo ($selType=='other')? 'selected':''; ?>>Other</option>
									</select>
							   	</div>
							</div>
							<div class="col-sm-4"></div>
						</div>
						</center>
					</form>
				<?php endif; ?>

				<section id="two" class="wrapper style2 align-center">
				<div class="container">
				<?php
					if(!isset($_GET['type']) OR $_GET['type'] == "all")
					{
					 	$sql = "SELECT * FROM fproduct WHERE 1";
					}
					if(isset($_GET['type']) AND $_GET['type'] == "fertilizer")
					{
						$sql = "SELECT * FROM fproduct WHERE pcat = 'Fertilizer'";
					}
					if(isset($_GET['type']) AND $_GET['type'] == "seed")
					{
						$sql = "SELECT * FROM fproduct WHERE pcat = 'Seed'";
					}
					if(isset($_GET['type']) AND $_GET['type'] == "tool")
					{
						$sql = "SELECT * FROM fproduct WHERE pcat = 'Tool'";
					}
					if(isset($_GET['type']) AND $_GET['type'] == "pesticide")
					{
						$sql = "SELECT * FROM fproduct WHERE pcat = 'Pesticide'";
					}
					if(isset($_GET['type']) AND $_GET['type'] == "other")
					{
						$sql = "SELECT * FROM fproduct WHERE pcat = 'Other'";
					}
					$result = mysqli_query($conn, $sql);

					if(isset($_SESSION['logged_in']) && $_SESSION['Category'] == 0): // Admin view ?>
						<div class="table-responsive" style="margin-top: 20px;">
							<table class="table table-striped table-bordered" style="color: black; background-color: white;">
								<thead>
									<tr style="background-color: white;">
										<th style="color: black; text-align:center;">Product</th>
										<th style="color: black; text-align:center;">Image</th>
										<th style="color: black; text-align:center;">Category</th>
										<th style="color: black; text-align:center;">Price</th>
										<th style="color: black; text-align:center;">Quantity</th>
										<th style="color: black; text-align:center;">Expiry</th>
										<th style="color: black; text-align:center;">Actions</th>
									</tr>
								</thead>
								<tbody>
								<?php while($row = $result->fetch_array()): 
									$picDestination = "images/productImages/".$row['pimage'];
									$quantity = isset($row['quantity']) ? (int)$row['quantity'] : 0;
								?>
									<tr>
										<td style="color: black;"><?php echo htmlspecialchars($row['product']); ?></td>
										<td style="color: black;"><img src="<?php echo $picDestination; ?>" alt="<?php echo htmlspecialchars($row['product']); ?>" style="max-height: 50px;"></td>
										<td style="color: black;"><?php echo htmlspecialchars($row['pcat']); ?></td>
										<td style="color: black;">₹<?php echo htmlspecialchars($row['price']); ?></td>
										<td style="color: black;">
											<form method="post" style="display: flex; gap: 8px; align-items: center; justify-content: center;">
												<input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
												<input type="hidden" name="update_quantity" value="1">
												<input type="number" name="new_quantity" value="<?php echo $quantity; ?>" min="0" style="width: 70px; padding: 6px; border: 1px solid #ddd; border-radius: 4px;">
												<button type="submit" class="btn btn-primary btn-sm" style="padding: 6px 12px;">
													<i class="fa fa-check"></i>
												</button>
											</form>
										</td>
										<td style="color: black; text-align:center;"><?php echo !empty($row['expiry_date']) ? htmlspecialchars($row['expiry_date']) : '—'; ?></td>
										<td style="color: black;">
											<form method="post" action="removeProduct.php" style="margin:0;" onsubmit="return confirm('Are you sure you want to remove this product?');">
												<input type="hidden" name="pid" value="<?php echo $row['pid']; ?>">
												<button type="submit" class="btn btn-danger btn-sm">
													<i class="fa fa-trash"></i> Remove
												</button>
											</form>
										</td>
									</tr>
								<?php endwhile; ?>
								</tbody>
							</table>
						</div>
					<?php else: // Regular user view ?>
						<div class="row">
						<?php
							mysqli_data_seek($result, 0); // Reset result pointer
							while($row = $result->fetch_array()):
								$picDestination = "images/productImages/".$row['pimage'];
						?>
							<div class="col-md-4">
								<section>
								<strong><h2 class="title" style="color:black; "><?php echo htmlspecialchars($row['product']); ?></h2></strong>
								<a href="review.php?pid=<?php echo $row['pid']; ?>" > <img class="image fit" src="<?php echo $picDestination; ?>" height="220px;"  /></a>

								<div style="align: left">
								<blockquote>
									<div style="margin-bottom: 8px;"><strong>Type:</strong> <?php echo htmlspecialchars($row['pcat']); ?></div>
									<div style="margin-bottom: 8px;"><strong>Price:</strong> ₹<?php echo htmlspecialchars($row['price']); ?></div>
									<?php 
										$qty = isset($row['quantity']) ? (int)$row['quantity'] : 0;
										$stockStatus = '';
										if ($qty < 5) {
											$stockStatus = '<div style="margin-bottom: 8px; color: #d9534f; font-weight: 600;"><i class="fa fa-exclamation-circle"></i> Low Stock: ' . $qty . ' left</div>';
										} elseif ($qty < 10) {
											$stockStatus = '<div style="margin-bottom: 8px; color: #f0ad4e; font-weight: 600;"><i class="fa fa-info-circle"></i> Limited Stock: ' . $qty . ' left</div>';
										} else {
											$stockStatus = '<div style="margin-bottom: 8px; color: #5cb85c; font-weight: 600;"><i class="fa fa-check-circle"></i> In Stock: ' . $qty . ' available</div>';
										}
										echo $stockStatus;
									?>
									<?php if(!empty($row['expiry_date'])) echo '<div><strong>Expiry:</strong> '.htmlspecialchars($row['expiry_date']).'</div>'; ?>
								</blockquote>
								</div>
								</section>
							</div>
						<?php endwhile; ?>
					<?php endif; ?>


					</div>

			</section>
					</header>

			</section>

	</body>
</html>
