<?php
// Fetch all reports submitted by the user
$reports = $conn->prepare("SELECT * FROM death_reports WHERE user_id = ? ORDER BY report_date DESC");
$reports->bind_param("i", $_SESSION['user_id']);
$reports->execute();
$result = $reports->get_result();

while ($row = $result->fetch_assoc()):
?>
    <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
        <strong>Deceased:</strong>
        <?php
        if (!empty($row['deceased_id'])) {
            // It's a registered user, fetch the name
            $stmt = $conn->prepare("SELECT full_name FROM users WHERE id = ?");
            $stmt->bind_param("i", $row['deceased_id']);
            $stmt->execute();
            $stmt->bind_result($deceased_name);
            $stmt->fetch();
            $stmt->close();
            echo htmlspecialchars($deceased_name);
        } else {
            echo htmlspecialchars($row['deceased_name']);
        }
        ?><br>

        <strong>Relationship:</strong> <?= htmlspecialchars($row['relationship']) ?><br>
        <strong>Date Reported:</strong> <?= $row['report_date'] ?><br>
        <?php if ($row['photo']): ?>
            <strong>Photo:</strong><br>
            <img src="<?= $row['photo'] ?>" alt="Deceased Photo" style="max-width: 200px;"><br>
        <?php endif; ?>

        <!-- 🔗 Admin approval link -->
        <a href="admin_approve_death.php?id=<?= $row['id'] ?>" style="color: green; text-decoration: underline;">
            Approve This Report
        </a>
    </div>
<?php endwhile; ?>


<table>
    <tr>
        <th>Reported By</th>
        <td><?php echo htmlspecialchars($report['reporter_name']); ?></td>
    </tr>
    <tr>
        <th>Report Date</th>
        <td><?php echo htmlspecialchars($report['report_date']); ?></td>
    </tr>

    <!-- If the deceased is a registered member -->
    <?php if ($report['deceased_id']): ?>
    <tr>
        <th>Deceased Member</th>
        <td><?php echo htmlspecialchars($report['deceased_member_name']); ?></td>
    </tr>
    <?php else: ?>
    <!-- For non-registered -->
    <tr>
        <th>Deceased Name</th>
        <td><?php echo htmlspecialchars($report['deceased_name']); ?></td>
    </tr>
    <tr>
        <th>Related Member</th>
        <td><?php echo htmlspecialchars($report['related_member_name']); ?></td>
    </tr>
    <?php endif; ?>

    <tr>
        <th>Relationship</th>
        <td><?php echo htmlspecialchars($report['relationship']); ?></td>
    </tr>
</table>

<div style="text-align: center;">
    <a href="report_death.php">Report Another Death</a>
</div>

</body>
</html>
