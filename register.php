<?php
session_start();
$conn = new mysqli("localhost:3307", "root", "", "edu_enroll_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first = $_POST['firstName'];
    $last = $_POST['lastName'];
    $email = $_POST['emailAddress'];
    $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("insert into Users (firstName, lastName, emailAddress, password) values (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $first, $last, $email, $hashedPassword);

    if ($stmt->execute()) {
        $userID = $stmt->insert_id;
        $_SESSION['userID'] = $userID;

        header("Location: profile.php");
        exit();
    } else {
        echo "Registration failed. Please try again.";
    }
}
?>

<form method="POST">
    <h2>Registration Page</h2>
    First Name: <input type="text" name="firstName" required><br>
    Last Name: <input type="text" name="lastName" required><br>
    Email: <input type="email" name="emailAddress" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Register">
    <button type="button" onclick="window.location.href='index.php'">Cancel</button>
</form>
