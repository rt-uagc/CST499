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

// count enrolled courses
$countEnroll = $conn->prepare("select count(*) as total from Enrollments where userID = ?");
$countEnroll->bind_param("i", $userID);
$countEnroll->execute();
$enrollTotal = $countEnroll->get_result()->fetch_assoc()['total'];

// count waitlisted courses
$countWaitlist = $conn->prepare("select count(*) as total from Waitlist where userID = ?");
$countWaitlist->bind_param("i", $userID);
$countWaitlist->execute();
$waitlistTotal = $countWaitlist->get_result()->fetch_assoc()['total'];

// fetch enrolled courses
$enrolledCourses = $conn->prepare("
    select c.courseID, c.title, c.season
    from Enrollments e
    join Courses c on e.courseID = c.courseID
    where e.userID = ?
");
$enrolledCourses->bind_param("i", $userID);
$enrolledCourses->execute();
$enrolledResult = $enrolledCourses->get_result();

// fetch waitlisted courses
$waitlistedCourses = $conn->prepare("
    select c.courseID, c.title, c.season
    from Waitlist w
    join Courses c on w.courseID = c.courseID
    where w.userID = ?
");
$waitlistedCourses->bind_param("i", $userID);
$waitlistedCourses->execute();
$waitlistResult = $waitlistedCourses->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Courses</title>
</head>
<body>
<nav style="margin-bottom: 20px;">
    <a href="courses.php" style="margin-right: 15px;">📘 Browse Courses</a>
    <a href="profile.php" style="margin-right: 15px;">👤 Profile</a>
    <a href="logout.php" style="color: red;">🚪 Log Out</a>
</nav>

<hr class="section-divider">

    <h2>📊 Dashboard Summary</h2>
    <ul>
        <li><strong>Enrolled Courses:</strong> <?= $enrollTotal ?></li>
        <li><strong>Waitlisted Courses:</strong> <?= $waitlistTotal ?></li>
    </ul>
    <hr>

    <h3>✅ Enrolled Courses</h3>
    <?php if ($enrolledResult->num_rows > 0): ?>
        <ul>
            <?php while ($row = $enrolledResult->fetch_assoc()): ?>
                <li>
                    <?= htmlspecialchars($row['courseID']) ?> - <?= htmlspecialchars($row['title']) ?> (<?= htmlspecialchars($row['season']) ?>)
                    <form method="POST" action="disenroll.php" style="display:inline;">
                        <input type="hidden" name="courseID" value="<?= $row['courseID'] ?>">
                        <button type="submit">Disenroll</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>You are not enrolled in any courses.</p>
    <?php endif; ?>

    <h3>⏳ Waitlisted Courses</h3>
    <?php if ($waitlistResult->num_rows > 0): ?>
        <ul>
            <?php while ($row = $waitlistResult->fetch_assoc()): ?>
                <li>
                    <?= htmlspecialchars($row['courseID']) ?> - <?= htmlspecialchars($row['title']) ?> (<?= htmlspecialchars($row['season']) ?>)
                    <form method="POST" action="leave_waitlist.php" style="display:inline;">
                        <input type="hidden" name="courseID" value="<?= $row['courseID'] ?>">
                        <button type="submit">Leave Waitlist</button>
                    </form>
                </li>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>You are not waitlisted for any courses.</p>
    <?php endif; ?>
</body>
</html>
