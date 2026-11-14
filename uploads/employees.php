<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullName = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $department = trim($_POST["department"] ?? "");
    $position = trim($_POST["position"] ?? "");
    $joiningDate = $_POST["joining_date"] ?? "";

    if ($fullName === "" || $email === "") {

        $message = "Full Name and Email are required.";

    } else {

        $sql = "INSERT INTO employees
                (
                    full_name,
                    email,
                    phone,
                    department,
                    position,
                    joining_date
                )
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "ssssss",
                $fullName,
                $email,
                $phone,
                $department,
                $position,
                $joiningDate
            );

            if (mysqli_stmt_execute($stmt)) {

                $message = "Employee added successfully!";

            } else {

                $message = "Error: " .
                    mysqli_stmt_error($stmt);
            }

            mysqli_stmt_close($stmt);

        } else {

            $message = "Database error: " .
                mysqli_error($conn);
        }
    }
}

$employees = mysqli_query(
    $conn,
    "SELECT *
     FROM employees
     ORDER BY id DESC"
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

    <title>Employee Management - HRTech</title>

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

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand fw-bold"
            href="dashboard.php"
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


<div class="container mt-4">

    <h2>Employee Management</h2>


    <?php if ($message !== "") { ?>

        <div class="alert alert-info">

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php } ?>


    <!-- Add Employee -->

    <div class="card shadow mt-4">

        <div class="card-body">

            <h4>Add New Employee</h4>

            <form method="POST">

                <div class="row">


                    <!-- Full Name -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            required
                        >

                    </div>


                    <!-- Phone -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                        >

                    </div>


                    <!-- Department -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Department
                        </label>

                        <input
                            type="text"
                            name="department"
                            class="form-control"
                            placeholder="Example: IT"
                        >

                    </div>


                    <!-- Position -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Position
                        </label>

                        <input
                            type="text"
                            name="position"
                            class="form-control"
                            placeholder="Example: Software Developer"
                        >

                    </div>


                    <!-- Joining Date -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Joining Date
                        </label>

                        <input
                            type="date"
                            name="joining_date"
                            class="form-control"
                        >

                    </div>

                </div>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Add Employee
                </button>

            </form>

        </div>

    </div>


    <!-- Employee List -->

    <div class="card shadow mt-4">

        <div class="card-body">

            <h4>Employee List</h4>

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-primary">

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Department</th>

                            <th>Position</th>

                            <th>Phone</th>

                            <th>Joining Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    if ($employees &&
                        mysqli_num_rows($employees) > 0) {

                        while (
                            $employee =
                            mysqli_fetch_assoc($employees)
                        ) {

                    ?>

                        <tr>

                            <td>
                                <?php
                                echo (int)$employee["id"];
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

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee["phone"] ?? ""
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee["joining_date"] ?? ""
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
                                colspan="7"
                                class="text-center text-muted"
                            >
                                No employees found.
                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>
</html>