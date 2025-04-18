<?php
session_start();
require_once 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "No valid member ID specified.";
    exit;
}

$id = intval($_GET['id']);

// Prevent self-deletion
if ($_SESSION['user_id'] == $id) {
    echo "You cannot delete your own account.";
    exit;
}

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: manage_members.php?deleted=1");
    exit;
} else {
    $stmt->close();
    echo "Error deleting member: " . $stmt->error;
}
?>
