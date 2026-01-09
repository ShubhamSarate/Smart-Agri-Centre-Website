<?php
require 'db.php';

echo "<h2>Blog Feedback Table Structure:</h2>";
$result = mysqli_query($conn, "DESCRIBE blogfeedback");
echo "<table border='1'>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    foreach($row as $cell) {
        echo "<td>" . $cell . "</td>";
    }
    echo "</tr>";
}
echo "</table>";

echo "<h2>All Comments in Database:</h2>";
$result = mysqli_query($conn, "SELECT * FROM blogfeedback ORDER BY commentTime DESC LIMIT 10");
echo "<table border='1'>";
echo "<tr><th>ID</th><th>BlogId</th><th>Comment</th><th>User</th><th>Pic</th><th>Time</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['blogId'] . "</td>";
    echo "<td>" . htmlspecialchars(substr($row['comment'], 0, 50)) . "...</td>";
    echo "<td>" . $row['commentUser'] . "</td>";
    echo "<td>" . $row['commentPic'] . "</td>";
    echo "<td>" . (isset($row['commentTime']) ? $row['commentTime'] : 'N/A') . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h2>Count of Comments per Blog:</h2>";
$result = mysqli_query($conn, "SELECT blogId, COUNT(*) as cnt FROM blogfeedback GROUP BY blogId ORDER BY blogId DESC");
echo "<table border='1'>";
echo "<tr><th>BlogId</th><th>Comment Count</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td>" . $row['blogId'] . "</td><td>" . $row['cnt'] . "</td></tr>";
}
echo "</table>";

mysqli_close($conn);
?>
