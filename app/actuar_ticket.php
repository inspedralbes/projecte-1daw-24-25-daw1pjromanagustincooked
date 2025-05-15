<?php
$host = 'daw.inspedralbes.cat';
$dbname = 'a24romnovkal_tickets';
$username = 'a24romnovkal_tickets';
$password = 'Roma0802hestia)';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection failed: " . $e->getMessage());
}

$id = $_GET['id'] ?? null;

if (!$id) {
    die("Ticket ID not provided.");
}

// Fetch ticket
$stmt = $pdo->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ticket) {
    die("Ticket not found.");
}

// Fetch technicians
$techStmt = $pdo->query("SELECT * FROM technicians");
$technicians = $techStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $time = $_POST['resolution_time'];
    $desc = $_POST['resolution_description'];
    $status = $_POST['status'];
    $tech_id = $_POST['technician_id'];

    // Update MySQL
    $updateStmt = $pdo->prepare("UPDATE incidents SET resolution_time = ?, resolution_description = ?, status = ?, technician_id = ? WHERE id = ?");
    $updateStmt->execute([$time, $desc, $status, $tech_id, $id]);

    // Log to MongoDB Atlas
    require 'vendor/autoload.php';
    $client = new MongoDB\Client("mongodb+srv://a24romnovkal:Roma0802mongodb@clustertickets.uplsnoh.mongodb.net/ticket_logs?retryWrites=true&w=majority&appName=ClusterTickets");
    $collection = $client->ticket_logs->actions;

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $hora = date("Y-m-d H:i:s");

    $collection->insertOne([
        'action' => 'ticket_updated',
        'ticket_id' => $id,
        'status' => $status,
        'resolution_time' => $time,
        'ip_origin' => $ip,
        'timestamp' => $hora
    ]);

    // Refresh ticket data and show success message
    $success = true;
    $stmt = $pdo->prepare("SELECT * FROM incidents WHERE id = ?");
    $stmt->execute([$id]);
    $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Actuar Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1>Update Ticket #<?= $ticket['id'] ?></h1>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Ticket updated successfully.</div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <div class="mb-3">
            <label class="form-label">Estimated Resolution Time (e.g. 2h, 1 day):</label>
            <input type="text" name="resolution_time" class="form-control" required
            value="<?= htmlspecialchars($ticket['resolution_time'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Resolution Description:</label>
            <textarea name="resolution_description" rows="4" class="form-control" required>
            <?= htmlspecialchars($ticket['resolution_description'] ?? '') ?>
            </textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Status:</label>
            <select name="status" class="form-select" required>
                <option value="Waiting" <?= $ticket['status'] === 'Waiting' ? 'selected' : '' ?>>Waiting</option>
                <option value="In Process" <?= $ticket['status'] === 'In Process' ? 'selected' : '' ?>>In Process</option>
                <option value="Done" <?= $ticket['status'] === 'Done' ? 'selected' : '' ?>>Done</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Technician:</label>
            <select name="technician_id" class="form-select" required>
                <?php foreach ($technicians as $tech): ?>
                    <option value="<?= $tech['id'] ?>"
                        <?= ($ticket['technician_id'] == $tech['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tech['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Submit Update</button>
        <a href="view_tickets.php" class="btn btn-secondary ms-2">Cancel</a>
    </form>
</div>
</body>
</html>