<?php
 	session_start();
	if(!isset($_SESSION['logged_in']) OR $_SESSION['logged_in'] == 0)
	{
		$_SESSION['message'] = "You need to first login to access this page !!!";
		header("Location: Login/error.php");
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
		<link rel="stylesheet" type="text/css" href="indexFooter.css">
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
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
		<style>
			body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
			.wrapper { background: transparent !important; }
			.market-card { background: white; border-radius: 12px; padding: 30px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.1); transition: all 0.3s ease; cursor: pointer; }
			.market-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0,0,0,0.2); }
			.market-card img { width: 100px; height: 100px; object-fit: contain; margin-bottom: 15px; }
			.market-card p { color: #667eea; font-weight: 600; font-size: 16px; }
		</style>
	</head>
	<?php require 'menu.php'; ?>
	<body>



		<!-- One -->
			<section id="one" class="wrapper style1 align-center">
				<div class="container">
					<h2><i class="fa fa-shopping-bag"></i> Welcome to Digital Market</h2>
					<br />
					<div class="row 200%">
						<section class="4u 12u$(small)">
							<div class="market-card">
								<a href="profileView.php" style="text-decoration: none;">
									<i class="fa fa-user" style="font-size: 50px; color: #667eea;"></i>
									<p>Your Profile</p>
								</a>
							</div>
						</section>
						<section class="4u 12u$(small)">
							<div class="market-card">
								<a href="productMenu.php?n=1" style="text-decoration: none;">
									<i class="fa fa-search" style="font-size: 50px; color: #667eea;"></i>
									<p>Search Products</p>
								</a>
							</div>
						</section>
						<section class="4u$ 12u$(small)">
							<div class="market-card">
								<a href="productmenu.php?n=0" style="text-decoration: none;">
									<i class="fa fa-cube" style="font-size: 50px; color: #667eea;"></i>
									<p>All Products</p>
								</a>
							</div>
						</section>
					</div>
				</div>
			</section>



		<!-- Footer -->
			<!--<footer id="footer">
				<div class="container">
					<div class="row">
						<section class="4u 6u(medium) 12u$(small)">
							<h3>Welcome to Digital market</h3>

							<ul class="alt">
								<li><a href="#">Lorem ipsum dolor sit amet.</a></li>
								<li><a href="#">Quod adipisci perferendis et itaque.</a></li>
								<li><a href="#">Itaque eveniet ullam, veritatis reiciendis?</a></li>
								<li><a href="#">Accusantium repellat accusamus a, soluta.</a></li>
							</ul>
						</section>
						<section class="4u 6u$(medium) 12u$(small)">
							<h3>Nostrum, repellat!</h3>
							<p>Tenetur voluptate exercitationem eius tempora! Obcaecati suscipit, soluta earum blanditiis.</p>
							<ul class="alt">
								<li><a href="#">Lorem ipsum dolor sit amet.</a></li>
								<li><a href="#">Id inventore, qui necessitatibus sunt.</a></li>
								<li><a href="#">Deleniti eum odit nostrum eveniet.</a></li>
								<li><a href="#">Illum consectetur quibusdam eos corporis.</a></li>
							</ul>
						</section>
						<section class="4u$ 12u$(medium) 12u$(small)">
							<h3>Contact Us</h3>
							<ul class="icons">
								<li><a href="#" class="icon rounded fa-twitter"><span class="label">Twitter</span></a></li>
								<li><a href="#" class="icon rounded fa-facebook"><span class="label">Facebook</span></a></li>
								<li><a href="#" class="icon rounded fa-pinterest"><span class="label">Pinterest</span></a></li>
								<li><a href="#" class="icon rounded fa-google-plus"><span class="label">Google+</span></a></li>
								<li><a href="#" class="icon rounded fa-linkedin"><span class="label">LinkedIn</span></a></li>
							</ul>
							<ul class="tabular">
								<li>
									<h3>Address</h3>
									1234 Somewhere Road<br>
									Nashville, TN 00000
								</li>
								<li>
									<h3>Mail</h3>
									<a href="#">someone@untitled.tld</a>
								</li>
								<li>
									<h3>Phone</h3>
									(000) 000-0000
								</li>
							</ul>
						</section>
					</div>
					<ul class="copyright">
						<li>&copy; Untitled. All rights reserved.</li>
						<li>Design: <a href="http://templated.co">TEMPLATED</a></li>
						<li>Images: <a href="http://unsplash.com">Unsplash</a></li>
					</ul>
				</div>
			</footer>-->




	</body>
</html>
