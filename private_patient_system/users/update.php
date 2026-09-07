<?php

require_once "../config/auth.php";
require_once "../config/database.php";


/* =========================================================
   ADMINISTRATOR ONLY
========================================================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {

    header("Location: ../dashboard.php");
    exit;

}


/* =========================================================
   REQUEST CHECK
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit;

}


/* =========================================================
   GET FORM DATA
========================================================= */

$id = isset($_POST["id"])
    ? intval($_POST["id"])
    : 0;

$fullName = isset($_POST["full_name"])
    ? trim($_POST["full_name"])
    : "";

$username = isset($_POST["username"])
    ? trim($_POST["username"])
    : "";

$role = isset($_POST["role"])
    ? trim($_POST["role"])
    : "";

$isActive = isset($_POST["is_active"])
    ? intval($_POST["is_active"])
    : 1;

$password = isset($_POST["password"])
    ? $_POST["password"]
    : "";

$confirmPassword = isset($_POST["confirm_password"])
    ? $_POST["confirm_password"]
    : "";


/* =========================================================
   BASIC VALIDATION
========================================================= */

if (
    $id <= 0 ||
    $fullName === "" ||
    $username === "" ||
    $role === ""
) {

    die("
        <script>
            alert('Please complete all required fields.');
            window.location.href = 'edit.php?id=" . $id . "';
        </script>
    ");

}


/* =========================================================
   VALIDATE ROLE
========================================================= */

if (
    $role !== "Staff" &&
    $role !== "Administrator"
) {

    die("
        <script>
            alert('Invalid user role.');
            window.location.href = 'edit.php?id=" . $id . "';
        </script>
    ");

}


/* =========================================================
   VALIDATE STATUS
========================================================= */

if (
    $isActive !== 0 &&
    $isActive !== 1
) {

    $isActive = 1;

}


/* =========================================================
   CHECK IF USER EXISTS
========================================================= */

$checkUserSql = "
    SELECT id
    FROM users
    WHERE id = ?
    LIMIT 1
";

$checkUserStmt = $conn->prepare($checkUserSql);

if (!$checkUserStmt) {

    die("Database error.");

}

$checkUserStmt->bind_param(
    "i",
    $id
);

$checkUserStmt->execute();

$checkUserResult =
    $checkUserStmt->get_result();


if ($checkUserResult->num_rows === 0) {

    $checkUserStmt->close();

    die("
        <script>
            alert('User account not found.');
            window.location.href = 'index.php';
        </script>
    ");

}

$checkUserStmt->close();


/* =========================================================
   CHECK USERNAME
   Make sure another user is not using it
========================================================= */

$checkUsernameSql = "
    SELECT id
    FROM users
    WHERE username = ?
    AND id != ?
    LIMIT 1
";

$checkUsernameStmt = $conn->prepare(
    $checkUsernameSql
);

if (!$checkUsernameStmt) {

    die("Database error.");

}

$checkUsernameStmt->bind_param(
    "si",
    $username,
    $id
);

$checkUsernameStmt->execute();

$checkUsernameResult =
    $checkUsernameStmt->get_result();


if ($checkUsernameResult->num_rows > 0) {

    $checkUsernameStmt->close();

    die("
        <script>
            alert('Username already exists. Please choose another username.');
            window.location.href = 'edit.php?id=" . $id . "';
        </script>
    ");

}

$checkUsernameStmt->close();


/* =========================================================
   PASSWORD VALIDATION
   Only validate password if user entered one
========================================================= */

if ($password !== "") {

    /* -----------------------------------------------------
       PASSWORD LENGTH
    ----------------------------------------------------- */

    if (strlen($password) < 6) {

        die("
            <script>
                alert('Password must be at least 6 characters.');
                window.location.href = 'edit.php?id=" . $id . "';
            </script>
        ");

    }


    /* -----------------------------------------------------
       PASSWORD CONFIRMATION
    ----------------------------------------------------- */

    if ($password !== $confirmPassword) {

        die("
            <script>
                alert('New Password and Confirm New Password do not match.');
                window.location.href = 'edit.php?id=" . $id . "';
            </script>
        ");

    }

}


/* =========================================================
   UPDATE USER
========================================================= */

if ($password !== "") {

    /* -----------------------------------------------------
       UPDATE WITH NEW PASSWORD
    ----------------------------------------------------- */

    $hashedPassword = password_hash(
        $password,
        PASSWORD_DEFAULT
    );

    $updateSql = "
        UPDATE users
        SET
            username = ?,
            password = ?,
            full_name = ?,
            role = ?,
            is_active = ?
        WHERE id = ?
    ";

    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {

        die("Database error.");

    }

    $updateStmt->bind_param(
        "ssssii",
        $username,
        $hashedPassword,
        $fullName,
        $role,
        $isActive,
        $id
    );

} else {

    /* -----------------------------------------------------
       UPDATE WITHOUT CHANGING PASSWORD
    ----------------------------------------------------- */

    $updateSql = "
        UPDATE users
        SET
            username = ?,
            full_name = ?,
            role = ?,
            is_active = ?
        WHERE id = ?
    ";

    $updateStmt = $conn->prepare($updateSql);

    if (!$updateStmt) {

        die("Database error.");

    }

    $updateStmt->bind_param(
        "sssii",
        $username,
        $fullName,
        $role,
        $isActive,
        $id
    );

}


/* =========================================================
   EXECUTE UPDATE
========================================================= */

if ($updateStmt->execute()) {

    $updateStmt->close();

    header("Location: index.php?updated=1");
    exit;

} else {

    $updateStmt->close();

    die("
        <script>
            alert('Unable to update user account.');
            window.location.href = 'edit.php?id=" . $id . "';
        </script>
    ");

}

?>