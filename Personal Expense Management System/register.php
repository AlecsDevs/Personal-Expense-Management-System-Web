<?php
require_once 'db.php';

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data and sanitize input
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    
    // Validate password match
    if ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash the password using bcrypt
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Check if username or email already exists
        $stmt = $conn->prepare("SELECT * FROM users WHERE username=? OR email=?");
        $stmt->bind_param("ss", $username, $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Username or Email already taken.";
        } else {
            // Insert the user data into the database
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt->execute()) {
                // Redirect to login page upon successful registration
                $_SESSION['success_message'] = "Registration successful! You can now log in.";
                header("Location: index.php");
                exit();
            } else {
                $error = "Error during registration. Please try again later.";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration</title>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-image: url(https://i.pinimg.com/originals/55/01/60/5501609ee45d514d1f2c4a63502045e2.gif);
            background-size: cover;
            background-position: center;
        }
        .container {
            display: flex;
            width: 800px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            overflow: hidden;
        }
        .illustration {
            flex: 1;
            background: #f0f4ff;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .form-container {
            flex: 1;
            background: #0d6efd;
            color: white;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-container h2 {
            margin-bottom: 20px;
        }
        .form-container form {
            display: flex;
            flex-direction: column;
        }
        .form-container label {
            margin-bottom: 5px;
        }
        .form-container input[type="text"],
        .form-container input[type="email"],
        .form-container input[type="password"] {
            padding: 10px;
            margin-bottom: 20px;
            border: none;
            border-radius: 40px;
        }
        .form-container input[type="submit"] {
            padding: 10px;
            background: #ffa500;
            border: none;
            border-radius: 5px;
            color: white;
            cursor: pointer;
        }
        .form-container input[type="submit"]:hover {
            background: #e68a00;
        }
        .form-container a {
            color: white;
            text-align: center;
            margin-top: 10px;
        }
        .error {
            color: red;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="illustration">
            <img src="https://scontent.fmnl13-4.fna.fbcdn.net/v/t1.15752-9/466904381_3824169561128667_4369688519508565837_n.png?stp=dst-png_s480x480&_nc_cat=107&ccb=1-7&_nc_sid=0024fc&_nc_ohc=YO8-eilgX58Q7kNvgG7iRy3&_nc_zt=23&_nc_ht=scontent.fmnl13-4.fna&oh=03_Q7cD1QHgK6E3Uu1b9we6wBYeMnSoOsxFhvUt2wRMhz9t2d6WHA&oe=677FB1A6" alt="Illustration">
        </div>

        <div class="form-container">
            <h2>Register</h2>
            <form method="post" action="register.php">
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" required>
                
                <label for="email">Email:</label>
                <input type="email" name="email" id="email" required>
                
                <label for="password">Password:</label>
                <input type="password" name="password" id="password" required>
                
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" name="confirm_password" id="confirm_password" required>
                
                <input type="submit" value="Register" >
                
                <!-- Show error message if exists -->
                <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
                
                <!-- Show success message after successful registration -->
                <?php if (isset($_SESSION['success_message'] )): ?>
                    <p class="success"><?= $_SESSION['success_message']; ?></p>
                    
                    <?php unset($_SESSION['success_message']); ?>
                          
                <?php endif; ?>
            </form>
            <a href="index.php">Already have an account? Login here</a>
        </div>
    </div>
</body>
</html>
