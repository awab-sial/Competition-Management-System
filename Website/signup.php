<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Competition Management System</title>
    <link rel="stylesheet" href="./signup.css">
</head>
<body>
    <header>
        <div class="logo">Competition Management System</div>
        <nav>
            <ul>
                <li><a href="#">Home</a></li>
                <li><a href="#">Competitions</a></li>
                <li><a href="#">Contact</a></li>
                <li><a href="#">Updates</a></li>
                <li><a href="#">About Us</a></li>
                <li><a href="#">Help</a></li>
            </ul>
        </nav>
    </header>
    
    <main>
        <section class="form-section">
            <div class="form-container">
                <h1>Enter Your Details</h1><br>
                <?php
                if ($_SERVER["REQUEST_METHOD"] == "POST") {
                    // Include database connection
                    include 'database.php';

                    // Input data
                    $username = $_POST["username"];
                    $firstname = $_POST["firstname"];
                    $lastname = $_POST["lastname"];
                    $phone = $_POST["phone"];
                    $email = $_POST["email"];
                    $cnic = $_POST["cnic"];
                    $password = $_POST["password"];
                    $confirmpassword = $_POST["confirmpassword"];
                    $role = $_POST["role"];

                    // Error handling
                    $errors = [];

                    // Validate data
                    if (empty($username) || empty($firstname) || empty($lastname) || empty($phone) || empty($email) || empty($cnic) || empty($password) || empty($confirmpassword) || $role == "select") {
                        $errors[] = "All fields are required.";
                    }

                    if ($role == "select") {
                        $errors[] = "Please select a valid role.";
                    }

                    if ($password !== $confirmpassword) {
                        $errors[] = "Passwords do not match.";
                    }

                    // Check for existing username or email
                    $sql_check = "SELECT * FROM Person WHERE username='$username' OR email='$email'";
                    $result_check = $conn->query($sql_check);

                    if ($result_check->num_rows > 0) {
                        $errors[] = "Username or email already exists.";
                    }

                    // If no errors, insert into database
                    if (empty($errors)) {
                        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                        $sql_insert = "INSERT INTO Person (f_name, l_name, email, phone, username, password, cnic, role) 
                                       VALUES ('$firstname', '$lastname', '$email', '$phone', '$username', '$hashed_password', '$cnic', '$role')";

                        if ($conn->query($sql_insert) === TRUE) {
                            echo "<p>Registration successful. <a href='./index.php'>Login here</a></p>";
                        } else {
                            echo "<p>Error: " . $sql_insert . "<br>" . $conn->error . "</p>";
                        }
                    } else {
                        foreach ($errors as $error) {
                            echo "<p style='color:red;'>$error</p>";
                        }
                    }

                    $conn->close();
                }
                ?>

                <form method="POST" action="">
                    <label for="username">User name:</label>
                    <input type="text" id="username" name="username" required placeholder="example" value="<?php echo isset($username) ? htmlspecialchars($username) : ''; ?>">
                    
                    <label for="firstname">First name:</label>
                    <input type="text" id="firstname" name="firstname" required placeholder="example" value="<?php echo isset($firstname) ? htmlspecialchars($firstname) : ''; ?>">
                    
                    <label for="lastname">Last name:</label>
                    <input type="text" id="lastname" name="lastname" required placeholder="example" value="<?php echo isset($lastname) ? htmlspecialchars($lastname) : ''; ?>">
                    
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" required placeholder="+92 331 000000" value="<?php echo isset($phone) ? htmlspecialchars($phone) : ''; ?>">
                    
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required placeholder="example@gmail.com" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>">
                    
                    <label for="cnic">CNIC:</label>
                    <input type="text" id="cnic" name="cnic" required placeholder="_____-_______-_" value="<?php echo isset($cnic) ? htmlspecialchars($cnic) : ''; ?>">
                    
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required value="<?php echo isset($password) ? htmlspecialchars($password) : ''; ?>">
                    
                    <label for="confirmpassword">Confirm password:</label>
                    <input type="password" id="confirmpassword" name="confirmpassword" required value="<?php echo isset($confirmpassword) ? htmlspecialchars($confirmpassword) : ''; ?>">
                    
                    <label for="role">Role:</label>
                    <select id="role" name="role">
                        <option value="select" <?php echo (isset($role) && $role == "select") ? 'selected' : ''; ?>>Select</option>
                        <option value="contestant" <?php echo (isset($role) && $role == "contestant") ? 'selected' : ''; ?>>Contestant</option>
                        <option value="judge" <?php echo (isset($role) && $role == "judge") ? 'selected' : ''; ?>>Judge</option>
                        <option value="invigilator" <?php echo (isset($role) && $role == "invigilator") ? 'selected' : ''; ?>>Invigilator</option>
                        <option value="admin" <?php echo (isset($role) && $role == "admin") ? 'selected' : ''; ?>>Admin</option>
                        <option value="organizer" <?php echo (isset($role) && $role == "organizer") ? 'selected' : ''; ?>>Organizer</option>

                    </select>
                    
                    <div class="already-registered">
                        <a href="./index.php">Already Registered Login &rarr;</a>
                    </div>
                    
                    <button type="submit" class="submit-button">Complete Registration</button>
                </form>
            </div>
            <div class="guidelines">
                <h2>Guidelines</h2>
                <ul>
                    <li>Lorem ipsum dolor sit amet, consectetur adipiscing elit.</li>
                    <li>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</li>
                    <li>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</li>
                    <li>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</li>
                    <li>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</li>
                </ul>
            </div>
        </section>
    </main>
</body>
</html>
