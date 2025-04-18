<?php
session_start();
include 'db_connect.php';

// Only admins can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all members
$sql = "SELECT id, full_name, id_number, role, status FROM users WHERE status = 'active' ORDER BY full_name";
$result = $conn->query($sql);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>Manage Members</h2>
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>ID Number</th>
            <th>Role</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['full_name']; ?></td>
                <td><?php echo $row['id_number']; ?></td>
                <td><?php echo ucfirst($row['role']); ?></td>
                <td>
                    <a href="edit_member.php?id=<?php echo $row['id']; ?>">Update</a> | 
                    <a href="delete_member.php?id=<?php echo $row['id']; ?>">Delete</a> | 
                    <a href="add_child.php?parent_id=<?php echo $row['id']; ?>">Add Child</a>
                </td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
