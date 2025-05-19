<?php
// Database connection
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

// Fetch departments dynamically
$deptStmt = $pdo->query("SELECT DISTINCT department FROM technicians ORDER BY department ASC");
$departments = $deptStmt->fetchAll(PDO::FETCH_ASSOC);

$ticket_submitted = false;
$submitted_data = [];

// Check if redirected with success message in query string (no sessions)
if (isset($_GET['submitted']) && $_GET['submitted'] === '1') {
    $ticket_submitted = true;
    $submitted_data = [
        'department' => $_GET['dept'] ?? '',
        'incident_date' => $_GET['date'] ?? '',
        'description' => $_GET['desc'] ?? ''
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department = $_POST['department'];
    $incident_date = $_POST['incident_date'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO incidents (department, incident_date, description) VALUES (?, ?, ?)");
    $stmt->execute([$department, $incident_date, $description]);

    require 'vendor/autoload.php';
        $uri = "mongodb+srv://a24romnovkal:Roma0802mongodb@clustertickets.uplsnoh.mongodb.net/ticket_logs?retryWrites=true&w=majority";
        $client = new MongoDB\Client($uri);
        $collection = $client->ticket_logs->actions;

        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $hora = date("Y-m-d H:i:s");

        $collection->insertOne([
        'action' => 'ticket_submitted',
        'department' => $department,
        'description' => $description,
        'ip_origin' => $ip,
        'timestamp' => $hora
    ]);

    $redirect_url = 'ticket_form.php?submitted=1'
    . '&dept=' . urlencode($department)
    . '&date=' . urlencode($incident_date)
    . '&desc=' . urlencode($description);

    header("Location: $redirect_url");
    exit();
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
   <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    <?php if ($ticket_submitted): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">Incidencia pujada correctament!</h4>
            <p><strong>Departament:</strong> <?= htmlspecialchars($submitted_data['department']) ?></p>
            <p><strong>Data de la incidencia:</strong> <?= htmlspecialchars($submitted_data['incident_date']) ?></p>
            <p><strong>Descripció:</strong><br><?= nl2br(htmlspecialchars($submitted_data['description'])) ?></p>
            <hr>
            <a href="ticket_form.php" class="btn btn-primary">Pujar una altre Incidencia</a>
        </div>
    <?php else: ?>

        <h1 class="mb-4">Incidencies Informatiques</h1>

        <form action="ticket_form.php" method="post" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="department" class="form-label">Departament:</label>
                <select id="department" name="department" class="form-select" required>
                    <option value="">-- Select Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?= htmlspecialchars($dept['department']) ?>">
                            <?= htmlspecialchars($dept['department']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="incident_date" class="form-label">Data d'incidencia:</label>
                <input type="date" id="incident_date" name="incident_date" class="form-control" required value="<?= date('Y-m-d') ?>" readonly>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Descripció:</label>
                <textarea id="description" name="description" rows="4" class="form-control" required minlength="20"></textarea>
            </div>

            <button type="submit" class="btn btn-success">Pujar incidencia</button>
            </form>

            <div class="mt-4 d-flex gap-2">
                <!-- Admin access form -->
                <form action="view_tickets.php" method="get" class="d-flex gap-2">
                    <input type="password" name="admin_code" class="form-control" placeholder="Admin code">
                    <button type="submit" class="btn btn-dark">Veure com a admin</button>
                </form>

                <!-- Technician access form -->
                <form action="view_tickets.php" method="get" class="d-flex gap-2">
                    <input type="number" name="tech_id" class="form-control" placeholder="Technician ID">
                    <button type="submit" class="btn btn-secondary">Veure com a tècnic</button>
                </form>
            </div>

    <?php endif; ?>

</div>
</body>
</html>
