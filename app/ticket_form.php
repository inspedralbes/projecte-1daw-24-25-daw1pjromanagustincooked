<?php
// Database connection
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

$ticket_submitted = false;
$submitted_data = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department = $_POST['department'];
    $incident_date = $_POST['incident_date'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO incidents (department, incident_date, description) VALUES (?, ?, ?)");
    $stmt->execute([$department, $incident_date, $description]);

    $ticket_submitted = true;
    $submitted_data = [
        'department' => $department,
        'incident_date' => $incident_date,
        'description' => $description
    ];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Open IT Incident Ticket</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    <?php if ($ticket_submitted): ?>
        <div class="alert alert-success">
            <h4 class="alert-heading">Ticket Submitted Successfully!</h4>
            <p><strong>Department:</strong> <?= htmlspecialchars($submitted_data['department']) ?></p>
            <p><strong>Date of Incident:</strong> <?= htmlspecialchars($submitted_data['incident_date']) ?></p>
            <p><strong>Description:</strong><br><?= nl2br(htmlspecialchars($submitted_data['description'])) ?></p>
            <hr>
            <a href="ticket_form.php" class="btn btn-primary">Submit Another Ticket</a>
            <hr>
            <a href="view_tickets.php" class="btn btn-secondary ms-2">View Submitted Tickets</a>
        </div>
    <?php else: ?>

        <h1 class="mb-4">Open IT Incident Ticket</h1>

        <form action="ticket_form.php" method="post" class="card p-4 shadow-sm">
            <div class="mb-3">
                <label for="department" class="form-label">Department:</label>
                <input type="text" id="department" name="department" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="incident_date" class="form-label">Date of Incident:</label>
                <input type="date" id="incident_date" name="incident_date" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description:</label>
                <textarea id="description" name="description" rows="4" class="form-control" required></textarea>
            </div>

            <button type="submit" class="btn btn-success">Submit Ticket</button>
            <br>
            <a href="view_tickets.php" class="btn btn-success" style="background-color:rgb(45, 24, 163);">View Submitted Tickets</a>
        </form>

    <?php endif; ?>

</div>
</body>
</html>
