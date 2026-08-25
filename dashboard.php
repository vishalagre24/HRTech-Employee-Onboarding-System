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
// Database Connection
// =====================================

require_once __DIR__ . "/../config/database.php";


// =====================================
// Default Counts
// =====================================

$totalEmployees = 0;

$pendingOnboarding = 0;

$completedOnboarding = 0;

$performanceReviews = 0;

$totalGoals = 0;

$inProgressGoals = 0;

$completedGoals = 0;


// =====================================
// Total Employees Count
// =====================================

$countQuery = "
    SELECT COUNT(*) AS total
    FROM employees
";

$countResult = mysqli_query(
    $conn,
    $countQuery
);

if ($countResult) {

    $countData = mysqli_fetch_assoc(
        $countResult
    );

    $totalEmployees = (int)
        $countData["total"];

}


// =====================================
// Employee Dashboard
// =====================================

if (
    $_SESSION["role"] === "employee"
) {

    $employeeName =
        trim($_SESSION["name"]);


    // =====================================
    // Find Employee Using Name
    // Same logic as my_goals.php
    // =====================================

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


    $employee = null;


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
    // If Employee Found
    // =====================================

    if ($employee) {

        $employeeId =
            (int) $employee["id"];


        // =====================================
        // Pending Onboarding
        // =====================================

        $pendingQuery = "
            SELECT COUNT(*) AS total
            FROM onboarding_tasks
            WHERE employee_id = ?
            AND status = 'Pending'
        ";


        $pendingStatement =
            mysqli_prepare(
                $conn,
                $pendingQuery
            );


        if ($pendingStatement) {

            mysqli_stmt_bind_param(
                $pendingStatement,
                "i",
                $employeeId
            );


            mysqli_stmt_execute(
                $pendingStatement
            );


            $pendingResult =
                mysqli_stmt_get_result(
                    $pendingStatement
                );


            $pendingData =
                mysqli_fetch_assoc(
                    $pendingResult
                );


            $pendingOnboarding =
                (int) $pendingData["total"];

        }


        // =====================================
        // Completed Onboarding
        // =====================================

        $completedQuery = "
            SELECT COUNT(*) AS total
            FROM onboarding_tasks
            WHERE employee_id = ?
            AND status = 'Completed'
        ";


        $completedStatement =
            mysqli_prepare(
                $conn,
                $completedQuery
            );


        if ($completedStatement) {

            mysqli_stmt_bind_param(
                $completedStatement,
                "i",
                $employeeId
            );


            mysqli_stmt_execute(
                $completedStatement
            );


            $completedResult =
                mysqli_stmt_get_result(
                    $completedStatement
                );


            $completedData =
                mysqli_fetch_assoc(
                    $completedResult
                );


            $completedOnboarding =
                (int) $completedData["total"];

        }


        // =====================================
        // Performance Reviews
        // =====================================

        $performanceQuery = "
            SELECT COUNT(*) AS total
            FROM performance_reviews
            WHERE employee_id = ?
        ";


        $performanceStatement =
            mysqli_prepare(
                $conn,
                $performanceQuery
            );


        if ($performanceStatement) {

            mysqli_stmt_bind_param(
                $performanceStatement,
                "i",
                $employeeId
            );


            mysqli_stmt_execute(
                $performanceStatement
            );


            $performanceResult =
                mysqli_stmt_get_result(
                    $performanceStatement
                );


            $performanceData =
                mysqli_fetch_assoc(
                    $performanceResult
                );


            $performanceReviews =
                (int) $performanceData["total"];

        }


        // =====================================
        // Total Goals
        // =====================================

        $totalGoalsQuery = "
            SELECT COUNT(*) AS total
            FROM employee_goals
            WHERE employee_id = ?
        ";


        $totalGoalsStatement =
            mysqli_prepare(
                $conn,
                $totalGoalsQuery
            );


        if ($totalGoalsStatement) {

            mysqli_stmt_bind_param(
                $totalGoalsStatement,
                "i",
                $employeeId
            );


            mysqli_stmt_execute(
                $totalGoalsStatement
            );


            $totalGoalsResult =
                mysqli_stmt_get_result(
                    $totalGoalsStatement
                );


            $totalGoalsData =
                mysqli_fetch_assoc(
                    $totalGoalsResult
                );


            $totalGoals =
                (int) $totalGoalsData["total"];

        }


        // =====================================
        // Goals In Progress
        // =====================================

        $inProgressQuery = "
            SELECT COUNT(*) AS total
            FROM employee_goals
            WHERE employee_id = ?
            AND TRIM(status) = 'In Progress'
        ";


        $inProgressStatement =
            mysqli_prepare(
                $conn,
                $inProgressQuery
            );


        if ($inProgressStatement) {

            mysqli_stmt_bind_param(
                $inProgressStatement,
                "i",
                $employeeId
            );


            mysqli_stmt_execute(
                $inProgressStatement
            );


            $inProgressResult =
                mysqli_stmt_get_result(
                    $inProgressStatement
                );


            $inProgressData =
                mysqli_fetch_assoc(
                    $inProgressResult
                );


            $inProgressGoals =
                (int) $inProgressData["total"];

        }


        // =====================================
        // Completed Goals
        // =====================================

        $completedGoalsQuery = "
            SELECT COUNT(*) AS total
            FROM employee_goals
            WHERE employee_id = ?
            AND TRIM(status) = 'Completed'
        ";


        $completedGoalsStatement =
            mysqli_prepare(
                $conn,
                $completedGoalsQuery
            );


        if ($completedGoalsStatement) {

            mysqli_stmt_bind_param(
                $completedGoalsStatement,
                "i",
                $employeeId
            );


            mysqli_stmt_execute(
                $completedGoalsStatement
            );


            $completedGoalsResult =
                mysqli_stmt_get_result(
                    $completedGoalsStatement
                );


            $completedGoalsData =
                mysqli_fetch_assoc(
                    $completedGoalsResult
                );


            $completedGoals =
                (int) $completedGoalsData["total"];

        }

    }

} else {

    // =====================================
    // Admin / Manager Counts
    // =====================================

    $pendingQuery = "
        SELECT COUNT(*) AS total
        FROM onboarding_tasks
        WHERE status = 'Pending'
    ";


    $pendingResult =
        mysqli_query(
            $conn,
            $pendingQuery
        );


    if ($pendingResult) {

        $pendingData =
            mysqli_fetch_assoc(
                $pendingResult
            );


        $pendingOnboarding =
            (int) $pendingData["total"];

    }


    $completedQuery = "
        SELECT COUNT(*) AS total
        FROM onboarding_tasks
        WHERE status = 'Completed'
    ";


    $completedResult =
        mysqli_query(
            $conn,
            $completedQuery
        );


    if ($completedResult) {

        $completedData =
            mysqli_fetch_assoc(
                $completedResult
            );


        $completedOnboarding =
            (int) $completedData["total"];

    }


    $performanceQuery = "
        SELECT COUNT(*) AS total
        FROM performance_reviews
    ";


    $performanceResult =
        mysqli_query(
            $conn,
            $performanceQuery
        );


    if ($performanceResult) {

        $performanceData =
            mysqli_fetch_assoc(
                $performanceResult
            );


        $performanceReviews =
            (int) $performanceData["total"];

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

    <title>HRTech Dashboard</title>


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

        <!-- Logo -->

        <a
            class="navbar-brand fw-bold"
            href="dashboard.php"
        >
            HRTech
        </a>


        <!-- Profile + Logout -->

        <div class="d-flex align-items-center gap-3">

            <!-- Profile Icon -->

            <div class="d-flex align-items-center">

                <div
                    class="profile-avatar"
                    title="<?php
                    echo htmlspecialchars(
                        $_SESSION["name"]
                    );
                    ?>"
                >

                    <?php

                    echo strtoupper(
                        substr(
                            $_SESSION["name"],
                            0,
                            1
                        )
                    );

                    ?>

                </div>


                <div class="ms-2 text-white d-none d-md-block">

                    <div class="fw-bold">

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["name"]
                        );

                        ?>

                    </div>


                    <small class="text-white-50">

                        <?php

                        echo ucfirst(
                            $_SESSION["role"]
                        );

                        ?>

                    </small>

                </div>

            </div>


            <!-- Logout -->

            <a
                href="logout.php"
                class="btn btn-light btn-sm px-3 py-1 ms-4"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- Main Dashboard -->

<!-- Main Dashboard -->

<div class="container mt-5">

    <h2>

        Welcome,

        <?php

        $nameParts = explode(
            " ",
            trim($_SESSION["name"])
        );

        $firstName = $nameParts[0];

        $lastName = end($nameParts);

        echo htmlspecialchars(
            strtoupper(
                $firstName . " " . $lastName
            )
        );

        ?>

    </h2>


    <p class="text-muted">

        Your Role:

        <strong>

            <?php

            echo htmlspecialchars(
                $_SESSION["role"]
            );

            ?>

        </strong>

    </p>

    <!-- Main Cards -->

    <div class="row mt-4">


        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>Total Employees</h5>

                    <h2>

                        <?php
                        echo $totalEmployees;
                        ?>

                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>Pending Onboarding</h5>

                    <h2>

                        <?php
                        echo $pendingOnboarding;
                        ?>

                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>Completed Onboarding</h5>

                    <h2>

                        <?php
                        echo $completedOnboarding;
                        ?>

                    </h2>

                </div>

            </div>

        </div>


        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>Performance Reviews</h5>

                    <h2>

                        <?php
                        echo $performanceReviews;
                        ?>

                    </h2>

                </div>

            </div>

        </div>


    </div>


    <!-- Employee Goal Summary -->

    <?php if (
        $_SESSION["role"] === "employee"
    ) { ?>


        <h4 class="mt-4">

            My Goals Summary

        </h4>


        <div class="row mt-2">


            <div class="col-md-4 mb-3">

                <div class="card shadow border-0">

                    <div class="card-body">

                        <h5>Total Goals</h5>

                        <h2>

                            <?php
                            echo $totalGoals;
                            ?>

                        </h2>

                    </div>

                </div>

            </div>


            <div class="col-md-4 mb-3">

                <div class="card shadow border-0">

                    <div class="card-body">

                        <h5>Goals In Progress</h5>

                        <h2>

                            <?php
                            echo $inProgressGoals;
                            ?>

                        </h2>

                    </div>

                </div>

            </div>


            <div class="col-md-4 mb-3">

                <div class="card shadow border-0">

                    <div class="card-body">

                        <h5>Completed Goals</h5>

                        <h2>

                            <?php
                            echo $completedGoals;
                            ?>

                        </h2>

                    </div>

                </div>

            </div>


        </div>


    <?php } ?>


    <!-- Quick Actions -->

    <div class="card shadow border-0 mt-4">

        <div class="card-body">


            <h4 class="mb-3">

                Quick Actions

            </h4>


            <!-- Admin -->

            <?php if (
                $_SESSION["role"] === "admin"
            ) { ?>


                <a
                    href="employees.php"
                    class="btn btn-primary me-2 mb-2"
                >

                    Add Employee

                </a>


                <a
                    href="onboarding.php"
                    class="btn btn-success me-2 mb-2"
                >

                    Assign Onboarding Task

                </a>


                <a
                    href="performance.php"
                    class="btn btn-warning me-2 mb-2"
                >

                    Performance Review

                </a>


            <?php } ?>


            <!-- Manager -->

            <?php if (
                $_SESSION["role"] === "manager"
            ) { ?>


                <a
                    href="#"
                    class="btn btn-success me-2 mb-2"
                >

                    Manage Team

                </a>


                <a
                    href="performance.php"
                    class="btn btn-warning me-2 mb-2"
                >

                    Performance Review

                </a>


            <?php } ?>


            <!-- Employee -->

            <?php if (
                $_SESSION["role"] === "employee"
            ) { ?>


                <a
                    href="my_tasks.php"
                    class="btn btn-primary me-2 mb-2"
                >

                    My Onboarding Tasks

                </a>


                <a
                    href="../modules/my_goals.php"
                    class="btn btn-success me-2 mb-2"
                >

                    My Goals

                </a>


                <a
                    href="my_performance.php"
                    class="btn btn-warning me-2 mb-2"
                >

                    My Performance

                </a>


            <?php } ?>


        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>