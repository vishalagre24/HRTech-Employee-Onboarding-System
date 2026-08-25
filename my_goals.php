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
// Only Employee Can Access
// =====================================

if (
    !isset($_SESSION["role"])
    ||
    $_SESSION["role"] !== "employee"
) {

    header("Location: dashboard.php");

    exit();
}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . "/../config/database.php";


// =====================================
// Show Message After Redirect
// =====================================

$message =
    $_SESSION["message"] ?? "";

$messageType =
    $_SESSION["messageType"] ?? "";


unset(
    $_SESSION["message"],
    $_SESSION["messageType"]
);


// =====================================
// Find Logged-in Employee
// =====================================

$employeeName =
    trim(
        $_SESSION["name"] ?? ""
    );


$employee = null;


$employeeQuery = "
    SELECT id, full_name
    FROM employees
    WHERE LOWER(TRIM(full_name))
    =
    LOWER(TRIM(?))
    LIMIT 1
";


$employeeStatement =
    mysqli_prepare(
        $conn,
        $employeeQuery
    );


if ($employeeStatement) {

    mysqli_stmt_bind_param(
        $employeeStatement,
        "s",
        $employeeName
    );


    mysqli_stmt_execute(
        $employeeStatement
    );


    $employeeResult =
        mysqli_stmt_get_result(
            $employeeStatement
        );


    $employee =
        mysqli_fetch_assoc(
            $employeeResult
        );

}


// =====================================
// Add New Goal
// =====================================

if (
    $_SERVER["REQUEST_METHOD"]
    ===
    "POST"
    &&
    isset($_POST["add_goal"])
) {

    if (!$employee) {

        $_SESSION["message"] =
            "Employee profile was not found.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    $goalTitle =
        trim(
            $_POST["goal_title"] ?? ""
        );


    $goalDescription =
        trim(
            $_POST["goal_description"] ?? ""
        );


    $targetDate =
        trim(
            $_POST["target_date"] ?? ""
        );


    // Validation

    if (
        $goalTitle === ""
        ||
        $targetDate === ""
    ) {

        $_SESSION["message"] =
            "Please enter goal title and target date.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    // Insert Goal

    $insertQuery = "
        INSERT INTO employee_goals
        (
            employee_id,
            goal_title,
            goal_description,
            target_date,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            'Not Started'
        )
    ";


    $insertStatement =
        mysqli_prepare(
            $conn,
            $insertQuery
        );


    if (!$insertStatement) {

        $_SESSION["message"] =
            "Database error: "
            .
            mysqli_error($conn);

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    mysqli_stmt_bind_param(
        $insertStatement,
        "isss",
        $employee["id"],
        $goalTitle,
        $goalDescription,
        $targetDate
    );


    if (
        mysqli_stmt_execute(
            $insertStatement
        )
    ) {

        $_SESSION["message"] =
            "Goal added successfully.";

        $_SESSION["messageType"] =
            "success";

    } else {

        $_SESSION["message"] =
            "Goal could not be added: "
            .
            mysqli_stmt_error(
                $insertStatement
            );

        $_SESSION["messageType"] =
            "danger";

    }


    // Redirect after POST

    header(
        "Location: my_goals.php"
    );

    exit();

}


// =====================================
// Update Goal Status
// =====================================

if (
    $_SERVER["REQUEST_METHOD"]
    ===
    "POST"
    &&
    isset($_POST["update_status"])
) {

    if (!$employee) {

        $_SESSION["message"] =
            "Employee profile was not found.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    $goalId =
        isset($_POST["goal_id"])
        ?
        (int) $_POST["goal_id"]
        :
        0;


    $goalStatus =
        trim(
            $_POST["status"] ?? ""
        );


    $allowedStatus = [

        "Not Started",

        "In Progress",

        "Completed"

    ];


    // Validate Goal ID

    if ($goalId <= 0) {

        $_SESSION["message"] =
            "Invalid goal ID.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    // Validate Status

    if (
        !in_array(
            $goalStatus,
            $allowedStatus,
            true
        )
    ) {

        $_SESSION["message"] =
            "Invalid goal status.";

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    // Update Goal

    $updateQuery = "
        UPDATE employee_goals
        SET status = ?
        WHERE id = ?
        AND employee_id = ?
    ";


    $updateStatement =
        mysqli_prepare(
            $conn,
            $updateQuery
        );


    if (!$updateStatement) {

        $_SESSION["message"] =
            "Database query error: "
            .
            mysqli_error($conn);

        $_SESSION["messageType"] =
            "danger";


        header(
            "Location: my_goals.php"
        );

        exit();

    }


    mysqli_stmt_bind_param(
        $updateStatement,
        "sii",
        $goalStatus,
        $goalId,
        $employee["id"]
    );


    if (
        mysqli_stmt_execute(
            $updateStatement
        )
    ) {

        if (
            mysqli_stmt_affected_rows(
                $updateStatement
            ) > 0
        ) {

            $_SESSION["message"] =
                "Goal status updated successfully.";

            $_SESSION["messageType"] =
                "success";

        } else {

            $_SESSION["message"] =
                "No change was made. This goal already has the selected status.";

            $_SESSION["messageType"] =
                "info";

        }

    } else {

        $_SESSION["message"] =
            "Goal update failed: "
            .
            mysqli_stmt_error(
                $updateStatement
            );

        $_SESSION["messageType"] =
            "danger";

    }


    // Redirect after POST

    header(
        "Location: my_goals.php"
    );

    exit();

}


// =====================================
// Get Employee Goals
// =====================================

$goalResult = null;


if ($employee) {

    $goalQuery = "
        SELECT *
        FROM employee_goals
        WHERE employee_id = ?
        ORDER BY id DESC
    ";


    $goalStatement =
        mysqli_prepare(
            $conn,
            $goalQuery
        );


    if ($goalStatement) {

        mysqli_stmt_bind_param(
            $goalStatement,
            "i",
            $employee["id"]
        );


        mysqli_stmt_execute(
            $goalStatement
        );


        $goalResult =
            mysqli_stmt_get_result(
                $goalStatement
            );

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Goals - HRTech</title>


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


<!-- Navbar -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">


        <a
            href="dashboard.php"
            class="navbar-brand fw-bold"
        >

            HRTech

        </a>


        <a
            href="dashboard.php"
            class="btn btn-light"
        >

            Dashboard

        </a>


    </div>

</nav>


<!-- Main Content -->

<div class="container mt-5">


    <h2>

        My Goals

    </h2>


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


    <!-- Success / Error Message -->

    <?php if ($message !== "") { ?>


        <div
            class="
            alert
            alert-<?php
            echo htmlspecialchars(
                $messageType
            );
            ?>
            alert-dismissible
            fade
            show
            "
        >


            <?php

            echo htmlspecialchars(
                $message
            );

            ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>


        </div>


    <?php } ?>


    <!-- Employee Not Found -->

    <?php if (!$employee) { ?>


        <div class="alert alert-warning">

            Your employee profile was not found.

            Please contact HR.

        </div>


    <?php } else { ?>


        <!-- Add Goal Form -->

        <div
            class="
            card
            shadow
            border-0
            mb-4
            "
        >


            <div class="card-body">


                <h4 class="mb-3">

                    Add New Goal

                </h4>


                <form method="POST">


                    <!-- Goal Title -->

                    <div class="mb-3">


                        <label class="form-label">

                            Goal Title

                        </label>


                        <input
                            type="text"
                            name="goal_title"
                            class="form-control"
                            placeholder="Example: Complete Java Course"
                            required
                        >


                    </div>


                    <!-- Goal Description -->

                    <div class="mb-3">


                        <label class="form-label">

                            Goal Description

                        </label>


                        <textarea
                            name="goal_description"
                            class="form-control"
                            rows="4"
                            placeholder="Write your goal details..."
                        ></textarea>


                    </div>


                    <!-- Target Date -->

                    <div class="mb-3">


                        <label class="form-label">

                            Target Date

                        </label>


                        <input
                            type="date"
                            name="target_date"
                            class="form-control"
                            required
                        >


                    </div>


                    <!-- Add Button -->

                    <button
                        type="submit"
                        name="add_goal"
                        value="1"
                        class="btn btn-success"
                    >

                        Add Goal

                    </button>


                </form>


            </div>

        </div>


        <!-- Goal List -->

        <div
            class="
            card
            shadow
            border-0
            "
        >


            <div class="card-body">


                <h4 class="mb-3">

                    My Goal List

                </h4>


                <div class="table-responsive">


                    <table
                        class="
                        table
                        table-bordered
                        align-middle
                        "
                    >


                        <thead class="table-primary">


                            <tr>

                                <th>Goal</th>

                                <th>Description</th>

                                <th>Target Date</th>

                                <th>Status</th>

                                <th>Update</th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php

                        if (
                            $goalResult
                            &&
                            mysqli_num_rows(
                                $goalResult
                            ) > 0
                        ) {

                            while (
                                $goal =
                                mysqli_fetch_assoc(
                                    $goalResult
                                )
                            ) {

                                $status =
                                    trim(
                                        $goal["status"]
                                    );


                                if (
                                    $status
                                    ===
                                    "Completed"
                                ) {

                                    $badge =
                                        "success";

                                } elseif (
                                    $status
                                    ===
                                    "In Progress"
                                ) {

                                    $badge =
                                        "primary";

                                } else {

                                    $badge =
                                        "secondary";

                                }

                        ?>


                            <tr>


                                <!-- Goal -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $goal[
                                            "goal_title"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Description -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $goal[
                                            "goal_description"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Target Date -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $goal[
                                            "target_date"
                                        ]
                                    );

                                    ?>

                                </td>


                                <!-- Status -->

                                <td>


                                    <span
                                        class="
                                        badge
                                        bg-<?php
                                        echo $badge;
                                        ?>
                                        "
                                    >


                                        <?php

                                        echo htmlspecialchars(
                                            $status
                                        );

                                        ?>


                                    </span>


                                </td>


                                <!-- Update -->

                                <td>


                                    <form
                                        method="POST"
                                        class="d-flex gap-2"
                                    >


                                        <input
                                            type="hidden"
                                            name="goal_id"
                                            value="<?php
                                            echo
                                            (int)
                                            $goal["id"];
                                            ?>"
                                        >


                                        <select
                                            name="status"
                                            class="
                                            form-select
                                            form-select-sm
                                            "
                                        >


                                            <option
                                                value="Not Started"
                                                <?php

                                                if (
                                                    $status
                                                    ===
                                                    "Not Started"
                                                ) {

                                                    echo
                                                    "selected";

                                                }

                                                ?>
                                            >

                                                Not Started

                                            </option>


                                            <option
                                                value="In Progress"
                                                <?php

                                                if (
                                                    $status
                                                    ===
                                                    "In Progress"
                                                ) {

                                                    echo
                                                    "selected";

                                                }

                                                ?>
                                            >

                                                In Progress

                                            </option>


                                            <option
                                                value="Completed"
                                                <?php

                                                if (
                                                    $status
                                                    ===
                                                    "Completed"
                                                ) {

                                                    echo
                                                    "selected";

                                                }

                                                ?>
                                            >

                                                Completed

                                            </option>


                                        </select>


                                        <button
                                            type="submit"
                                            name="update_status"
                                            value="1"
                                            class="
                                            btn
                                            btn-primary
                                            btn-sm
                                            "
                                        >

                                            Save

                                        </button>


                                    </form>


                                </td>


                            </tr>


                        <?php

                            }

                        } else {

                        ?>


                            <tr>


                                <td
                                    colspan="5"
                                    class="
                                    text-center
                                    text-muted
                                    "
                                >

                                    No goals added yet.

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


</body>

</html>