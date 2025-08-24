<?php
session_start();
$conn = new mysqli("localhost:3307", "root", "", "edu_enroll_db");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT userID, password FROM Users WHERE emailAddress = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 0) {
        echo "User not found. <a href='register.php'>Register here</a>";
    } else {
        $stmt->bind_result($userID, $hashedPassword);
        $stmt->fetch();
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['userID'] = $userID;
            header("Location: profile.php");
        } else {
            echo "Incorrect password.";
        }
    }
}
?>
<form method="POST">
    <h2>Login</h2>
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <input type="submit" value="Login">
</form>