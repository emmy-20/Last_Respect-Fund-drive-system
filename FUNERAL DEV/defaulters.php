<?php
session_start();

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

// Get all pending contributions with user and death info
$stmt = $conn->prepare("
    SELECT u.full_name, u.phone_number, d.deceased_name, c.death_id
    FROM contributions c
    JOIN users u ON c.user_id = u.id
    JOIN deaths d ON c.death_id = d.id
    WHERE c.status = 'pending'
    ORDER BY c.death_id DESC
");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>All Defaulters</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>All Pending Contributors (Defaulters)</h2>

<button onclick="notifyAll()">Notify All Defaulters</button>

<table border="1">
    <thead>
        <tr>
            <th>Member Name</th>
            <th>Phone</th>
            <th>Pending For</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['phone_number']); ?></td>
                <td><?php echo htmlspecialchars($row['deceased_name']); ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script>
function notifyAll() {
    alert("Notifications sent to all members with pending contributions.");
    // In future: use AJAX to trigger real notifications.
}
</script>

</body>
</html>
