<?php
session_start();
include 'db_connect.php';

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Approve Logic
if (isset($_GET['approve_id'])) {
    $death_id = intval($_GET['approve_id']);

    // Fetch report info
    $res = $conn->query("SELECT * FROM deaths WHERE id = $death_id");
    $death = $res->fetch_assoc();

    if (!$death) {
        $error = "Death report not found.";
    } else {
        $deceased_user_id = $death['deceased_user_id'];

        // If deceased is a registered user, update status to deceased
        if (!empty($deceased_user_id)) {
            $stmt = $conn->prepare("UPDATE users SET status = 'deceased' WHERE id = ?");
            $stmt->bind_param("i", $deceased_user_id);
            $stmt->execute();
            $stmt->close();
        }

        // Approve the report
        $stmt = $conn->prepare("UPDATE deaths SET status = 'approved' WHERE id = ?");
        $stmt->bind_param("i", $death_id);
        $stmt->execute();
        $stmt->close();

        // Create contributions for all active members
        $contrib_members = $conn->query("SELECT id, marital_status FROM users WHERE status = 'active'");
        $stmt_c = $conn->prepare("INSERT INTO contributions (user_id, death_id, status, amount) VALUES (?, ?, 'pending', ?)");

        while ($m = $contrib_members->fetch_assoc()) {
            $uid = $m['id'];
            $marital_status = $m['marital_status'];
            $amount = ($marital_status === 'single') ? 100 : 200;

            $stmt_c->bind_param("iid", $uid, $death_id, $amount);
            $stmt_c->execute();
        }

        $stmt_c->close();
        // Send notification to all active members
$photo_url = $death['deceased_photo'];
$deceased_name = $death['deceased_name'];
$deadline = $death['deadline'];
$notif_msg = "Death reported: {$deceased_name}. Please contribute before {$deadline}.";

$notif_stmt = $conn->prepare("INSERT INTO notifications (user_id, message, photo_url) VALUES (?, ?, ?)");

// Fetch all active members again
$active_members = $conn->query("SELECT id FROM users WHERE status = 'active'");
while ($member = $active_members->fetch_assoc()) {
    $user_id = $member['id'];
    $notif_stmt->bind_param("iss", $user_id, $notif_msg, $photo_url);
    $notif_stmt->execute();
}
$notif_stmt->close();


        $success = "Death report approved and contributions created.";
    }
}

// Reject Logic
if (isset($_GET['reject_id'])) {
    $death_id = intval($_GET['reject_id']);
    $stmt = $conn->prepare("UPDATE deaths SET status = 'rejected' WHERE id = ?");
    $stmt->bind_param("i", $death_id);
    $stmt->execute();
    $stmt->close();
    $success = "Death report rejected.";
}

// Fetch pending reports
$pending = $conn->query("SELECT d.*, u.full_name AS affected_member 
                        FROM deaths d 
                        JOIN users u ON d.affected_user_id = u.id 
                        WHERE d.status = 'pending'");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Pending Death Reports</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        .death-card {
            border: 1px solid #ccc;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .death-photo {
            max-width: 200px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<h2>Pending Death Reports</h2>

<?php if ($success): ?>
    <p style="color: green;"><?php echo $success; ?></p>
<?php elseif ($error): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<?php while ($d = $pending->fetch_assoc()): ?>
    <div class="death-card">
        <h3><?php echo htmlspecialchars($d['deceased_name']); ?></h3>
        <p><strong>Affected Member:</strong> <?php echo htmlspecialchars($d['affected_member']); ?></p>
        <p><strong>Date of Death:</strong> <?php echo $d['date_of_death']; ?></p>
        <p><strong>Deadline:</strong> <?php echo $d['deadline']; ?></p>

        <?php if (!empty($d['relationship'])): ?>
            <p><strong>Relationship:</strong> <?php echo htmlspecialchars($d['relationship']); ?></p>
        <?php endif; ?>

        <p><strong>Burial Permit:</strong> <a href="<?php echo $d['burial_permit']; ?>" target="_blank">View Document</a></p>

        <p><strong>Photo of Deceased:</strong><br>
            <img src="<?php echo $d['deceased_photo']; ?>" alt="Deceased Photo" class="death-photo">
        </p>

        <a href="?approve_id=<?php echo $d['id']; ?>" onclick="return confirm('Approve this death report?')">
            <button>Approve</button>
        </a>
        <a href="?reject_id=<?php echo $d['id']; ?>" onclick="return confirm('Reject this death report?')">
            <button style="background-color: red; color: white;">Reject</button>
        </a>
    </div>
<?php endwhile; ?>

</body>
</html>
