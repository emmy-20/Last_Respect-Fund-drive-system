<?php
session_start();
require_once 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch all users and their next of kin details
$sql = "SELECT u.id, u.full_name, u.id_number, u.email, u.phone_number, u.role, u.marital_status, u.status,
        n.next_of_kin_name, n.next_of_kin_phone, n.next_of_kin_relationship
        FROM users u
        LEFT JOIN next_of_kin n ON u.id = n.user_id
        ORDER BY u.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Members</title>
</head>
<body>
<?php include 'navbar.php'; ?>

    <h2>All Registered Members</h2>

    <table border="1" cellpadding="10" cellspacing="0">
        <tr>
            <th>Full Name</th>
            <th>ID Number</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Role</th>
            <th>Marital Status</th>
            <th>Status</th>
            <th>Next of Kin</th>
            <th>Next of Kin Phone</th>
            <th>Next of Kin Relationship</th>
            <th>Actions</th>
        </tr>
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['id_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                    <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td><?php echo htmlspecialchars($row['marital_status']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td><?php echo htmlspecialchars($row['next_of_kin_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['next_of_kin_phone']); ?></td>
                    <td><?php echo htmlspecialchars($row['next_of_kin_relationship']); ?></td>
                    <td>
                        <a href="edit_member.php?id=<?php echo $row['id']; ?>">Edit</a> |
                        <a href="delete_member.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a> |
                        <a href="delete_next_of_kin.php?id=<?php echo $kin['user_id']; ?>" onclick="return confirm('Are you sure you want to delete this next of kin record?');">Delete</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr><td colspan="11">No members found.</td></tr>
        <?php endif; ?>
    </table>
</body>
</html>
