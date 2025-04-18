<?php
session_start();

// Only admin can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Include DB connection
include 'db_connect.php';

// Handle form submission
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name']);
    $id_number = trim($_POST['id_number']);
    $email = trim($_POST['email']);
    $phone_number = trim($_POST['phone_number']);
    $role = $_POST['role'];
    $marital_status = $_POST['marital_status'];

    // Insert into users table
    $sql = "INSERT INTO users (full_name, id_number, email, phone_number, role, marital_status, status, password)
            VALUES (?, ?, ?, ?, ?, ?, 'active', NULL)";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("ssssss", $full_name, $id_number, $email, $phone_number, $role, $marital_status);
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;  // Get the inserted user's ID
            $success = "Member registered successfully! They will set their own password at first login.";

            // Insert next of kin information
            $next_of_kin_name = trim($_POST['next_of_kin_name']);
            $next_of_kin_phone = trim($_POST['next_of_kin_phone']);
            $next_of_kin_relationship = trim($_POST['next_of_kin_relationship']);

            $sql_kin = "INSERT INTO next_of_kin (user_id, next_of_kin_name, next_of_kin_phone, next_of_kin_relationship)
                        VALUES (?, ?, ?, ?)";
            $stmt_kin = $conn->prepare($sql_kin);
            $stmt_kin->bind_param("isss", $user_id, $next_of_kin_name, $next_of_kin_phone, $next_of_kin_relationship);
            $stmt_kin->execute();
            $stmt_kin->close();
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $error = "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" href="styles.css">
    <title>Register Member</title>
</head>
<body>
<?php include 'navbar.php'; ?>
    <h2>Register New Member</h2>
    
    <?php if ($success): ?>
        <p style="color: green;"><?php echo $success; ?></p>
    <?php elseif ($error): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="">
        <label>Full Name:</label><br>
        <input type="text" name="full_name" required><br><br>

        <label>ID Number:</label><br>
        <input type="text" name="id_number" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Phone Number:</label><br>
        <input type="text" name="phone_number"><br><br>

        <label>Role:</label><br>
        <select name="role" required>
            <option value="member">Member</option>
            <option value="admin">Admin</option>
        </select><br><br>

        <label>Marital Status:</label><br>
        <select name="marital_status" required>
            <option value="single">Single</option>
            <option value="married">Married</option>
        </select><br><br>

        <!-- Next of Kin Information (for both single and married members) -->
        <label>Next of Kin Name:</label><br>
        <input type="text" name="next_of_kin_name" required><br><br>

        <label>Next of Kin Phone:</label><br>
        <input type="text" name="next_of_kin_phone" required><br><br>

        <label>Next of Kin Relationship:</label><br>
        <input type="text" name="next_of_kin_relationship" required><br><br>

        <button type="submit">Register Member</button>
    </form>
</body>
</html>
