<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb+srv://a24romnovkal:Roma0802mongodb@clustertickets.uplsnoh.mongodb.net/ticket_logs?retryWrites=true&w=majority&appName=ClusterTickets");
$collection = $client->ticket_logs->actions;

$logs = $collection->find([], ['sort' => ['timestamp' => -1]]);
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Ticket Logs</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="text-primary">Logs Acció Incidencies</h1>
        <a href="view_tickets.php?admin_code=kirilydavidgey" class="btn btn-dark">Exit</a>
    </div>

    <ul class="list-group">
        <?php foreach ($logs as $log): ?>
            <?php
                $action = htmlspecialchars($log['action'] ?? 'unknown');
                $ip = htmlspecialchars($log['ip_origin'] ?? 'unknown');
                $time = htmlspecialchars($log['timestamp'] ?? '---');
                $desc = htmlspecialchars($log['description'] ?? ($log['ticket_id'] ?? '—'));
            ?>
            <li class="list-group-item">
                <strong><?= $action ?></strong> — <?= $desc ?>  
                <br><small class="text-muted"><?= $time ?> (from <?= $ip ?>)</small>
            </li>
        <?php endforeach; ?>
    </ul>

</div>
</body>
</html>