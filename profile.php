<?php
session_start();
if (!isset($_SESSION['userID'])) {
    header("Location: login.php");
    exit();
}

$userID = $_SESSION['userID'];
$conn = new mysqli("localhost:3307", "root", "", "edu_enroll_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$stmt = $conn->prepare("select userID,firstName, lastName, emailAddress from Users where userID = ?");
$stmt->bind_param("i", $userID);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Profile</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        .nav-links a {
            margin-right: 15px;
            text-decoration: none;
        }
        .nav-links a:last-child {
            color: red;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 100px;
        }
        hr {
            margin: 25px 0;
            border: none;
            border-top: 1px solid #ccc;
        }
        .welcome {
            font-size: 1.4em;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="nav-links">
        <a href="courses.php">📘 Browse Courses</a>
        <a href="mycourses.php">🛠️ Manage Courses</a>
        <a href="logout.php">🚪 Log Out</a>
    </nav>

    <hr>

    <div class="welcome"> Welcome, <?= htmlspecialchars($user['firstName']) ?>!</div>
    <div><span class="info-label">UserID:</span> <?= htmlspecialchars($user['userID']) ?></div>
    <div><span class="info-label">Full Name:</span> <?= htmlspecialchars($user['firstName'] . ' ' . $user['lastName']) ?></div>
    <div><span class="info-label">Email:</span> <?= htmlspecialchars($user['emailAddress']) ?></div>
</body>
</html>
