<?php
session_start();
require 'db.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 0) {
    $_SESSION['message'] = "You need admin access to perform this action!";
    header("Location: Login/error.php");
    exit();
}

// Check if product ID was provided
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pid'])) {
    $pid = mysqli_real_escape_string($conn, $_POST['pid']);
    
    // Get the image filename before deleting the product
    $sql = "SELECT pimage FROM fproduct WHERE pid = '$pid'";
    $result = mysqli_query($conn, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $imagePath = "images/productImages/" . $row['pimage'];
        
        // Delete the product from database
        $sql = "DELETE FROM fproduct WHERE pid = '$pid'";
        if (mysqli_query($conn, $sql)) {
            // Delete associated cart entries
            mysqli_query($conn, "DELETE FROM mycart WHERE pid = '$pid'");
            
            // Delete the image file if it exists
            if (file_exists($imagePath) && $row['pimage'] != '') {
                unlink($imagePath);
            }
            
            $_SESSION['message'] = "Product removed successfully!";
            header("Location: productMenu.php?n=1");
            exit();
        }
    }
    
    // If we get here, something went wrong
    $_SESSION['message'] = "Error removing product. Please try again.";
    header("Location: productMenu.php?n=1");
    exit();
}

// If we get here, no product ID was provided
$_SESSION['message'] = "No product specified for removal.";
header("Location: productMenu.php?n=1");
exit();
?>