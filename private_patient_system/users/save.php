<?php

require_once "../config/auth.php";
require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| ADMINISTRATOR ONLY
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {

    header("Location: ../dashboard.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;

}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$fullName = isset($_POST['full_name'])
    ? trim($_POST['full_name'])
    : '';

$username = isset($_POST['username'])
    ? trim($_POST['username'])
    : '';

$password = isset($_POST['password'])
    ? $_POST['password']
    : '';

$confirmPassword = isset($_POST['confirm_password'])
    ? $_POST['confirm_password']
    : '';

$role = isset($_POST['role'])
    ? trim($_POST['role'])
    : '';


/*
|--------------------------------------------------------------------------
| VALIDATE REQUIRED FIELDS
|--------------------------------------------------------------------------
*/

if (
    $fullName === '' ||
    $username === '' ||
    $password === '' ||
    $confirmPassword === '' ||
    $role === ''
) {

    die("
        <script>
            alert('Please complete all required fields.');
            window.location.href = 'add.php';
        </script>
    ");

}


/*
|--------------------------------------------------------------------------
| VALIDATE PASSWORD LENGTH
|--------------------------------------------------------------------------
*/

if (strlen($password) < 6) {

    die("
        <script>
            alert('Password must be at least 6 characters.');
            window.location.href = 'add.php';
        </script>
    ");

}


/*
|--------------------------------------------------------------------------
| CONFIRM PASSWORD
|--------------------------------------------------------------------------
*/

if ($password !== $confirmPassword) {

    die("
        <script>
            alert('Password and Confirm Password do not match.');
            window.location.href = 'add.php';
        </script>
    ");

}


/*
|--------------------------------------------------------------------------
| VALIDATE ROLE
|--------------------------------------------------------------------------
*/

if (
    $role !== 'Staff' &&
    $role !== 'Administrator'
) {

    die("
        <script>
            alert('Invalid user role.');
            window.location.href = 'add.php';
        </script>
    ");

}


/*
|--------------------------------------------------------------------------
| CHECK USERNAME
|--------------------------------------------------------------------------
*/

$checkSql = "
    SELECT id
    FROM users
    WHERE username = ?
    LIMIT 1
";

$checkStmt = $conn->prepare($checkSql);


if (!$checkStmt) {

    die("Database error.");

}


$checkStmt->bind_param(
    "s",
    $username
);

$checkStmt->execute();

$checkResult = $checkStmt->get_result();


if ($checkResult->num_rows > 0) {

    $checkStmt->close();

    die("
        <script>
            alert('Username already exists. Please choose another username.');
            window.location.href = 'add.php';
        </script>
    ");

}


$checkStmt->close();


/*
|--------------------------------------------------------------------------
| HASH PASSWORD
|--------------------------------------------------------------------------
*/

$hashedPassword = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/*
|--------------------------------------------------------------------------
| INSERT USER
|--------------------------------------------------------------------------
*/

$insertSql = "
    INSERT INTO users
    (
        username,
        password,
        full_name,
        role,
        is_active
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        1
    )
";

$insertStmt = $conn->prepare($insertSql);


if (!$insertStmt) {

    die("Database error.");

}


$insertStmt->bind_param(
    "ssss",
    $username,
    $hashedPassword,
    $fullName,
    $role
);


if ($insertStmt->execute()) {

    $insertStmt->close();

    header("Location: index.php?success=1");
    exit;

} else {

    $insertStmt->close();

    die("
        <script>
            alert('Unable to create user account.');
            window.location.href = 'add.php';
        </script>
    ");

}

?>