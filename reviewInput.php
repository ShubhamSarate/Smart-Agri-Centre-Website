<?php
    session_start();
    require 'db.php';

    // validate and sanitize inputs
    $rating = isset($_POST['rating']) ? intval($_POST['rating']) : 0;
    $review = isset($_POST['comment']) ? trim($_POST['comment']) : '';
    $name = isset($_SESSION['Name']) ? $_SESSION['Name'] : 'Anonymous';
    $pid = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

    // Require a non-empty review comment
    if ($review === '' || strlen($review) == 0) {
        $_SESSION['message'] = "Please write a review before submitting.";
        header('Location: review.php?pid=' . $pid);
        exit();
    }

    // escape values to avoid SQL issues
    $review_esc = mysqli_real_escape_string($conn, $review);
    $name_esc = mysqli_real_escape_string($conn, $name);

    $sql = "INSERT INTO review(pid,name,rating,comment) VALUES('$pid','$name_esc', '$rating', '$review_esc')";

    $result = mysqli_query($conn, $sql);
    if(!$result)
    {
        // show a friendly message on failure
        $_SESSION['message'] = "Could not save review. Please try again.";
        header('Location: review.php?pid=' . $pid);
        exit();
    }
    else {
        header("Location: review.php?pid=".$pid);
        exit();
    }

?>
