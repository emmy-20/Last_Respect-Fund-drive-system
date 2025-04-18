<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id'])) {
    echo "Invalid member ID.";
    exit;
}

$id = $_GET['id'];
$success = $error = '';

// Fetch member
$stmt = $conn->prepare("SELECT full_name, id_number, email, phone_number, role, marital_status FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$member = $result->fetch_assoc();
$stmt->close();

if (!$member) {
    echo "Member not found.";
    exit;
}

// Update form
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST['full_name']);
    $id_number = trim($_POST['id_number']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $role = $_POST['role'];
    $marital_status = $_POST['marital_status'];

    $update = $conn->prepare("UPDATE users SET full_name = ?, id_number = ?, email = ?, phone_number = ?, role = ?, marital_status = ? WHERE id = ?");
    $update->bind_param("ssssssi", $full_name, $id_number, $email, $phone_number, $role, $marital_status, $id);

    if ($update->execute()) {
        $success = "Member updated successfully.";
    } else {
        $error = "Error: " . $update->error;
    }
    $update->close();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Member</title>
</head>
<body>
<?php include 'navbar.php'; ?>
<h2>Edit Member</h2>

<?php if ($success): ?>
    <p style="color:green;"><?php echo $success; ?></p>
<?php elseif ($error): ?>
    <p style="color:red;"><?php echo $error; ?></p>
<?php endif; ?>

<form method="POST">
    <label>Full Name:</label><br>
    <input type="text" name="full_name" value="<?php echo htmlspecialchars($member['full_name']); ?>" required><br><br>

    <label>ID Number:</label><br>
    <input type="text" name="id_number" value="<?php echo htmlspecialchars($member['id_number']); ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" required><br><br>

    <label>Phone Number:</label><br>
    <input type="text" name="phone_number" value="<?php echo htmlspecialchars($member['phone_number']); ?>"><br><br>

    <label>Role:</label><br>
    <select name="role">
        <option value="member" <?php if ($member['role'] === 'member') echo 'selected'; ?>>Member</option>
        <option value="admin" <?php if ($member['role'] === 'admin') echo 'selected'; ?>>Admin</option>
    </select><br><br>

    <label>Marital Status:</label><br>
    <select name="marital_status">
        <option value="single" <?php if ($member['marital_status'] === 'single') echo 'selected'; ?>>Single</option>
        <option value="married" <?php if ($member['marital_status'] === 'married') echo 'selected'; ?>>Married</option>
    </select><br><br>

    <button type="submit">Update</button>
</form>
</body>
</html>
