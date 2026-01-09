<?php
session_start();
require 'db.php';

// Admin-only access
if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 0) {
    $_SESSION['message'] = "Admin access required to view comments.";
    header('Location: Login/error.php');
    exit();
}

// Handle deletion (use blogId + commentTime to uniquely identify a row)
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment'])) {
    $blogId = isset($_POST['blogId']) ? intval($_POST['blogId']) : 0;
    $commentTime = isset($_POST['commentTime']) ? $_POST['commentTime'] : '';
    if($blogId && $commentTime) {
        $stmt = mysqli_prepare($conn, "DELETE FROM blogfeedback WHERE blogId = ? AND commentTime = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, 'is', $blogId, $commentTime);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        $_SESSION['message'] = "Comment deleted (if it existed).";
        header('Location: adminComments.php');
        exit();
    }
}

// Fetch recent comments
$res = mysqli_query($conn, "SELECT blogId, comment, commentUser, commentPic, commentTime FROM blogfeedback ORDER BY commentTime DESC LIMIT 200");

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Admin - Blog Comments</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/global-style.css">
    <style>
        body { padding: 20px; }
        table td, table th { vertical-align: middle !important; }
        .comment-text { max-width: 640px; word-wrap: break-word; }
    </style>
</head>
<body>
<?php require 'menu.php'; ?>

<div class="container">
    <h1>Blog Comments (Admin)</h1>
    <?php if(isset($_SESSION['message'])) { echo '<div class="alert alert-info">'.htmlspecialchars($_SESSION['message']).'</div>'; unset($_SESSION['message']); } ?>

    <p>Showing latest comments. You may delete individual comments.</p>

    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Blog</th>
                <th>Comment</th>
                <th>User</th>
                <th>Pic</th>
                <th>Time</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><a href="blogView.php?id=<?php echo intval($row['blogId']); ?>" target="_blank">View #<?php echo intval($row['blogId']); ?></a></td>
                <td class="comment-text"><?php echo nl2br(htmlspecialchars($row['comment'])); ?></td>
                <td><?php echo htmlspecialchars($row['commentUser']); ?></td>
                <td><?php echo htmlspecialchars($row['commentPic']); ?></td>
                <td><?php echo htmlspecialchars($row['commentTime']); ?></td>
                <td>
                    <form method="post" style="display:inline" onsubmit="return confirm('Delete this comment?');">
                        <input type="hidden" name="blogId" value="<?php echo intval($row['blogId']); ?>">
                        <input type="hidden" name="commentTime" value="<?php echo htmlspecialchars($row['commentTime']); ?>">
                        <button type="submit" name="delete_comment" class="btn btn-sm btn-danger">Delete</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <p><a href="adminDashboard.php" class="btn btn-secondary">Back to Dashboard</a></p>
</div>

</body>
</html>
