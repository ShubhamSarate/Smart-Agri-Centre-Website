<?php
    session_start();

	if(!isset($_SESSION['logged_in']) OR $_SESSION['logged_in'] != 1)
	{
		$_SESSION['message'] = "You have to Login to view this page!";
		header("Location: Login/error.php");
	}
?>

<!DOCTYPE HTML>

<html lang="en">
    <head>
           <title><?php echo ($_SESSION['Category'] == 1 ? 'User' : 'Admin'); ?> Profile: <?php echo $_SESSION['Username']; ?></title>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="bootstrap\css\bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="bootstrap\js\bootstrap.min.js"></script>
        <meta name="description" content="" />
		<meta name="keywords" content="" />
		<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
		<link rel="stylesheet" href="login.css"/>
		<script src="js/jquery.min.js"></script>
		<script src="js/skel.min.js"></script>
		<script src="js/skel-layers.min.js"></script>
		<script src="js/init.js"></script>
		<link rel="stylesheet" href="css/skel.css" />
        <link rel="stylesheet" href="css/style.css" />
        <link rel="stylesheet" href="css/style-xlarge.css" />
	        <link rel="stylesheet" href="css/global-style.css" />
	        <link rel="stylesheet" href="css/font-awesome.min.css">
	        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px 0; }
            .wrapper { background: transparent !important; padding: 40px 0; }
            .inner { max-width: 900px; margin: 0 auto; }
            .box { background: white; border-radius: 15px; padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
            .section-info { background: #f8f9fa; border-radius: 8px; padding: 35px; margin: 25px 0; border-left: 5px solid #667eea; }
            .section-info b { 
                color: #667eea; 
                font-size: 18px; 
                display: block; 
                margin-bottom: 12px;
                font-weight: 700;
            }
            .section-info .col-sm-4, 
            .section-info .col-sm-12 { 
                color: #333; 
                font-size: 18px; 
                line-height: 2; 
                margin-bottom: 25px;
                padding: 15px 0;
                border-bottom: 1px solid #e0e0e0;
            }
            .section-info .col-sm-4:last-child,
            .section-info .col-sm-12:last-child {
                border-bottom: none;
            }
            .action-buttons { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; margin-top: 25px; }
            .action-buttons a { 
                flex: 1; 
                min-width: 140px; 
                text-align: center; 
                padding: 12px 20px !important;
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
            }
            header { text-align: center; margin-bottom: 30px; }
            header h2 { color: #333; font-weight: 700; margin: 15px 0 5px 0; font-size: 32px; }
            header h4 { color: #667eea; margin: 0; font-size: 28px; font-weight: 700; }
        </style>

    </head>


    <body>

        <?php
            require 'menu.php';
        ?>

        <section id="one" class="wrapper style1 align">
            <div class="inner">
                <div class="box">
                <header>
                    <center>
                    <?php
                        require 'db.php';
                        // Fetch the profile picture and full name from database
                        $userId = $_SESSION['id'];
                        $userCategory = isset($_SESSION['Category']) ? $_SESSION['Category'] : 0;
                        
                        $profilePic = 'profile0.png';
                        $displayName = 'User'; // default fallback
                        
                        if ($userCategory == 1) {
                            // Farmer
                            $colCheckSql = "SHOW COLUMNS FROM farmer LIKE 'picExt'";
                            $colRes = mysqli_query($conn, $colCheckSql);
                            if (!$colRes || mysqli_num_rows($colRes) == 0) {
                                $alterSql = "ALTER TABLE farmer ADD COLUMN picExt VARCHAR(10) DEFAULT 'png'";
                                @mysqli_query($conn, $alterSql);
                            }
                            $sql = "SELECT picExt, fname FROM farmer WHERE fid='$userId'";
                            $result = mysqli_query($conn, $sql);
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $picExt = isset($row['picExt']) && !empty($row['picExt']) ? $row['picExt'] : 'png';
                                $profilePic = "profile_user_" . $userId . "." . $picExt;
                                $displayName = isset($row['fname']) && !empty($row['fname']) ? $row['fname'] : 'User';
                            }
                        } else {
                            // Buyer/Admin
                            $colCheckSql = "SHOW COLUMNS FROM buyer LIKE 'picExt'";
                            $colRes = mysqli_query($conn, $colCheckSql);
                            if (!$colRes || mysqli_num_rows($colRes) == 0) {
                                $alterSql = "ALTER TABLE buyer ADD COLUMN picExt VARCHAR(10) DEFAULT 'png'";
                                @mysqli_query($conn, $alterSql);
                            }
                            $sql = "SELECT picExt, bname FROM buyer WHERE bid='$userId'";
                            $result = mysqli_query($conn, $sql);
                            if ($result && mysqli_num_rows($result) > 0) {
                                $row = mysqli_fetch_assoc($result);
                                $picExt = isset($row['picExt']) && !empty($row['picExt']) ? $row['picExt'] : 'png';
                                $profilePic = "profile_admin_" . $userId . "." . $picExt;
                                $displayName = isset($row['bname']) && !empty($row['bname']) ? $row['bname'] : 'User';
                            }
                        }
                    ?>
                    <span>
                        <img src="<?php echo 'images/profileImages/'.$profilePic.'?'.mt_rand(); ?>" alt="Profile Image" style="width:150px;height:150px;object-fit:cover;border-radius:50%;border:5px solid white;box-shadow:0 4px 15px rgba(0,0,0,0.2);" />
                    </span>
                    <br>
                    <h2 style="color:#2c3e50;"><?php echo htmlspecialchars($displayName);?></h2>
                    <br>
                </center>
                </header>
                <div style="text-align:center;margin-bottom:12px;">
                    <a href="market.php" class="button" style="background:#007bff;color:white;padding:10px 18px;border-radius:8px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;"><i class="fa fa-arrow-left"></i> Back to Market</a>
                </div>
                    <div class="section-info">
                        <div class="row">
                            
                            <div class="col-sm-4">
                                <b>Email ID:</b><br>
                                <?php echo $_SESSION['Email'];?>
                            </div>
                            <div class="col-sm-4">
                                <b>Mobile No:</b><br>
                                <?php echo $_SESSION['Mobile'];?>
                            </div>
                        </div>
                    </div>
                    <div class="section-info">
                        <div class="row">
                            <div class="col-sm-12">
                                <b>ADDRESS:</b><br>
                                <?php echo $_SESSION['Addr'];?>
                            </div>
                        </div>
                    </div>
                    <div class="action-buttons">
                        <a href="Profile/changePassPage.php" class="button">Change Password</a>
                        <a href="profileEdit.php" class="button">Edit Profile</a>
                        <?php if(isset($_SESSION['Category']) && $_SESSION['Category'] == 0): ?>
                            <a href="uploadProduct.php" class="button">Upload Product</a>
                        <?php endif; ?>
                        <a href="Login/logout.php" class="button secondary">LOG OUT</a>
                                    </div>
                                </div>
                            </center>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Scripts -->
            <script src="assets/js/jquery.min.js"></script>
            <script src="assets/js/jquery.scrolly.min.js"></script>
            <script src="assets/js/jquery.scrollex.min.js"></script>
            <script src="assets/js/skel.min.js"></script>
            <script src="assets/js/util.js"></script>
            <script src="assets/js/main.js"></script>



    </body>
</html>
