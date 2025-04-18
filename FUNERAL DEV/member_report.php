<?php
session_start();
include 'db_connect.php';

// Only members allowed here
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header("Location: login.php");
    exit;
}

$success = '';
$error = '';

// Fetch all active members for linking unregistered deaths
$members = [];
$result = $conn->query("SELECT id, full_name FROM users WHERE status = 'active'");
while ($row = $result->fetch_assoc()) {
    $members[] = $row;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $is_registered = $_POST['is_registered'];
    $affected_user_id = $_POST['affected_user_id'];
    $date_of_death = $_POST['date_of_death'];
    $deadline = $_POST['deadline'];
    $reported_by = $_SESSION['user_id'];

    // File upload handling
    $targetDir = "uploads/";
    $permit_path = $targetDir . basename($_FILES["burial_permit"]["name"]);
    $photo_path = $targetDir . basename($_FILES["deceased_photo"]["name"]);

    move_uploaded_file($_FILES["burial_permit"]["tmp_name"], $permit_path);
    move_uploaded_file($_FILES["deceased_photo"]["tmp_name"], $photo_path);

    if ($is_registered === 'yes') {
        $deceased_user_id = $_POST['deceased_user_id'];
        $res = $conn->query("SELECT full_name FROM users WHERE id = $deceased_user_id");
        $deceased_name = $res->fetch_assoc()['full_name'];
        $relationship = null;
    } else {
        $deceased_user_id = NULL;
        $deceased_name = trim($_POST['deceased_name']);
        $relationship = trim($_POST['relationship']);
        // Append affected member name in brackets
$res = $conn->query("SELECT full_name FROM users WHERE id = $affected_user_id");
if ($res && $res->num_rows > 0) {
    $affected_name = $res->fetch_assoc()['full_name'];
    $deceased_name .= " ({$affected_name})";
}

    }

    // Insert pending death report
    $stmt = $conn->prepare("INSERT INTO deaths 
        (deceased_user_id, deceased_name, affected_user_id, date_of_death, relationship, burial_permit, deceased_photo, reported_by, deadline, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

    $stmt->bind_param("isissssis", 
        $deceased_user_id, $deceased_name, $affected_user_id, $date_of_death,
        $relationship, $permit_path, $photo_path, $reported_by, $deadline
    );

    if ($stmt->execute()) {
        $success = "Death report submitted. Awaiting admin approval.";
    } else {
        $error = "Error: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Report Death</title>
    <link rel="stylesheet" href="styles.css">
    <script>
        function toggleDeceasedInput(val) {
            document.getElementById('registered_user').style.display = val === 'yes' ? 'block' : 'none';
            document.getElementById('unregistered_input').style.display = val === 'no' ? 'block' : 'none';
        }
    </script>
</head>
<body>
<?php include 'navbar.php'; ?>
<h2>Report a Death</h2>

<?php if ($success): ?>
    <p style="color: green;"><?php echo $success; ?></p>
<?php elseif ($error): ?>
    <p style="color: red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST" action="" enctype="multipart/form-data">
    <label>Is the deceased registered?</label><br>
    <select name="is_registered" onchange="toggleDeceasedInput(this.value)" required>
        <option value="">--Select--</option>
        <option value="yes">Yes</option>
        <option value="no">No</option>
    </select><br><br>

    <div id="registered_user" style="display:none;">
        <label>Select Deceased Member:</label><br>
        <select name="deceased_user_id">
            <?php foreach ($members as $m): ?>
                <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['full_name']); ?></option>
            <?php endforeach; ?>
        </select><br><br>
    </div>

    <div id="unregistered_input" style="display:none;">
        <label>Deceased Full Name:</label><br>
        <input type="text" name="deceased_name"><br><br>

        <label>Relationship to Affected Member:</label><br>
        <input type="text" name="relationship"><br><br>
    </div>

    <label>Affected Member:</label><br>
    <select name="affected_user_id" required>
        <?php foreach ($members as $m): ?>
            <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['full_name']); ?></option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Date of Death:</label><br>
    <input type="date" name="date_of_death" required><br><br>

    <label>Deadline for Contributions:</label><br>
    <input type="date" name="deadline" required><br><br>

    <label>Upload Burial Permit:</label><br>
    <input type="file" name="burial_permit" accept="image/*,.pdf" required><br><br>

    <label>Upload Photo of Deceased:</label><br>
    <input type="file" name="deceased_photo" accept="image/*" required><br><br>

    <button type="submit">Submit Death Report</button>
</form>
</body>
</html>
