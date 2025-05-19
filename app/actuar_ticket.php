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

$id = $_GET['id'] ?? $_POST['id'] ?? null;
if (!$id) die("Ticket ID not provided.");

$extra_query = '';
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['admin_code'])) {
        $extra_query = '?admin_code=' . urlencode($_POST['admin_code']);
    } elseif (isset($_POST['tech_id'])) {
        $extra_query = '?tech_id=' . intval($_POST['tech_id']);
    }
} else {
    if (isset($_GET['admin_code'])) {
        $extra_query = '?admin_code=' . urlencode($_GET['admin_code']);
    } elseif (isset($_GET['tech_id'])) {
        $extra_query = '?tech_id=' . intval($_GET['tech_id']);
    }
}

$stmt = $pdo->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$ticket) die("Ticket not found.");

$techStmt = $pdo->query("SELECT * FROM technicians");
$technicians = $techStmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $time = $_POST['resolution_time'];
    $desc = $_POST['resolution_description'];
    $status = $_POST['status'];
    $tech_id = $_POST['technician_id'];

    $updateStmt = $pdo->prepare("UPDATE incidents SET resolution_time = ?, resolution_description = ?, status = ?, technician_id = ? WHERE id = ?");
    $updateStmt->execute([$time, $desc, $status, $tech_id, $id]);

    // Insert actuation record into MySQL
    $insertActuation = $pdo->prepare("INSERT INTO actuations 
        (ticket_id, technician_id, resolution_time, resolution_description, status) 
        VALUES (?, ?, ?, ?, ?)");
    $insertActuation->execute([$id, $tech_id, $time, $desc, $status]);

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

// Set flag to show success message
$success = true;

// Refresh ticket data to display updated info
$stmt = $pdo->prepare("SELECT * FROM incidents WHERE id = ?");
$stmt->execute([$id]);
$ticket = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Actuar Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1>Update Ticket #<?= $ticket['id'] ?></h1>
    <?php if (!empty($success)): ?>
        <div class="alert alert-success">Incidencia actualitzada satisfactoriament.</div>
    <?php endif; ?>

    <form method="post" class="card p-4 shadow-sm">
        <input type="hidden" name="id" value="<?= $ticket['id'] ?>">
        <?php if (isset($_GET['admin_code'])): ?>
            <input type="hidden" name="admin_code" value="<?= htmlspecialchars($_GET['admin_code']) ?>">
        <?php elseif (isset($_GET['tech_id'])): ?>
            <input type="hidden" name="tech_id" value="<?= intval($_GET['tech_id']) ?>">
        <?php endif; ?>

        <div class="mb-3">
            <label class="form-label">Temps estimat de reparació:</label>
            <input type="text" name="resolution_time" class="form-control" required value="<?= htmlspecialchars($ticket['resolution_time'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label">Descripcio de la resolució:</label>
            <textarea name="resolution_description" rows="4" class="form-control" required><?= htmlspecialchars($ticket['resolution_description'] ?? '') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label">Estat:</label>
            <select name="status" class="form-select" required>
                <option value="Waiting" <?= $ticket['status'] === 'Waiting' ? 'selected' : '' ?>>Esperant</option>
                <option value="In Process" <?= $ticket['status'] === 'In Process' ? 'selected' : '' ?>>En procés</option>
                <option value="Done" <?= $ticket['status'] === 'Done' ? 'selected' : '' ?>>Fet</option>
            </select>
        </div>

        <div class="mb-3">
            <label class="form-label">Tecnics:</label>
            <select name="technician_id" class="form-select" required>
                <?php foreach ($technicians as $tech): ?>
                    <option value="<?= $tech['id'] ?>" <?= ($ticket['technician_id'] == $tech['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($tech['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-success">Pujar actualització</button>
        <hr>
        <a href="view_tickets.php<?= $extra_query ?>" class="btn btn-success" style="background-color:rgb(45, 16, 124)">Retornar</a>
    </form>
</div>
</body>
</html>