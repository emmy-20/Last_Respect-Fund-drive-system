<?php
session_start();
include 'db_connect.php'; // Connect to the database
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Last Respect FundDrive Platform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'navbar.php'; ?>

<header>
    <h1>Welcome to the Last Respect FundDrive Platform</h1>
</header>

<main>
    <section class="intro">
        <h2>Ensuring Community Support for Bereaved Families</h2>
        <p>This system helps the village keep track of contributions, manage registrations, and support bereaved families efficiently.</p>
    </section>

    <?php if (!isset($_SESSION['user_id'])): ?>
        <!-- Not logged in -->
        <p class="echo">To access full features, please log in.</p>
        <a href="login.php"><button>Login</button></a>
    <?php else: ?>
        <!-- Logged in -->
        <?php
            $full_name = isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : "Member";
            $id_number = isset($_SESSION['id_number']) ? htmlspecialchars($_SESSION['id_number']) : "N/A";
        ?>

        <p>Welcome, <strong><?= $full_name; ?></strong> (ID: <strong><?= $id_number; ?></strong>)</p>

        <section class="notices">
            <h3>Recent Death Notices</h3>

            <?php
            $stmt = $conn->prepare("
                SELECT 
                    d.id, 
                    d.deceased_name, 
                    d.date_of_death, 
                    du.full_name AS deceased_member_name
                FROM deaths d
                LEFT JOIN users du ON d.deceased_user_id = du.id
                WHERE d.deadline >= CURDATE() AND d.status = 'approved'
                ORDER BY d.date_of_death DESC
                LIMIT 5
            ");

            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();

            if ($result->num_rows > 0):
                while ($row = $result->fetch_assoc()):
                    $deceased_display = !empty($row['deceased_member_name']) 
                        ? htmlspecialchars($row['deceased_member_name']) . " (Registered)"
                        : htmlspecialchars($row['deceased_name']);
            ?>
                <div class="notice">
                    <p><strong><?= $deceased_display; ?></strong></p>
                    <p><em>Date of Death:</em> <?= htmlspecialchars($row['date_of_death']); ?></p>
                    <a href="death_details.php?death_id=<?= $row['id']; ?>" class="btn">View Details & Contribute</a>
                    <hr>
                </div>
            <?php
                endwhile;
            else:
                echo "<p>No recent notices.</p>";
            endif;
            ?>
        </section>
    <?php endif; ?>
</main>

<?php include 'footer.php'; ?>

</body>
</html>
