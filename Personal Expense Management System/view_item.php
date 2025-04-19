<?php
require_once 'db.php';
include('dashboard.php');

// Get the user_id from the session (this assumes the user is logged in)
$user_id = $_SESSION['user_id'];

$message = "";

// Handle category filter if form is submitted
$category_filter = '';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['category_filter'])) {
        $category_filter = $_POST['category_filter'];
    }
}

// Fetch all unique categories for the filter dropdown
$category_query = $conn->query("SELECT DISTINCT category FROM expenses WHERE user_id = $user_id");
$categories = [];
while ($row = $category_query->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Prepare SQL query based on the filter (now filtering by user_id)
if ($category_filter) {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? AND category = ?");
    $stmt->bind_param("is", $user_id, $category_filter);
} else {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-1 {
            margin-left: 260px;
            padding: 20px;
        }
        .table-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5 container-1">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h3>View Your Expense</h3>
            </div>

            <?php if (!empty($message)) { ?>
                <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <!-- View By Category -->
            <form method="POST" class="mb-4">
                <div class="form-group">
                    <label for="category_filter">Filter by Category</label>
                    <select name="category_filter" id="category_filter" class="form-control" onchange="this.form.submit()">
                        <option value="">-- Select Category --</option>
                        <?php foreach ($categories as $category) { ?>
                            <option value="<?php echo htmlspecialchars($category); ?>" <?php echo ($category_filter == $category) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </form>

            <!-- Expense Table -->
            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Amount (PHP)</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) { ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['date']); ?></td>
                                <td><?php echo htmlspecialchars($row['category']); ?></td>
                                <td><?php echo htmlspecialchars($row['amount']); ?></td>
                                <td><?php echo htmlspecialchars($row['description']); ?></td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr><td colspan="4" class="text-center">No expenses found</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
