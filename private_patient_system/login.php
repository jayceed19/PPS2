<?php

require_once "config/database.php";
require_once "config/activity_log.php";

/*
|--------------------------------------------------------------------------
| START SESSION
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| PREVENT CACHE
|--------------------------------------------------------------------------
*/

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


/*
|--------------------------------------------------------------------------
| IF ALREADY LOGGED IN
|--------------------------------------------------------------------------
*/

if (
    isset($_SESSION['user_id']) &&
    !empty($_SESSION['user_id'])
) {
    header("Location: dashboard.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

$error = "";


/*
|--------------------------------------------------------------------------
| LOGIN PROCESS
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = isset($_POST['username'])
        ? trim($_POST['username'])
        : '';

    $password = isset($_POST['password'])
        ? $_POST['password']
        : '';


    /*
    |--------------------------------------------------------------------------
    | VALIDATE INPUT
    |--------------------------------------------------------------------------
    */

    if ($username === "" || $password === "") {

        $error = "Please enter your username and password.";

    } else {

        $sql = "
            SELECT
                id,
                username,
                password,
                full_name,
                role,
                is_active
            FROM users
            WHERE username = ?
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);


        /*
        |--------------------------------------------------------------------------
        | CHECK DATABASE QUERY
        |--------------------------------------------------------------------------
        */

        if (!$stmt) {

            $error = "Database error.";

        } else {

            $stmt->bind_param(
                "s",
                $username
            );

            $stmt->execute();

            $result = $stmt->get_result();


            /*
            |--------------------------------------------------------------------------
            | CHECK USER
            |--------------------------------------------------------------------------
            */

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | CHECK ACTIVE ACCOUNT
                |--------------------------------------------------------------------------
                */

                if ((int)$user['is_active'] !== 1) {

                    $error = "This account is inactive.";

                } elseif (
                    password_verify(
                        $password,
                        $user['password']
                    )
                ) {


                    /*
                    |--------------------------------------------------------------------------
                    | REGENERATE SESSION ID
                    |--------------------------------------------------------------------------
                    */

                    session_regenerate_id(true);


                    /*
                    |--------------------------------------------------------------------------
                    | SAVE USER SESSION
                    |--------------------------------------------------------------------------
                    */

                    $_SESSION['user_id'] = $user['id'];

                    $_SESSION['username'] = $user['username'];

                    $_SESSION['full_name'] = $user['full_name'];

                    $_SESSION['role'] = $user['role'];


                    /*
                    |--------------------------------------------------------------------------
                    | ACTIVITY LOG
                    |--------------------------------------------------------------------------
                    */

                    logActivity(
                        $conn,
                        "LOGIN",
                        "User logged into the system."
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | REDIRECT
                    |--------------------------------------------------------------------------
                    */

                    header("Location: dashboard.php");
                    exit;


                } else {

                    $error = "Invalid username or password.";

                }


            } else {

                $error = "Invalid username or password.";

            }


            $stmt->close();

        }

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

    <title>
        Login - Private Patient System
    </title>


    <!-- FAVICON -->

    <link
        rel="icon"
        type="image/png"
        href="/private_patient_system/asset/images/DCMDLOGO.png?v=1"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | RESET
        |--------------------------------------------------------------------------
        */

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        body {

            min-height: 100vh;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background:
                linear-gradient(
                    135deg,
                    #eaf3f9 0%,
                    #f5f8fb 45%,
                    #e6f0f7 100%
                );

            color: #243447;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px;

            position: relative;

            overflow: hidden;

        }


        /*
        |--------------------------------------------------------------------------
        | BACKGROUND DECORATION
        |--------------------------------------------------------------------------
        */

        body::before {

            content: "";

            position: absolute;

            width: 520px;

            height: 520px;

            border-radius: 50%;

            background:
                rgba(31, 78, 120, 0.06);

            top: -220px;

            left: -180px;

        }


        body::after {

            content: "";

            position: absolute;

            width: 600px;

            height: 600px;

            border-radius: 50%;

            background:
                rgba(58, 137, 180, 0.05);

            bottom: -300px;

            right: -220px;

        }


        /*
        |--------------------------------------------------------------------------
        | MAIN WRAPPER
        |--------------------------------------------------------------------------
        */

        .login-wrapper {

            width: 100%;

            max-width: 960px;

            min-height: 560px;

            display: flex;

            background: #ffffff;

            border-radius: 16px;

            overflow: hidden;

            border: 1px solid #dbe4eb;

            box-shadow:
                0 18px 50px rgba(25, 55, 80, 0.14);

            position: relative;

            z-index: 2;

        }


        /*
        |--------------------------------------------------------------------------
        | LEFT PANEL
        |--------------------------------------------------------------------------
        */

        .login-brand {

            width: 48%;

            background:
                linear-gradient(
                    145deg,
                    #173f63 0%,
                    #1f4e78 55%,
                    #28658f 100%
                );

            color: #ffffff;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 55px 45px;

            position: relative;

            overflow: hidden;

        }


        /*
        |--------------------------------------------------------------------------
        | LEFT PANEL DECORATION
        |--------------------------------------------------------------------------
        */

        .login-brand::before {

            content: "";

            position: absolute;

            width: 380px;

            height: 380px;

            border: 1px solid rgba(255,255,255,0.10);

            border-radius: 50%;

            top: -160px;

            left: -130px;

        }


        .login-brand::after {

            content: "";

            position: absolute;

            width: 450px;

            height: 450px;

            border: 1px solid rgba(255,255,255,0.08);

            border-radius: 50%;

            bottom: -260px;

            right: -230px;

        }


        .brand-content {

            width: 100%;

            max-width: 330px;

            position: relative;

            z-index: 2;

            text-align: center;

        }


        /*
        |--------------------------------------------------------------------------
        | LOGO
        |--------------------------------------------------------------------------
        */

        .brand-logo {

            width: 125px;

            height: 125px;

            object-fit: contain;

            background: #ffffff;

            border-radius: 50%;

            padding: 12px;

            display: block;

            margin: 0 auto 25px auto;

            box-shadow:
                0 8px 25px rgba(0,0,0,0.16);

        }


        /*
        |--------------------------------------------------------------------------
        | BRAND TITLE
        |--------------------------------------------------------------------------
        */

        .brand-title {

            font-size: 25px;

            font-weight: 700;

            letter-spacing: 1.3px;

            line-height: 1.35;

            margin-bottom: 12px;

        }


        .brand-subtitle {

            font-size: 14px;

            line-height: 1.7;

            color: rgba(255,255,255,0.82);

            margin-bottom: 30px;

        }


        /*
        |--------------------------------------------------------------------------
        | BRAND DIVIDER
        |--------------------------------------------------------------------------
        */

        .brand-divider {

            width: 55px;

            height: 3px;

            background: rgba(255,255,255,0.75);

            border-radius: 10px;

            margin: 0 auto 25px auto;

        }


        /*
        |--------------------------------------------------------------------------
        | BRAND FOOTER TEXT
        |--------------------------------------------------------------------------
        */

        .brand-note {

            font-size: 11px;

            color: rgba(255,255,255,0.62);

            line-height: 1.6;

        }


        /*
        |--------------------------------------------------------------------------
        | RIGHT PANEL
        |--------------------------------------------------------------------------
        */

        .login-panel {

            width: 52%;

            padding: 55px 60px;

            display: flex;

            align-items: center;

            justify-content: center;

            background: #ffffff;

        }


        .login-content {

            width: 100%;

            max-width: 380px;

        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN HEADER
        |--------------------------------------------------------------------------
        */

        .login-heading {

            margin-bottom: 32px;

        }


        .login-heading h1 {

            font-size: 27px;

            font-weight: 700;

            color: #1d3448;

            margin-bottom: 8px;

        }


        .login-heading p {

            font-size: 13px;

            color: #7a8794;

            line-height: 1.6;

        }


        /*
        |--------------------------------------------------------------------------
        | ERROR MESSAGE
        |--------------------------------------------------------------------------
        */

        .error-message {

            display: flex;

            align-items: center;

            gap: 10px;

            background: #fff4f4;

            color: #a32121;

            border: 1px solid #f0caca;

            border-left: 4px solid #c53030;

            border-radius: 6px;

            padding: 12px 13px;

            margin-bottom: 20px;

            font-size: 13px;

            line-height: 1.4;

        }


        .error-icon {

            width: 20px;

            height: 20px;

            border-radius: 50%;

            background: #c53030;

            color: #ffffff;

            display: flex;

            align-items: center;

            justify-content: center;

            font-size: 12px;

            font-weight: bold;

            flex-shrink: 0;

        }


        /*
        |--------------------------------------------------------------------------
        | FORM GROUP
        |--------------------------------------------------------------------------
        */

        .form-group {

            margin-bottom: 20px;

        }


        .form-group label {

            display: block;

            font-size: 12px;

            font-weight: 700;

            color: #34495e;

            margin-bottom: 8px;

        }


        /*
        |--------------------------------------------------------------------------
        | INPUT WRAPPER
        |--------------------------------------------------------------------------
        */

        .input-wrapper {

            position: relative;

        }


        /*
        |--------------------------------------------------------------------------
        | INPUT
        |--------------------------------------------------------------------------
        */

        .form-input {

            width: 100%;

            height: 46px;

            border: 1px solid #ccd6df;

            border-radius: 7px;

            background: #fbfcfd;

            color: #243447;

            font-size: 14px;

            padding: 0 13px;

            outline: none;

            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease,
                background 0.2s ease;

        }


        .form-input:focus {

            border-color: #1f4e78;

            background: #ffffff;

            box-shadow:
                0 0 0 3px rgba(31, 78, 120, 0.10);

        }


        .form-input::placeholder {

            color: #a1acb6;

        }


        /*
        |--------------------------------------------------------------------------
        | PASSWORD INPUT
        |--------------------------------------------------------------------------
        */

        .password-input {

            padding-right: 48px;

        }


        .show-password {

            position: absolute;

            right: 10px;

            top: 50%;

            transform: translateY(-50%);

            width: 30px;

            height: 30px;

            border: none;

            background: transparent;

            color: #7c8a97;

            cursor: pointer;

            font-size: 13px;

            border-radius: 5px;

        }


        .show-password:hover {

            background: #edf3f7;

            color: #1f4e78;

        }


        /*
        |--------------------------------------------------------------------------
        | LOGIN BUTTON
        |--------------------------------------------------------------------------
        */

        .login-button {

            width: 100%;

            height: 46px;

            border: none;

            border-radius: 7px;

            background:
                linear-gradient(
                    135deg,
                    #1f4e78,
                    #28658f
                );

            color: #ffffff;

            font-size: 14px;

            font-weight: 700;

            letter-spacing: 0.2px;

            cursor: pointer;

            margin-top: 5px;

            box-shadow:
                0 5px 12px rgba(31, 78, 120, 0.20);

            transition:
                transform 0.15s ease,
                box-shadow 0.15s ease,
                background 0.15s ease;

        }


        .login-button:hover {

            background:
                linear-gradient(
                    135deg,
                    #173a5c,
                    #1f557b
                );

            box-shadow:
                0 7px 16px rgba(31, 78, 120, 0.25);

            transform: translateY(-1px);

        }


        .login-button:active {

            transform: translateY(0);

            box-shadow:
                0 3px 8px rgba(31, 78, 120, 0.18);

        }


        /*
        |--------------------------------------------------------------------------
        | SECURITY NOTE
        |--------------------------------------------------------------------------
        */

        .security-note {

            display: flex;

            align-items: center;

            justify-content: center;

            gap: 7px;

            margin-top: 20px;

            color: #8a96a3;

            font-size: 11px;

            text-align: center;

        }


        .security-icon {

            font-size: 12px;

        }


        /*
        |--------------------------------------------------------------------------
        | FOOTER
        |--------------------------------------------------------------------------
        */

        .login-footer {

            text-align: center;

            margin-top: 35px;

            padding-top: 20px;

            border-top: 1px solid #edf0f3;

            color: #9aa5af;

            font-size: 10px;

            line-height: 1.5;

        }


        /*
        |--------------------------------------------------------------------------
        | RESPONSIVE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 800px) {

            body {

                padding: 20px;

            }


            .login-wrapper {

                max-width: 500px;

                min-height: auto;

                flex-direction: column;

            }


            .login-brand {

                width: 100%;

                padding: 35px 25px;

            }


            .brand-logo {

                width: 90px;

                height: 90px;

                margin-bottom: 18px;

            }


            .brand-title {

                font-size: 21px;

            }


            .brand-subtitle {

                font-size: 12px;

                margin-bottom: 10px;

            }


            .brand-divider,
            .brand-note {

                display: none;

            }


            .login-panel {

                width: 100%;

                padding: 40px 30px;

            }

        }


        @media (max-width: 480px) {

            body {

                padding: 12px;

            }


            .login-wrapper {

                border-radius: 12px;

            }


            .login-brand {

                padding: 30px 20px;

            }


            .login-panel {

                padding: 32px 22px;

            }


            .login-heading h1 {

                font-size: 23px;

            }

        }

    </style>

</head>


<body>


<div class="login-wrapper">


    <!-- =========================================================
         LEFT BRAND PANEL
    ========================================================== -->

    <section class="login-brand">

        <div class="brand-content">


            <img
                src="/private_patient_system/asset/images/DCMD.png?v=1"
                alt="DCMD Logo"
                class="brand-logo"
            >


            <div class="brand-divider"></div>


            <h2 class="brand-title">
                PRIVATE PATIENT SYSTEM
            </h2>


            <p class="brand-subtitle">
                Patient Management and Medical Records System
            </p>


            <p class="brand-note">
                Authorized personnel only.<br>
                Please sign in to access the system.
            </p>


        </div>

    </section>


    <!-- =========================================================
         RIGHT LOGIN PANEL
    ========================================================== -->

    <section class="login-panel">

        <div class="login-content">


            <div class="login-heading">

                <h1>
                    Welcome Back
                </h1>

                <p>
                    Sign in using your authorized staff account
                    to continue.
                </p>

            </div>


            <!-- =====================================================
                 ERROR MESSAGE
            ====================================================== -->

            <?php if ($error !== "") { ?>

                <div class="error-message">

                    <span class="error-icon">
                        !
                    </span>

                    <span>

                        <?php
                        echo htmlspecialchars($error);
                        ?>

                    </span>

                </div>

            <?php } ?>


            <!-- =====================================================
                 LOGIN FORM
            ====================================================== -->

            <form
                method="POST"
                action=""
                autocomplete="off"
            >


                <!-- USERNAME -->

                <div class="form-group">

                    <label for="username">
                        Username
                    </label>

                    <div class="input-wrapper">

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-input"
                            autocomplete="username"
                            placeholder="Enter your username"
                            required
                            autofocus
                        >

                    </div>

                </div>


                <!-- PASSWORD -->

                <div class="form-group">

                    <label for="password">
                        Password
                    </label>

                    <div class="input-wrapper">

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input password-input"
                            autocomplete="current-password"
                            placeholder="Enter your password"
                            required
                        >

                        <button
                            type="button"
                            class="show-password"
                            id="togglePassword"
                            aria-label="Show password"
                        >
                            👁
                        </button>

                    </div>

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="login-button"
                >
                    Sign In
                </button>


            </form>


            <!-- SECURITY NOTE -->

            <div class="security-note">

                <span class="security-icon">
                    🔒
                </span>

                <span>
                    Secure access for authorized staff only
                </span>

            </div>


            <!-- FOOTER -->

            <div class="login-footer">

                Private Patient System © 2026

            </div>


        </div>

    </section>


</div>


<script>

/*
|--------------------------------------------------------------------------
| SHOW / HIDE PASSWORD
|--------------------------------------------------------------------------
*/

var passwordInput =
    document.getElementById("password");

var togglePassword =
    document.getElementById("togglePassword");


if (togglePassword && passwordInput) {

    togglePassword.addEventListener(
        "click",
        function () {

            if (passwordInput.type === "password") {

                passwordInput.type = "text";

                togglePassword.innerHTML = "🙈";

                togglePassword.setAttribute(
                    "aria-label",
                    "Hide password"
                );

            } else {

                passwordInput.type = "password";

                togglePassword.innerHTML = "👁";

                togglePassword.setAttribute(
                    "aria-label",
                    "Show password"
                );

            }

        }
    );

}

</script>


</body>

</html>