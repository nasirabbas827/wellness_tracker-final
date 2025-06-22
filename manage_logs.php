<?php
include('config.php');
session_start();

// Check if user is logged in
if (!isset($_SESSION["id"]) || empty($_SESSION["id"])) {
    header("location: index.php");
    exit;
}

$user_id = $_SESSION["id"];

// Fetch username
$sql = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($username);
$stmt->fetch();
$stmt->close();

// Handle form submissions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['log_type'])) {
        $log_type = $_POST['log_type'];
        $date = date('Y-m-d H:i:s');

        switch ($log_type) {
            case 'water':
                $amount = $_POST['water_amount'];
                $stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, amount, date_added) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $log_type, $amount, $date);
                break;
            case 'exercise':
                $exercise_type = $_POST['exercise_type'];
                $duration = $_POST['duration'];
                $stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, details, duration, date_added) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $log_type, $exercise_type, $duration, $date);
                break;
            case 'sleep':
                $start_time = $_POST['start_time'];
                $end_time = $_POST['end_time'];
                $stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, start_time, end_time, date_added) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $log_type, $start_time, $end_time, $date);
                break;
            case 'mood':
                $mood = $_POST['mood'];
                $notes = $_POST['notes'];
                $stmt = $conn->prepare("INSERT INTO logs (user_id, log_type, details, notes, date_added) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $user_id, $log_type, $mood, $notes, $date);
                break;
        }
        $stmt->execute();
        $stmt->close();
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
        .view-logs-btn {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #3498db;
            color: white;
            border-radius: 25px;
            text-decoration: none;
            transition: background 0.3s ease;
            margin-bottom: 1rem;
        }
        .view-logs-btn:hover {
            background: #2980b9;
        }
        .card {
            border: none;
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            padding: 1.5rem;
            margin-bottom: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 32px rgba(0,0,0,0.2);
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
        .form-control {
            border-radius: 10px;
            border: 1px solid #e0e0e0;
            padding: 0.75rem;
            transition: border-color 0.3s ease;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .btn-custom {
            width: 100%;
            padding: 0.75rem;
            font-size: 1.1rem;
            border-radius: 10px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-custom:hover {
            transform: translateY(-2px);
        }
        .water-card { background: linear-gradient(135deg, #e6f3ff 0%, #b3d7ff 100%); }
        .exercise-card { background: linear-gradient(135deg, #e6ffe6 0%, #b3ffb3 100%); }
        .sleep-card { background: linear-gradient(135deg, #f3e6ff 0%, #d4b3ff 100%); }
        .mood-card { background: linear-gradient(135deg, #fff3e6 0%, #ffd9b3 100%); }
        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }
        .form-group i {
            position: absolute;
            top: 50%;
            left: 1rem;
            transform: translateY(-50%);
            color: #6c757d;
        }
        .form-control-icon {
            padding-left: 2.5rem !important;
        }
        label {
            font-weight: 500;
            color: #34495e;
            margin-bottom: 0.5rem;
        }
    </style>
</head>
<body>
<?php include('navbar.php'); ?>

<div class="container">
    <a href="view_logs.php" class="view-logs-btn float-right "><i class="fas fa-history mr-2"></i>View Logs</a>

    <h2 class="welcome-header"><i class="fas fa-user-circle mr-2"></i>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <div class="row">
        <div class="col-md-6">
            <div class="card water-card">
                <div class="card-header"><i class="fas fa-tint"></i> Water Intake</div>
                <form method="POST">
                    <input type="hidden" name="log_type" value="water">
                    <div class="form-group">
                        <label>Glasses of Water</label>
                        <i class="fas fa-glass-water"></i>
                        <input type="number" name="water_amount" class="form-control form-control-icon" placeholder="How many glasses?" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-custom"><i class="fas fa-save mr-2"></i>Log Water Intake</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card exercise-card">
                <div class="card-header"><i class="fas fa-dumbbell"></i> Exercise</div>
                <form method="POST">
                    <input type="hidden" name="log_type" value="exercise">
                    <div class="form-group">
                        <label>Exercise Type</label>
                        <i class="fas fa-running"></i>
                        <select name="exercise_type" class="form-control form-control-icon" required>
                            <option value="">Select exercise type</option>
                            <option value="Running">Running</option>
                            <option value="Yoga">Yoga</option>
                            <option value="Weight Lifting">Weight Lifting</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Duration (minutes)</label>
                        <i class="fas fa-clock"></i>
                        <input type="number" name="duration" class="form-control form-control-icon" placeholder="Duration in minutes" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-custom"><i class="fas fa-save mr-2"></i>Log Exercise</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card sleep-card">
                <div class="card-header"><i class="fas fa-bed"></i> Sleep</div>
                <form method="POST">
                    <input type="hidden" name="log_type" value="sleep">
                    <div class="form-group">
                        <label>Sleep Start Time</label>
                        <i class="fas fa-moon"></i>
                        <input type="time" name="start_time" class="form-control form-control-icon" required>
                    </div>
                    <div class="form-group">
                        <label>Wake Up Time</label>
                        <i class="fas fa-sun"></i>
                        <input type="time" name="end_time" class="form-control form-control-icon" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-custom" style="background: #8e44ad; border: none;"><i class="fas fa-save mr-2"></i>Log Sleep</button>
                </form>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mood-card">
                <div class="card-header"><i class="fas fa-smile"></i> Mood</div>
                <form method="POST">
                    <input type="hidden" name="log_type" value="mood">
                    <div class="form-group">
                        <label>How are you feeling?</label>
                        <i class="fas fa-heart"></i>
                        <select name="mood" class="form-control form-control-icon" required>
                            <option value="">Select your mood</option>
                            <option value="Happy">Happy</option>
                            <option value="Sad">Sad</option>
                            <option value="Neutral">Neutral</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Notes (optional)</label>
                        <i class="fas fa-sticky-note"></i>
                        <input type="text" name="notes" class="form-control form-control-icon" placeholder="Any additional thoughts?">
                    </div>
                    <button type="submit" class="btn btn-warning btn-custom"><i class="fas fa-save mr-2"></i>Log Mood</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>