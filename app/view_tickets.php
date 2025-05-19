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

$is_admin = false;
$technician_id = null;
$extra_query = '';

if (isset($_GET['admin_code']) && $_GET['admin_code'] === 'kirilydavidgey') {
    $is_admin = true;
    $extra_query = '?admin_code=' . urlencode($_GET['admin_code']);
} elseif (isset($_GET['tech_id'])) {
    $technician_id = intval($_GET['tech_id']);
    $extra_query = '?tech_id=' . $technician_id;

    $stmt = $pdo->prepare("SELECT department FROM technicians WHERE id = ?");
    $stmt->execute([$technician_id]);
    $tech = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$tech) die("Technician not found.");
    $tech_dept = $tech['department'];
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update_priority_id'])) {
    $update_id = intval($_POST['update_priority_id']);
    $new_priority = $_POST['priority'];

    $stmt = $pdo->prepare("UPDATE incidents SET priority = ? WHERE id = ?");
    $stmt->execute([$new_priority, $update_id]);

    $redirect = 'view_tickets.php';
    if (isset($_POST['admin_code'])) {
        $redirect .= '?admin_code=' . urlencode($_POST['admin_code']);
    } elseif (isset($_POST['tech_id'])) {
        $redirect .= '?tech_id=' . intval($_POST['tech_id']);
    }

    header("Location: $redirect");
    exit();
}

if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $pdo->prepare("DELETE FROM incidents WHERE id = ?");
    $stmt->execute([$delete_id]);
    header("Location: view_tickets.php" . $extra_query);
    exit();
}

if ($is_admin) {
    $stmt = $pdo->query("SELECT * FROM incidents ORDER BY submitted_at DESC");
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} elseif (!empty($tech_dept)) {
    $stmt = $pdo->prepare("SELECT * FROM incidents WHERE department = ? ORDER BY submitted_at DESC");
    $stmt->execute([$tech_dept]);
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    die("Access denied. Please log in as admin or technician.");
}
?>

<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <title>Veure incindencies</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h1 class="mb-4">Incidencies pujades</h1>
    <a href="ticket_form.php" class="btn btn-primary mb-3">pujar nova incidencia</a>
    <?php if ($is_admin): ?>
        <a href="ticket_logs.php" class="btn btn-primary mb-3" style="background-color:rgb(45, 24, 163);">Veure Logs</a>
    <?php endif; ?>

    <?php if (count($tickets) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Departament</th>
                        <th>Data</th>
                        <th>Descripció</th>
                        <th>Data de tramesa</th>
                        <th>Prioritat</th>
                        <th>Acció</th>
                        <th>Gestionar</th>
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
                                <form method="post" action="view_tickets.php<?= $extra_query ?>" class="d-flex align-items-center">
                                    <input type="hidden" name="update_priority_id" value="<?= $ticket['id'] ?>">
                                    <?php if ($is_admin): ?>
                                        <input type="hidden" name="admin_code" value="kirilydavidgey">
                                    <?php elseif ($technician_id): ?>
                                        <input type="hidden" name="tech_id" value="<?= $technician_id ?>">
                                    <?php endif; ?>
                                    <select name="priority" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="Low" <?= $ticket['priority'] === 'Low' ? 'selected' : '' ?>>baixa</option>
                                        <option value="Medium" <?= $ticket['priority'] === 'Medium' ? 'selected' : '' ?>>mitjana</option>
                                        <option value="High" <?= $ticket['priority'] === 'High' ? 'selected' : '' ?>>Alta</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <a href="view_tickets.php?delete_id=<?= $ticket['id'] ?><?= $extra_query ? '&' . ltrim($extra_query, '?') : '' ?>"
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('Are you sure you want to delete this ticket: <?= addslashes(htmlspecialchars($ticket['description'])) ?>?');">
                                   Esborrar
                                </a>
                            </td>
                            <td>
                                <a href="actuar_ticket.php?id=<?= $ticket['id'] ?><?= $extra_query ? '&' . ltrim($extra_query, '?') : '' ?>" class="btn btn-dark btn-sm">Actualitzar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="alert alert-info">No s'ha pujat cap incidencia.</div>
    <?php endif; ?>
</div>
</body>
</html>