<?php
	session_start();
	require 'db.php';
	if(!isset($_SESSION['logged_in']) OR $_SESSION['logged_in'] == 0)
	{
		$_SESSION['message'] = "You need to first login to access this page !!!";
		header("Location: Login/error.php");
	}
    $bid = $_SESSION['id'];
    // Add to cart (existing behavior)
    if(isset($_GET['flag']))
    {
        $pid = dataFilter($_GET['pid']);

        $sql = "INSERT INTO mycart (bid,pid)
               VALUES ('$bid', '$pid')";
        $result = mysqli_query($conn, $sql);
    }

    // Remove from cart (new behavior)
    if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_pid'])) {
        $removePid = dataFilter($_POST['remove_pid']);
        $sql = "DELETE FROM mycart WHERE bid='$bid' AND pid='$removePid' LIMIT 1";
        mysqli_query($conn, $sql);
        // redirect to avoid form resubmission
        header("Location: myCart.php");
        exit();
    }

?>
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>AgroCulture: My Cart</title>
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
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
		<link rel="stylesheet" href="css/global-style.css" />
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<style>
			body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
			.wrapper { background: transparent !important; }
			.cart-item { background: #f8f9fa; border-radius: 8px; padding: 15px; margin: 15px 0; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
			.product-info { display: flex; gap: 20px; align-items: flex-start; }
			.product-img { flex: 0 0 150px; }
			.product-img img { width: 100%; border-radius: 8px; }
			/* Ensure cart product images are uniform */
			section#two .image.fit {
				width: 100%;
				height: 160px; /* fixed display box height */
				display: block;
				object-fit: cover; /* crops but preserves aspect ratio */
				border-radius: 12px !important;
			}

			@media (max-width: 768px) {
				section#two .image.fit { height: 120px; }
			}
			.product-details { flex: 1; }
		</style>
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
					<header class="major">
						<h2>My Cart</h2>
						<a href="productMenu.php?n=1" class="button" style="display:inline-flex;align-items:center;gap:8px;margin-top:12px;"><i class="fa fa-arrow-left"></i> Go Back</a>
					</header>

				<section id="two" class="wrapper style2 align-center">
				<div class="container">
					<?php
                        $sql = "SELECT * FROM mycart WHERE bid = '$bid'";
                        $result = mysqli_query($conn, $sql);
						while($row = $result->fetch_array()):
                            $pid = $row['pid'];
                            $sql = "SELECT * FROM fproduct WHERE pid = '$pid'";
                            $result1 = mysqli_query($conn, $sql);
                            $row1 = $result1->fetch_array();
							$picDestination = "images/productImages/".$row1['pimage'];
						?>
							<div class="col-md-4">
							<section>
							<strong><h2 class="title" style="color: #333; "><?php echo $row1['product'].'';?></h2></strong>
							<a href="review.php?pid=<?php echo $row1['pid'] ;?>" > <img class="image fit" src="<?php echo $picDestination;?>" alt="" style="border-radius: 12px;" /></a>

					<div style="align: left">
					<blockquote><?php echo "Type : ".$row1['pcat'].'';?><br><?php echo "Price : ".$row1['price'].' /-';?><br><?php if(!empty($row1['expiry_date'])) echo 'Expiry: '.htmlspecialchars($row1['expiry_date'])."<br>"; ?></blockquote>
					<form method="post" onsubmit="return confirm('Remove this item from cart?');" style="text-align:center;margin:15px 0;">
						<input type="hidden" name="remove_pid" value="<?php echo $row1['pid']; ?>">
						<button type="submit" class="btn btn-danger" style="padding:8px 20px;font-size:14px;border-radius:4px;text-transform:uppercase;letter-spacing:1px;">
							<i class="fa fa-trash"></i> Remove
						</button>
					</form>
				</div>
				</section>
				</div>

                    <?php endwhile;    ?>

					</div>

			</section>
					</header>

			</section>

	</body>
</html>
