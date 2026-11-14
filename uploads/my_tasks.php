<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}
if (!isset($_SESSION["role"]) || $_SESSION["role"] !== "employee") {
    header("Location: dashboard.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$employeeName = $_SESSION["name"] ?? "";
$employee = null;
$tasks = [];
$attachmentsByTask = [];
$pageError = "";

function h($value): string {
    return htmlspecialchars((string)($value ?? ""), ENT_QUOTES, "UTF-8");
}

$employeeQuery = "SELECT id, full_name FROM employees WHERE LOWER(full_name) = LOWER(?) LIMIT 1";
$employeeStatement = mysqli_prepare($conn, $employeeQuery);

if ($employeeStatement) {
    mysqli_stmt_bind_param($employeeStatement, "s", $employeeName);
    if (mysqli_stmt_execute($employeeStatement)) {
        $employeeResult = mysqli_stmt_get_result($employeeStatement);
        $employee = mysqli_fetch_assoc($employeeResult);
    } else {
        $pageError = "Could not load your employee profile.";
    }
    mysqli_stmt_close($employeeStatement);
} else {
    $pageError = "Could not prepare the employee query.";
}

if ($employee && $pageError === "") {
    $taskQuery = "SELECT id, task_name, description, due_date, status FROM onboarding_tasks WHERE employee_id = ? ORDER BY id DESC";
    $taskStatement = mysqli_prepare($conn, $taskQuery);

    if ($taskStatement) {
        mysqli_stmt_bind_param($taskStatement, "i", $employee["id"]);
        if (mysqli_stmt_execute($taskStatement)) {
            $taskResult = mysqli_stmt_get_result($taskStatement);
            while ($row = mysqli_fetch_assoc($taskResult)) {
                $tasks[] = $row;
            }
        } else {
            $pageError = "Could not load your onboarding tasks.";
        }
        mysqli_stmt_close($taskStatement);
    } else {
        $pageError = "Could not prepare the tasks query.";
    }
}

/* Optional: show files already saved in task_attachments. */
if ($employee && $pageError === "") {
    $attachmentQuery = "SELECT task_id, file_name, file_path, file_type FROM task_attachments WHERE employee_id = ? ORDER BY id DESC";
    $attachmentStatement = mysqli_prepare($conn, $attachmentQuery);
    if ($attachmentStatement) {
        mysqli_stmt_bind_param($attachmentStatement, "i", $employee["id"]);
        if (mysqli_stmt_execute($attachmentStatement)) {
            $attachmentResult = mysqli_stmt_get_result($attachmentStatement);
            while ($attachment = mysqli_fetch_assoc($attachmentResult)) {
                $attachmentsByTask[(int)$attachment["task_id"]][] = $attachment;
            }
        }
        mysqli_stmt_close($attachmentStatement);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Onboarding Tasks | HRTech</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .submission-modal .modal-content { border: 0; border-radius: 1rem; }
        .submission-modal .modal-header { background: #f1f5ff; }
        .file-hint { font-size: .875rem; color: #6c757d; }
        .attachment-list a { overflow-wrap: anywhere; }
    </style>
</head>
<body class="bg-light">
<nav class="navbar navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="dashboard.php">HRTech</a>
        <a href="logout.php" class="btn btn-light">Logout</a>
    </div>
</nav>

<main class="container my-5">
    <h2>My Onboarding Tasks</h2>
    <p class="text-muted">Welcome, <strong><?php echo h($employeeName); ?></strong></p>

    <?php if (isset($_GET["success"])): ?>
        <div class="alert alert-success" role="alert">Work submitted successfully. Your task status is now Completed.</div>
    <?php endif; ?>

    <?php if (isset($_GET["error"]) && $_GET["error"] === "empty"): ?>
        <div class="alert alert-danger" role="alert">Enter work text or select at least one file.</div>
    <?php elseif (isset($_GET["error"]) && $_GET["error"] === "database"): ?>
        <div class="alert alert-danger" role="alert">The submission could not be saved because of a database error.</div>
    <?php elseif (isset($_GET["error"]) && $_GET["error"] === "notfound"): ?>
        <div class="alert alert-warning" role="alert">This task was not found for your employee account.</div>
    <?php elseif (isset($_GET["error"]) && $_GET["error"] === "upload"): ?>
        <div class="alert alert-danger" role="alert">A file could not be uploaded. Check the file type and size, then try again.</div>
    <?php elseif (isset($_GET["error"]) && $_GET["error"] === "profile"): ?>
        <div class="alert alert-danger" role="alert">Your employee profile could not be matched to your login.</div>
    <?php endif; ?>

    <?php if ($pageError !== ""): ?>
        <div class="alert alert-danger" role="alert"><?php echo h($pageError); ?></div>
    <?php elseif (!$employee): ?>
        <div class="alert alert-warning" role="alert">Your employee profile was not found. Please contact HR.</div>
    <?php else: ?>
        <div class="card shadow border-0">
            <div class="card-body">
                <h4 class="mb-3">Assigned Tasks</h4>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-primary">
                        <tr><th>Task</th><th>Description</th><th>Due Date</th><th>Status</th><th>Action</th></tr>
                        </thead>
                        <tbody>
                        <?php if ($tasks): ?>
                            <?php foreach ($tasks as $task): ?>
                                <?php $taskId = (int)$task["id"]; $isCompleted = ($task["status"] === "Completed"); ?>
                                <tr>
                                    <td><?php echo h($task["task_name"]); ?></td>
                                    <td><?php echo nl2br(h($task["description"])); ?></td>
                                    <td><?php echo !empty($task["due_date"]) ? h($task["due_date"]) : "Not set"; ?></td>
                                    <td>
                                        <?php if ($isCompleted): ?>
                                            <span class="badge bg-success">Completed</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark"><?php echo h($task["status"]); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$isCompleted): ?>
                                            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#submitModal<?php echo $taskId; ?>">Submit Work</button>
                                        <?php else: ?>
                                            <span class="text-success fw-bold">Work Submitted</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php if ($isCompleted && !empty($attachmentsByTask[$taskId])): ?>
                                    <tr><td colspan="5">
                                        <strong>Submitted attachments:</strong>
                                        <ul class="attachment-list mb-0">
                                        <?php foreach ($attachmentsByTask[$taskId] as $attachment): ?>
                                            <li><a href="<?php echo h($attachment["file_path"]); ?>" target="_blank" rel="noopener"><?php echo h($attachment["file_name"]); ?></a> (<?php echo h($attachment["file_type"]); ?>)</li>
                                        <?php endforeach; ?>
                                        </ul>
                                    </td></tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="5" class="text-center">No onboarding tasks assigned.</td></tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal markup is intentionally outside the table. -->
        <?php foreach ($tasks as $task): ?>
            <?php if ($task["status"] !== "Completed"): ?>
                <?php $taskId = (int)$task["id"]; ?>
                <div class="modal fade submission-modal" id="submitModal<?php echo $taskId; ?>" tabindex="-1" aria-labelledby="submitModalLabel<?php echo $taskId; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <form action="submit_work.php" method="POST" enctype="multipart/form-data">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="submitModalLabel<?php echo $taskId; ?>">Submit Work</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="task_id" value="<?php echo $taskId; ?>">
                                    <p><strong>Task:</strong> <?php echo h($task["task_name"]); ?></p>
                                    <div class="mb-3">
                                        <label for="submissionText<?php echo $taskId; ?>" class="form-label">Work description (optional if you attach a file)</label>
                                        <textarea id="submissionText<?php echo $taskId; ?>" name="submission_text" class="form-control" rows="5" placeholder="Describe the work you completed..."></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="attachments<?php echo $taskId; ?>" class="form-label">Upload PDF or images</label>
                                        <input id="attachments<?php echo $taskId; ?>" type="file" name="attachments[]" class="form-control" accept=".pdf,.jpg,.jpeg,.png,.webp,application/pdf,image/jpeg,image/png,image/webp" multiple>
                                        <div class="file-hint mt-1">Allowed: PDF, JPG, JPEG, PNG, WEBP. Maximum 5 MB per file. Text, files, or both are accepted.</div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-success">Submit Work</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php endif; ?>
</main>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
