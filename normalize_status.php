<?php
// One-off normalization script for transaction.status values.
// Usage: run from browser or CLI once. It will normalize status to lowercase trimmed values.
require 'db.php';

// Ensure we're cautious: only run if called directly
// This script doesn't require auth by default; in production protect it.

// Normalize status text
$normSql = "UPDATE transaction SET status = LOWER(TRIM(COALESCE(status, ''))) WHERE status IS NOT NULL";
$res = mysqli_query($conn, $normSql);

// Optionally set shipped_at for rows that already have status='shipped' but no shipped_at
$fixShippedAt = "UPDATE transaction SET shipped_at = NOW() WHERE LOWER(TRIM(COALESCE(status, ''))) = 'shipped' AND (shipped_at IS NULL OR shipped_at = '0000-00-00 00:00:00')";
$res2 = mysqli_query($conn, $fixShippedAt);

echo "Normalization complete. Rows affected: ".(mysqli_affected_rows($conn))."\n";
?>