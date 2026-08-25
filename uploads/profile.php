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
// Logged-in User
// =====================================

$userId = (int) $_SESSION["user_id"];

$userName = $_SESSION["name"];

$userRole = $_SESSION["role"];


// =====================================
// Get User Details
// =====================================

$userQuery = "
    SELECT
        id,
        name,
        email,
        role
    FROM users
    WHERE id = ?
    LIMIT 1
";


$userStatement = mysqli_prepare(
    $conn,
    $userQuery
);


$user = null;


if ($userStatement) {

    mysqli_stmt_bind_param(
        $userStatement,
        "i",
        $userId
    );


    mysqli_stmt_execute(
        $userStatement
    );


    $userResult = mysqli_stmt_get_result(
        $userStatement
    );


    $user = mysqli_fetch_assoc(
        $userResult
    );

}


// =====================================
// Get Employee Details
// =====================================

$employee = null;


if ($userRole === "employee") {

    $employeeQuery = "
        SELECT
            id,
            full_name,
            department,
            designation,
            joining_date
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


        $employeeResult = mysqli_stmt_get_result(
            $employeeStatement
        );


        $employee = mysqli_fetch_assoc(
            $employeeResult
        );

    }

}


// =====================================
// Profile Name
// =====================================

$nameParts = explode(
    " ",
    trim($userName)
);


$firstName = $nameParts[0];

$lastName = end($nameParts);


$shortName = strtoupper(
    $firstName . " " . $lastName
);


$firstLetter = strtoupper(
    substr(
        $userName,
        0,
        1
    )
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
        My Profile - HRTech
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Your CSS -->

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
            href="dashboard.php"
            class="navbar-brand fw-bold"
        >

            HRTech

        </a>


        <a
            href="dashboard.php"
            class="btn btn-light btn-sm px-3"
        >

            Dashboard

        </a>


    </div>

</nav>


<!-- Profile -->

<div class="container mt-5">


    <div class="row justify-content-center">


        <div class="col-lg-8">


            <div class="card shadow border-0">


                <!-- Profile Header -->

                <div class="card-body text-center p-5">


                    <div class="profile-avatar-large">

                        <?php

                        echo htmlspecialchars(
                            $firstLetter
                        );

                        ?>

                    </div>


                    <h2 class="mt-3">

                        <?php

                        echo htmlspecialchars(
                            $shortName
                        );

                        ?>

                    </h2>


                    <p class="text-muted mb-0">

                        <?php

                        echo htmlspecialchars(
                            ucfirst($userRole)
                        );

                        ?>

                    </p>


                </div>


                <hr class="m-0">


                <!-- Profile Details -->

                <div class="card-body p-4">


                    <h4 class="mb-4">

                        Profile Information

                    </h4>


                    <div class="row">


                        <!-- Full Name -->

                        <div class="col-md-6 mb-4">


                            <small class="text-muted">

                                Full Name

                            </small>


                            <h6>

                                <?php

                                echo htmlspecialchars(
                                    $user["name"]
                                    ?? $userName
                                );

                                ?>

                            </h6>


                        </div>


                        <!-- Email -->

                        <div class="col-md-6 mb-4">


                            <small class="text-muted">

                                Email Address

                            </small>


                            <h6>

                                <?php

                                echo htmlspecialchars(
                                    $user["email"]
                                    ?? "Not available"
                                );

                                ?>

                            </h6>


                        </div>


                        <!-- Role -->

                        <div class="col-md-6 mb-4">


                            <small class="text-muted">

                                Role

                            </small>


                            <h6>

                                <?php

                                echo htmlspecialchars(
                                    ucfirst($userRole)
                                );

                                ?>

                            </h6>


                        </div>


                        <!-- Employee ID -->

                        <div class="col-md-6 mb-4">


                            <small class="text-muted">

                                Employee ID

                            </small>


                            <h6>

                                <?php

                                if ($employee) {

                                    echo
                                    "EMP-"
                                    .
                                    str_pad(
                                        $employee["id"],
                                        4,
                                        "0",
                                        STR_PAD_LEFT
                                    );

                                } else {

                                    echo "Not assigned";

                                }

                                ?>

                            </h6>


                        </div>


                        <?php if ($employee) { ?>


                            <!-- Department -->

                            <div class="col-md-6 mb-4">


                                <small class="text-muted">

                                    Department

                                </small>


                                <h6>

                                    <?php

                                    echo htmlspecialchars(
                                        $employee[
                                            "department"
                                        ]
                                        ??
                                        "Not available"
                                    );

                                    ?>

                                </h6>


                            </div>


                            <!-- Designation -->

                            <div class="col-md-6 mb-4">


                                <small class="text-muted">

                                    Designation

                                </small>


                                <h6>

                                    <?php

                                    echo htmlspecialchars(
                                        $employee[
                                            "designation"
                                        ]
                                        ??
                                        "Not available"
                                    );

                                    ?>

                                </h6>


                            </div>


                            <!-- Joining Date -->

                            <div class="col-md-6 mb-3">


                                <small class="text-muted">

                                    Joining Date

                                </small>


                                <h6>

                                    <?php

                                    echo htmlspecialchars(
                                        $employee[
                                            "joining_date"
                                        ]
                                        ??
                                        "Not available"
                                    );

                                    ?>

                                </h6>


                            </div>


                        <?php } ?>


                    </div>


                </div>


            </div>


        </div>


    </div>


</div>


</body>

</html>