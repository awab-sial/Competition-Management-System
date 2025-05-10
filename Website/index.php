<?php
session_start();
include("database.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usernameOrEmail = $_POST['username'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    // Prepare and execute the SQL query to check credentials
    $stmt = $conn->prepare("SELECT * FROM Person WHERE (username = ? OR email = ?) AND role = ?");
    $stmt->bind_param("sss", $usernameOrEmail, $usernameOrEmail, $role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify the password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['person_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect based on role
            switch ($role) {
                case 'contestant':
                    header('Location: contestant.php');
                    exit();
                case 'judge':
                    header('Location: judge.php');
                    exit();
                case 'invigilator':
                    header('Location: invigilator.php');
                    exit();
                case 'organizer':
                    header('Location: organizer.php');
                    exit();
                case 'admin':
                    header('Location: admin_dash.php');
                    exit();
                default:
                    $error = "Invalid role selected.";
                    break;
            }
        } else {
            $error = "Wrong Credentials Provided.";
        }
    } else {
        $error = "Wrong Credentials Provided.";
    }

    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.2.0/css/all.css'>
    <link rel='stylesheet' href='https://use.fontawesome.com/releases/v5.2.0/css/fontawesome.css'>
    <link rel="stylesheet" href="./login.css">
</head>
<body>

<div style="text-align: center; padding-top: 50px;">
    <h1 style="color: aliceblue; border: solid; border-radius: 20px; display: inline-block; padding: 10px;">COMPETITION MANAGEMENT SYSTEM</h1>
</div>


<div class="container">
    <div class="screen">
        <div class="screen__content">
            <form class="login" method="POST" action="index.php">
                <h3>Login</h3>
                <?php
                if (isset($error)) {
                    echo "<p style='color: red;'>$error</p>";
                }
                ?>
                <div class="login__field">
                    <i class="login__icon fas fa-user"></i>
                    <input type="text" class="login__input" placeholder="User name / Email" name="username" required>
                </div>
                <div class="login__field">
                    <i class="login__icon fas fa-lock"></i>
                    <input type="password" class="login__input" placeholder="Password" name="password" required>
                </div>
                <div class="input-group">
                    <label for="role">Role</label><br>
                    <select id="role" name="role" required>
                        <option value="contestant">Contestant</option>
                        <option value="judge">Judge</option>
                        <option value="invigilator">Invigilator</option>
                        <option value="organizer">Organizer</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <br><br><br>
                <button type="submit" class="button login__submit">
                    <span class="button__text">Log In Now</span>
                    <i class="button__icon fas fa-chevron-right"></i>
                </button>
                <button class="button login__submit" onclick="window.location.href='./signup.php'; return false;">
                    <span class="button__text">Sign Up</span>
                    <i class="button__icon fas fa-chevron-right"></i>
                </button>		
            </form>
            <div class="social-login">
            </div>
        </div>
        <div class="screen__background">
            <span class="screen__background__shape screen__background__shape4"></span>
            <span class="screen__background__shape screen__background__shape3"></span>		
            <span class="screen__background__shape screen__background__shape2"></span>
            <span class="screen__background__shape screen__background__shape1"></span>
        </div>		
    </div>
</div>
</body>
</html>
