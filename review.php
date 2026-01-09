<?php
	session_start();
	require 'db.php';
	$pid = $_GET['pid'];
?>


<!DOCTYPE html>
<html>
<head>
	<title>AgroCulture: Product</title>
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
		<link rel="stylesheet" href="login.css"/>
		<link rel="stylesheet" type="text/css" href="indexFooter.css">
		<script src="js/jquery.min.js"></script>
		<script src="js/skel.min.js"></script>
		<script src="js/skel-layers.min.js"></script>
		<script src="js/init.js"></script>
		<link rel="stylesheet" href="Blog/commentBox.css" />
		<!-- Product-focused styles (lightweight, non-invasive) -->
		<link rel="stylesheet" href="css/products-inspired.css" />
		<link rel="stylesheet" href="css/global-style.css" />
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<noscript>
			<link rel="stylesheet" href="css/skel.css" />
			<link rel="stylesheet" href="css/style.css" />
			<link rel="stylesheet" href="css/style-xlarge.css" />
		</noscript>
		<style>
			body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
			.wrapper { background: transparent !important; }
			.product-view { background: white; border-radius: 12px; padding: 30px; box-shadow: 0 6px 20px rgba(0,0,0,0.12); }
			.image.fit { max-width: 100%; height: auto; border-radius: 8px; }
			.btn-primary { background: #007bff; border-color: #007bff; }
			.con { background: #f8f9fa; padding: 12px; border-radius: 8px; margin-bottom: 12px; }
			.reviews-box {
				max-height: 420px;
				overflow-y: auto;
				padding: 18px;
				background: rgba(255,255,255,0.98);
				border-radius: 28px; /* larger rounded corners */
				box-shadow: 0 8px 24px rgba(0,0,0,0.08);
				width: 100%;
				max-width: 1000px; /* increased width */
				margin: 24px 0; /* align left within column */
				border: 1px solid rgba(0,0,0,0.04);
				/* Hide native scrollbars but keep scrolling functional */
				-ms-overflow-style: none;  /* IE and Edge */
				scrollbar-width: none;  /* Firefox */
			}
			/* WebKit browsers (Chrome, Safari) */
			.reviews-box::-webkit-scrollbar {
				display: none;
				width: 0;
				height: 0;
			}
			@media (max-width: 992px) {
				.reviews-box { max-width: 90%; }
			}
			.review-item { padding: 24px; margin-bottom: 40px; border-radius: 12px; background: white; line-height: 1.8; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border: 1px solid #e0e0e0; }
			.review-comment { color: #333; font-style: italic; margin-bottom: 18px; padding-bottom: 16px; border-bottom: 3px solid #667eea; }
			.review-meta { display: flex; flex-direction: column; gap: 10px; }
			.review-rating { color: #667eea; font-weight: 700; font-size: 15px; }
			.review-author { color: #888; font-size: 14px; font-weight: 500; }
			.reviews-heading { color: #ffffff; font-weight: 700; margin-bottom: 18px; }
			.product-action-row { margin-bottom: 40px; }
			.reviews-heading { margin-top: 24px; }
			.review-submit {
				display: inline-flex !important;
				align-items: center !important;
				justify-content: center !important;
				margin: 16px auto !important;
				height: 44px !important; /* fixed height to ensure vertical centering */
				padding: 0 24px !important; /* horizontal padding only */
				min-width: 140px !important;
				background: #007bff !important;
				color: #fff !important;
				border: none !important;
				border-radius: 6px !important;
				cursor: pointer !important;
				font-weight: 600 !important;
				font-size: 16px !important;
				vertical-align: middle !important;
				line-height: normal !important; /* rely on flex centering */
				box-sizing: border-box !important;
				box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
				-webkit-appearance: none !important;
				-moz-appearance: none !important;
			}
			.review-submit:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.12); }
		</style>
</head>
<body>


				<?php
					require 'menu.php';

					$sql="SELECT * FROM fproduct WHERE pid = '$pid'";
					$result = mysqli_query($conn, $sql);
					$row = mysqli_fetch_assoc($result);

					$fid = $row['fid'];
					$sql = "SELECT * FROM farmer WHERE fid = '$fid'";
					$result = mysqli_query($conn, $sql);
					$frow = mysqli_fetch_assoc($result);

					$picDestination = "images/productImages/".$row['pimage'];

					?>
				<section id="main" class="wrapper style1 align-center">
					<div class="container">
						<div style="text-align:left; margin-bottom:12px;">
							<a href="productMenu.php" class="btn btn-default" style="display:inline-flex;align-items:center;gap:8px;text-decoration:none;padding:8px 12px;border-radius:6px;border:1px solid #ddd;background:#fff;color:#333;"><i class="fa fa-arrow-left"></i> Back</a>
						</div>
						<div class="row">
								<div class="col-sm-4">
									<img class="image fit" src="<?php echo $picDestination.'';?>" alt="" />
								</div><!-- Image of farmer-->
								<div class="col-12 col-sm-6">
									<p style="font: 50px Times new roman;"><?= $row['product']; ?></p>
									<?php if(!empty($frow['fname']) && strtolower(trim($frow['fname'])) !== 'ironman'): ?>
										<p style="font: 30px Times new roman;">Product Owner : <?= htmlspecialchars($frow['fname']); ?></p>
									<?php endif; ?>
									<p style="font: 30px Times new roman;">Price : <?= $row['price'].' /-'; ?></p>
									<?php if(!empty($row['expiry_date'])): ?>
										<p style="font: 20px Times new roman;">Expiry date: <?= htmlspecialchars($row['expiry_date']); ?></p>
									<?php endif; ?>
								</div>
							</div><br />
							<div class="row">
								<div class="col-12 col-sm-12" style="font: 25px Times new roman;">
									<?= $row['pinfo']; ?>
								</div>
							</div>
						</div>

						<br /><br />

						<div class="12u$ product-action-row">
                            <center>
                                <div class="row uniform">
                                    <div class="6u 12u$(large)">
                                        <a href="myCart.php?flag=1&pid=<?= $pid; ?>" class="btn btn-primary" style="text-decoration: none;"><span class="glyphicon glyphicon-shopping-cart"> AddToCart</a>
                                    </div>
                                    <div class="6u 12u$(large)">
                                        <a href="buyNow.php?pid=<?= $pid; ?>" class="btn btn-primary" style="text-decoration: none;">Buy Now</a>
                                    </div>
                                </div>
                            </center>
                        </div>

					<div class="container">
						<h1 class="reviews-heading">Product Reviews</h1>
					<div class="row">
						<?php
							$sql = "SELECT * FROM review WHERE pid='$pid'";
							$result = mysqli_query($conn, $sql);
						?>
						<div class="col-12 col-sm-7">
							<div class="reviews-box">
							<?php
								if($result) :
									while($row1 = $result->fetch_array()) :
							?>
								<div class="review-item con">
									<div class="review-comment">
										<?= nl2br(htmlspecialchars($row1['comment'])); ?>
									</div>
									<div class="review-meta">
										<span class="review-rating">Rating: <?php echo $row1['rating']; ?> / 10</span>
										<span class="review-author">From: <?php echo htmlspecialchars($row1['name']); ?></span>
									</div>
								</div>
							<?php
									endwhile;
								endif;
							?>
							</div>
						</div>
				</div>
			</div>
			<?php

			?>
			<div class="container">
				<?php if(isset($_SESSION['message'])): ?>
					<div class="alert alert-info"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
				<?php endif; ?>
				<p style="font: 20px Times new roman; align: left;">Rate this product</p>
				<form method="POST" action="reviewInput.php?pid=<?= $pid; ?>">
					<div class="row">
						<div class="col-sm-7">
							<textarea style="background-color:white;color: black;" cols="5" name="comment" placeholder="Write a review"></textarea>
						</div>
						<div class="col-sm-5">
							<br />
							Rating: <input type="number" min="0" max="10" name="rating" value="0"/>
						</div>
					</div>
					<div class="row">
							<div class="col-sm-12">
								<br />
								<input type="submit" value="Submit" class="review-submit" />
							</div>
						</div>
				</form>
			</div>


	</body>
	</html>
