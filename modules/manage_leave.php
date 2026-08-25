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
// Only Admin / Manager Can Access
// =====================================

$role = $_SESSION["role"] ?? "";

if (
    $role !== "admin"
    &&
    $role !== "manager"
) {

    header("Location: ../uploads/dashboard.php");

    exit();

}


// =====================================
// Database Connection
// =====================================

require_once __DIR__ . "/../config/database.php";


// =====================================
// Default Message
// =====================================

$message = "";

$messageType = "";


// =====================================
// Approve / Reject Leave
// =====================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    &&
    isset($_POST["update_leave"])
) {

    $leaveId = isset(
        $_POST["leave_id"]
    )
    ?
    (int) $_POST["leave_id"]
    :
    0;


    $status = trim(
        $_POST["status"] ?? ""
    );


    // Allowed Status

    $allowedStatus = [

        "Approved",

        "Rejected"

    ];


    if ($leaveId <= 0) {

        $message =
            "Invalid leave request.";

        $messageType =
            "danger";

    } elseif (

        !in_array(
            $status,
            $allowedStatus,
            true
        )

    ) {

        $message =
            "Invalid leave status.";

        $messageType =
            "danger";

    } else {

        $updateQuery = "

            UPDATE leave_requests

            SET status = ?

            WHERE id = ?

        ";


        $updateStatement = mysqli_prepare(

            $conn,

            $updateQuery

        );


        if (!$updateStatement) {

            $message =
                "Database error: "
                .
                mysqli_error($conn);

            $messageType =
                "danger";

        } else {

            mysqli_stmt_bind_param(

                $updateStatement,

                "si",

                $status,

                $leaveId

            );


            if (

                mysqli_stmt_execute(
                    $updateStatement
                )

            ) {

                $_SESSION[
                    "manage_leave_message"
                ] =

                "Leave request "
                .
                strtolower($status)
                .
                " successfully.";


                $_SESSION[
                    "manage_leave_type"
                ] =

                "success";


                // Reload same page

                header(

                    "Location: manage_leave.php"

                );


                exit();

            } else {

                $message =

                "Leave status could not be updated.";

                $messageType =

                "danger";

            }

        }

    }

}


// =====================================
// Show Session Message
// =====================================

if (

    isset(
        $_SESSION[
            "manage_leave_message"
        ]
    )

) {

    $message =

    $_SESSION[
        "manage_leave_message"
    ];


    $messageType =

    $_SESSION[
        "manage_leave_type"
    ]

    ??

    "success";


    unset(

        $_SESSION[
            "manage_leave_message"
        ],

        $_SESSION[
            "manage_leave_type"
        ]

    );

}


// =====================================
// Get All Leave Requests
// =====================================

$leaveQuery = "

    SELECT

        leave_requests.id,

        leave_requests.leave_type,

        leave_requests.start_date,

        leave_requests.end_date,

        leave_requests.reason,

        leave_requests.status,

        employees.full_name

    FROM leave_requests

    INNER JOIN employees

    ON

    leave_requests.employee_id

    =

    employees.id

    ORDER BY

    leave_requests.id DESC

";


$leaveResult = mysqli_query(

    $conn,

    $leaveQuery

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

        Manage Leave Requests - HRTech

    </title>


    <!-- Bootstrap -->

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

        Manage Leave Requests

    </h2>


    <p class="text-muted">

        Review and approve or reject
        employee leave requests.

    </p>


    <!-- Message -->

    <?php

    if ($message !== "") {

    ?>

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

    <?php

    }

    ?>


    <!-- Leave Table -->

    <div class="card shadow border-0">


        <div class="card-body">


            <div class="table-responsive">


                <table class="table table-bordered table-hover">


                    <thead class="table-primary">


                        <tr>

                            <th>Employee</th>

                            <th>Leave Type</th>

                            <th>Start Date</th>

                            <th>End Date</th>

                            <th>Reason</th>

                            <th>Status</th>

                            <th>Action</th>

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

                            $status = trim(

                                $leave["status"]

                            );


                            // Badge Color

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


                            <!-- Employee -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "full_name"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- Leave Type -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "leave_type"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- Start Date -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "start_date"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- End Date -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "end_date"
                                    ]

                                );

                                ?>

                            </td>


                            <!-- Reason -->

                            <td>

                                <?php

                                echo htmlspecialchars(

                                    $leave[
                                        "reason"
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


                            <!-- Action -->

                            <td>


                            <?php

                            if (

                                $status ===
                                "Pending"

                            ) {

                            ?>


                                <form
                                    method="POST"
                                    class="d-flex gap-2"
                                >


                                    <input
                                        type="hidden"
                                        name="leave_id"
                                        value="<?php

                                        echo (int)

                                        $leave["id"];

                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        name="status"
                                        value="Approved"
                                        class="
                                        btn
                                        btn-success
                                        btn-sm
                                        "
                                    >

                                        Approve

                                    </button>


                                    <button
                                        type="submit"
                                        name="status"
                                        value="Rejected"
                                        class="
                                        btn
                                        btn-danger
                                        btn-sm
                                        "
                                    >

                                        Reject

                                    </button>


                                    <input
                                        type="hidden"
                                        name="update_leave"
                                        value="1"
                                    >


                                </form>


                            <?php

                            } else {

                            ?>


                                <span class="text-muted">

                                    Decision Completed

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
                                colspan="7"
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


</div>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>