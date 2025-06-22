<?php
include('config.php');
session_start();

// Set PHP timezone to PKT (Asia/Karachi)
date_default_timezone_set('Asia/Karachi');

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Set MySQL session timezone to PKT
$conn->query("SET time_zone = '+05:00';");

// Fetch username
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Fetch reminders for today, tomorrow, and the day after
$today = date('Y-m-d 00:00:00');
$two_days_later = date('Y-m-d 23:59:59', strtotime('+2 days'));
$sql = "SELECT id, type, reminder_datetime FROM reminders WHERE user_id = ? AND reminder_datetime BETWEEN ? AND ? AND is_complete = 0";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("iss", $user_id, $today, $two_days_later);
$stmt->execute();
$reminders_result = $stmt->get_result();
$reminders = $reminders_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Fetch top 5 recent logs
$sql = "SELECT log_type, amount, details, duration, start_time, end_time, notes, date_added FROM logs WHERE user_id = ? ORDER BY date_added DESC LIMIT 5";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("i", $user_id);
$stmt->execute();
$logs_result = $stmt->get_result();
$logs = $logs_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Check for each log type today
$today_start = date('Y-m-d 00:00:00');
$today_end = date('Y-m-d 23:59:59');
$log_types = ['water', 'exercise', 'sleep', 'mood'];
$missing_log_types = [];

foreach ($log_types as $type) {
    $sql = "SELECT COUNT(*) FROM logs WHERE user_id = ? AND log_type = ? AND date_added BETWEEN ? AND ?";
    
    // For exercise, we need to ensure duration is not NULL or 0
    if ($type == 'exercise') {
        $sql .= " AND duration > 0";
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
    $stmt->bind_param("isss", $user_id, $type, $today_start, $today_end);
    if (!$stmt->execute()) {
        die("Execute failed: " . $stmt->error);
    }
    $stmt->bind_result($count);
    $stmt->fetch();
    
    // More detailed debug logging
    error_log("Today's $type log check: count=$count, user_id=$user_id, range=$today_start to $today_end");
    
    if ($count === 0) {
        $missing_log_types[] = $type;
    }
    $stmt->close();
}

// Fetch data for weekly trends
$week_ago = date('Y-m-d 00:00:00', strtotime('-6 days'));
$sql = "SELECT log_type, amount, duration, start_time, end_time, date_added FROM logs WHERE user_id = ? AND date_added >= ? ORDER BY date_added";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}
$stmt->bind_param("is", $user_id, $week_ago);
$stmt->execute();
$weekly_logs_result = $stmt->get_result();
$weekly_logs = $weekly_logs_result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Process weekly data for charts
$water_data = array_fill(0, 7, 0);
$sleep_data = array_fill(0, 7, 0);
$exercise_data = array_fill(0, 7, 0);
$dates = [];
for ($i = 6; $i >= 0; $i--) {
    $dates[] = date('Y-m-d', strtotime("-$i days"));
}
foreach ($weekly_logs as $log) {
    $log_date = date('Y-m-d', strtotime($log['date_added']));
    $index = array_search($log_date, $dates);
    if ($index !== false) {
        if ($log['log_type'] == 'water' && $log['amount']) {
            $water_data[$index] += (int)$log['amount'];
        } elseif ($log['log_type'] == 'sleep' && $log['start_time'] && $log['end_time']) {
            $start = new DateTime($log['start_time']);
            $end = new DateTime($log['end_time']);
            $hours = $end->diff($start)->h + ($end->diff($start)->i / 60);
            if ($hours < 0) $hours += 24; // Handle overnight sleep
            $sleep_data[$index] += $hours;
        } elseif ($log['log_type'] == 'exercise' && $log['duration']) {
            $exercise_data[$index] += (int)$log['duration'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>StudyBuddy Dashboard</title>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <link rel="stylesheet" href="./css/style.css" />

    <style>

        .container {
            max-width: 1200px;
            padding-top: 2rem;
        }
        .welcome-header {
            color: #2c3e50;
            font-weight: 700;
            margin-bottom: 1.5rem;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card-header {
            background: none;
            border-bottom: none;
            font-size: 1.4rem;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            padding: 0;
            margin-bottom: 1rem;
        }
        .card-header i {
            font-size: 1.8rem;
            margin-right: 0.75rem;
            color: #3498db;
        }
        .log-item {
            padding: 0.5rem 0;
            border-bottom: 1px solid #e0e0e0;
        }
        .log-item:last-child {
            border-bottom: none;
        }
        .chart-container {
            position: relative;
            height: 200px;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container">
    <h2 class="welcome-header"><i class="fas fa-user-circle mr-2"></i>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>

    <!-- Reminders Alerts -->
    <?php foreach ($reminders as $reminder): ?>
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-bell mr-2"></i>
            Reminder: <strong><?php echo htmlspecialchars(ucfirst($reminder['type'])); ?></strong> at <?php echo date('M d, Y h:i A', strtotime($reminder['reminder_datetime'])); ?>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    <?php endforeach; ?>

    <!-- Missing Log Types Alerts -->
    <?php foreach ($missing_log_types as $type): ?>
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i>
            You haven't logged any <strong><?php echo htmlspecialchars(ucfirst($type)); ?></strong> activities today. Start tracking now!
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">×</span>
            </button>
        </div>
    <?php endforeach; ?>

    <!-- Recent Logs -->
    <div class="card">
        <div class="card-header"><i class="fas fa-history"></i> Recent Logs (Top 5)</div>
        <div class="card-body">
            <?php if (empty($logs)): ?>
                <p>No logs found.</p>
            <?php else: ?>
                <?php foreach ($logs as $log): ?>
                    <div class="log-item">
                        <strong><?php echo htmlspecialchars(ucfirst($log['log_type'])); ?>:</strong>
                        <?php
                        if ($log['log_type'] == 'water' && $log['amount']) {
                            echo $log['amount'] . ' glasses';
                        } elseif ($log['log_type'] == 'exercise' && $log['details'] && $log['duration']) {
                            echo htmlspecialchars($log['details']) . ' for ' . $log['duration'] . ' minutes';
                        } elseif ($log['log_type'] == 'sleep' && $log['start_time'] && $log['end_time']) {
                            echo 'From ' . $log['start_time'] . ' to ' . $log['end_time'];
                        } elseif ($log['log_type'] == 'mood' && $log['details']) {
                            echo htmlspecialchars($log['details']);
                            if ($log['notes']) {
                                echo ' (' . htmlspecialchars($log['notes']) . ')';
                            }
                        }
                        ?>
                        <br>
                        <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($log['date_added'])); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Weekly Trends -->
    <div class="card">
        <div class="card-header"><i class="fas fa-chart-line"></i> Weekly Wellness Trends</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="chart-container">
                        <canvas id="waterChart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container">
                        <canvas id="sleepChart"></canvas>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="chart-container">
                        <canvas id="exerciseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
    // Chart.js for Weekly Trends
    const dates = <?php echo json_encode(array_map(function($date) { return date('M d', strtotime($date)); }, $dates)); ?>;
    
    // Water Chart
    new Chart(document.getElementById('waterChart'), {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Glasses of Water',
                data: <?php echo json_encode($water_data); ?>,
                backgroundColor: 'rgba(52, 152, 219, 0.6)',
                borderColor: 'rgba(52, 152, 219, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Sleep Chart
    new Chart(document.getElementById('sleepChart'), {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: 'Sleep Hours',
                data: <?php echo json_encode($sleep_data); ?>,
                backgroundColor: 'rgba(142, 68, 173, 0.6)',
                borderColor: 'rgba(142, 68, 173, 1)',
                borderWidth: 2,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    // Exercise Chart
    new Chart(document.getElementById('exerciseChart'), {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [{
                label: 'Exercise Minutes',
                data: <?php echo json_encode($exercise_data); ?>,
                backgroundColor: 'rgba(46, 204, 113, 0.6)',
                borderColor: 'rgba(46, 204, 113, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
</script>
</body>
</html>