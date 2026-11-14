<?php
session_start();

mysqli_report(MYSQLI_REPORT_OFF);

// Check login
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Check employee role
if (
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "employee"
) {
    header("Location: dashboard.php");
    exit();
}

// Database connection
require_once __DIR__ . "/../config/database.php";

// Accept POST request only
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: my_tasks.php");
    exit();
}

// Get submitted data
$taskId = (int) ($_POST["task_id"] ?? 0);
$submissionText = trim($_POST["submission_text"] ?? "");
$employeeName = $_SESSION["name"] ?? "";

// Validate input
if ($taskId <= 0 || $submissionText === "") {
    header("Location: my_tasks.php?error=empty");
    exit();
}

// Find employee ID using the same method as my_tasks.php
$employeeQuery = "
    SELECT id
    FROM employees
    WHERE LOWER(full_name) = LOWER(?)
    LIMIT 1
";

$employeeStatement = mysqli_prepare(
    $conn,
    $employeeQuery
);

if (!$employeeStatement) {
    error_log("Employee query error: " . mysqli_error($conn));
    header("Location: my_tasks.php?error=database");
    exit();
}

mysqli_stmt_bind_param(
    $employeeStatement,
    "s",
    $employeeName
);

if (!mysqli_stmt_execute($employeeStatement)) {
    error_log("Employee lookup failed: " . mysqli_stmt_error($employeeStatement));
    mysqli_stmt_close($employeeStatement);

    header("Location: my_tasks.php?error=database");
    exit();
}

$employeeResult = mysqli_stmt_get_result($employeeStatement);
$employee = mysqli_fetch_assoc($employeeResult);

mysqli_stmt_close($employeeStatement);

if (!$employee) {
    header("Location: my_tasks.php?error=profile");
    exit();
}

$employeeId = (int) $employee["id"];

// Update only this employee's task
$updateQuery = "
    UPDATE onboarding_tasks
    SET
        submission_text = ?,
        submitted_at = NOW(),
        status = 'Completed'
    WHERE id = ?
      AND employee_id = ?
";

$updateStatement = mysqli_prepare(
    $conn,
    $updateQuery
);

if (!$updateStatement) {
    error_log("Task update preparation failed: " . mysqli_error($conn));
    header("Location: my_tasks.php?error=database");
    exit();
}

mysqli_stmt_bind_param(
    $updateStatement,
    "sii",
    $submissionText,
    $taskId,
    $employeeId
);

if (!mysqli_stmt_execute($updateStatement)) {
    error_log("Task update failed: " . mysqli_stmt_error($updateStatement));
    mysqli_stmt_close($updateStatement);

    header("Location: my_tasks.php?error=database");
    exit();
}

$affectedRows = mysqli_stmt_affected_rows($updateStatement);

mysqli_stmt_close($updateStatement);

// Redirect according to the result
if ($affectedRows > 0) {
    header("Location: my_tasks.php?success=1");
    exit();
}

header("Location: my_tasks.php?error=notfound");
exit();
?>