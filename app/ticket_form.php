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

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $department = $_POST['department'];
    $incident_date = $_POST['incident_date'];
    $description = $_POST['description'];

    // Insert the ticket into the database
    $stmt = $pdo->prepare("INSERT INTO incidents (department, incident_date, description) VALUES (?, ?, ?)");
    $stmt->execute([$department, $incident_date, $description]);

    echo "<h1>Ticket Submitted!</h1>";
    echo "<p><strong>Department:</strong> $department</p>";
    echo "<p><strong>Date of Incident:</strong> $incident_date</p>";
    echo "<p><strong>Description:</strong><br>" . nl2br(htmlspecialchars($description)) . "</p>";
    echo '<p><a href="ticket_form.php">Submit another ticket</a></p>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Open IT Incident Ticket</title>
</head>
<body>
    <h1>Open IT Incident Ticket</h1>
    <form action="ticket_form.php" method="post">
        <label for="department">Department:</label><br>
        <input type="text" id="department" name="department" required><br><br>

        <label for="incident_date">Date of Incident:</label><br>
        <input type="date" id="incident_date" name="incident_date" required><br><br>

        <label for="description">Description:</label><br>
        <textarea id="description" name="description" rows="5" cols="40" required></textarea><br><br>

        <input type="submit" value="Submit Ticket">
    </form>
    <a href="view_tickets.php" style="
    display: inline-block;
    margin-top: 10px;
    padding: 10px 20px;
    background-color: #007BFF;
    color: white;
    text-decoration: none;
    border-radius: 5px;
    font-family: sans-serif;
">View Submitted Tickets</a>
</body>
</html>
