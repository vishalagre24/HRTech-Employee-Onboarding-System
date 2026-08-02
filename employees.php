<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

require_once __DIR__ . "/../config/database.php";

$message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $employeeId = trim($_POST["employee_id"]);
    $fullName = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $department = trim($_POST["department"]);
    $designation = trim($_POST["designation"]);
    $joiningDate = $_POST["joining_date"];

    $sql = "INSERT INTO employees
            (
                employee_id,
                full_name,
                email,
                phone,
                department,
                designation,
                joining_date
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "sssssss",
        $employeeId,
        $fullName,
        $email,
        $phone,
        $department,
        $designation,
        $joiningDate
    );

    if (mysqli_stmt_execute($stmt)) {
        $message = "Employee added successfully!";
    } else {
        $message = "Error: " . mysqli_error($conn);
    }
}

$employees = mysqli_query(
    $conn,
    "SELECT * FROM employees ORDER BY id DESC"
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
            class="navbar-brand"
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

    <?php if ($message != "") { ?>

        <div class="alert alert-success">

            <?php echo $message; ?>

        </div>

    <?php } ?>


    <div class="card shadow mt-4">

        <div class="card-body">

            <h4>Add New Employee</h4>

            <form method="POST">

                <div class="row">

                    <div class="col-md-6 mb-3">

                        <label>
                            Employee ID
                        </label>

                        <input
                            type="text"
                            name="employee_id"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label>
                            Full Name
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="col-md-6 mb-3">

                        <label>
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-4 mb-3">

                        <label>
                            Department
                        </label>

                        <input
                            type="text"
                            name="department"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-4 mb-3">

                        <label>
                            Designation
                        </label>

                        <input
                            type="text"
                            name="designation"
                            class="form-control"
                        >

                    </div>


                    <div class="col-md-4 mb-3">

                        <label>
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


    <div class="card shadow mt-4">

        <div class="card-body">

            <h4>Employee List</h4>

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Department</th>

                            <th>Designation</th>

                            <th>Status</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php while (
                        $employee =
                        mysqli_fetch_assoc($employees)
                    ) { ?>

                        <tr>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "employee_id"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "full_name"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "email"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "department"
                                    ]
                                );
                                ?>
                            </td>

                            <td>
                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "designation"
                                    ]
                                );
                                ?>
                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $employee[
                                        "status"
                                    ]
                                );
                                ?>

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