<?php
session_start();

// ensure user is logged in
if (!isset($_SESSION['userID'])) {
    header("Location: login.php?message=Please log in to enroll.");
    exit();
}

$userID = $_SESSION['userID'];
$courseID = $_POST['courseID'] ?? null;

if (!$courseID) {
    header("Location: courses.php?message=Invalid course selection.");
    exit();
}

// connect to database
$conn = new mysqli("localhost:3307", "root", "", "edu_enroll_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// check if already enrolled
$checkEnroll = $conn->prepare("select 1 from Enrollments where userID = ? and courseID = ?");
$checkEnroll->bind_param("ii", $userID, $courseID);
$checkEnroll->execute();
if ($checkEnroll->get_result()->num_rows > 0) {
    header("Location: courses.php?message=You're already enrolled in this course.");
    exit();
}

// check if already waitlisted
$checkWaitlist = $conn->prepare("select 1 from Waitlist where userID = ? and courseID = ?");
$checkWaitlist->bind_param("ii", $userID, $courseID);
$checkWaitlist->execute();
if ($checkWaitlist->get_result()->num_rows > 0) {
    header("Location: courses.php?message=You're already on the waitlist for this course.");
    exit();
}

// get course capacity
$courseQuery = $conn->prepare("select capacity from Courses where courseID = ?");
$courseQuery->bind_param("i", $courseID);
$courseQuery->execute();
$course = $courseQuery->get_result()->fetch_assoc();

if (!$course) {
    header("Location: courses.php?message=Course not found.");
    exit();
}

$capacity = (int)$course['capacity'];

// count current enrollments
$enrolledCountQuery = $conn->prepare("select count(*) as count from Enrollments where courseID = ?");
$enrolledCountQuery->bind_param("i", $courseID);
$enrolledCountQuery->execute();
$enrolledCount = (int)$enrolledCountQuery->get_result()->fetch_assoc()['count'];

// enroll or waitlist based on capacity
if ($enrolledCount < $capacity) {
    // Enroll user
    $enrollInsert = $conn->prepare("insert into Enrollments (userID, courseID) values (?, ?)");
    $enrollInsert->bind_param("ii", $userID, $courseID);
    $enrollInsert->execute();

    header("Location: courses.php?message=Enrollment successful!");
    exit();
} else {
    // Add to waitlist
    $waitlistInsert = $conn->prepare("insert into Waitlist (userID, courseID, waitlistDate) values (?, ?, NOW())");
    $waitlistInsert->bind_param("ii", $userID, $courseID);
    $waitlistInsert->execute();

    header("Location: courses.php?message=Course is full. You've been added to the waitlist.");
    exit();
}
?>
