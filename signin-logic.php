<?php
require 'config/database.php';

if (isset($_POST['submit'])) {
    $username_email = filter_var($_POST['username_email'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_FULL_SPECIAL_CHARS);

    if (!$username_email) {
        $_SESSION['signin'] = "Username or Email required";
    } elseif (!$password) {
        $_SESSION['signin'] = "Password required";
    } else {
        // Use prepared statement for better security
        $stmt = $connection->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->bind_param("ss", $username_email, $username_email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user-id'] = $user['id'];

                if ($user['is_admin'] == 1) {
                    $_SESSION['user_is_admin'] = true;
                }

                header('Location: ' . ROOT_URL . 'admin/');
                exit();
            } else {
                $_SESSION['signin'] = "Incorrect password.";
            }
        } else {
            $_SESSION['signin'] = "User not found.";
        }
    }

    // Preserve form data if there's an error
    $_SESSION['signin-data'] = $_POST;
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
} else {
    header('Location: ' . ROOT_URL . 'signin.php');
    exit();
}
