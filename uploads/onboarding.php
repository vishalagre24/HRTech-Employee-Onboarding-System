<?php

session_start();


// =====================================
// Login Check
// =====================================

if (!isset($_SESSION["user_id"])) {

    header("Location: login.php");
    exit();

}


// =====================================
// Admin Access Check
// =====================================

$userRole = strtolower(
    trim($_SESSION["role"] ?? "")
);


if ($userRole !== "admin") {

    header("Location: dashboard.php");
    exit();

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
        (int) ($_POST["employee_id"] ?? 0);


    $taskTitle =
        trim($_POST["task_title"] ?? "");


    $taskDescription =
        trim($_POST["description"] ?? "");


    $dueDate =
        trim($_POST["due_date"] ?? "");


    // =====================================
    // Validation
    // =====================================

    if ($employeeId <= 0) {

        $error =
            "Please select an employee.";

    } elseif ($taskTitle === "") {

        $error =
            "Please enter a task title.";

    } else {

        // =====================================
        // Insert Task
        // =====================================

        $insertQuery = "

            INSERT INTO onboarding_tasks
            (
                employee_id,
                task_name,
                description,
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


        $insertStatement =
            mysqli_prepare(
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
                    "Task could not be assigned: "
                    .
                    mysqli_stmt_error(
                        $insertStatement
                    );

            }


            mysqli_stmt_close(
                $insertStatement
            );

        } else {

            $error =
                "Database query preparation failed: "
                .
                mysqli_error($conn);

        }

    }

}


// =====================================
// Get All Employees
// =====================================

$employeeQuery = "

    SELECT
        id,
        full_name,
        department,
        position

    FROM employees

    ORDER BY full_name ASC

";


$employeeResult =
    mysqli_query(
        $conn,
        $employeeQuery
    );


// =====================================
// Get Assigned Tasks
// =====================================

$taskQuery = "

    SELECT

        onboarding_tasks.id,

        onboarding_tasks.task_name,

        onboarding_tasks.description,

        onboarding_tasks.due_date,

        onboarding_tasks.status,

        employees.id AS employee_database_id,

        employees.full_name,

        employees.department

    FROM onboarding_tasks

    INNER JOIN employees

    ON onboarding_tasks.employee_id
    =
    employees.id

    ORDER BY onboarding_tasks.id DESC

";


$taskResult =
    mysqli_query(
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
        Assign Onboarding Task - HRTech
    </title>


    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Custom CSS -->

    <link
        rel="stylesheet"
        href="../css/style.css"
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
            echo htmlspecialchars($message);
            ?>

        </div>


    <?php } ?>


    <!-- Error Message -->

    <?php if ($error !== "") { ?>


        <div class="alert alert-danger">

            <?php
            echo htmlspecialchars($error);
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
                            mysqli_num_rows($employeeResult) > 0
                        ) {

                            while (
                                $employee =
                                mysqli_fetch_assoc($employeeResult)
                            ) {

                        ?>


                            <option
                                value="<?php
                                echo $employee["id"];
                                ?>"
                            >


                                <?php

                                echo htmlspecialchars(
                                    $employee["full_name"]
                                );

                                ?>


                                <?php

                                if (
                                    !empty(
                                        $employee["department"]
                                    )
                                ) {

                                    echo
                                    " - "
                                    .
                                    htmlspecialchars(
                                        $employee["department"]
                                    );

                                }

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
                        name="description"
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

                            <th>Employee</th>

                            <th>Employee ID</th>

                            <th>Department</th>

                            <th>Task</th>

                            <th>Description</th>

                            <th>Due Date</th>

                            <th>Status</th>

                        </tr>


                    </thead>


                    <tbody>


                    <?php

                    if (
                        $taskResult &&
                        mysqli_num_rows($taskResult) > 0
                    ) {

                        while (
                            $task =
                            mysqli_fetch_assoc($taskResult)
                        ) {

                            $status =
                                trim(
                                    $task["status"]
                                );

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["full_name"]
                                );

                                ?>

                            </td>


                            <!-- Database Employee ID -->

                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task[
                                        "employee_database_id"
                                    ]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task[
                                        "department"
                                    ] ?? ""
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task["task_name"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $task[
                                        "description"
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

                                if (
                                    $status === "Completed"
                                ) {

                                    $badge = "success";

                                } elseif (
                                    $status === "In Progress"
                                ) {

                                    $badge = "primary";

                                } else {

                                    $badge = "warning";

                                }

                                ?>


                                <span
                                    class="
                                    badge
                                    bg-<?php echo $badge; ?>
                                    "
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $status
                                    );

                                    ?>

                                </span>


                            </td>


                        </tr>


                    <?php

                        }

                    } else {

                    ?>


                        <tr>

                            <td
                                colspan="7"
                                class="text-center text-muted"
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