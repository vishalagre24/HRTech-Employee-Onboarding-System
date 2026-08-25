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
// Session Data
// =====================================

$role = $_SESSION["role"] ?? "";

$userName = trim(
    $_SESSION["name"] ?? ""
);


// =====================================
// Helper Function
// =====================================

function getCount(
    $conn,
    $query,
    $types = "",
    $params = []
) {

    $statement = mysqli_prepare(
        $conn,
        $query
    );


    if (!$statement) {

        return 0;

    }


    if (!empty($params)) {

        mysqli_stmt_bind_param(
            $statement,
            $types,
            ...$params
        );

    }


    mysqli_stmt_execute(
        $statement
    );


    $result = mysqli_stmt_get_result(
        $statement
    );


    $data = mysqli_fetch_assoc(
        $result
    );


    return (int) (
        $data["total"] ?? 0
    );

}


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
// Total Employees
// =====================================

$totalEmployees = getCount(

    $conn,

    "
    SELECT COUNT(*) AS total
    FROM employees
    "

);


// =====================================
// Employee Dashboard
// =====================================

if ($role === "employee") {

    $employeeQuery = "

        SELECT id

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


    $employee = null;


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


    if ($employee) {

        $employeeId = (int)

            $employee["id"];


        // Pending Onboarding

        $pendingOnboarding = getCount(

            $conn,

            "
            SELECT COUNT(*) AS total
            FROM onboarding_tasks
            WHERE employee_id = ?
            AND status = 'Pending'
            ",

            "i",

            [$employeeId]

        );


        // Completed Onboarding

        $completedOnboarding = getCount(

            $conn,

            "
            SELECT COUNT(*) AS total
            FROM onboarding_tasks
            WHERE employee_id = ?
            AND status = 'Completed'
            ",

            "i",

            [$employeeId]

        );


        // Performance Reviews

        $performanceReviews = getCount(

            $conn,

            "
            SELECT COUNT(*) AS total
            FROM performance_reviews
            WHERE employee_id = ?
            ",

            "i",

            [$employeeId]

        );


        // Total Goals

        $totalGoals = getCount(

            $conn,

            "
            SELECT COUNT(*) AS total
            FROM employee_goals
            WHERE employee_id = ?
            ",

            "i",

            [$employeeId]

        );


        // Goals In Progress

        $inProgressGoals = getCount(

            $conn,

            "
            SELECT COUNT(*) AS total
            FROM employee_goals
            WHERE employee_id = ?
            AND TRIM(status) = 'In Progress'
            ",

            "i",

            [$employeeId]

        );


        // Completed Goals

        $completedGoals = getCount(

            $conn,

            "
            SELECT COUNT(*) AS total
            FROM employee_goals
            WHERE employee_id = ?
            AND TRIM(status) = 'Completed'
            ",

            "i",

            [$employeeId]

        );

    }

}


// =====================================
// Admin / Manager Dashboard
// =====================================

else {

    $pendingOnboarding = getCount(

        $conn,

        "
        SELECT COUNT(*) AS total
        FROM onboarding_tasks
        WHERE status = 'Pending'
        "

    );


    $completedOnboarding = getCount(

        $conn,

        "
        SELECT COUNT(*) AS total
        FROM onboarding_tasks
        WHERE status = 'Completed'
        "

    );


    $performanceReviews = getCount(

        $conn,

        "
        SELECT COUNT(*) AS total
        FROM performance_reviews
        "

    );

}


// =====================================
// Display Name
// =====================================

$nameParts = preg_split(

    "/\s+/",

    $userName

);


$firstName =

    $nameParts[0] ?? "";


$lastName =

    count($nameParts) > 1

    ?

    end($nameParts)

    :

    "";


$displayName = strtoupper(

    trim(

        $firstName

        . " "

        . $lastName

    )

);


// =====================================
// Display Role
// =====================================

$displayRole = ucfirst(

    strtolower($role)

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

        HRTech Dashboard

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


<!-- =====================================
Navbar
===================================== -->

<nav class="navbar navbar-dark bg-primary shadow-sm">

    <div class="container">


        <!-- Logo -->

        <a
            href="dashboard.php"
            class="navbar-brand fw-bold"
        >

            HRTech

        </a>


        <!-- Profile and Logout -->

        <div class="d-flex align-items-center gap-3">


            <div class="d-flex align-items-center">


                <!-- Profile Avatar -->

                <div
                    class="profile-avatar"
                    title="<?php
                    echo htmlspecialchars(
                        $userName
                    );
                    ?>"
                >

                    <?php

                    echo strtoupper(

                        substr(

                            $userName,

                            0,

                            1

                        )

                    );

                    ?>

                </div>


                <!-- Name and Role -->

                <div
                    class="
                    ms-2
                    text-white
                    d-none
                    d-md-block
                    "
                >


                    <div class="fw-bold">

                        <?php

                        echo htmlspecialchars(

                            $userName

                        );

                        ?>

                    </div>


                    <small class="text-white-50">

                        <?php

                        echo htmlspecialchars(

                            $displayRole

                        );

                        ?>

                    </small>


                </div>


            </div>


            <!-- Logout -->

            <a
                href="logout.php"
                class="
                btn
                btn-light
                btn-sm
                px-3
                py-1
                ms-4
                "
            >

                Logout

            </a>


        </div>


    </div>

</nav>


<!-- =====================================
Dashboard
===================================== -->

<div class="container mt-5">


    <!-- Welcome -->

    <h2>

        Welcome,

        <?php

        echo htmlspecialchars(

            $displayName

        );

        ?>

    </h2>


    <p class="text-muted">

        Your Role:

        <strong>

            <?php

            echo htmlspecialchars(

                $displayRole

            );

            ?>

        </strong>

    </p>


    <!-- =====================================
Dashboard Cards
===================================== -->

    <div class="row mt-4">


        <!-- Total Employees -->

        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>

                        Total Employees

                    </h5>


                    <h2>

                        <?php

                        echo $totalEmployees;

                        ?>

                    </h2>

                </div>

            </div>

        </div>


        <!-- Pending Onboarding -->

        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>

                        Pending Onboarding

                    </h5>


                    <h2>

                        <?php

                        echo $pendingOnboarding;

                        ?>

                    </h2>

                </div>

            </div>

        </div>


        <!-- Completed Onboarding -->

        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>

                        Completed Onboarding

                    </h5>


                    <h2>

                        <?php

                        echo $completedOnboarding;

                        ?>

                    </h2>

                </div>

            </div>

        </div>


        <!-- Performance Reviews -->

        <div class="col-md-3 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>

                        Performance Reviews

                    </h5>


                    <h2>

                        <?php

                        echo $performanceReviews;

                        ?>

                    </h2>

                </div>

            </div>

        </div>


    </div>


<!-- =====================================
Employee Goals
===================================== -->

<?php

if ($role === "employee") {

?>


    <h4 class="mt-4">

        My Goals Summary

    </h4>


    <div class="row mt-2">


        <div class="col-md-4 mb-3">

            <div class="card shadow border-0">

                <div class="card-body">

                    <h5>

                        Total Goals

                    </h5>


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

                    <h5>

                        Goals In Progress

                    </h5>


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

                    <h5>

                        Completed Goals

                    </h5>


                    <h2>

                        <?php

                        echo $completedGoals;

                        ?>

                    </h2>

                </div>

            </div>

        </div>


    </div>


<?php

}

?>


<!-- =====================================
Quick Actions
===================================== -->

<div class="card shadow border-0 mt-4">

    <div class="card-body">


        <h4 class="mb-3">

            Quick Actions

        </h4>


        <!-- =====================================
        ADMIN BUTTONS
        ===================================== -->

        <?php

        if ($role === "admin") {

        ?>


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


            <a
                href="../modules/manage_leave.php"
                class="btn btn-danger me-2 mb-2"
            >

                Manage Leave Requests

            </a>


        <?php

        }

        ?>


        <!-- =====================================
       <!-- =====================================
MANAGER BUTTONS
===================================== -->

<?php

if ($role === "manager") {

?>

    <!-- Manage Team -->

   <a
        href="../modules/manage_team.php"
        class="btn btn-success me-2 mb-2"
    >
        Manage Team
    </a>
      

    <!-- Performance Review -->

    <a
        href="performance.php"
        class="btn btn-warning me-2 mb-2"
    >
        Performance Review
    </a>


    <!-- Manage Leave Requests -->

    <a
        href="../modules/manage_leave.php"
        class="btn btn-danger me-2 mb-2"
    >
        Manage Leave Requests
    </a>


<?php

}

?>
              <!--  Performance Review

            </a>


            <a
                href="../modules/manage_leave.php"
                class="btn btn-danger me-2 mb-2"
            >

                Manage Leave Requests

            </a>


        <?php

        

        ?>


        <!-- =====================================
        EMPLOYEE BUTTONS
        ===================================== -->

        <?php

        if ($role === "employee") {

        ?>


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


            <a
                href="../modules/leave_request.php"
                class="btn btn-danger me-2 mb-2"
            >

                Apply Leave

            </a>


        <?php

        }

        ?>


    </div>

</div>


</div>


<!-- Bootstrap JavaScript -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>