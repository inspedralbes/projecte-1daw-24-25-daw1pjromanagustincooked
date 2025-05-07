<?php
// DB connection
$host = 'db';
$dbname = 'tickets';
$username = 'usuari';
$password = 'paraula_de_pas';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle delete if a request is made
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM incidents WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: view_tickets.php");
    exit();
}

// Fetch all tickets
$stmt = $pdo->query("SELECT * FROM incidents ORDER BY submitted_at DESC");
$tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Tickets</title>
</head>
<body>
    <h1>All Submitted Tickets</h1>
    <p><a href="ticket_form.php">Submit a new ticket</a></p>

    <?php if (count($tickets) > 0): ?>
        <table border="1" cellpadding="10">
            <tr>
                <th>ID</th>
                <th>Department</th>
                <th>Date</th>
                <th>Description</th>
                <th>Submitted At</th>
                <th>Action</th>
            </tr>
            <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td><?= htmlspecialchars($ticket['id']) ?></td>
                    <td><?= htmlspecialchars($ticket['department']) ?></td>
                    <td><?= htmlspecialchars($ticket['incident_date']) ?></td>
                    <td><?= nl2br(htmlspecialchars($ticket['description'])) ?></td>
                    <td><?= htmlspecialchars($ticket['submitted_at']) ?></td>
                    <td><a href="view_tickets.php?delete_id=<?= $ticket['id'] ?>" onclick="return confirm('Are you sure you want to delete this ticket?');">Delete</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No tickets submitted yet.</p>
    <?php endif; ?>
</body>
</html>
