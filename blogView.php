<?php
	session_start();

	require 'db.php';

	if(!isset($_SESSION['logged_in']) OR $_SESSION['logged_in'] == 0)
	{
		$_SESSION['message'] = "You need to first login to access this page !!!";
		header("Location: Login/error.php");
	}

	if ($_SERVER["REQUEST_METHOD"] == "POST" AND isset($_SESSION['logged_in']) AND $_SESSION['logged_in'] == 1)
	{
		// Admin inline comment deletion
		if (isset($_POST['delete_comment_inline']) && isset($_SESSION['Category']) && $_SESSION['Category'] == 0) {
			$delBlogId = isset($_POST['delete_comment_blogId']) ? intval($_POST['delete_comment_blogId']) : 0;
			$delTime = isset($_POST['delete_comment_time']) ? mysqli_real_escape_string($conn, $_POST['delete_comment_time']) : '';
			if ($delBlogId && $delTime) {
				mysqli_query($conn, "DELETE FROM blogfeedback WHERE blogId = $delBlogId AND commentTime = '$delTime' LIMIT 1");
				$_SESSION['message'] = "Comment deleted.";
				header("Location: blogView.php?id=$delBlogId");
				exit();
			}
		}
		if(isset($_POST['comment']) AND $_POST['comment'] != "")
		{
			$sql = "SELECT * FROM blogdata ORDER BY blogId DESC";
			$result = mysqli_query($conn, $sql);

			while($row = $result->fetch_array())
			{
				$check = "submit".$row['blogId'];
				if(isset($_POST[$check]))
				{
					$blogId = $row['blogId'];
					break;
	 			}
			}

			$comment = dataFilter($_POST['comment']);
			if(isset($_SESSION['logged_in']) AND $_SESSION['logged_in'] == 1)
			{
				$commentUser = $_SESSION['Username'];
				$pic = $_SESSION['picName'];
			}
			else {
				$commentUser = "Anonymous";
				$pic = "profile0.png";
			}
			if(isset($blogId))
			{
				// Ensure commentTime column exists
				$colCheckSql = "SHOW COLUMNS FROM blogfeedback LIKE 'commentTime'";
				$colRes = mysqli_query($conn, $colCheckSql);
				if (!$colRes || mysqli_num_rows($colRes) == 0) {
					$alter = "ALTER TABLE blogfeedback ADD COLUMN commentTime TIMESTAMP DEFAULT CURRENT_TIMESTAMP";
					$alterResult = mysqli_query($conn, $alter);
					if (!$alterResult) {
						// Column might already exist or there's an error
						// Continue anyway - the comment will still work
					}
				}
				
				// Use prepared statement for safety
				$blogId = intval($blogId);  // Ensure it's an integer
				$sql = "INSERT INTO blogfeedback (blogId, comment, commentUser, commentPic, commentTime)
						VALUES ($blogId, ?, ?, ?, NOW());";
				$stmt = mysqli_prepare($conn, $sql);
				mysqli_stmt_bind_param($stmt, "sss", $comment, $commentUser, $pic);
				$result = mysqli_stmt_execute($stmt);
				
				if($result) {
					mysqli_stmt_close($stmt);
					// Store the new comment ID in session for highlighting
					$_SESSION['new_comment_id'] = mysqli_insert_id($conn);
					// Refresh page to show new comment
					header("Location: blogView.php?id=" . $blogId . "#comments");
					exit();
				} else {
					echo "Comment Error: " . mysqli_error($conn);
					echo "<br>BlogId: " . $blogId;
					echo "<br>Comment: " . $comment;
					echo "<br>User: " . $commentUser;
					mysqli_stmt_close($stmt);
				}
			}
		}

		else
		{
			$sql = "SELECT * FROM blogdata ORDER BY blogId DESC";
			$result = mysqli_query($conn, $sql);

			$deleteTarget = null;
			$likeTarget = null;
			while($row = $result->fetch_array())
			{
				$checkLike = "like".$row['blogId'];
				$checkDelete = "delete".$row['blogId'];
				if(isset($_POST[$checkDelete]))
				{
					$deleteTarget = intval($row['blogId']);
					break;
				}
				if(isset($_POST[$checkLike]))
				{
					$likeTarget = $row['blogId'];
					break;
				}
			}

			// Handle delete (admin-only)
			if($deleteTarget !== null)
			{
				if(!isset($_SESSION['Category']) || $_SESSION['Category'] != 0)
				{
					$_SESSION['message'] = "You need admin access to delete blogs.";
					header("Location: blogView.php");
					exit();
				}
				$did = $deleteTarget;
				$queries = [];
				$queries[] = "DELETE FROM blogfeedback WHERE blogId = $did";
				$queries[] = "DELETE FROM likedata WHERE blogId = $did";
				$queries[] = "DELETE FROM blogdata WHERE blogId = $did";
				$ok = true;
				foreach($queries as $q)
				{
					if(!mysqli_query($conn, $q))
					{
						$ok = false;
						// Log error to session for visibility
						$_SESSION['message'] = "Error deleting blog: ".mysqli_error($conn);
					}
				}
				if($ok)
				{
					$_SESSION['message'] = "Blog $did deleted successfully.";
				}
				header("Location: blogView.php");
				exit();
			}

			// proceed to like handling if any
			if($likeTarget !== null)
			{
				$blogId = $likeTarget;
				$likeCheck = "isLiked".$blogId;
				if(!isset($_SESSION[$likeCheck]) OR $_SESSION[$likeCheck] == 0)
				{
					$id = $_SESSION['id'];
					$sql = "SELECT * FROM likedata WHERE blogId = '$blogId' AND blogUserId = '$id'";
					$result = mysqli_query($conn, $sql);
					$num_rows = mysqli_num_rows($result);
					if($num_rows == 0)
					{
						$sql = "INSERT INTO likedata (blogId, blogUserId)
								VALUES('$blogId', '$id')";
						$result = mysqli_query($conn, $sql);
						$sql = "UPDATE blogdata SET likes = likes + 1 WHERE blogId = '$blogId'";
						$result = mysqli_query($conn, $sql);
						$_SESSION[$likeCheck] = 1;
					}
				}
			}
		}
	}

	function dataFilter($data)
	{
		$data = trim($data);
		$data = stripslashes($data);
		// Remove HTML tags but do not encode entities here; escape on output instead
		$data = strip_tags($data);
		return $data;
	}

	$sql = "SELECT * FROM blogdata ORDER BY blogId DESC";
	$result = mysqli_query($conn, $sql);

	function formatDate($date)
	{
		return date('g:i a', strtotime($date));
	}

?>

<!DOCTYPE HTML>

<html>
	<head>
		<title>AgroCulture : Blogs</title>
		<meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <link href="bootstrap\css\bootstrap.min.css" rel="stylesheet">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
        <script src="bootstrap\js\bootstrap.min.js"></script>
        <meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="description" content="" />
		<meta name="keywords" content="" />
		<!--[if lte IE 8]><script src="css/ie/html5shiv.js"></script><![endif]-->
		<script src="js/jquery.min.js"></script>
		<script src="js/skel.min.js"></script>
		<script src="js/skel-layers.min.js"></script>
		<script src="js/init.js"></script>
		<link rel="stylesheet" href="css/skel.css" />
		<link rel="stylesheet" href="css/style.css" />
		<link rel="stylesheet" href="css/style-xlarge.css" />
		<link rel="stylesheet" href="Blog/commentBox.css" />
		<link rel="stylesheet" href="css/global-style.css" />
		<link rel="stylesheet" href="css/font-awesome.min.css">
		<style>
			:root{
				--accent: #667eea;
				--muted: #6b7280;
				--card-bg: #ffffff;
			}
			body { background: linear-gradient(135deg, var(--accent) 0%, #764ba2 100%); color: #222; }
			.wrapper { background: transparent !important; padding: 40px 0; }
			.blog-list.container { max-width: 920px; margin: 0 auto; }
			.blog-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
			.blog-header h1 { color: var(--accent); margin: 0; font-size: 34px; display: flex; gap: 10px; align-items: center; }
			.blog-header .button { white-space: nowrap; }
			/* Ensure buttons and their icons/text are centered */
			.button,
			.button.small,
			.button.special,
			a.button,
			button,
			input[type="button"],
			input[type="submit"],
			input[type="submit"].button {
				display: inline-flex !important;
				align-items: center;
				justify-content: center;
				text-align: center;
				gap: 8px;
			}
			input[type="submit"].button,
			input[type="submit"] { padding: 8px 14px; }
			
			.box { background: var(--card-bg) !important; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.08); padding: 22px; margin-bottom: 20px; }
			.box h2 { margin-top: 0; color: #111; font-size: 26px; line-height: 1.2; }
			.box blockquote { margin: 10px 0 0 0; color: #333; background: #fbfbfd; padding: 14px; border-left: 6px solid var(--accent); border-radius: 6px; font-size: 17px; line-height: 1.5;
				/* Reduced-height scrollable content box (per-blog) */
				max-height: 140px; overflow-y: auto;
				-ms-overflow-style: none; /* IE and Edge */
				scrollbar-width: none; /* Firefox */
			}
			.box blockquote::-webkit-scrollbar { display: none; } /* WebKit */
			.box blockquote p { margin: 10px 0 0 0; color: var(--muted); font-size: 15px; }
			.box .meta { display: flex; justify-content: space-between; gap: 12px; color: var(--muted); font-size: 15px; margin-top: 12px; }
			
			/* Action row */
			.box .actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-top: 14px; }
			.box .actions .left { display:flex; gap:10px; align-items:center; }
			.box .actions .left .likes { display:flex; gap:8px; align-items:center; color:var(--muted); }
			.box .actions .right { display:flex; align-items:center; gap:8px; }
			
			/* Comment form */
			.box textarea { width: 100%; min-height: 80px; padding: 14px 16px; border: 1px solid #e6e6ef; border-radius: 8px; resize: vertical; font-family: inherit; font-size: 16px; }
			.box .comment-submit { display:flex; justify-content:center; align-items:center; margin-top:12px; padding-top:6px; }
			.box .button.small { padding: 8px 14px; }
			
			/* Comments list */
				.con.darker { background: #ffffff; margin-top: 12px; padding: 14px; border-radius: 8px; border: 1px solid #f0f0f5; font-size: 16px; line-height: 1.6; color: #000 !important; }
				.con.darker img { width:44px; height:44px; border-radius:50%; object-fit:cover; margin-right:12px; vertical-align:middle; }
				.con.darker span em { font-style: normal; font-weight:700; margin-left:8px; color:#000 !important; }
				.con.darker .time-right { float:right; color:#000 !important; font-size:13px; }
				.con.darker a { color: #000 !important; }
			
			@media (max-width: 768px){
				.blog-list.container { padding: 0 18px; }
				.box { padding: 16px; }
			}
		</style>
		<style>
			/* Override to vertically center submit text */
			.box .comment-submit input[type="submit"],
			.box .comment-submit .button {
				display: inline-flex !important;
				align-items: center;
				justify-content: center;
				height: 40px;
				padding: 0 14px;
				line-height: 1;
				border-radius: 8px;
			}
		</style>
	</head>
	<body class="subpage">

		<?php
			require 'menu.php';

		?>

		<section id="main" class="wrapper">
			<div class="inner">
					<div class="blog-list container">
					<!-- Comments box styles -->
					<style>
					.comments-container {
						max-height: 320px;
						overflow-y: auto;
						padding: 10px;
						border: 1px solid #e6e6ef;
						border-radius: 8px;
						background: #ffffff;
					}
					.comment-item {
						display: flex;
						gap: 12px;
						padding: 12px 8px;
						border-bottom: 1px solid #f0f0f5;
						align-items: flex-start;
					}
					.comment-avatar {
						width: 48px; height: 48px; border-radius: 50%; object-fit: cover; flex: 0 0 48px;
					}
					.comment-body { flex: 1; }
					.comment-meta { font-size: 13px; color: #333; }
					.comment-user { font-weight: 700; margin-right: 8px; }
					.comment-time { color: #777; font-size: 12px; }
					.comment-text { margin-top: 6px; white-space: pre-wrap; }
					/* small screens */
					@media (max-width:600px){ .comments-container{max-height:260px;} .comment-avatar{width:40px;height:40px;flex:0 0 40px;} }
					</style>
					<div class="blog-header">
						<h1><i class="fa fa-newspaper-o"></i> Blogs</h1>
						<a href="blogWrite.php" class="button"><i class="fa fa-pencil"></i> Write a Blog</a>
					</div>
					<?php
						while($row = $result->fetch_array()) :
							$id = intval($row['blogId']);
							$count_sql = "SELECT COUNT(*) as cnt FROM blogfeedback WHERE blogId = $id";
							$count_result = mysqli_query($conn, $count_sql);
							$count_row = mysqli_fetch_assoc($count_result);
							$numComment = $count_row['cnt'];
					?>
					<div class="box">
						<h2><?= $row['blogTitle']; ?></h2>
						<blockquote>
							<?= $row['blogContent']; ?>
							<p>--- <?= $row['blogUser']; ?></p>
							<p><?= $row['blogTime']; ?></p>
						</blockquote>

						<form method="post" action="blogView.php">
							<div class="row">
								<div class="6u 12u$(xsmall)">
									<button type="submit" class="button special small" name="<?php echo 'like'.$id; ?>">
									<span class="glyphicon glyphicon-thumbs-up"></span> Like</button>
									<span><?= $row['likes']?></span>
								</div>
								<div class="6u 12u$(xsmall)">
									<span class="glyphicon glyphicon-pencil"></span>
									Comments: <?= intval($numComment) ?>
								</div>
								<?php if(isset($_SESSION['logged_in']) && isset($_SESSION['Category']) && $_SESSION['Category'] == 0): ?>
									<div class="6u 12u$(xsmall)" style="display:flex; align-items:center; justify-content:flex-end; text-align:right;">
										<input type="submit" name="<?php echo 'delete'.$id; ?>" class="button" value="Delete" style="background:#d9534f;color:#fff;border-radius:6px;padding-left:10px;padding-right:10px;padding-top:4px;padding-bottom:8px;height:36px;line-height:12px;" onclick="return confirm('Delete this blog and all its comments?');" />
									</div>
								<?php endif; ?>
								<div class="12u$">
									<br>
									<textarea name="comment" id="comment" rows="1" placeholder="Write a Comment!"></textarea>
								</div>
								<div class="12u$">
									<div class="comment-submit">
										<input type="submit" name="<?php echo 'submit'.$id; ?>" class="button special small" value="Submit"/>
									</div>
								</div>
							</div>
						</form>

						<!-- React Comments Component mounts here -->
						<div id="comments-root" data-blogid="<?= intval($id) ?>"></div>
						<!-- Server-rendered comments fallback (visible without React) -->
						<div id="comments-<?= intval($id) ?>" class="comments-container" style="margin-top:12px;">
						<?php
							$commSql = "SELECT comment, commentUser, commentPic, commentTime FROM blogfeedback WHERE blogId = $id ORDER BY commentTime DESC";
							$commRes = mysqli_query($conn, $commSql);
							if ($commRes && mysqli_num_rows($commRes) > 0) {
								while ($crow = mysqli_fetch_assoc($commRes)) {
									echo '<div class="comment-item">';
									echo '<img class="comment-avatar" src="/Shubham/images/profileImages/'.htmlspecialchars($crow['commentPic']).'" alt="Avatar">';
									echo '<div class="comment-body">';
									echo '<div class="comment-meta"><span class="comment-user">'.htmlspecialchars($crow['commentUser']).'</span> <span class="comment-time">'.htmlspecialchars($crow['commentTime']).'</span></div>';
									echo '<div class="comment-text">'.nl2br(htmlspecialchars($crow['comment'])).'</div>';
									if (isset($_SESSION['logged_in']) && isset($_SESSION['Category']) && $_SESSION['Category'] == 0) {
										echo '<form method="post" style="margin-top:8px;">';
										echo '<input type="hidden" name="delete_comment_blogId" value="'.intval($id).'">';
										echo '<input type="hidden" name="delete_comment_time" value="'.htmlspecialchars($crow['commentTime']).'">';
										echo '<button type="submit" name="delete_comment_inline" class="btn btn-xs btn-danger" style="margin-left:12px;">Delete</button>';
										echo '</form>';
									}
									echo '</div>';
									echo '</div>';
								}
							} else {
								echo '<div style="color:#999;font-size:13px;">No comments yet.</div>';
							}
						?>
						</div>
						<noscript>
							<p style="color:#999; font-size:12px;">JavaScript is required to post comments.</p>
						</noscript>
					</div>

					<?php endwhile; ?>

				</div>
			</section>

		<script>

		/*	function ajax()
			{
				var req = new XMLHttpRequest();
				req.onreadystatechange = function()
				{
					if(req.readyState == 4 && req.status == 200)
					{
						document.getElementById('liked').innerHTML = req.responseText;
					}
				}
				req.open('POST', 'Blog/blogViewProcess.php', 'true');
				req.send();
			}
			setInterval(function(){ajax()}, 1000);*/

		</script>


		<script src="jquery/jquery-3.2.1.min.js"></script>
		<!-- Scripts -->
			<script src="assets/js/jquery.min.js"></script>
			<script src="assets/js/jquery.scrolly.min.js"></script>
			<script src="assets/js/jquery.scrollex.min.js"></script>
			<script src="assets/js/skel.min.js"></script>
		<script src="assets/js/util.js"></script>
		<script src="assets/js/main.js"></script>

		<!-- React Comments Bundle (built by Vite) -->
		<script src="/Shubham/js/comments.bundle.js" defer></script>

	</body>
</html>