<?php
session_start();

// Ensure member is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit;
}

include 'db_connect.php';

$user_id = $_SESSION['user_id'];

// Fetch all contributions made by the logged-in user
$query = "
    SELECT 
        c.id AS contrib_id,
        c.amount,                 
        c.status,
        c.payment_method,
        c.created_at AS contribution_date,
        d.id AS death_id,
        d.deceased_name,
        d.date_of_death,
        d.deadline,
        du.full_name AS deceased_member_name,
        du.id_number AS deceased_id_number,
        du.phone_number AS deceased_phone,
        au.full_name AS affected_member_name,
        au.id_number AS affected_id_number,
        au.phone_number AS affected_phone,
        u.marital_status
    FROM contributions c
    JOIN deaths d ON c.death_id = d.id
    LEFT JOIN users du ON d.deceased_user_id = du.id
    LEFT JOIN users au ON d.affected_user_id = au.id
    JOIN users u ON c.user_id = u.id
    WHERE c.user_id = ?
    ORDER BY d.date_of_death DESC
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Contributions</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>My Contributions</h2>

<table border="1">
    <thead>
        <tr>
            <th>Deceased Name</th>
            <th>Date of Death</th>
            <th>Reported By</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Contribution Date</th>
            <th>Deadline</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td>
                <?php 
                    if (!empty($row['deceased_member_name'])) {
                        echo htmlspecialchars($row['deceased_member_name']) . " (Registered)<br>ID: " . $row['deceased_id_number'] . "<br>Phone: " . $row['deceased_phone'];
                    } else {
                        echo htmlspecialchars($row['deceased_name']);
                    }
                ?>
            </td>
            <td><?php echo htmlspecialchars($row['date_of_death']); ?></td>
            <td>
                <?php 
                    if (!empty($row['affected_member_name'])) {
                        echo htmlspecialchars($row['affected_member_name']) . "<br>ID: " . $row['affected_id_number'] . "<br>Phone: " . $row['affected_phone'];
                    } else {
                        echo 'N/A';
                    }
                ?>
            </td>
            <td>
                <?php
                    // Display fixed amount unless admin updated it
                    if ($row['amount'] > 0) {
                        echo "KES " . number_format($row['amount'], 2);
                    } else {
                        $fixed = ($row['marital_status'] === 'single') ? 100 : 200;
                        echo "KES " . number_format($fixed, 2);
                    }
                ?>
            </td>
            <td><?php echo ucfirst($row['status']); ?></td>
            <td><?php echo htmlspecialchars($row['payment_method']); ?></td>
            <td><?php echo htmlspecialchars(date("Y-m-d", strtotime($row['contribution_date']))); ?></td>
            <td><?php echo htmlspecialchars($row['deadline']); ?></td>
            <td>
                <?php if ($row['status'] === 'pending'): ?>
                    <a href="stk_push.php?contrib_id=<?php echo $row['contrib_id']; ?>">Pay via MPESA</a>
                <?php else: ?>
                    Paid
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>

</body>
</html>
