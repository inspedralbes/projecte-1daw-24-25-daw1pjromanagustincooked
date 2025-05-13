<?php
require 'vendor/autoload.php';

$client = new MongoDB\Client("mongodb://root:example@mongo:27017");
$collection = $client->ticket_logs->actions;

$logs = $collection->find([], ['sort' => ['timestamp' => -1]]);

echo "<h1>Ticket Action Logs</h1><ul>";
foreach ($logs as $log) {
    $action = htmlspecialchars($log['action'] ?? 'unknown');
    $ip = htmlspecialchars($log['ip_origin'] ?? 'unknown');
    $time = htmlspecialchars($log['timestamp'] ?? '---');
    $desc = htmlspecialchars($log['description'] ?? ($log['ticket_id'] ?? '—'));

    echo "<li><strong>$action</strong> — $desc — <em>$time</em> (from $ip)</li>";
}
echo "</ul>";