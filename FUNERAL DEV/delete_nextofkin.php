<?php
session_start();
require_once 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $kin_id = $_GET['id'];

    // Delete the next of kin record
    $sql = "DELETE FROM next_of_kin WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $kin_id);
    if ($stmt->execute()) {
        $stmt->close();
        header("Location: manage_members.php?msg=Next+of+kin+deleted+successfully");
        exit;
    } else {
        $stmt->close();
        header("Location: manage_members.php?error=Failed+to+delete+record");
        exit;
    }
} else {
    header("Location: manage_members.php?error=Invalid+request");
    exit;
}
?>
 