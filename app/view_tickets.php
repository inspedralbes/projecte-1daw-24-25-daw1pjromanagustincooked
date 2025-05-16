<?php
// DB connection
$host = 'daw.inspedralbes.cat';
$dbname = 'a24romnovkal_tickets';
$username = 'a24romnovkal_tickets';
$password = 'Roma0802hestia)';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Handle priority update
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_priority_id'])) {
    $update_id = intval($_POST['update_priority_id']);
    $new_priority = $_POST['priority'];

    $stmt = $pdo->prepare("UPDATE incidents SET priority = ? WHERE id = ?");
    $stmt->execute([$new_priority, $update_id]);

    header("Location: view_tickets.php"); // avoid resubmission
    exit();
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="mb-4">All Submitted Tickets</h1>
    <a href="ticket_form.php" class="btn btn-primary mb-3">Submit a New Ticket</a>
    <a href="ticket_logs.php" class="btn btn-primary mb-3" style="background-color:rgb(45, 24, 163);">View Logs</a>

    <?php if (count($tickets) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Submitted At</th>
                        <th>Priority</th>
                        <th>Action</th>
                        <th>Actuacion</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><?= htmlspecialchars($ticket['id']) ?></td>
                            <td><?= htmlspecialchars($ticket['department']) ?></td>
                            <td><?= htmlspecialchars($ticket['incident_date']) ?></td>
                            <td><?= nl2br(htmlspecialchars($ticket['description'])) ?></td>
                            <td><?= htmlspecialchars($ticket['submitted_at']) ?></td>
                            <td>
                                <form method="post" action="view_tickets.php" class="d-flex align-items-center">
                                    <input type="hidden" name="update_priority_id" value="<?= $ticket['id'] ?>">
                                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="Low" <?= $ticket['priority'] === 'Low' ? 'selected' : '' ?>>Low</option>
                                        <option value="Medium" <?= $ticket['priority'] === 'Medium' ? 'selected' : '' ?>>Medium</option>
                                        <option value="High" <?= $ticket['priority'] === 'High' ? 'selected' : '' ?>>High</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="view_tickets.php?delete_id=<?= $ticket['id'] ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this ticket: <?= addslashes(htmlspecialchars($ticket['description'])) ?>?');">
                                   Delete
                                </a>
                            </td>
                            <td>
                                <a href="actuar_ticket.php?id=<?= $ticket['id'] ?>" class="btn btn-dark btn-sm">Actuacionar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No tickets submitted yet.</div>
    <?php endif; ?>
</div>
</body>
</html>