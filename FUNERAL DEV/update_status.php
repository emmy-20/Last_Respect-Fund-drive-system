<?php
include 'db_connect.php';
session_start();

// Restrict to admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get and sanitize inputs
$contribution_id = $_POST['id'];
$new_status = $_POST['status'];

// Update the contribution status using the ID
$query = "UPDATE contributions SET status = ? WHERE id = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("si", $new_status, $contribution_id);

if ($stmt->execute()) {
    header("Location: admin_contributions.php?msg=updated");
    exit();
} else {
    echo "Error updating status: " . $stmt->error;
}
?>
