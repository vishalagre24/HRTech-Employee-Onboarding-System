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


// Find employee profile

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

    <title>My Performance</title>


    <!-- Bootstrap CSS -->

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

        My Performance

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


    <?php if (!$employee) { ?>


        <div class="alert alert-warning">

            Your employee profile was not found.

            Please contact HR.

        </div>


    <?php } else { ?>


        <?php

        // Get employee performance reviews

        $reviewQuery = "
            SELECT
                rating,
                feedback,
                review_date
            FROM performance_reviews
            WHERE employee_id = ?
            ORDER BY review_date DESC
        ";


        $reviewStatement = mysqli_prepare(
            $conn,
            $reviewQuery
        );


        mysqli_stmt_bind_param(
            $reviewStatement,
            "i",
            $employee["id"]
        );


        mysqli_stmt_execute(
            $reviewStatement
        );


        $reviewResult = mysqli_stmt_get_result(
            $reviewStatement
        );

        ?>


        <div class="card shadow border-0">


            <div class="card-body">


                <h4 class="mb-4">

                    Performance Review History

                </h4>


                <div class="table-responsive">


                    <table class="table table-bordered">


                        <thead class="table-primary">


                            <tr>

                                <th>

                                    Rating

                                </th>


                                <th>

                                    Feedback

                                </th>


                                <th>

                                    Review Date

                                </th>

                            </tr>


                        </thead>


                        <tbody>


                        <?php

                        if (
                            mysqli_num_rows(
                                $reviewResult
                            ) > 0
                        ) {

                            while (
                                $review =
                                mysqli_fetch_assoc(
                                    $reviewResult
                                )
                            ) {

                        ?>


                            <tr>


                                <!-- Rating -->


                                <td>


                                    <span
                                        class="
                                        badge
                                        bg-success
                                        fs-6
                                        "
                                    >


                                        <?php

                                        echo
                                        htmlspecialchars(
                                            $review[
                                                "rating"
                                            ]
                                        );

                                        ?>


                                        / 5


                                    </span>


                                </td>


                                <!-- Feedback -->


                                <td>


                                    <?php

                                    echo htmlspecialchars(
                                        $review[
                                            "feedback"
                                        ]
                                    );

                                    ?>


                                </td>


                                <!-- Review Date -->


                                <td>


                                    <?php

                                    echo htmlspecialchars(
                                        $review[
                                            "review_date"
                                        ]
                                    );

                                    ?>


                                </td>


                            </tr>


                        <?php

                            }

                        } else {

                        ?>


                            <tr>


                                <td
                                    colspan="3"
                                    class="
                                    text-center
                                    text-muted
                                    "
                                >


                                    No performance reviews found.


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


</body>

</html>