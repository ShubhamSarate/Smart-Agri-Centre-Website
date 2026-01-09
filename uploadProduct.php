<?php
 	session_start();
	require 'db.php';

    // Check if user is logged in and is a buyer
    if (!isset($_SESSION['logged_in']) || $_SESSION['Category'] != 0) {
        $_SESSION['message'] = "Only buyers can upload products!";
        header("Location: Login/error.php");
        exit();
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
		$productType = $_POST['type'];
		$productName = dataFilter($_POST['pname']);
		$productInfo = $_POST['pinfo'];
		$productPrice = dataFilter($_POST['price']);
		$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;
		$expiryDate = null;
		if(!empty($_POST['expiry'])){
			$expiryDate = dataFilter($_POST['expiry']); // expected YYYY-MM-DD
		}
		$fid = $_SESSION['id'];

		// Try to insert including expiry_date if provided; if the column doesn't exist, create it and retry

		$fid = $_SESSION['id'];

		// Ensure quantity column exists
		$colCheckSql = "SHOW COLUMNS FROM fproduct LIKE 'quantity'";
		$colRes = mysqli_query($conn, $colCheckSql);
		if (!$colRes || mysqli_num_rows($colRes) == 0) {
			$alter = "ALTER TABLE fproduct ADD COLUMN quantity INT DEFAULT 0";
			@mysqli_query($conn, $alter);
		}

		// If an expiry was provided, ensure the column exists first to avoid fatal errors
		if ($expiryDate !== null) {
			$colCheckSql = "SHOW COLUMNS FROM fproduct LIKE 'expiry_date'";
			$colRes = mysqli_query($conn, $colCheckSql);
			if (!$colRes || mysqli_num_rows($colRes) == 0) {
				$alter = "ALTER TABLE fproduct ADD COLUMN expiry_date DATE";
				@mysqli_query($conn, $alter); // attempt to add column; suppress warning if concurrent
			}
		}

		// Build insert SQL depending on whether expiry was provided
		if($expiryDate !== null){
			$sql = "INSERT INTO fproduct (fid, product, pcat, pinfo, price, expiry_date, quantity) VALUES ('$fid', '$productName', '$productType', '$productInfo', '$productPrice', '$expiryDate', '$quantity')";
		} else {
			$sql = "INSERT INTO fproduct (fid, product, pcat, pinfo, price, quantity) VALUES ('$fid', '$productName', '$productType', '$productInfo', '$productPrice', '$quantity')";
		}

		// Execute insert with exception handling (some setups throw mysqli_sql_exception)
		try {
			$result = mysqli_query($conn, $sql);
		} catch (mysqli_sql_exception $e) {
			// If the error is unknown column (1054) and expiry was provided, try to add the column and retry once
			if ($e->getCode() == 1054 && $expiryDate !== null) {
				$alter = "ALTER TABLE fproduct ADD COLUMN expiry_date DATE";
				@mysqli_query($conn, $alter);
				$result = mysqli_query($conn, $sql);
			} else {
				$result = false;
			}
		}

		if(!$result)
		{
			$_SESSION['message'] = "Unable to upload Product !!!";
			header("Location: Login/error.php");
		}
		else {
			$_SESSION['message'] = "successfull !!!";
		}

		$pic = $_FILES['productPic'];
		$picName = $pic['name'];
		$picTmpName = $pic['tmp_name'];
		$picSize = $pic['size'];
		$picError = $pic['error'];
		$picType = $pic['type'];
		$picExt = explode('.', $picName);
		$picActualExt = strtolower(end($picExt));
		$allowed = array('jpg','jpeg','png');

		if(in_array($picActualExt, $allowed))
		{
			if($picError === 0)
			{
				$_SESSION['productPicId'] = $_SESSION['id'];
				$picNameNew = $productName.$_SESSION['productPicId'].".".$picActualExt ;
				$_SESSION['productPicName'] = $picNameNew;
				$_SESSION['productPicExt'] = $picActualExt;
				$picDestination = "images/productImages/".$picNameNew;
				move_uploaded_file($picTmpName, $picDestination);
				$id = $_SESSION['id'];

				$sql = "UPDATE fproduct SET picStatus=1, pimage='$picNameNew' WHERE product='$productName';";

				$result = mysqli_query($conn, $sql);
				if($result)
				{

					$_SESSION['message'] = "Product Image Uploaded successfully !!!";
					header("Location: market.php");
				}
				else
				{
					//die("bad");
					$_SESSION['message'] = "There was an error in uploading your product Image! Please Try again!";
					header("Location: Login/error.php");
				}
			}
			else
			{
				$_SESSION['message'] = "There was an error in uploading your product image! Please Try again!";
				header("Location: Login/error.php");
			}
		}
		else
		{
			$_SESSION['message'] = "You cannot upload files with this extension!!!";
			header("Location: Login/error.php");
		}
	}

	function dataFilter($data)
	{
	    $data = trim($data);
	    $data = stripslashes($data);
	    $data = htmlspecialchars($data);
	    return $data;
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
		<script src="https://cdn.ckeditor.com/4.8.0/full/ckeditor.js"></script>
		<link rel="stylesheet" href="css/global-style.css" />
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<!--[if lte IE 8]><link rel="stylesheet" href="css/ie/v8.css" /><![endif]-->
		<style>
			body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
			.wrapper { background: transparent !important; }
			.form-card { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
			
			.form-group {
				margin-bottom: 20px;
			}
			
			.form-group label {
				display: block;
				font-weight: 600;
				color: #333;
				margin-bottom: 8px;
				font-size: 14px;
			}
			
			.select-wrapper { 
				width: 100%;
			}
			
			.select-wrapper select,
			#pname,
			#price,
			#expiry {
				width: 100%;
				padding: 12px 15px;
				border: 2px solid #ddd;
				border-radius: 8px;
				font-size: 14px;
				color: black;
				background: white;
				transition: border-color 0.3s ease;
				box-sizing: border-box;
				text-align: left;
			}
			
			.select-wrapper select {
				appearance: none;
				-webkit-appearance: none;
				-moz-appearance: none;
				padding-right: 30px;
				cursor: pointer;
				background: white url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E") no-repeat right 10px center;
				background-size: 12px;
			}
			
			.select-wrapper select option {
				color: black;
				background: white;
				padding: 5px 10px;
			}
			
			.select-wrapper select option:checked {
				background: #667eea;
				color: white;
			}
			
			.select-wrapper select:focus,
			#pname:focus,
			#price:focus,
			#expiry:focus {
				border-color: #667eea;
				outline: none;
				box-shadow: 0 0 5px rgba(102, 126, 234, 0.2);
			}
			
			.form-fields-grid {
				display: grid;
				grid-template-columns: 1fr 1fr;
				gap: 20px;
				margin-bottom: 25px;
			}
			
			@media (max-width: 768px) {
				.form-fields-grid {
					grid-template-columns: 1fr;
				}
			}
			
			textarea { 
				width: 100%; 
				padding: 12px 15px; 
				border: 2px solid #ddd; 
				border-radius: 8px; 
				font-size: 14px; 
				font-family: inherit;
				color: #333;
				transition: border-color 0.3s ease;
				box-sizing: border-box;
			}
			
			textarea:focus {
				border-color: #667eea;
				outline: none;
				box-shadow: 0 0 5px rgba(102, 126, 234, 0.2);
			}
			
			.submit-row { 
				display: flex; 
				gap: 15px; 
				margin-top: 30px;
				justify-content: center;
				flex-wrap: wrap;
			}
			.submit-row button,
			.submit-row a { 
				flex: 1;
				min-width: 200px;
				padding: 14px 30px !important;
				text-align: center;
				border-radius: 8px;
				font-weight: 600;
				cursor: pointer;
				transition: all 0.3s ease;
				display: inline-flex !important;
				align-items: center;
				justify-content: center;
				gap: 8px;
				text-decoration: none !important;
				border: none !important;
			}
			.submit-row button { 
				background: #007bff;
				color: white;
				box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
			}
			.submit-row button:hover { 
				background: #0056b3;
				transform: translateY(-2px);
				box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
			}
			.submit-row a { 
				background: #f0f0f0;
				color: black;
				border: 2px solid #e0e0e0 !important;
			}
			.submit-row a:hover { 
				background: #e8e8e8;
				border-color: #007bff !important;
				color: #007bff;
			}
		</style>
	</head>
	<body>

		<?php require 'menu.php'; ?>

		<!-- One -->

			<section id="one" class="wrapper style1 align-center">
				<div class="container">
					<div class="form-card">
						<h2><i class="fa fa-upload"></i> Upload Product</h2>
						<form method="POST" action="uploadProduct.php" enctype="multipart/form-data">
							<div style="margin: 25px 0; padding: 20px; background: #f8f9fa; border-radius: 8px; border: 2px dashed #667eea;">
								<label style="font-weight: 600; color: #333; margin-bottom: 10px; display: block;">
									<i class="fa fa-image"></i> Product Image
								</label>
								<input type="file" name="productPic" />
							</div>
							
							<div class="form-fields-grid">
								<div class="form-group">
									<label for="type"><i class="fa fa-list"></i> Category</label>
									<div class="select-wrapper">
										<select name="type" id="type" required>
											<option value="">- Select Category -</option>
											<option value="Fertilizer">Fertilizers</option>
											<option value="Seed">Seeds</option>
											<option value="Tool">Tools & Equipment</option>
											<option value="Pesticide">Pesticides</option>
											<option value="Other">Other</option>
										</select>
									</div>
								</div>
								
								<div class="form-group">
									<label for="pname"><i class="fa fa-cube"></i> Product Name</label>
									<input type="text" name="pname" id="pname" placeholder="Enter product name" required />
								</div>
								
								<div class="form-group">
									<label for="expiry"><i class="fa fa-calendar"></i> Expiry Date</label>
									<input type="date" name="expiry" id="expiry" />
								</div>
								
								<div class="form-group">
									<label for="price"><i class="fa fa-rupee"></i> Price</label>
									<input type="number" name="price" id="price" placeholder="Enter price" min="0" step="0.01" required />
								</div>

								<div class="form-group">
									<label for="quantity"><i class="fa fa-boxes"></i> Initial Quantity</label>
									<input type="number" name="quantity" id="quantity" placeholder="Enter quantity in stock" min="0" step="1" value="0" required />
								</div>
							</div>
							
							<div class="form-group" style="margin-top: 25px;">
								<label for="pinfo" style="display: block;"><i class="fa fa-file-text"></i> Product Description</label>
								<textarea name="pinfo" id="pinfo" rows="10" placeholder="Enter product details..."></textarea>
							</div>
							
							<div class="submit-row">
								<button type="submit" class="button"><i class="fa fa-check"></i> Upload Product</button>
								<a href="market.php" class="button secondary"><i class="fa fa-times"></i> Cancel</a>
							</div>
						</form>
					</div>
				</div>
			</section>

		<script>
			 CKEDITOR.replace( 'pinfo' );
		</script>
	</body>
</html>
