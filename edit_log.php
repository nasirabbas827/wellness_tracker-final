<?php
include('config.php');
session_start();

if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];
$id = intval($_GET['id']);
$message = "";

// Fetch existing data
$stmt = $conn->prepare("SELECT * FROM logs WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$log = $result->fetch_assoc();

if (!$log) {
    die("Log not found.");
}

// Handle update
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $log_type = $log['log_type'];

    switch ($log_type) {
        case 'water':
            $amount = intval($_POST['amount']);
            $stmt = $conn->prepare("UPDATE logs SET amount = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sss", $amount, $id, $user_id);
            break;
        case 'exercise':
            $details = $_POST['details'];
            $duration = intval($_POST['duration']);
            $stmt = $conn->prepare("UPDATE logs SET details = ?, duration = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssss", $details, $duration, $id, $user_id);
            break;
        case 'sleep':
            $start_time = $_POST['start_time'];
            $end_time = $_POST['end_time'];
            $stmt = $conn->prepare("UPDATE logs SET start_time = ?, end_time = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssss", $start_time, $end_time, $id, $user_id);
            break;
        case 'mood':
            $details = $_POST['details'];
            $notes = $_POST['notes'];
            $stmt = $conn->prepare("UPDATE logs SET details = ?, notes = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssss", $details, $notes, $id, $user_id);
            break;
    }

    if ($stmt->execute()) {
        $message = "Log updated successfully.";
        header("Location: view_logs.php");
        exit;
    } else {
        $message = "Failed to update log.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Edit Log</title>
    <meta charset="UTF-8" />
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" />
    <link rel="stylesheet" href="./css/style.css" />

</head>
<body>
<?php include('navbar.php'); ?>

<div class="container mt-4">
        <div class="card mx-auto" style="max-width: 500px;">
        <div class="card-body">
    <h2>Edit Log</h2>
    <?php if (!empty($message)) echo "<div class='alert alert-info'>$message</div>"; ?>

    <form method="POST">
        <?php if ($log['log_type'] == 'water'): ?>
            <div class="form-group">
                <label>Amount (glasses/ml)</label>
                <input type="number" name="amount" class="form-control" value="<?php echo $log['amount']; ?>" required>
            </div>
        <?php elseif ($log['log_type'] == 'exercise'): ?>
            <div class="form-group">
                <label>Exercise Type</label>
                <input type="text" name="details" class="form-control" value="<?php echo htmlspecialchars($log['details']); ?>" required>
            </div>
            <div class="form-group">
                <label>Duration (minutes)</label>
                <input type="number" name="duration" class="form-control" value="<?php echo $log['duration']; ?>" required>
            </div>
        <?php elseif ($log['log_type'] == 'sleep'): ?>
            <div class="form-group">
                <label>Start Time</label>
                <input type="time" name="start_time" class="form-control" value="<?php echo $log['start_time']; ?>" required>
            </div>
            <div class="form-group">
                <label>End Time</label>
                <input type="time" name="end_time" class="form-control" value="<?php echo $log['end_time']; ?>" required>
            </div>
        <?php elseif ($log['log_type'] == 'mood'): ?>
    <div class="form-group">
        <label>Mood</label>
        <select name="details" class="form-control" required>
            <option value="">Select mood</option>
            <option value="Happy" <?php if ($log['details'] == 'Happy') echo 'selected'; ?>>Happy</option>
            <option value="Sad" <?php if ($log['details'] == 'Sad') echo 'selected'; ?>>Sad</option>
            <option value="Neutral" <?php if ($log['details'] == 'Neutral') echo 'selected'; ?>>Neutral</option>
        </select>
    </div>
    <div class="form-group">
        <label>Notes</label>
        <input type="text" name="notes" class="form-control" value="<?php echo htmlspecialchars($log['notes']); ?>">
    </div>
<?php endif; ?>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="view_logs.php" class="btn btn-secondary">Back</a>
    </form>
</div>
</div>
</div>
</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
