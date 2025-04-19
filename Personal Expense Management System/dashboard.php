<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username']; // Get the logged-in user's username
$user_id = $_SESSION['user_id']; // Get the user_id from session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <!-- Include Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            height: 100vh;
            margin: 0;
        }
        /* Sidebar Styles */
        #sidebar {
            width: 250px;
            background-color: #007bff;
            color: white;
            padding-top: 20px;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
        }
        #sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        #sidebar .profile {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
        }
        #sidebar .profile i {
            font-size: 50px;
            margin-right: 10px;
        }
        #sidebar a {
            display: block;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            margin-bottom: 10px;
        }
        #sidebar a:hover {
            background-color: #34495e;
        }
        /* Main Content Area */
        #main-content {
            margin-left: 250px;
            padding: 20px;
            width: 100%;
            background-color: #f4f6f7;
            height: 100vh;
            overflow-y: auto;
        }
        button {
            padding: 10px;
            margin: 10px;
            background-color: #3498db;
            color: white;
            border: none;
            cursor: pointer;
        }
        button:hover {
            background-color: #2980b9;
        }
    </style>
    <script>
        // Function to load content dynamically
        function loadContent(page) {
    fetch(page)
        .then(response => response.text())
        .then(data => {
            document.getElementById('main-content').innerHTML = data;
        })
        .catch(error => console.error('Error loading content:', error));
}
    </script>
</head>
<body>

    <div id="sidebar">
        <!-- Profile Section -->
        <div class="profile">
            <i class="bi bi-person-circle"></i>
            <h2><?php echo $username; ?></h2>
        </div>

        <!-- Sidebar Links -->
        <a href="home.php">
            <i class="bi bi-house-door"></i> Home
        </a>
        <a href="add_item.php">
            <i class="bi bi-plus-circle"></i> Add Expenses
        </a>
        <a href="view_item.php">
            <i class="bi bi-eye"></i> View Expenses
        </a>
        <a href="edit_item.php">
            <i class="bi bi-pencil"></i> Edit Expenses
        </a>
        <a href="delete_item.php">
            <i class="bi bi-trash"></i> Delete Expenses
        </a>
       
        <a href="Generate_Report.php">
            <i class="bi bi-bar-chart"></i> Generate Report
        </a>
        
        <!-- Logout Button -->
        <form action="logout.php" method="post">
            <button type="submit" style="margin-top: 20px; background-color: #e74c3c;">
                <i class="bi bi-box-arrow-right"></i> Logout
            </button>
        </form>
    </div>

    <!-- Main Content Area -->


</body>
</html>
