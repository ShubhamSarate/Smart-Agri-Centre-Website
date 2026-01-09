<?php
// Simple admin script to list and optionally delete specific blog posts.
// USAGE:
// 1) Put this file in the project root: d:\xampp\htdocs\Shubham\remove_blogs.php
// 2) Open in browser to preview matches: http://localhost/Shubham/remove_blogs.php
// 3) If matches are correct, run with confirm=1 to delete: http://localhost/Shubham/remove_blogs.php?confirm=1

require 'db.php';

// Titles or content snippets to match (update as needed)
$targets = [
    'Banana is Organic',
    'First Blog'
];

// Additional content snippets (optional)
$snippets = [
    'Its Awesome websitewink',
    'yes it was taste',
    'Mast yarr'
];

function quote_list($arr){
    return implode(', ', array_map(function($s){ return "'".mysqli_real_escape_string($GLOBALS['conn'],$s)."'"; }, $arr));
}

// Find matching blog posts by title or by content snippet
$whereParts = [];
if(count($targets)>0) $whereParts[] = "blogTitle IN (".quote_list($targets).")";
foreach($snippets as $sn){
    $snEsc = mysqli_real_escape_string($conn,$sn);
    $whereParts[] = "blogContent LIKE '%$snEsc%'";
}
$whereSql = implode(' OR ', $whereParts);

$sql = "SELECT * FROM blogdata WHERE $whereSql";
$res = mysqli_query($conn, $sql);

$matches = [];
if($res){
    while($r = mysqli_fetch_assoc($res)){
        $matches[] = $r;
    }
}

$confirm = isset($_GET['confirm']) && $_GET['confirm'] == '1';

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Remove Blogs - Preview</title>
<style>body{font-family:Arial,Helvetica,sans-serif;padding:20px;background:#f7f7fb} .card{background:#fff;padding:16px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.06);margin-bottom:12px} .muted{color:#666;font-size:13px}</style>
</head>
<body>
<h2>Remove Blogs - Preview</h2>
<p class="muted">This tool will search for blog posts matching the configured titles/snippets. When you confirm, it will delete matching rows from <code>blogfeedback</code>, <code>likedata</code> and <code>blogdata</code>. Make a DB backup first if unsure.</p>

<?php if(empty($matches)): ?>
    <div class="card">No matching blog posts found.</div>
<?php else: ?>
    <h3>Matches (<?php echo count($matches); ?>)</h3>
    <?php foreach($matches as $m): ?>
        <div class="card">
            <strong>ID:</strong> <?php echo htmlspecialchars($m['blogId']); ?> &nbsp; <strong>Title:</strong> <?php echo htmlspecialchars($m['blogTitle']); ?><br>
            <div class="muted"><strong>User:</strong> <?php echo htmlspecialchars($m['blogUser']); ?> &nbsp; <strong>Time:</strong> <?php echo htmlspecialchars($m['blogTime']); ?></div>
            <p><?php echo nl2br(htmlspecialchars(substr($m['blogContent'],0,400))); if(strlen($m['blogContent'])>400) echo '...'; ?></p>
        </div>
    <?php endforeach; ?>

    <?php if(!$confirm): ?>
        <div style="margin-top:12px">
            <a href="?confirm=1" style="display:inline-block;padding:10px 16px;background:#d9534f;color:#fff;border-radius:6px;text-decoration:none">Delete these posts (CONFIRM)</a>
            &nbsp; <a href="?" style="display:inline-block;padding:10px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none">Cancel</a>
        </div>
    <?php else: ?>
        <?php
            // Collect IDs
            $ids = array_map(function($r){ return intval($r['blogId']); }, $matches);
            $idList = implode(',', $ids);
            // Delete related data first
            $queries = [];
            if(!empty($idList)){
                $queries[] = "DELETE FROM blogfeedback WHERE blogId IN ($idList)";
                $queries[] = "DELETE FROM likedata WHERE blogId IN ($idList)";
                $queries[] = "DELETE FROM blogdata WHERE blogId IN ($idList)";
            }
            $ok = true;
            foreach($queries as $q){
                if(!mysqli_query($conn, $q)){
                    echo "<div class=\"card\"><strong>Error running:</strong> ".htmlspecialchars($q)."<br>".htmlspecialchars(mysqli_error($conn))."</div>";
                    $ok = false;
                }
            }
            if($ok){
                echo "<div class=\"card\"><strong>Deleted posts:</strong> ".htmlspecialchars($idList)."</div>";
            }
        ?>
    <?php endif; ?>
<?php endif; ?>

</body>
</html>
