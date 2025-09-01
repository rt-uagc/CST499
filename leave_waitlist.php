<?php
session_start();
if (!isset($_SESSION['userID']) || !isset($_POST['courseID'])) {
    header("Location: mycourses.php");
    exit();
}

$userID = $_SESSION['userID'];
$courseID = $_POST['courseID'];

$conn = new mysqli("localhost:3307", "root", "", "edu_enroll_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// remove from Waitlist
$delete = $conn->prepare("delete from Waitlist where userID = ? and courseID = ?");
$delete->bind_param("ii", $userID, $courseID);
$delete->execute();

header("Location: mycourses.php");
exit();
?>
