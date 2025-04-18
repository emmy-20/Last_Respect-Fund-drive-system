<?php
session_start();

// Only admins can access this page
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid_id'])) {
    $id = $_POST['mark_paid_id'];
    $update = $conn->prepare("UPDATE contributions SET status = 'paid' WHERE id = ?");
    $update->bind_param("i", $id);
    $update->execute();
    $update->close();
}

// Get all death records
$deaths = $conn->query("SELECT id, deceased_name, created_at FROM deaths ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Contributions Overview</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>Contributions Tracker (Admin)</h2>
<a href="defaulters.php"><button>View All Defaulters</button></a>

<?php while ($death = $deaths->fetch_assoc()): 
    $death_id = $death['id'];
    $death_name = htmlspecialchars($death['deceased_name']);

    // Get total contributed
    // Get total contributed (only paid)
$stmt = $conn->prepare("SELECT SUM(amount) AS total FROM contributions WHERE death_id = ? AND status = 'paid'");
$stmt->bind_param("i", $death_id);
$stmt->execute();
$stmt->bind_result($total);
$stmt->fetch();
$stmt->close();

    // Get all contributions
    $contribs = $conn->prepare("
        SELECT c.id, u.full_name, c.amount, c.status, c.payment_method 
        FROM contributions c 
        JOIN users u ON c.user_id = u.id 
        WHERE c.death_id = ?
    ");
    $contribs->bind_param("i", $death_id);
    $contribs->execute();
    $contrib_result = $contribs->get_result();

    // Get defaulters (only users with 'pending' contributions for this death)
    $defaulters = $conn->prepare("
        SELECT DISTINCT u.full_name, u.phone_number 
        FROM users u
        JOIN contributions c ON u.id = c.user_id
        WHERE u.role = 'member' 
          AND c.death_id = ? 
          AND c.status = 'pending'
    ");
    $defaulters->bind_param("i", $death_id);
    $defaulters->execute();
    $defaulter_result = $defaulters->get_result();
?>
    <hr>
    <h3><?php echo $death_name; ?> (Total Collected: KES <?php echo number_format($total, 2); ?>)</h3>
    
    <table border="1">
        <thead>
            <tr>
                <th>Member Name</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Payment Method</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($row = $contrib_result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                <td><?php echo htmlspecialchars($row['status']); ?></td>
                <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
                <td>
                    <?php if ($row['status'] === 'pending'): ?>
                        <form method="POST">
                            <input type="hidden" name="mark_paid_id" value="<?php echo $row['id']; ?>">
                            <button type="submit">Mark as Paid</button>
                        </form>
                    <?php else: ?>
                        Paid
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

    <h4>Defaulters (<?php echo $defaulter_result->num_rows; ?>):</h4>
    <ul>
        <?php while ($defaulter = $defaulter_result->fetch_assoc()): ?>
            <li>
                <?php echo htmlspecialchars($defaulter['full_name']); ?> - 
                <?php echo htmlspecialchars($defaulter['phone_number']); ?>
                <button onclick="alert('Notification sent to <?php echo $defaulter['phone_number']; ?>')">Notify</button>
            </li>
        <?php endwhile; ?>
    </ul>

<?php endwhile; ?>

</body>
</html>
