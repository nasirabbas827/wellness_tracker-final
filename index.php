<?php
include('config.php');
session_start();

// Set PHP timezone to PKT (Asia/Karachi)
date_default_timezone_set('Asia/Karachi');

// Check if user is logged in to redirect to dashboard
if (isset($_SESSION["id"]) && !empty($_SESSION["id"])) {
    header("location: dashboard.php");
    exit;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Personal Wellness Tracker - Track Your Health</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="./css/style.css">
    <style>

        .jumbotron {
            background-image: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('./images/hotel.jpg');
            background-size: cover;
            color: white;
            padding: 100px 0;
            text-align: center;
            margin-bottom: 0;
            height:500px;
        }
        .jumbotron h1 {
            font-size: 4rem;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .jumbotron p {
            font-size: 1.5rem;
            margin-bottom: 30px;
        }
        .features-section {
            padding: 60px 0;
            background-color: #fff;
        }
        .feature-item {
            text-align: center;
            padding: 20px;
        }
        .feature-icon {
            font-size: 3rem;
            color: #17a2b8;
            margin-bottom: 15px;
        }
        .feature-item h3 {
            font-size: 1.5rem;
            margin-bottom: 10px;
            color: #343a40;
        }
        .feature-item p {
            color: #6c757d;
        }
        .logs-section {
            padding: 60px 0;
            background-color: #f2f2f2;
        }
        .log-card {
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 30px;
        }
        .log-card img {
            height: 200px;
            object-fit: cover;
        }
        .log-card-body {
            padding: 20px;
            text-align: center;
        }
        .log-card-title {
            font-size: 1.25rem;
            margin-bottom: 10px;
            color: #343a40;
        }
        .log-card-type {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 15px;
        }
        .call-to-action {
            padding: 80px 0;
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('./images/tracker.jpg');
            background-size: cover;
            color: white;
            text-align: center;
        }
        .call-to-action h2 {
            font-size: 3rem;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .call-to-action p {
            font-size: 1.2rem;
            margin-bottom: 30px;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 40px 0;
            margin-top: 60px;
        }
        .footer h5 {
            color: white;
            margin-bottom: 15px;
        }
        .footer a {
            color: #adb5bd;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        .footer a:hover {
            color: white;
        }
    </style>
</head>
<body>

<?php include('navbar.php'); ?>

    <div class="jumbotron text-center">
        <h1>Track Your Wellness Journey</h1>
        <p>Monitor your water intake, exercise, sleep, and mood to live a healthier life.</p>
        <a href="register.php" class="btn btn-primary btn-lg">Start Tracking Now</a>
        <a href="login.php" class="btn btn-outline-light btn-lg ml-2">Log In</a>
    </div>

    <div class="features-section">
        <div class="container">
            <h2 class="text-center mb-4">Why Choose Online Personal Wellness Tracker?</h2>
            <div class="row">
                <div class="col-md-3 feature-item">
                    <i class="fas fa-tint feature-icon"></i>
                    <h3>Track Water Intake</h3>
                    <p>Log your daily water consumption to stay hydrated and healthy.</p>
                </div>
                <div class="col-md-3 feature-item">
                    <i class="fas fa-running feature-icon"></i>
                    <h3>Monitor Exercise</h3>
                    <p>Record your workouts to keep track of your fitness progress.</p>
                </div>
                <div class="col-md-3 feature-item">
                    <i class="fas fa-bed feature-icon"></i>
                    <h3>Track Sleep</h3>
                    <p>Monitor sleep patterns to ensure you're getting enough rest.</p>
                </div>
                <div class="col-md-3 feature-item">
                    <i class="fas fa-smile feature-icon"></i>
                    <h3>Log Your Mood</h3>
                    <p>Keep track of your emotional well-being with daily mood logs.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="logs-section">
        <div class="container">
            <h2 class="text-center mb-4">Sample Wellness Logs</h2>
            <p class="text-center mb-5">See how easy it is to track your wellness activities!</p>
            <div class="row">
                <?php
                // Sample static data for demonstration
                $sample_logs = [
                    [
                        'type' => 'Water',
                        'details' => '8 glasses',
                        'image' => 'water.jpg',
                        'description' => 'Stayed hydrated with 8 glasses of water today.'
                    ],
                    [
                        'type' => 'Exercise',
                        'details' => 'Running for 30 minutes',
                        'image' => 'exercise.jpg',
                        'description' => 'Completed a 30-minute run in the park.'
                    ],
                    [
                        'type' => 'Sleep',
                        'details' => '7 hours',
                        'image' => 'sleep.jpg',
                        'description' => 'Slept from 11:00 PM to 6:00 AM.'
                    ],
                    [
                        'type' => 'Mood',
                        'details' => 'Happy',
                        'image' => 'mood.jpg',
                        'description' => 'Feeling positive after a productive day.'
                    ]
                ];
                ?>
                <?php foreach ($sample_logs as $log): ?>
                    <div class="col-md-3">
                        <div class="card log-card">
                            <div class="card-body log-card-body">
                                <h5 class="card-title log-card-title"><?php echo htmlspecialchars($log['type']); ?></h5>
                                <p class="card-text log-card-type"><?php echo htmlspecialchars($log['details']); ?></p>
                                <p class="card-text"><?php echo htmlspecialchars($log['description']); ?></p>
                                <a href="login.php" class="btn btn-outline-primary btn-sm">Start Tracking</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="call-to-action">
        <h2>Ready to Take Control of Your Wellness?</h2>
        <p>Join our community and start tracking your health today!</p>
        <a href="register.php" class="btn btn-light btn-lg">Sign Up Now</a>
        <a href="login.php" class="btn btn-outline-light btn-lg ml-2">Log In</a>
    </div>

    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h5>Online Personal Wellness Tracker</h5>
                    <p>Empowering you to live a healthier, happier life through simple tracking.</p>
                </div>
                <div class="col-md-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Wellness Tips</a></li>
                        <li><a href="#">Support</a></li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h5>Follow Us</h5>
                    <div class="mt-2">
                        <a href="#" class="mr-2"><i class="fab fa-facebook fa-lg"></i></a>
                        <a href="#" class="mr-2"><i class="fab fa-instagram fa-lg"></i></a>
                        <a href="#" class=""><i class="fab fa-twitter fa-lg"></i></a>
                    </div>
                </div>
            </div>
            <hr class="bg-light mt-3 mb-2">
            <div class="text-center">
                <p>© 2025 Online Personal Wellness Tracker. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>