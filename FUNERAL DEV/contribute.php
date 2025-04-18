<?php
session_start();

// Only  members can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit;}

include 'db_connect.php';

// Handle payment submission (Only Admin can update the status to "paid")
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_SESSION['role'] === 'admin') {
    $contrib_id = $_POST['contrib_id'];
    $payment_method = 'MPESA';

    $update_stmt = $conn->prepare("UPDATE contributions SET status = 'paid', payment_method = ? WHERE id = ?");
    $update_stmt->bind_param("si", $payment_method, $contrib_id);
    $update_stmt->execute();
    $update_stmt->close();

    header("Location: contribute.php?death_id=" . $_GET['death_id']);
    exit;
}



// If no death_id selected yet
if (!isset($_GET['death_id'])) {
    $result = $conn->query("SELECT id, deceased_name FROM deaths WHERE status = 'approved' ORDER BY created_at DESC");
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Select Death Record</title>
        <link rel="stylesheet" href="styles.css">
    </head>
    <body>
    <?php include 'navbar.php'; ?>
    <h2>Select a Deceased to Contribute To</h2>
    <table border="1">
        <tr>
            <th>Deceased Name</th>
            <th>Action</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['deceased_name']); ?></td>
                <td><a href="contribute.php?death_id=<?php echo $row['id']; ?>">Contribute</a></td>
            </tr>
        <?php endwhile; ?>
    </table>
    </body>
    </html>
    <?php exit; 
}

// --- If death_id is present, show contributions ---
$death_id = (int)$_GET['death_id'];

// Fetch deceased and affected member info (join both deceased_user_id and affected_user_id to users table)
$stmt = $conn->prepare("
    SELECT 
        d.deceased_name,
        du.full_name AS deceased_member_name,
        du.id_number AS deceased_id_number,
        du.phone_number AS deceased_phone,
        au.full_name AS affected_member_name,
        au.id_number AS affected_id_number,
        au.phone_number AS affected_phone
    FROM deaths d
    LEFT JOIN users du ON d.deceased_user_id = du.id
    LEFT JOIN users au ON d.affected_user_id = au.id
    WHERE d.id = ? AND d.status = 'approved'
");
$stmt->bind_param("i", $death_id);
$stmt->execute();
$stmt->bind_result(
    $deceased_name,
    $deceased_member_name,
    $deceased_id_number,
    $deceased_phone,
    $affected_member_name,
    $affected_id_number,
    $affected_phone
);
if (!$stmt->fetch()) {
    die("<p style='color:red;'>Deceased not found.</p>");
}
$stmt->close();

// Fetch contributors
$contrib_query = "
    SELECT c.id AS contrib_id, u.full_name, u.marital_status, c.amount, c.status, c.payment_method 
    FROM contributions c
    JOIN users u ON c.user_id = u.id
    JOIN deaths d ON c.death_id = d.id
    WHERE c.death_id = ? AND d.status = 'approved'
";
$stmt_contrib = $conn->prepare($contrib_query);
$stmt_contrib->bind_param("i", $death_id);
$stmt_contrib->execute();
$contrib_result = $stmt_contrib->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Contribute for <?php echo htmlspecialchars($deceased_name); ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<h2>Contribute for Deceased: <?php echo htmlspecialchars($deceased_name); ?></h2>

<?php if (!empty($deceased_member_name)): ?>
    <h4>Deceased Member (Registered): <?php echo htmlspecialchars($deceased_member_name); ?> | ID: <?php echo htmlspecialchars($deceased_id_number); ?> | Phone: <?php echo htmlspecialchars($deceased_phone); ?></h4>
<?php endif; ?>

<?php if (!empty($affected_member_name)): ?>
    <h4>Reported Under Member: <?php echo htmlspecialchars($affected_member_name); ?> | ID: <?php echo htmlspecialchars($affected_id_number); ?> | Phone: <?php echo htmlspecialchars($affected_phone); ?></h4>
<?php endif; ?>

<table border="1">
    <thead>
        <tr>
            <th>Contributor Name</th>
            <th>Marital Status</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Payment Method</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
    <?php while ($row = $contrib_result->fetch_assoc()): ?>
        <tr>
            <form method="POST" action="contribute.php?death_id=<?php echo $death_id; ?>">
                <input type="hidden" name="contrib_id" value="<?php echo $row['contrib_id']; ?>">

                <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                <td><?php echo htmlspecialchars($row['marital_status']); ?></td>

                <?php 
                $fixed_amount = ($row['marital_status'] === 'single') ? 100 : 200;
                ?>

                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <input type="number" name="amount" value="<?php echo htmlspecialchars($row['amount'] ?? $fixed_amount); ?>" required readonly>
                    <?php else: ?>
                        <input type="number" name="amount" value="<?php echo htmlspecialchars($fixed_amount); ?>" required readonly>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <select name="status" required>
                            <option value="pending" <?php echo ($row['status'] === 'pending') ? 'selected' : ''; ?>>Pending</option>
                            <option value="paid" <?php echo ($row['status'] === 'paid') ? 'selected' : ''; ?>>Paid</option>
                        </select>
                    <?php else: ?>
                        <input type="text" value="<?php echo htmlspecialchars($row['status']); ?>" readonly>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <select name="payment_method" required>
                            <option value="MPESA" <?php echo ($row['payment_method'] === 'MPESA') ? 'selected' : ''; ?>>MPESA</option>
                        </select>
                    <?php else: ?>
                        <input type="text" value="<?php echo htmlspecialchars($row['payment_method']); ?>" readonly>
                    <?php endif; ?>
                </td>

                <td>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <button type="submit">Mark as Paid</button>
                    <?php else: ?>
                        <?php if ($row['status'] === 'pending'): ?>
                            <input type="hidden" name="marital_status" value="<?php echo htmlspecialchars($row['marital_status']); ?>">
<input type="tel" name="phone" placeholder="Enter Phone (07XXXXXXXX)" pattern="07[0-9]{8}" required>
<button type="submit" formaction="stk_push.php">Pay via MPESA</button>


                        <?php else: ?>
                            <p>Payment Completed</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </form>
        </tr>
    <?php endwhile; ?>
    </tbody>
</table>
<?php
// Calculate total paid contributions
$total_stmt = $conn->prepare("
    SELECT SUM(amount) AS total_amount 
    FROM contributions 
    WHERE death_id = ? AND status = 'paid'
");
$total_stmt->bind_param("i", $death_id);
$total_stmt->execute();
$total_result = $total_stmt->get_result();
$total_row = $total_result->fetch_assoc();
$total_amount = $total_row['total_amount'] ?? 0;
$total_stmt->close();
?>

<h3>Total Contributions Collected (Paid Only): KES <?php echo number_format($total_amount, 2); ?></h3>


</body>
</html>
