<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$message = "";

// Handle delete
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    $stmt = $conn->prepare("DELETE FROM logs WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $delete_id, $user_id);
    if ($stmt->execute()) {
        $message = "Log deleted successfully.";
    } else {
        $message = "Failed to delete log.";
    }
    $stmt->close();
}

// Handle search/filter
$where = "WHERE user_id = ?";
$params = [$user_id];
$types = "i";

if (!empty($_GET['log_type'])) {
    $where .= " AND log_type = ?";
    $params[] = $_GET['log_type'];
    $types .= "s";
}

if (!empty($_GET['from_date']) && !empty($_GET['to_date'])) {
    $where .= " AND date_added BETWEEN ? AND ?";
    $params[] = $_GET['from_date'] . " 00:00:00";
    $params[] = $_GET['to_date'] . " 23:59:59";
    $types .= "ss";
}

$sql = "SELECT id, log_type, amount, details, duration, start_time, end_time, notes, date_added FROM logs $where ORDER BY date_added DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Your Logs</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./css/style.css" />

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
    <h2>Your Wellness Logs</h2>

    <?php if (!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>

    <form method="GET" class="form-inline mb-3">
        <select name="log_type" class="form-control mr-2">
            <option value="">All Types</option>
            <option value="water" <?php if ($_GET['log_type'] ?? '' == 'water') echo 'selected'; ?>>Water</option>
            <option value="exercise" <?php if ($_GET['log_type'] ?? '' == 'exercise') echo 'selected'; ?>>Exercise</option>
            <option value="sleep" <?php if ($_GET['log_type'] ?? '' == 'sleep') echo 'selected'; ?>>Sleep</option>
            <option value="mood" <?php if ($_GET['log_type'] ?? '' == 'mood') echo 'selected'; ?>>Mood</option>
        </select>
        <input type="date" name="from_date" class="form-control mr-2" value="<?php echo htmlspecialchars($_GET['from_date'] ?? ''); ?>">
        <input type="date" name="to_date" class="form-control mr-2" value="<?php echo htmlspecialchars($_GET['to_date'] ?? ''); ?>">
        <button type="submit" class="btn btn-primary">Search / Filter</button>
        <a href="view_logs.php" class="btn btn-secondary ml-2">Reset</a>
    </form>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Date Added</th>
                <th>Type</th>
                <th>Details</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['date_added']); ?></td>
                <td><?php echo htmlspecialchars($row['log_type']); ?></td>
                <td>
                    <?php
                    switch ($row['log_type']) {
                        case 'water':
                            echo "{$row['amount']} glasses/ml";
                            break;
                        case 'exercise':
                            echo "{$row['details']} - {$row['duration']} min";
                            break;
                        case 'sleep':
                            echo "{$row['start_time']} - {$row['end_time']}";
                            break;
                        case 'mood':
                            echo "{$row['details']} - " . htmlspecialchars($row['notes']);
                            break;
                    }
                    ?>
                </td>
                <td>
                    <a href="edit_log.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-primary">Edit</a>
                    <a href="view_logs.php?delete_id=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete this log?');">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
