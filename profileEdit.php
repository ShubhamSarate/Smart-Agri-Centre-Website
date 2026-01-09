<?php
    session_start();
?>

<?php
    // Safe fallbacks for session values to avoid undefined index warnings
    $categoryPrefix = (isset($_SESSION['Category']) && $_SESSION['Category'] == 1) ? 'user' : 'admin';
    $picExt = $_SESSION['picExt'] ?? 'png';
    $profilePic = "profile_" . $categoryPrefix . "_" . $_SESSION['id'] . "." . $picExt;
    // Fallback to default if not set
    if (!isset($_SESSION['picName']) || empty($_SESSION['picName'])) {
        $profilePic = 'profile0.png';
    }
    $displayName = isset($_SESSION['Name']) ? $_SESSION['Name'] : '';
    $displayUsername = isset($_SESSION['Username']) ? $_SESSION['Username'] : '';
    // some code uses MobileNo, others use Mobile — prefer Mobile then MobileNo
    $displayMobile = isset($_SESSION['Mobile']) ? $_SESSION['Mobile'] : (isset($_SESSION['MobileNo']) ? $_SESSION['MobileNo'] : '');
    $displayEmail = isset($_SESSION['Email']) ? $_SESSION['Email'] : '';
    $displayAddr = isset($_SESSION['Addr']) ? $_SESSION['Addr'] : '';
    $displayCity = isset($_SESSION['City']) ? $_SESSION['City'] : '';
    $displayPincode = isset($_SESSION['Pincode']) ? $_SESSION['Pincode'] : '';
    $displaySection = isset($_SESSION['Section']) ? $_SESSION['Section'] : 'Other';
    $displayPost = isset($_SESSION['Post']) ? $_SESSION['Post'] : '';
?>

<!DOCTYPE HTML>

<html lang="en">
    <head>
        <title>Profile: <?php echo $_SESSION['Username']; ?></title>
        <meta charset="utf-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="bootstrap\css\bootstrap.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="bootstrap\js\bootstrap.min.js"></script>
        <link rel="stylesheet" href="assets/css/main.css" />
        <link rel="stylesheet" href="css/font-awesome.min.css">
        <style>
            * { margin: 0; padding: 0; box-sizing: border-box; }
            
            body {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                min-height: 100vh;
                padding: 40px 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            
            .wrapper {
                background: transparent !important;
            }
            
            .inner {
                max-width: 900px;
                margin: 0 auto;
                padding: 0 20px;
            }
            
            .main-card {
                background: white;
                border-radius: 15px;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
                overflow: hidden;
                animation: slideUp 0.5s ease-out;
            }
            
            @keyframes slideUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .profile-header {
                display: flex;
                align-items: center;
                gap: 30px;
                padding: 40px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }
            
            .profile-img {
                flex: 0 0 auto;
            }
            
            .profile-img img {
                border-radius: 50%;
                width: 150px;
                height: 150px;
                object-fit: cover;
                border: 5px solid white;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            }
            
            .profile-info h2 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
                text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .profile-info h4 {
                margin: 8px 0 0;
                font-size: 16px;
                opacity: 0.95;
            }
            
            .profile-content {
                padding: 40px;
            }
            
            .pic-upload-section {
                background: #f8f9fa;
                padding: 25px;
                border-radius: 10px;
                margin-bottom: 30px;
                border: 2px dashed #667eea;
            }
            
            .pic-upload-section label {
                display: block;
                margin-bottom: 12px;
                font-weight: 600;
                color: #333;
            }
            
            .pic-upload-section input[type="file"] {
                display: block;
                width: 100%;
                padding: 10px;
                margin-bottom: 15px;
                border: 1px solid #ddd;
                border-radius: 6px;
                cursor: pointer;
            }
            
            .button-group-pic {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
            }
            
            .row.uniform {
                display: grid;
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                gap: 20px;
                align-items: end;
            }
            
            .row.uniform input[type="text"],
            .row.uniform input[type="email"],
            .row.uniform input[type="password"] {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.3s ease;
                font-family: inherit;
            }
            
            .row.uniform input[type="text"]:focus,
            .row.uniform input[type="email"]:focus,
            .row.uniform input[type="password"]:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .select-wrapper {
                position: relative;
            }
            
            .select-wrapper select {
                width: 100%;
                padding: 12px 15px;
                border: 2px solid #e0e0e0;
                border-radius: 8px;
                font-size: 14px;
                background: white;
                cursor: pointer;
                transition: all 0.3s ease;
                font-family: inherit;
                appearance: none;
                background-image: url("data:image/svg+xml;charset=UTF-8,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'%3e%3cpolyline points='6 9 12 15 18 9'%3e%3c/polyline%3e%3c/svg%3e");
                background-repeat: no-repeat;
                background-position: right 10px center;
                background-size: 20px;
                padding-right: 40px;
            }
            
            .select-wrapper select:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            
            .form-section {
                margin-bottom: 30px;
            }
            
            .form-section-title {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 15px;
                padding-bottom: 12px;
                border-bottom: 2px solid #667eea;
                font-weight: 600;
                color: #333;
            }
            
            .form-section-title i {
                color: #667eea;
                font-size: 18px;
            }
            
            .radio-group {
                display: flex;
                gap: 20px;
                flex-wrap: wrap;
                margin-bottom: 20px;
            }
            
            .radio-group label {
                display: flex;
                align-items: center;
                gap: 8px;
                cursor: pointer;
                font-weight: 500;
                color: #555;
            }
            
            .radio-group input[type="radio"] {
                cursor: pointer;
                width: 18px;
                height: 18px;
                accent-color: #667eea;
            }
            
            .button-container {
                display: flex;
                gap: 12px;
                justify-content: center;
                margin-top: 35px;
                padding-top: 25px;
                border-top: 2px solid #e0e0e0;
                flex-wrap: wrap;
            }
            
            .button.special,
            .button.primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 12px 30px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
            }
            
            .button.special:hover,
            .button.primary:hover {
                transform: translateY(-2px);
                box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            }
            
            .button.special:active,
            .button.primary:active {
                transform: translateY(0);
            }
            
            .button.secondary {
                background: #f0f0f0;
                color: #333;
                border: 2px solid #e0e0e0;
                padding: 12px 30px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.3s ease;
            }
            
            .button.secondary:hover {
                background: #e8e8e8;
                border-color: #667eea;
                color: #667eea;
            }
            
            .button.small {
                padding: 8px 16px;
                font-size: 13px;
            }
            
            .pic-upload-section .button-group-pic .button {
                flex: 1;
                min-width: 120px;
            }
            
            @media (max-width: 768px) {
                .profile-header {
                    flex-direction: column;
                    text-align: center;
                    padding: 30px 20px;
                }
                
                .profile-img img {
                    width: 120px;
                    height: 120px;
                }
                
                .profile-info h2 {
                    font-size: 24px;
                }
                
                .profile-content {
                    padding: 20px;
                }
                
                .row.uniform {
                    grid-template-columns: 1fr;
                }
                
                .button-container {
                    flex-direction: column;
                }
                
                .button-container .button {
                    width: 100%;
                }
            }
        </style>
    </head>

    <body class="subpage">

        <section id="post" class="wrapper">
            <div class="inner">
                <div class="main-card">
                    <div class="profile-header">
                        <div class="profile-img">
                            <img src="<?php echo 'images/profileImages/'.$profilePic.'?'.mt_rand(); ?>" alt="Profile Image" />
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($displayName); ?></h2>
                            <h4><?php echo htmlspecialchars($displayUsername); ?></h4>
                        </div>
                    </div>
                    <div class="profile-content">
                        <form method="post" action="Profile/updatePic.php" enctype="multipart/form-data">
                            <div class="pic-upload-section">
                                <label><i class="fa fa-image"></i> Update Profile Picture</label>
                                <input type="file" name="profilePic" id="profilePic" />
                                <div class="button-group-pic">
                                    <input type="submit" class="button special small" name="upload" value="Upload" />
                                    <input type="submit" class="button secondary small" name="remove" value="Remove" />
                                </div>
                            </div>
                        </form>
                        <form method="post" action="Profile/updateProfile.php">
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fa fa-user"></i> Personal Information
                                </div>
                                <div class="row uniform">
                                    <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($displayName);?>" placeholder="Full Name" required />
                                    <input type="text" name="mobile" id="mobile" value="<?php echo htmlspecialchars($displayMobile);?>" placeholder="Mobile No" required/>
                                    <input type="text" name="uname" id="uname" value="<?php echo htmlspecialchars($displayUsername);?>" placeholder="Username" required/>
                                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($displayEmail);?>" placeholder="Email" required/>
                                    <input type="text" name="addr" id="addr" value="<?php echo htmlspecialchars($displayAddr);?>" placeholder="Address" required/>
                                    <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($displayCity);?>" placeholder="City" />
                                    <input type="text" name="pincode" id="pincode" value="<?php echo htmlspecialchars($displayPincode);?>" placeholder="Pincode" />
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fa fa-briefcase"></i> Professional Details
                                </div>
                                <div class="row uniform" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));">
                                    <div class="select-wrapper">
                                        <select name="section" id="section">
                                            <option value="<?php echo htmlspecialchars($displaySection);?>"><?php echo htmlspecialchars($displaySection);?></option>
                                            <option value="Band">Band</option>
                                            <option value="Drama">Drama</option>
                                            <option value="Dance">Dance</option>
                                            <option value="Decoration">Decoration</option>
                                            <option value="Other">Other</option>
                                        </select>
                                    </div>
                                    <input type="text" name="post" id="post" value="<?php echo htmlspecialchars($displayPost);?>" placeholder="Post Name" required/>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fa fa-graduation-cap"></i> Education
                                </div>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" id="diploma" name="edu" value="Diploma" checked>
                                        Diploma
                                    </label>
                                    <label>
                                        <input type="radio" id="btech" name="edu" value="B.TECH">
                                        B.TECH
                                    </label>
                                    <label>
                                        <input type="radio" id="mtech" name="edu" value="M.TECH">
                                        M.TECH
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <div class="form-section-title">
                                    <i class="fa fa-calendar"></i> Year of Study
                                </div>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" id="year1" name="year" value="1" checked>
                                        1<sup>st</sup> Year
                                    </label>
                                    <label>
                                        <input type="radio" id="year2" name="year" value="2">
                                        2<sup>nd</sup> Year
                                    </label>
                                    <label>
                                        <input type="radio" id="year3" name="year" value="3">
                                        3<sup>rd</sup> Year
                                    </label>
                                    <label>
                                        <input type="radio" id="year4" name="year" value="4">
                                        4<sup>th</sup> Year
                                    </label>
                                </div>
                            </div>
                            
                            <div class="button-container">
                                <input type="submit" class="button special" value="Update Profile" />
                                <a href="profileView.php" class="button secondary" style="text-decoration: none; text-align: center;">Cancel</a>
                            </div>
                        </form>
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
