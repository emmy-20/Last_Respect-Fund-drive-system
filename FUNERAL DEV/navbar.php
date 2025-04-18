<?php
// Start session if needed
// session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Navigation</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to external CSS file -->
</head>
<body>

<nav class="navbar">
    <ul class="nav-list">
        <img class="logo" src="images/logo.jpg" alt="Logo">
        <li><a href="index.php">Home</a></li>
        <!-- Admin Dashboard with Dropdown -->
        <li class="dropdown">
          <li> <a href="admin_dashboard.php">Admin Dashboard</a></li>  
            <ul class="dropdown-content">
                <li><a href="register_member.php">Register Member</a></li>
                <li><a href="view_members.php">Manage Members</a></li>
                <li><a href="deaths.php">Report Death</a></li>
                <li><a href="view_deaths.php">View Death Reports</a></li>
                <li><a href="admin_contributions.php">View Members Contributions</a></li>
            </ul>
        </li>

       <li class="dropdown">
          <li> <a href="member_dashboard.php">Member Dashboard</a></li>
           <ul class="dropdown-content">
           <li><a href="login.php">Login</a></li>
            <li><a href="contribute.php">Contribute</a></li>
            <li><a href="notifications.php">Notifications</a></li>
    </ul>
</li>
<li><a href="logout.php">Logout</a></li>
        <?php if (isset($_SESSION['user_id'])): ?>
            
            <!-- <li><a href="logout.php">Logout</a></li> -->
        <?php else: ?>
            <li><a href="login.php">Login</a></li>
        <?php endif; ?>
    </ul>
</nav>

</body>
</html>
