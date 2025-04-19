<?php
require_once 'db.php';
include('dashboard.php');

// Get the logged-in user’s ID
$user_id = $_SESSION['user_id'];

$message = "";
$search = "";

// Handle form submissions for searching or updating
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Search functionality
    if (isset($_POST['search'])) {
        $search = $_POST['search'];
    }
    // Update functionality
    else {
        $expense_id = $_POST['expense_id'];
        $category = $_POST['category'];
        $amount = $_POST['amount'];
        $date = $_POST['date'];
        $description = $_POST['description'];

        // Update the expense for the logged-in user
        $stmt = $conn->prepare("UPDATE expenses SET category=?, amount=?, date=?, description=? WHERE expenses_id=? AND user_id=?");
        $stmt->bind_param("ssssii", $category, $amount, $date, $description, $expense_id, $user_id);
        if ($stmt->execute()) {
            $message = "Expense updated successfully!";
        } else {
            $message = "Failed to update expense.";
        }
    }
}

// Fetch expenses based on the logged-in user
if (!empty($search)) {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ? AND (category LIKE ? OR description LIKE ?)");
    $likeSearch = "%" . $search . "%";
    $stmt->bind_param("iss", $user_id, $likeSearch, $likeSearch);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $stmt = $conn->prepare("SELECT * FROM expenses WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .container-1 {
            margin-left: 260px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container mt-5 container-1">
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-5">
                <h3>Edit Your Expenses</h3>
                <form method="POST" class="form-inline">
                    <input type="text" name="search" class="form-control mr-2" placeholder="Search expenses..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </form>
            </div>

            <?php if (!empty($message)) { ?>
                <div class="alert alert-info"> <?php echo htmlspecialchars($message); ?> </div>
            <?php } ?>

            <table class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Amount (PHP)</th>
                        <th>Description</th>
                        <th>Action</th>
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
                                <td>
                                    <button class="btn btn-warning btn-sm" onclick="populateForm(<?php echo htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                </td>
                            </tr>
                        <?php }
                    } else { ?>
                        <tr><td colspan="5" class="text-center">No expenses found</td></tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Edit Expense</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="expense_id" id="expense_id">

                        <div class="form-group">
                            <label for="category">Category</label>
                            <input type="text" class="form-control" id="category" name="category" required>
                        </div>

                        <div class="form-group">
                            <label for="amount">Amount (PHP)</label>
                            <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                        </div>

                        <div class="form-group">
                            <label for="date">Date</label>
                            <input type="date" class="form-control" id="date" name="date" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Expense</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function populateForm(data) {
            document.getElementById('expense_id').value = data.expenses_id;
            document.getElementById('category').value = data.category;
            document.getElementById('amount').value = data.amount;
            document.getElementById('date').value = data.date;
            document.getElementById('description').value = data.description;
            $('#editModal').modal('show');
        }
    </script>
</body>
</html>
