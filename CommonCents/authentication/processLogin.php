<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    $stmt = $conn->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['userID']    = $user['userID'];
            $_SESSION['firstName'] = $user['firstName'];
            $_SESSION['lastName']  = $user['lastName'];
            $_SESSION['email']     = $user['email'];

            header("Location: ../frontend/home.php");
            exit();
        } else {
            $_SESSION['error'] = "Incorrect password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Email not found.";
        header("Location: login.php");
        exit();
    }
    $stmt->close();
}
$conn->close();
?>
