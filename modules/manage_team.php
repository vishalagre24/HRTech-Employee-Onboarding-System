<?php

session_start();


// Login check
if (!isset($_SESSION["user_id"])) {
    header("Location: ../uploads/login.php");
    exit();
}


// Only Manager can access
$role = $_SESSION["role"] ?? "";

if ($role !== "manager") {
    header("Location: ../uploads/dashboard.php");
    exit();
}


// Database
require_once __DIR__ . "/../config/database.php";


// Search
$search = trim($_GET["search"] ?? "");


// Employee query
if ($search !== "") {

    $query = "
        SELECT
            id,
            full_name,
            email,
            department,
            position
        FROM employees
        WHERE full_name LIKE ?
        ORDER BY full_name ASC
    ";

    $statement = mysqli_prepare($conn, $query);

    $searchValue = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $statement,
        "s",
        $searchValue
    );

    mysqli_stmt_execute($statement);

    $result = mysqli_stmt_get_result($statement);

} else {

    $query = "
        SELECT
            id,
            full_name,
            email,
            department,
            position
        FROM employees
        ORDER BY full_name ASC
    ";

    $result = mysqli_query($conn, $query);
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

    <title>Manage Team - HRTech</title>

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
            Back to Dashboard
        </a>

    </div>

</nav>


<!-- Main -->

<div class="container mt-5">


    <div class="card shadow border-0">

        <div class="card-body">


            <h3 class="mb-4">
                Manage Team
            </h3>


            <!-- Search -->

            <form
                method="GET"
                class="row g-2 mb-4"
            >

                <div class="col-md-10">

                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Search employee by name..."
                        value="<?php echo htmlspecialchars($search); ?>"
                    >

                </div>


                <div class="col-md-2">

                    <button
                        type="submit"
                        class="btn btn-primary w-100"
                    >
                        Search
                    </button>

                </div>

            </form>


            <!-- Employee List -->

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-primary">

                        <tr>

                            <th>ID</th>

                            <th>Employee Name</th>

                            <th>Email</th>

                            <th>Department</th>

                            <th>Position</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php

                    if ($result && mysqli_num_rows($result) > 0) {

                        while ($employee = mysqli_fetch_assoc($result)) {

                    ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee["id"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee["full_name"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee["email"]
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee["department"] ?? ""
                                );
                                ?>
                            </td>


                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee["position"] ?? ""
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
                                class="text-center text-muted"
                            >
                                No employees found.
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