<?php session_start(); ?>

<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Krishna Sheti Seva Kendra</title>
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
		<noscript>
			<link rel="stylesheet" href="css/skel.css" />
			<link rel="stylesheet" href="css/style.css" />
			<link rel="stylesheet" href="css/style-xlarge.css" />
		</noscript>
		<link rel="stylesheet" href="indexfooter.css" />
		<!-- Product-focused styles (lightweight, non-invasive) -->
		<link rel="stylesheet" href="css/products-inspired.css" />
		<style>
			/* Use site-wide background and colors from css/global-style.css
			   Keep banner image with an overlay for legibility. */
			#banner {
				/* Prefer banner1.jpg (use the attached image) and fall back to banner.jpg */
				background-image: linear-gradient(rgba(0,0,0,0.20), rgba(0,0,0,0.12)), url('images/banner1.jpg'), url('images/banner.jpg');
				background-size: cover, cover, cover;
				background-position: center center;
				background-repeat: no-repeat;
				color: #fff;
				min-height: 360px; /* ensure banner area is visible */
				display: flex;
				align-items: center;
			}
			#banner .container { background: transparent; }
			#banner h2, #banner p { color: #fff; text-shadow: 0 2px 6px rgba(0,0,0,0.35); }

			/* Section 'One' header and feature cards styling */
			#one .container header {
				display: inline-block;
				background: linear-gradient(90deg, rgba(102,126,234,0.10), rgba(118,75,162,0.08));
				padding: 14px 20px;
				border-radius: 10px;
				margin-bottom: 18px;
			}
			#one .container header h2 { color: #2b2b2b; }
			#one .container header p { color: #444; margin-top:6px; }

			/* Feature boxes (Digital Market / Blog / Register) */
			#one .row > section {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
				color: #fff;
				padding: 18px 16px;
				border-radius: 10px;
				box-shadow: 0 8px 20px rgba(118,75,162,0.09);
				display: flex;
				flex-direction: column;
				align-items: center;
				gap: 10px;
			}
			#one .row > section p { color: #fff; margin: 0; font-weight:600; }
			#one .row > section .icon { background: rgba(255,255,255,0.12); color: #fff; padding: 14px; border-radius: 12px; }
		</style>
		<!-- Flying birds decoration -->
	<style>
		.flying-birds {
			position: fixed;
			top: 20px;
			left: 20px;
			width: 400px;
			height: 280px;
			z-index: 999;
			pointer-events: none;
			opacity: 1;
		}			#banner {
				position: relative;
				z-index: 1;
			}
		</style>
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
		<script>
			function togglePasswordVisibility(fieldId) {
				var field = document.getElementById(fieldId);
				var icon = event.target;
				
				if (field.type === "password") {
					field.type = "text";
					icon.textContent = "👁️‍🗨️";
				} else {
					field.type = "password";
					icon.textContent = "👁️";
				}
			}

			function openLoginModal() {
				console.log('Login modal opening...');
				var modal = document.getElementById('id01');
				if (modal) {
					modal.style.display = 'block';
					console.log('Modal display set to block');
				} else {
					console.log('Modal element not found!');
				}
				document.getElementById('uname').value = '';
				document.getElementById('pass').value = '';
				document.getElementById('farmer').checked = true;
			}

			function openRegisterModal() {
				console.log('Register modal opening...');
				var modal = document.getElementById('id02');
				if (modal) {
					modal.style.display = 'block';
					console.log('Modal display set to block');
				} else {
					console.log('Modal element not found!');
				}
				document.getElementById('signup-name').value = '';
				document.getElementById('signup-uname').value = '';
				document.getElementById('signup-mobile').value = '';
				document.getElementById('signup-email').value = '';
				document.getElementById('signup-pass').value = '';
				document.getElementById('signup-cpass').value = '';
			}

			window.onclick = function(event) {
				var modal1 = document.getElementById('id01');
				var modal2 = document.getElementById('id02');
				if (event.target == modal1) {
					modal1.style.display = 'none';
				}
				if (event.target == modal2) {
					modal2.style.display = 'none';
				}
			}
		</script>
		<!-- ===== LOGIN CSS - LOADED LAST TO OVERRIDE ALL OTHER STYLES ===== -->
		<link rel="stylesheet" href="login.css?v=2.0"/>
		<style>
			/* Direct inline overrides for login modal buttons */
			#id01 input[type="submit"],
			#id02 input[type="submit"] {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
				color: white !important;
			}
			
			/* Style LOGIN and REGISTER buttons in banner - copy from modal button styling */
			button[onclick="openLoginModal()"],
			button[onclick="openRegisterModal()"] {
				background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
				color: white !important;
				padding: 14px 40px !important;
				border: none !important;
				border-radius: 8px !important;
				cursor: pointer !important;
				font-size: 16px !important;
				font-weight: 600 !important;
				transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
				box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3) !important;
				display: inline-block !important;
				width: auto !important;
				pointer-events: auto !important;
				z-index: 100 !important;
				line-height: 1.5 !important;
				vertical-align: middle !important;
				height: auto !important;
				min-height: 48px !important;
				align-items: center !important;
			}
			
			/* Hover effect for banner buttons */
			button[onclick="openLoginModal()]:hover,
			button[onclick="openRegisterModal()]:hover {
				transform: translateY(-3px) !important;
				box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4) !important;
			}
			
			/* Active state for banner buttons */
			button[onclick="openLoginModal()]:active,
			button[onclick="openRegisterModal()]:active {
				transform: translateY(-1px) !important;
			}
			
			/* Fix signup form layout */
			#id02 .container .row {
				display: block !important;
				width: 100% !important;
			}
			
			#id02 .container .row > div {
				width: 100% !important;
				padding: 0 !important;
				margin: 0 !important;
			}
			
			#id02 input[type="text"],
			#id02 input[type="password"],
			#id02 input[type="email"] {
				width: 100% !important;
			}
		</style>
	</head>

	<?php
		require 'menu.php';
	?>
	


		<!-- Banner -->
			<section id="banner" class="wrapper">
				<div class="container">
				<h2>Krishna Sheti Seva Kendra</h2>
				<p>At your Service</p>
				<br><br>
				<center>
					<div class="row uniform">
						<div class="6u 12u$(xsmall)">
							<button class="button fit" onclick="openLoginModal()" style="width:auto">LOGIN</button>
						</div>

						<div class="6u 12u$(xsmall)">
							<button class="button fit" onclick="openRegisterModal()" style="width:auto">REGISTER</button>
						</div>
					</div>
				</center>


			</section>

		<!-- One -->
			<section id="one" class="wrapper style1 align-center">
				<div class="container">
					<header>
						<h2>Krishna Sheti Seva Kendra</h2>
						<p>Explore the new way of trading...</p>
					</header>
					<div class="row 200%">
						<section class="4u 12u$(small)">
							<i class="icon big rounded fa-clock-o"></i>
							<p>Digital Market</p>
						</section>
						<section class="4u 12u$(small)">
							<i class="icon big rounded fa-comments"></i>
							<p>Krishna Sheti Seva Kendra-Blog</p>
						</section>
						<section class="4u$ 12u$(small)">
							<i class="icon big rounded fa-user"></i>
							<p>Register with us</p>
						</section>
					</div>
				</div>
			</section>


		<!-- Footer -->
		<footer class="footer-distributed" style="background-color:black" id="aboutUs">
		<center>
			<h1 style="font: 35px calibri;">About Us</h1>
		</center>
		<div class="footer-left">
			<h3 style="font-family: 'Times New Roman', cursive;">Krishna Sheti Seva Kendra &copy; </h3>
		<!--	<div class="logo">
				<a href="index.php"><img src="images/logo.png" width="200px"></a>
			</div>-->
			<br />
			<p style="font-size:20px;color:white">At your Service</p>
			<br />
		</div>

		<div class="footer-center">
			<div>
				<i class="fa fa-map-marker"></i>
				<p style="font-size:20px">Krishna Sheti Seva Kendra Fam<span>Peth</span></p>
			</div>
			<div>
				<i class="fa fa-phone"></i>
				<p style="font-size:20px">+91 9763945551</p>
			</div>
			<div>
				<i class="fa fa-envelope"></i>
				<p style="font-size:20px"><a href="mailto:KrishnaShetiKendra@gmail.com" style="color:white">KrishnaShetiKendra@Gmail.com</a></p>
			</div>
		</div>

		<div class="footer-right">
			<p class="footer-company-about" style="color:white">
				<span style="font-size:20px"><b>About Krishna Sheti Seva Kendra</b></span>
				Krishna Sheti Seva Kendra is e-commerce trading platform for grains & grocerries...
			</p>
			<div class="footer-icons">
				<a  href="#"><i style="margin-left: 0;margin-top:5px;"class="fa fa-facebook"></i></a>
				<a href="#"><i style="margin-left: 0;margin-top:5px" class="fa fa-instagram"></i></a>
				<a href="#"><i style="margin-left: 0;margin-top:5px" class="fa fa-youtube"></i></a>
			</div>
		</div>

	</footer>


			<div id="id01" class="modal">

  <form class="modal-content animate" action="Login/login.php" method='POST'>
    <div class="imgcontainer">
      <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
    </div>

    <div class="container">
    <h3>Login</h3>
							<form method="post" action="Login/login.php">
								<div class="row uniform 50%">
									<div class="7u$">
										<input type="text" name="uname" id="uname" value="" placeholder="UserName" style="width:80%" required autocomplete="off"/>
									</div>
									<div class="7u$">
										<div class="password-wrapper">
											<input type="password" name="pass" id="pass" value="" placeholder="Password" style="width:80%" required autocomplete="off"/>
											<span class="toggle-password" onclick="togglePasswordVisibility('pass')">👁️</span>
										</div>
									</div>
								</div>
									<div class="row uniform">
										<p>
				                            <b>Category : </b>
				                        </p>
				                        <div class="3u 12u$(small)">
				                            <input type="radio" id="farmer" name="category" value="1" checked>
				                            <label for="farmer">User</label>
				                        </div>
				                        <div class="3u 12u$(small)">
				                            <input type="radio" id="buyer" name="category" value="0">
				                            <label for="buyer">Admin</label>
				                        </div>
									</div>
									<center>
									<div class="row uniform">
										<div class="7u 12u$(small)">
											<input type="submit" value="Login" />
										</div>
									</div>
									</center>
								</div>
							</form>
						</section>
</div>
    </div>
    </div>
  </form>
</div>


<div id="id02" class="modal">

  <form class="modal-content animate" action="Login/signUp.php" method='POST'>
    <div class="imgcontainer">
      <span onclick="document.getElementById('id02').style.display='none'" class="close" title="Close Modal">&times;</span>
    </div>

    <div class="container">

<section>
							<h3>SignUp</h3>
							<form method="post" action="Login/signUp.php">
								<center>
								<div class="row uniform">
									<div class="3u 12u$(xsmall)">
										<input type="text" name="name" id="signup-name" value="" placeholder="Name" required autocomplete="off"/>
									</div>
									<div class="3u 12u$(xsmall)">
										<input type="text" name="uname" id="signup-uname" value="" placeholder="UserName" required autocomplete="off"/>
									</div>
								</div>
								<div class="row uniform">
									<div class="3u 12u$(xsmall)">
										<input type="text" name="mobile" id="signup-mobile" value="" placeholder="Mobile Number" required autocomplete="off"/>
									</div>

									<div class="3u 12u$(xsmall)">
										<input type="email" name="email" id="signup-email" value="" placeholder="Email" required autocomplete="off"/>
									</div>
								</div>
								<div class="row uniform">
									<div class="3u 12u$(xsmall)">
										<div class="password-wrapper">
											<input type="password" name="password" id="signup-pass" value="" placeholder="Password" required autocomplete="off"/>
											<span class="toggle-password" onclick="togglePasswordVisibility('signup-pass')">👁️</span>
										</div>
									</div>
									<div class="3u 12u$(xsmall)">
										<div class="password-wrapper">
											<input type="password" name="pass" id="signup-cpass" value="" placeholder="Retype Password" required autocomplete="off"/>
											<span class="toggle-password" onclick="togglePasswordVisibility('signup-cpass')">👁️</span>
										</div>
									</div>
								</div>
								<div class="row uniform">
									<div class="6u 12u$(xsmall)">
										<input type="text" name="addr" id="addr" value="" placeholder="Address" style="width:80%" required/>
									</div>
								</div>
								<div class="row uniform">
									<p>
										   <b>Category : </b>
										</p>
									<div class="3u 12u$(small)">
										<input type="radio" id="signup_farmer" name="category" value="1" checked>
										<label for="signup_farmer">User</label>
									</div>
									<div class="3u 12u$(small)">
										<input type="radio" id="signup_admin" name="category" value="0">
										<label for="signup_admin">Admin</label>
									</div>
								</div>
								<div class="row uniform">
									<div class="2u 12u$(small)">
										<input type="submit" value="Submit" name="submit" class="special" /></li>
									</div>
									<div class="2u 12u$(small)">
										<input type="reset" value="Reset" name="reset"/></li>
									</div>
									<div class="2u 12u$(small)">
										<button type="button" onclick="document.getElementById('id02').style.display='none'" class="button">Cancel</button>
									</div>
								</div>
							</center>
							</form>
						</section>

    </div>
    </div>
  </form>
</div>



<script>
// Get the modal
var modal = document.getElementById('id01');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

var modal1= document.getElementById('id02');

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal1) {
        modal1.style.display = "none";
    }
}

</script>


	</body>
</html>
