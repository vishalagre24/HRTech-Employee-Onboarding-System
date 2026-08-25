<?php

session_start();


// =====================================
// Login Check
// =====================================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../uploads/login.php");

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

    header("Location: ../uploads/dashboard.php");

    exit();
}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . "/../config/database.php";


// =====================================
// Default Values
// =====================================

$message = "";

$messageType = "";

$employee = null;

$userId = (int) $_SESSION["user_id"];

$userName = trim(
    $_SESSION["name"] ?? ""
);


// =====================================
// Find Employee
// =====================================

// First try using user_id

$employeeQuery = "

    SELECT id, full_name

    FROM employees

    WHERE user_id = ?

    LIMIT 1

";


$employeeStatement = mysqli_prepare(
    $conn,
    $employeeQuery
);


if ($employeeStatement) {

    mysqli_stmt_bind_param(
        $employeeStatement,
        "i",
        $userId
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
// Fallback: Find Using Name
// =====================================

if (!$employee) {

    $employeeQuery = "

        SELECT id, full_name

        FROM employees

        WHERE LOWER(TRIM(full_name))
        =
        LOWER(TRIM(?))

        LIMIT 1

    ";


    $employeeStatement = mysqli_prepare(
        $conn,
        $employeeQuery
    );


    if ($employeeStatement) {

        mysqli_stmt_bind_param(
            $employeeStatement,
            "s",
            $userName
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

}


// =====================================
// Submit Leave Request
// =====================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["submit_leave"])
) {

    if (!$employee) {

        $message =
            "Employee profile was not found.";

        $messageType =
            "danger";

    } else {

        $leaveType = trim(
            $_POST["leave_type"] ?? ""
        );


        $startDate = trim(
            $_POST["start_date"] ?? ""
        );


        $endDate = trim(
            $_POST["end_date"] ?? ""
        );


        $reason = trim(
            $_POST["reason"] ?? ""
        );


        // Validation

        if (
            $leaveType === ""
            ||
            $startDate === ""
            ||
            $endDate === ""
            ||
            $reason === ""
        ) {

            $message =
                "Please fill all required fields.";

            $messageType =
                "danger";

        } elseif (
            $endDate < $startDate
        ) {

            $message =
                "End date cannot be before start date.";

            $messageType =
                "danger";

        } else {

            // Insert leave request

            $insertQuery = "

                INSERT INTO leave_requests
                (
                    employee_id,
                    leave_type,
                    start_date,
                    end_date,
                    reason,
                    status
                )

                VALUES
                (
                    ?,
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


            if (!$insertStatement) {

                $message =
                    "Database error: "
                    .
                    mysqli_error($conn);

                $messageType =
                    "danger";

            } else {

                $employeeId =
                    (int) $employee["id"];


                mysqli_stmt_bind_param(

                    $insertStatement,

                    "issss",

                    $employeeId,

                    $leaveType,

                    $startDate,

                    $endDate,

                    $reason

                );


                if (
                    mysqli_stmt_execute(
                        $insertStatement
                    )
                ) {

                    // Success message

                    $_SESSION[
                        "leave_message"
                    ] =
                    "Leave request submitted successfully.";


                    $_SESSION[
                        "leave_message_type"
                    ] =
                    "success";


                    // IMPORTANT:
                    // Redirect to the same file.
                    // Do not use my_leave.php.

                    header(
                        "Location: leave_request.php"
                    );

                    exit();

                } else {

                    $message =
                        "Leave request could not be submitted.";

                    $messageType =
                        "danger";

                }

            }

        }

    }

}


// =====================================
// Show Success Message
// =====================================

if (
    isset(
        $_SESSION["leave_message"]
    )
) {

    $message =
        $_SESSION["leave_message"];


    $messageType =
        $_SESSION[
            "leave_message_type"
        ]
        ??
        "success";


    unset(

        $_SESSION[
            "leave_message"
        ],

        $_SESSION[
            "leave_message_type"
        ]

    );

}


// =====================================
// Get Leave History
// =====================================

$leaveResult = null;


if ($employee) {

    $leaveQuery = "

        SELECT *

        FROM leave_requests

        WHERE employee_id = ?

        ORDER BY id DESC

    ";


    $leaveStatement =
        mysqli_prepare(
            $conn,
            $leaveQuery
        );


    if ($leaveStatement) {

        $employeeId =
            (int) $employee["id"];


        mysqli_stmt_bind_param(

            $leaveStatement,

            "i",

            $employeeId

        );


        mysqli_stmt_execute(
            $leaveStatement
        );


        $leaveResult =
            mysqli_stmt_get_result(
                $leaveStatement
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


    <title>
        Leave Request - HRTech
    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <link
        rel="stylesheet"
        href="../css/style.css"
    >

</head>


<body class="bg-light">


<!-- Navbar -->

<nav class="navbar navbar-dark bg-primary shadow-sm">

    <div class="container">


        <a
            href="../uploads/dashboard.php"
            class="navbar-brand fw-bold"
        >

            HRTech

        </a>


        <a
            href="../uploads/dashboard.php"
            class="btn btn-light btn-sm"
        >

            Dashboard

        </a>


    </div>

</nav>


<!-- Main Content -->

<div class="container mt-5">


    <h2>

        My Leave Requests

    </h2>


    <p class="text-muted">

        Apply for leave and check
        your request status.

    </p>


    <!-- Message -->

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

            Employee profile was not found.
            Please contact HR.

        </div>


    <?php } else { ?>


    <!-- Leave Form -->

    <div class="card shadow border-0 mb-4">


        <div class="card-body">


            <h4 class="mb-3">

                Apply for Leave

            </h4>


            <form method="POST">


                <!-- Leave Type -->

                <div class="mb-3">


                    <label class="form-label">

                        Leave Type

                    </label>


                    <select
                        name="leave_type"
                        class="form-select"
                        required
                    >


                        <option value="">

                            Select Leave Type

                        </option>


                        <option value="Sick Leave">

                            Sick Leave

                        </option>


                        <option value="Casual Leave">

                            Casual Leave

                        </option>


                        <option value="Annual Leave">

                            Annual Leave

                        </option>


                        <option value="Emergency Leave">

                            Emergency Leave

                        </option>


                        <option value="Other">

                            Other

                        </option>


                    </select>


                </div>


                <div class="row">


                    <!-- Start Date -->

                    <div class="col-md-6 mb-3">


                        <label class="form-label">

                            Start Date

                        </label>


                        <input
                            type="date"
                            name="start_date"
                            class="form-control"
                            required
                        >


                    </div>


                    <!-- End Date -->

                    <div class="col-md-6 mb-3">


                        <label class="form-label">

                            End Date

                        </label>


                        <input
                            type="date"
                            name="end_date"
                            class="form-control"
                            required
                        >


                    </div>


                </div>


                <!-- Reason -->

                <div class="mb-3">


                    <label class="form-label">

                        Reason

                    </label>


                    <textarea
                        name="reason"
                        class="form-control"
                        rows="4"
                        placeholder="Enter leave reason..."
                        required
                    ></textarea>


                </div>


                <button
                    type="submit"
                    name="submit_leave"
                    value="1"
                    class="btn btn-primary"
                >

                    Submit Leave Request

                </button>


            </form>


        </div>


    </div>


    <!-- Leave History -->

    <div class="card shadow border-0">


        <div class="card-body">


            <h4 class="mb-3">

                My Leave History

            </h4>


            <div class="table-responsive">


                <table class="table table-bordered">


                    <thead class="table-primary">


                        <tr>

                            <th>Leave Type</th>

                            <th>Start Date</th>

                            <th>End Date</th>

                            <th>Reason</th>

                            <th>Status</th>

                        </tr>


                    </thead>


                    <tbody>


                    <?php

                    if (
                        $leaveResult
                        &&
                        mysqli_num_rows(
                            $leaveResult
                        ) > 0
                    ) {

                        while (
                            $leave =
                            mysqli_fetch_assoc(
                                $leaveResult
                            )
                        ) {

                            $status =
                                trim(
                                    $leave[
                                        "status"
                                    ]
                                );


                            if (
                                $status ===
                                "Approved"
                            ) {

                                $badge =
                                    "success";

                            } elseif (
                                $status ===
                                "Rejected"
                            ) {

                                $badge =
                                    "danger";

                            } else {

                                $badge =
                                    "warning";

                            }

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $leave[
                                        "leave_type"
                                    ]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $leave[
                                        "start_date"
                                    ]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $leave[
                                        "end_date"
                                    ]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $leave[
                                        "reason"
                                    ]
                                );

                                ?>

                            </td>


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

                                No leave requests found.

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


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>