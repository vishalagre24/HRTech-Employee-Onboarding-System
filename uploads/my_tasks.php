<?php

session_start();

// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// Only employee can access
if ($_SESSION["role"] !== "employee") {
    header("Location: dashboard.php");
    exit();
}

// Database connection
require_once __DIR__ . "/../config/database.php";

// Logged-in employee name
$employeeName = $_SESSION["name"];

// Find employee using name
$employeeQuery = "
    SELECT id, full_name
    FROM employees
    WHERE LOWER(full_name) = LOWER(?)
    LIMIT 1
";

$employeeStatement = mysqli_prepare(
    $conn,
    $employeeQuery
);

mysqli_stmt_bind_param(
    $employeeStatement,
    "s",
    $employeeName
);

mysqli_stmt_execute(
    $employeeStatement
);

$employeeResult = mysqli_stmt_get_result(
    $employeeStatement
);

$employee = mysqli_fetch_assoc(
    $employeeResult
);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Onboarding Tasks</title>

    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<!-- Navbar -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="dashboard.php"
        >

            HRTech

        </a>

        <a
            href="logout.php"
            class="btn btn-light"
        >

            Logout

        </a>

    </div>

</nav>


<!-- Main Content -->

<div class="container mt-5">

    <h2>My Onboarding Tasks</h2>

    <p class="text-muted">

        Welcome,

        <strong>

            <?php

            echo htmlspecialchars(
                $_SESSION["name"]
            );

            ?>

        </strong>

    </p>


    <!-- Success Message -->

    <?php if (isset($_GET["success"])) { ?>

        <div class="alert alert-success">

            Work submitted successfully.
            Task status is now Completed.

        </div>

    <?php } ?>


    <!-- Empty Work Error -->

    <?php

    if (
        isset($_GET["error"])
        &&
        $_GET["error"] === "empty"
    ) {

    ?>

        <div class="alert alert-danger">

            Please enter your work before submitting.

        </div>

    <?php } ?>


    <?php if (!$employee) { ?>

        <div class="alert alert-warning">

            Your employee profile was not found.
            Please contact HR.

        </div>

    <?php } else { ?>


        <?php

        // Get assigned tasks

        $taskQuery = "
            SELECT *
            FROM onboarding_tasks
            WHERE employee_id = ?
            ORDER BY id DESC
        ";

        $taskStatement = mysqli_prepare(
            $conn,
            $taskQuery
        );

        mysqli_stmt_bind_param(
            $taskStatement,
            "i",
            $employee["id"]
        );

        mysqli_stmt_execute(
            $taskStatement
        );

        $taskResult = mysqli_stmt_get_result(
            $taskStatement
        );

        ?>


        <div class="card shadow border-0">

            <div class="card-body">

                <h4 class="mb-3">

                    Assigned Tasks

                </h4>


                <div class="table-responsive">

                    <table class="table table-bordered align-middle">

                        <thead class="table-primary">

                            <tr>

                                <th>Task</th>

                                <th>Description</th>

                                <th>Due Date</th>

                                <th>Status</th>

                                <th>Action</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        if (
                            mysqli_num_rows(
                                $taskResult
                            ) > 0
                        ) {

                            while (
                                $task = mysqli_fetch_assoc(
                                    $taskResult
                                )
                            ) {

                                $taskId =
                                    $task["id"];

                        ?>


                            <tr>

                                <!-- Task Title -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $task["task_title"]
                                    );

                                    ?>

                                </td>


                                <!-- Task Description -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $task[
                                            "task_description"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Due Date -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $task["due_date"]
                                    );

                                    ?>

                                </td>


                                <!-- Status -->

                                <td>

                                    <?php

                                    if (
                                        $task["status"]
                                        === "Completed"
                                    ) {

                                    ?>

                                        <span class="badge bg-success">

                                            Completed

                                        </span>

                                    <?php

                                    } elseif (
                                        $task["status"]
                                        === "In Progress"
                                    ) {

                                    ?>

                                        <span class="badge bg-primary">

                                            In Progress

                                        </span>

                                    <?php

                                    } else {

                                    ?>

                                        <span
                                            class="
                                            badge
                                            bg-warning
                                            text-dark
                                            "
                                        >

                                            Pending

                                        </span>

                                    <?php

                                    }

                                    ?>

                                </td>


                                <!-- Action -->

                                <td>

                                    <?php

                                    if (
                                        $task["status"]
                                        !== "Completed"
                                    ) {

                                    ?>

                                        <button
                                            type="button"
                                            class="
                                            btn
                                            btn-primary
                                            btn-sm
                                            "
                                            data-bs-toggle="modal"
                                            data-bs-target="#submitModal<?php echo $taskId; ?>"
                                        >

                                            Submit Work

                                        </button>

                                    <?php

                                    } else {

                                    ?>

                                        <span
                                            class="
                                            text-success
                                            fw-bold
                                            "
                                        >

                                            Work Submitted

                                        </span>

                                    <?php

                                    }

                                    ?>

                                </td>

                            </tr>


                            <!-- Submit Work Modal -->

                            <div
                                class="modal fade"
                                id="submitModal<?php echo $taskId; ?>"
                                tabindex="-1"
                                aria-hidden="true"
                            >

                                <div class="modal-dialog">

                                    <div class="modal-content">

                                        <form
                                            action="submit_work.php"
                                            method="POST"
                                        >

                                            <div class="modal-header">

                                                <h5 class="modal-title">

                                                    Submit Work

                                                </h5>

                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"
                                                ></button>

                                            </div>


                                            <div class="modal-body">

                                                <input
                                                    type="hidden"
                                                    name="task_id"
                                                    value="<?php echo $taskId; ?>"
                                                >


                                                <label
                                                    class="form-label"
                                                >

                                                    Enter your work

                                                </label>


                                                <textarea
                                                    name="submission_text"
                                                    class="form-control"
                                                    rows="6"
                                                    placeholder="Write your completed work here..."
                                                    required
                                                ></textarea>

                                            </div>


                                            <div class="modal-footer">

                                                <button
                                                    type="button"
                                                    class="
                                                    btn
                                                    btn-secondary
                                                    "
                                                    data-bs-dismiss="modal"
                                                >

                                                    Cancel

                                                </button>


                                                <button
                                                    type="submit"
                                                    class="
                                                    btn
                                                    btn-success
                                                    "
                                                >

                                                    Submit Work

                                                </button>

                                            </div>

                                        </form>

                                    </div>

                                </div>

                            </div>


                        <?php

                            }

                        } else {

                        ?>


                            <tr>

                                <td
                                    colspan="5"
                                    class="text-center"
                                >

                                    No onboarding tasks assigned.

                                </td>

                            </tr>


                        <?php

                        }

                        ?>


                        </tbody>

                    </table>

                </div>

            </div>

        </div>


    <?php } ?>


</div>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>
<link
    rel="stylesheet"
    href="../css/style.css"
>

</body>

</html>