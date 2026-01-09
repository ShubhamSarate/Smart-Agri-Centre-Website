<?php
session_start();
require 'db.php';

if(!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] == 0 || $_SESSION['Category'] != 0) {
    $_SESSION['message'] = "You need admin access to perform this action.";
    header('Location: Login/error.php');
    exit();
}

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tid']) && isset($_POST['action'])) {
    $tid = mysqli_real_escape_string($conn, $_POST['tid']);
    $action = $_POST['action'];

    if($action === 'shipped') {
        // Ensure shipped_at column exists (store timestamp when shipped)
        $colCheck = mysqli_query($conn, "SHOW COLUMNS FROM transaction LIKE 'shipped_at'");
        if(!$colCheck || mysqli_num_rows($colCheck) == 0) {
            @mysqli_query($conn, "ALTER TABLE transaction ADD COLUMN shipped_at DATETIME NULL");
        }

        // Normalize and set status to 'shipped' and record shipped time
        // Fetch transaction details: pid, quantity_purchased and current status
        $txnRes = mysqli_query($conn, "SELECT pid, quantity_purchased, COALESCE(status, '') AS status FROM transaction WHERE tid = '$tid' LIMIT 1");
        if (!$txnRes || mysqli_num_rows($txnRes) == 0) {
            $_SESSION['message'] = "Transaction not found.";
        } else {
            $txn = mysqli_fetch_assoc($txnRes);
            $pid = $txn['pid'];
            $qtyPurchased = isset($txn['quantity_purchased']) ? intval($txn['quantity_purchased']) : 1;
            $currentStatus = strtolower(trim($txn['status']));

            if ($currentStatus === 'shipped') {
                $_SESSION['message'] = "Order is already marked as shipped.";
            } else {
                $updateSql = "UPDATE transaction SET status = 'shipped', shipped_at = NOW() WHERE tid = '$tid'";
                if (mysqli_query($conn, $updateSql)) {
                    // Decrement stock by purchased quantity, but only if enough stock exists
                    if ($pid) {
                        $safeDecr = "UPDATE fproduct SET quantity = GREATEST(quantity - $qtyPurchased, 0) WHERE pid = '" . mysqli_real_escape_string($conn, $pid) . "'";
                        mysqli_query($conn, $safeDecr);
                        if (mysqli_affected_rows($conn) > 0) {
                            $_SESSION['message'] = "Order marked as shipped. Product stock decremented by $qtyPurchased.";
                        } else {
                            $_SESSION['message'] = "Order marked as shipped. Note: could not decrement product stock.";
                        }
                    } else {
                        $_SESSION['message'] = "Order marked as shipped.";
                    }
                } else {
                    $_SESSION['message'] = "Could not update order status.";
                }
            }
        }
    }
    elseif($action === 'delete') {
        // delete the transaction
        if(mysqli_query($conn, "DELETE FROM transaction WHERE tid = '$tid'")) {
            // If table is now empty, reset AUTO_INCREMENT to 1
            $cntRes = mysqli_query($conn, "SELECT COUNT(*) AS c FROM transaction");
            if($cntRes) {
                $cntRow = mysqli_fetch_assoc($cntRes);
                if(isset($cntRow['c']) && intval($cntRow['c']) === 0) {
                    // Reset auto-increment for transaction
                    @mysqli_query($conn, "ALTER TABLE transaction AUTO_INCREMENT = 1");
                }
            }
            $_SESSION['message'] = "Order deleted.";
        } else {
            $_SESSION['message'] = "Could not delete order.";
        }
    }
}
header('Location: adminOrders.php');
exit();
?>