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

// handle season filter
$selectedSeason = $_GET['season'] ?? '';
$seasonClause = $selectedSeason ? "where season = ?" : "";
$coursesStmt = $conn->prepare("select * from Courses $seasonClause");
if ($selectedSeason) {
    $coursesStmt->bind_param("s", $selectedSeason);
}
$coursesStmt->execute();
$coursesQuery = $coursesStmt->get_result();

// fetch user's current enrollments
$enrolledIDs = [];
$enrollQuery = $conn->prepare("select courseID from Enrollments where userID = ?");
$enrollQuery->bind_param("i", $userID);
$enrollQuery->execute();
$enrollResult = $enrollQuery->get_result();
while ($row = $enrollResult->fetch_assoc()) {
    $enrolledIDs[] = $row['courseID'];
}

// fetch user's current waitlist
$waitlistIDs = [];
$waitlistQuery = $conn->prepare("select courseID from Waitlist where userID = ?");
$waitlistQuery->bind_param("i", $userID);
$waitlistQuery->execute();
$waitlistResult = $waitlistQuery->get_result();
while ($row = $waitlistResult->fetch_assoc()) {
    $waitlistIDs[] = $row['courseID'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Available Courses</title>
</head>
<body>
    <nav style="margin-bottom: 20px;">
        <a href="mycourses.php" style="margin-right: 15px;">📚 My Courses</a>
        <a href="profile.php" style="margin-right: 15px;">👤 Profile</a>
        <a href="logout.php" style="color: red;">🚪 Log Out</a>
    </nav>

    <hr class="section-divider">

    <h2>📚 Available Courses</h2>

    <form method="GET" action="courses.php" style="margin-bottom: 20px;">
        <label for="season">Filter by Season:</label>
        <select name="season" id="season">
            <option value="">-- All Seasons --</option>
            <option value="Spring" <?= $selectedSeason === "Spring" ? "selected" : "" ?>>Spring</option>
            <option value="Summer" <?= $selectedSeason === "Summer" ? "selected" : "" ?>>Summer</option>
            <option value="Fall" <?= $selectedSeason === "Fall" ? "selected" : "" ?>>Fall</option>
            <option value="Winter" <?= $selectedSeason === "Winter" ? "selected" : "" ?>>Winter</option>
        </select>
        <button type="submit">Apply</button>
    </form>

    <?php if ($coursesQuery->num_rows > 0): ?>
        <ul>
            <?php while ($course = $coursesQuery->fetch_assoc()): ?>
                <?php
                $courseID = $course['courseID'];

                // get current enrollment count
                $countStmt = $conn->prepare("select count(*) as enrolled from Enrollments where courseID = ?");
                $countStmt->bind_param("i", $courseID);
                $countStmt->execute();
                $enrolledCount = $countStmt->get_result()->fetch_assoc()['enrolled'];
                ?>

                <li>
                    <strong><?= htmlspecialchars($course['title']) ?></strong><br>
                    Season: <?= htmlspecialchars($course['season']) ?><br>
                    Capacity: <?= $course['capacity'] ?><br>
                    Enrolled: <?= $enrolledCount ?><br>

                    <?php
                    if (in_array($courseID, $enrolledIDs)) {
                        echo "<em>✅ Enrolled</em>";
                    } elseif (in_array($courseID, $waitlistIDs)) {
                        echo "<em>⏳ Waitlisted</em>";
                    } else {
                        echo "<form method='POST' action='enroll.php'>
                                <input type='hidden' name='courseID' value='$courseID'>";
                        if ($enrolledCount < $course['capacity']) {
                            echo "<button type='submit'>Enroll</button>";
                        } else {
                            echo "<button type='submit'>Join Waitinglist</button>";
                        }
                        echo "</form>";
                    }
                    ?>
                </li>
                <hr>
            <?php endwhile; ?>
        </ul>
    <?php else: ?>
        <p>No courses available for <?= $selectedSeason ?: "any season" ?>.</p>
    <?php endif; ?>
</body>
</html>
