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
// Admin / Manager Access
// =====================================

$userRole = strtolower(
    trim(
        $_SESSION["role"] ?? ""
    )
);


if (
    $userRole !== "admin" &&
    $userRole !== "manager"
) {

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
// Save Performance Review
// =====================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId =
        isset($_POST["employee_id"])
        ? (int) $_POST["employee_id"]
        : 0;


    $rating =
        isset($_POST["rating"])
        ? (int) $_POST["rating"]
        : 0;


    $feedback =
        isset($_POST["feedback"])
        ? trim($_POST["feedback"])
        : "";


    $reviewDate =
        isset($_POST["review_date"])
        ? trim($_POST["review_date"])
        : date("Y-m-d");


    // Validation

    if ($employeeId <= 0) {

        $error =
            "Please select an employee.";

    } elseif (
        $rating < 1 ||
        $rating > 5
    ) {

        $error =
            "Rating must be between 1 and 5.";

    } elseif ($feedback === "") {

        $error =
            "Please enter feedback.";

    } else {

        $insertQuery = "
            INSERT INTO performance_reviews
            (
                employee_id,
                rating,
                feedback,
                review_date
            )
            VALUES
            (
                ?,
                ?,
                ?,
                ?
            )
        ";


        $insertStatement = mysqli_prepare(
            $conn,
            $insertQuery
        );


        if ($insertStatement) {

            mysqli_stmt_bind_param(
                $insertStatement,
                "iiss",
                $employeeId,
                $rating,
                $feedback,
                $reviewDate
            );


            if (
                mysqli_stmt_execute(
                    $insertStatement
                )
            ) {

                $message =
                    "Performance review saved successfully.";

            } else {

                $error =
                    "Performance review could not be saved.";

            }

        } else {

            $error =
                "Database query preparation failed.";

        }

    }

}


// =====================================
// Get Employees
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
// Get Performance Reviews
// =====================================

$reviewListQuery = "
    SELECT
        performance_reviews.id,
        performance_reviews.rating,
        performance_reviews.feedback,
        performance_reviews.review_date,
        employees.full_name,
        employees.employee_id

    FROM performance_reviews

    INNER JOIN employees

    ON performance_reviews.employee_id
    =
    employees.id

    ORDER BY
    performance_reviews.review_date DESC,
    performance_reviews.id DESC
";


$reviewListResult = mysqli_query(
    $conn,
    $reviewListQuery
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

        Performance Review

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


<!-- Main Content -->

<div class="container mt-5">


    <h2>

        Performance Review

    </h2>


    <p class="text-muted">

        Add and manage employee performance reviews.

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


    <!-- Add Review Form -->

    <div class="card shadow border-0 mb-5">

        <div class="card-body">


            <h4 class="mb-4">

                Add Performance Review

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


                <!-- Rating -->

                <div class="mb-3">

                    <label class="form-label">

                        Rating

                    </label>


                    <select
                        name="rating"
                        class="form-select"
                        required
                    >

                        <option value="">

                            Select Rating

                        </option>

                        <option value="1">

                            1 / 5

                        </option>

                        <option value="2">

                            2 / 5

                        </option>

                        <option value="3">

                            3 / 5

                        </option>

                        <option value="4">

                            4 / 5

                        </option>

                        <option value="5">

                            5 / 5

                        </option>

                    </select>

                </div>


                <!-- Feedback -->

                <div class="mb-3">

                    <label class="form-label">

                        Feedback

                    </label>


                    <textarea
                        name="feedback"
                        class="form-control"
                        rows="4"
                        placeholder="Enter employee performance feedback"
                        required
                    ></textarea>

                </div>


                <!-- Review Date -->

                <div class="mb-3">

                    <label class="form-label">

                        Review Date

                    </label>


                    <input
                        type="date"
                        name="review_date"
                        class="form-control"
                        value="<?php

                        echo date(
                            "Y-m-d"
                        );

                        ?>"
                        required
                    >

                </div>


                <!-- Submit -->

                <button
                    type="submit"
                    class="btn btn-warning"
                >

                    Save Performance Review

                </button>


            </form>


        </div>

    </div>


    <!-- Review List -->

    <div class="card shadow border-0">

        <div class="card-body">


            <h4 class="mb-4">

                Performance Review History

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
                        $reviewListResult &&
                        mysqli_num_rows(
                            $reviewListResult
                        ) > 0
                    ) {

                        while (
                            $review =
                            mysqli_fetch_assoc(
                                $reviewListResult
                            )
                        ) {

                    ?>


                        <tr>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $review["full_name"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $review["employee_id"]
                                );

                                ?>

                            </td>


                            <td>

                                <span
                                    class="
                                    badge
                                    bg-success
                                    "
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $review["rating"]
                                    );

                                    ?>

                                    / 5

                                </span>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $review["feedback"]
                                );

                                ?>

                            </td>


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
                                colspan="5"
                                class="text-center"
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


</div>


</body>

</html>