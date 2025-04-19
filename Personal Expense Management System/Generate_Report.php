<?php
require_once 'db.php';
include('dashboard.php');

$user_id = $_SESSION['user_id']; // Get the logged-in user's ID

// Default values for the month and year
$month = date('m');
$year = date('Y');
$message = "";

// Filter Request
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['month'])) {
    $month = $_POST['month'];
    $year = $_POST['year'];
}

// Add a report for the current user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_report'])) {
    // Step 1: Calculate the total amount of expenses for the given user, month, and year
    $sql_total_amount = "
    SELECT SUM(e.amount) AS total_amount
    FROM expenses e
    WHERE MONTH(e.date) = ? AND YEAR(e.date) = ? AND e.user_id = ?";

    $stmt_total_amount = $conn->prepare($sql_total_amount);
    $stmt_total_amount->bind_param("iii", $month, $year, $user_id);
    $stmt_total_amount->execute();
    $result_total = $stmt_total_amount->get_result();
    $row_total = $result_total->fetch_assoc();
    $total_amount = $row_total['total_amount'];
    $stmt_total_amount->close();

    // Step 2: Insert the report into the reports table with the calculated total amount
    $sql_insert_report = "
        INSERT INTO reports (expenses_id, report_data, total_amount, user_id)
        SELECT expenses_id, NOW(), ?, ? 
        FROM expenses
        WHERE MONTH(date) = ? AND YEAR(date) = ? AND user_id = ?";

    // Prepare and execute the insertion                                                                                              
    $stmt_insert = $conn->prepare($sql_insert_report);
    $stmt_insert->bind_param("diiii", $total_amount, $user_id, $month, $year, $user_id);
    if ($stmt_insert->execute()) {
        $message = "Report added successfully!";
    } else {
        $message = "Failed to add report.";
    }
    $stmt_insert->close();
}

// Query to summarize expenses by category and month for the current user
$sql = "
SELECT
    e.category,
    COUNT(e.expenses_id) AS expense_count,
    SUM(e.amount) AS total_amount,
    DATE_FORMAT(e.date, '%M') AS report_month
FROM
    expenses e
WHERE
    MONTH(e.date) = ? AND YEAR(e.date) = ? AND e.user_id = ?
GROUP BY
    e.category, report_month";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $month, $year, $user_id); // Bind user_id
$stmt->execute();
$result = $stmt->get_result();

// Variable to store the total amount of all records
$total_all_amount = 0;

// Query to fetch historical reports
$sql_history = "
SELECT r.report_id, r.report_data, r.total_amount, DATE_FORMAT(r.report_data, '%M %d, %Y') AS formatted_date
FROM reports r
WHERE r.user_id = ? 
ORDER BY r.report_data DESC";
$stmt_history = $conn->prepare($sql_history);
$stmt_history->bind_param("i", $user_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Reports</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; font-family: Arial, sans-serif; }
        
        .container-2 { margin-left: 260px; padding: 40px; max-width: 1000px; width: 100%; }
        .table { margin-top: 30px; font-size: 1.1rem; }
        .form-label, .form-control, .btn { font-size: 1.1rem; }
        h1 { font-size: 2rem; margin-bottom: 30px; font-weight: bold; }
        .table th, .table td { padding: 15px; text-align: center; }
        .table thead { background-color: #343a40; color: white; }
        .table-striped tbody tr:nth-of-type(odd) { background-color: #f2f2f2; }
        .btn-primary { background-color: #007bff; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-info { background-color: #17a2b8; }
        .btn-info:hover { background-color: #117a8b; }
        /* Custom button container with spacing */
        .btn_filter, .add_report, .show_history {
            margin-top: 20px; /* Add spacing between buttons */
            margin-right: 10px; /* Add space between buttons */
        }

        /* Flexbox for aligning buttons */
        .row.g-3 {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .form-control {
            max-width: 200px; /* Optional: limit the width of the form fields */
        }
    </style>
</head>
<body>
<div class="container-2">
    <h1 class="text-center">Monthly Expense Report</h1>

    <!-- Filter Form -->
    <form method="POST" class="mb-4">
        <div class="row g-3 align-items-center justify-content-center">
            <div class="col-auto">
                <label for="month" class="form-label">Month:</label>
                <select id="month" name="month" class="form-control">
                    <?php for ($m = 1; $m <= 12; $m++): ?>
                        <option value="<?= $m; ?>" <?= ($m == $month) ? 'selected' : ''; ?>>
                            <?= date('F', mktime(0, 0, 0, $m, 1)); ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-auto">
                <label for="year" class="form-label">Year:</label>
                <input type="number" id="year" name="year" class="form-control" value="<?= $year; ?>" min="2000" max="<?= date('Y'); ?>">
            </div>

            <!-- Button container with spacing -->
            <div class="col-auto btn_filter">
                <button type="submit" class="btn btn-primary">Filter</button>
            </div>

            <!-- Add Report Button -->
            <div class="col-auto add_report">
                <form method="POST">
                    <button type="submit" name="add_report" class="btn btn-success">Add Report</button>
                </form>
            </div>

            <!-- Show History Button -->
            <div class="col-auto show_history">
                <button type="button" class="btn btn-info" data-toggle="modal" data-target="#historyModal">Show Report</button>
            </div>
        </div>
    </form>

    <!-- Display Report -->
    <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Report ID</th>
                    <th>Category</th>
                    <th>Total Expenses (Count)</th>
                    <th>Total Amount</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $report_id = 1;
                while ($row = $result->fetch_assoc()):
                    $total_all_amount += $row['total_amount'];
                ?>
                <tr>
                    <td><?= $report_id++; ?></td>
                    <td><?= $row['category']; ?></td>
                    <td><?= $row['expense_count']; ?></td>
                    <td><?= number_format($row['total_amount'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="mt-4 text-end">
            <strong>Total Amount for Expenses: </strong>
            <span class="h4"><?= number_format($total_all_amount, 2); ?></span>
        </div>
    <?php else: ?>
        <p class="text-center text-danger">No reports found for the selected month and year.</p>
    <?php endif; ?>
</div>

<!-- Success Modal -->
<div class="modal fade" id="successModal" tabindex="-1" role="dialog" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="successModalLabel">Success</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?= isset($message) ? $message : ''; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Report History Modal -->
<div class="modal fade" id="historyModal" tabindex="-1" role="dialog" aria-labelledby="historyModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historyModalLabel">Report History</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php if ($result_history->num_rows > 0): ?>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Report ID</th>
                                <th>Report Date</th>
                                <th>Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row_history = $result_history->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $row_history['report_id']; ?></td>
                                    <td><?= $row_history['formatted_date']; ?></td>
                                    <td><?= number_format($row_history['total_amount'], 2); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-center text-danger">No history found.</p>
                <?php endif; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<!-- Show success modal if message is set -->
<?php if ($message): ?>
    <script>$('#successModal').modal('show');</script>
<?php endif; ?>

</body>
</html>

<?php
$stmt->close();
$stmt_history->close();
$conn->close();
?>
