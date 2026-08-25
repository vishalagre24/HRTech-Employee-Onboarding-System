<?php

session_start();


// Login check

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");

    exit();

}


// =====================================
// Admin Access Check
// =====================================

if (
    !isset($_SESSION["role"]) ||
    strtolower(trim($_SESSION["role"])) !== "admin"
) {

    die(
        "Access denied. Only HR Admin can assign onboarding tasks."
    );

}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . "/../config/database.php";


$message = "";

$error = "";


// =====================================
// Assign Onboarding Task
// =====================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId =
        isset($_POST["employee_id"])
        ? (int) $_POST["employee_id"]
        : 0;


    $taskTitle =
        isset($_POST["task_title"])
        ? trim($_POST["task_title"])
        : "";


    $taskDescription =
        isset($_POST["task_description"])
        ? trim($_POST["task_description"])
        : "";


    $dueDate =
        isset($_POST["due_date"])
        ? trim($_POST["due_date"])
        : "";


    // Validation

    if (
        $employeeId <= 0 ||
        $taskTitle === ""
    ) {

        $error =
            "Please select an employee and enter a task title.";

    } else {

        // Insert task

        $insertQuery = "
            INSERT INTO onboarding_tasks
            (
                employee_id,
                task_title,
                task_description,
                due_date,
                status
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?,
                'Pending'
            )
        ";


        $insertStatement = mysqli_prepare(
            $conn,
            $insertQuery
        );


        if ($insertStatement) {

            mysqli_stmt_bind_param(
                $insertStatement,
                "isss",
                $employeeId,
                $taskTitle,
                $taskDescription,
                $dueDate
            );


            if (
                mysqli_stmt_execute(
                    $insertStatement
                )
            ) {

                $message =
                    "Onboarding task assigned successfully.";

            } else {

                $error =
                    "Task could not be assigned.";

            }

        } else {

            $error =
                "Database query preparation failed.";

        }

    }

}


// =====================================
// Get All Employees
// =====================================

$employeeQuery = "
    SELECT
        id,
        employee_id,
        full_name,
        department
    FROM employees
    ORDER BY full_name ASC
";


$employeeResult = mysqli_query(
    $conn,
    $employeeQuery
);


// =====================================
// Get Assigned Tasks
// =====================================

$taskQuery = "
    SELECT
        onboarding_tasks.id,
        onboarding_tasks.task_title,
        onboarding_tasks.task_description,
        onboarding_tasks.due_date,
        onboarding_tasks.status,
        employees.full_name,
        employees.employee_id
    FROM onboarding_tasks

    INNER JOIN employees

    ON onboarding_tasks.employee_id
    =
    employees.id

    ORDER BY onboarding_tasks.id DESC
";


$taskResult = mysqli_query(
    $conn,
    $taskQuery
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

    <title>

        Assign Onboarding Task

    </title>


    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<!-- =====================================
Navbar
===================================== -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">


        <a
            href="dashboard.php"
            class="navbar-brand fw-bold"
        >

            HRTech

        </a>


        <div>


            <a
                href="dashboard.php"
                class="btn btn-light me-2"
            >

                Dashboard

            </a>


            <a
                href="logout.php"
                class="btn btn-danger"
            >

                Logout

            </a>


        </div>


    </div>

</nav>


<!-- =====================================
Main Content
===================================== -->

<div class="container mt-5">


    <h2>

        Assign Onboarding Task

    </h2>


    <p class="text-muted">

        Assign onboarding tasks to employees.

    </p>


    <!-- Success Message -->

    <?php if ($message !== "") { ?>


        <div class="alert alert-success">

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>


    <?php } ?>


    <!-- Error Message -->

    <?php if ($error !== "") { ?>


        <div class="alert alert-danger">

            <?php

            echo htmlspecialchars(
                $error
            );

            ?>

        </div>


    <?php } ?>


    <!-- =====================================
    Assign Task Form
    ===================================== -->

    <div class="card shadow border-0 mb-5">

        <div class="card-body">


            <h4 class="mb-4">

                Create New Task

            </h4>


            <form method="POST">


                <!-- Employee -->

                <div class="mb-3">

                    <label class="form-label">

                        Select Employee

                    </label>


                    <select
                        name="employee_id"
                        class="form-select"
                        required
                    >

                        <option value="">

                            Select Employee

                        </option>


                        <?php

                        if (
                            $employeeResult &&
                            mysqli_num_rows(
                                $employeeResult
                            ) > 0
                        ) {

                            while (
                                $employee =
                                mysqli_fetch_assoc(
                                    $employeeResult
                                )
                            ) {

                        ?>


                            <option
                                value="<?php

                                echo
                                $employee["id"];

                                ?>"
                            >

                                <?php

                                echo htmlspecialchars(
                                    $employee["full_name"]
                                );

                                ?>

                                -

                                <?php

                                echo htmlspecialchars(
                                    $employee["employee_id"]
                                );

                                ?>

                            </option>


                        <?php

                            }

                        }

                        ?>


                    </select>

                </div>


                <!-- Task Title -->

                <div class="mb-3">

                    <label class="form-label">

                        Task Title

                    </label>


                    <input
                        type="text"
                        name="task_title"
                        class="form-control"
                        placeholder="Enter task title"
                        required
                    >

                </div>


                <!-- Description -->

                <div class="mb-3">

                    <label class="form-label">

                        Task Description

                    </label>


                    <textarea
                        name="task_description"
                        class="form-control"
                        rows="4"
                        placeholder="Enter task description"
                    ></textarea>

                </div>


                <!-- Due Date -->

                <div class="mb-3">

                    <label class="form-label">

                        Due Date

                    </label>


                    <input
                        type="date"
                        name="due_date"
                        class="form-control"
                    >

                </div>


                <!-- Submit -->

                <button
                    type="submit"
                    class="btn btn-primary"
                >

                    Assign Task

                </button>


            </form>


        </div>

    </div>


    <!-- =====================================
    Assigned Tasks List
    ===================================== -->

    <div class="card shadow border-0">

        <div class="card-body">


            <h4 class="mb-4">

                Assigned Onboarding Tasks

            </h4>


            <div class="table-responsive">


                <table
                    class="table table-bordered table-hover"
                >


                    <thead class="table-primary">

                        <tr>

                            <th>

                                Employee

                            </th>

                            <th>

                                Employee ID

                            </th>

                            <th>

                                Task

                            </th>

                            <th>

                                Description

                            </th>

                            <th>

                                Due Date

                            </th>

                            <th>

                                Status

                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php

                    if (
                        $taskResult &&
                        mysqli_num_rows(
                            $taskResult
                        ) > 0
                    ) {

                        while (
                            $task =
                            mysqli_fetch_assoc(
                                $taskResult
                            )
                        ) {

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["full_name"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["employee_id"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["task_title"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task[
                                        "task_description"
                                    ] ?? ""
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task[
                                        "due_date"
                                    ] ?? ""
                                );

                                ?>

                            </td>


                            <td>


                                <?php

                                $status =
                                $task["status"];


                                if (
                                    $status ===
                                    "Completed"
                                ) {

                                ?>


                                    <span
                                        class="
                                        badge
                                        bg-success
                                        "
                                    >

                                        Completed

                                    </span>


                                <?php

                                } elseif (
                                    $status ===
                                    "In Progress"
                                ) {

                                ?>


                                    <span
                                        class="
                                        badge
                                        bg-primary
                                        "
                                    >

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


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="6"
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


</div>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>