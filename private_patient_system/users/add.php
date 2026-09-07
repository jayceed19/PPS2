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


/*
|--------------------------------------------------------------------------
| PAGE SETTINGS
|--------------------------------------------------------------------------
*/

$pageTitle = "Add User";
$pageSubtitle = "Create New System User";

$basePath = "../";

$activePage = "users";


/*
|--------------------------------------------------------------------------
| HEADER & NAVIGATION
|--------------------------------------------------------------------------
*/

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navigation.php";

?>

<style>

/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.user-form-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 25px 20px 40px 20px;
}


/*
|--------------------------------------------------------------------------
| PAGE HEADER
|--------------------------------------------------------------------------
*/

.user-form-header {
    margin-bottom: 20px;
}

.user-form-header h2 {
    margin: 0 0 5px 0;

    color: #1f3447;

    font-size: 22px;
    font-weight: 700;
}

.user-form-header p {
    margin: 0;

    color: #7a8794;

    font-size: 13px;
}


/*
|--------------------------------------------------------------------------
| FORM CARD
|--------------------------------------------------------------------------
*/

.user-form-card {
    background: #ffffff;

    border: 1px solid #dfe5ea;

    border-radius: 8px;

    box-shadow:
        0 2px 8px rgba(0, 0, 0, 0.05);

    overflow: hidden;
}


/*
|--------------------------------------------------------------------------
| CARD HEADER
|--------------------------------------------------------------------------
*/

.user-form-card-header {
    padding: 16px 20px;

    background: #f7f9fb;

    border-bottom: 1px solid #e3e8ed;
}

.user-form-card-header h3 {
    margin: 0 0 4px 0;

    color: #34495e;

    font-size: 15px;
}

.user-form-card-header p {
    margin: 0;

    color: #8793a0;

    font-size: 11px;
}


/*
|--------------------------------------------------------------------------
| FORM BODY
|--------------------------------------------------------------------------
*/

.user-form-body {
    padding: 25px 25px 10px 25px;
}


/*
|--------------------------------------------------------------------------
| FORM GRID
|--------------------------------------------------------------------------
*/

.form-grid {
    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;
}


/*
|--------------------------------------------------------------------------
| FORM GROUP
|--------------------------------------------------------------------------
*/

.form-group {
    margin-bottom: 12px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;

    margin-bottom: 7px;

    color: #34495e;

    font-size: 12px;

    font-weight: 700;
}

.required {
    color: #c53030;
}


/*
|--------------------------------------------------------------------------
| INPUT
|--------------------------------------------------------------------------
*/

.form-input,
.form-select {
    width: 100%;

    height: 43px;

    padding: 0 12px;

    border: 1px solid #ccd6df;

    border-radius: 6px;

    background: #fbfcfd;

    color: #263746;

    font-size: 13px;

    outline: none;

    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease,
        background 0.2s ease;
}

.form-input:focus,
.form-select:focus {
    border-color: #1f4e78;

    background: #ffffff;

    box-shadow:
        0 0 0 3px rgba(31, 78, 120, 0.10);
}


/*
|--------------------------------------------------------------------------
| PASSWORD WRAPPER
|--------------------------------------------------------------------------
*/

.password-wrapper {
    position: relative;
}

.password-wrapper .form-input {
    padding-right: 48px;
}

.password-toggle {
    position: absolute;

    right: 8px;
    top: 50%;

    transform: translateY(-50%);

    width: 30px;
    height: 30px;

    border: none;

    background: transparent;

    color: #7b8995;

    border-radius: 5px;

    cursor: pointer;
}

.password-toggle:hover {
    background: #edf3f7;

    color: #1f4e78;
}


/*
|--------------------------------------------------------------------------
| FIELD NOTE
|--------------------------------------------------------------------------
*/

.field-note {
    display: block;

    margin-top: 6px;

    color: #8a96a2;

    font-size: 10px;
}


/*
|--------------------------------------------------------------------------
| FORM FOOTER
|--------------------------------------------------------------------------
*/

.user-form-footer {
    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 16px 25px;

    background: #fafbfc;

    border-top: 1px solid #edf0f3;
}


/*
|--------------------------------------------------------------------------
| BUTTONS
|--------------------------------------------------------------------------
*/

.form-button {
    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 39px;

    padding: 0 16px;

    border-radius: 6px;

    font-size: 12px;

    font-weight: 700;

    text-decoration: none;

    cursor: pointer;
}

.cancel-button {
    background: #ffffff;

    color: #526372;

    border: 1px solid #cfd8df;
}

.cancel-button:hover {
    background: #f3f6f8;
}

.save-button {
    background: #1f4e78;

    color: #ffffff;

    border: 1px solid #1f4e78;
}

.save-button:hover {
    background: #173a5c;

    border-color: #173a5c;
}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 650px) {

    .user-form-page {
        padding:
            20px 12px 30px 12px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .form-group.full-width {
        grid-column: auto;
    }

    .user-form-body {
        padding: 20px 18px 5px 18px;
    }

    .user-form-footer {
        padding: 15px 18px;

        flex-direction: column-reverse;

        align-items: stretch;
    }

    .form-button {
        width: 100%;
    }

}

</style>


<div class="user-form-page">


    <!-- =========================================================
         PAGE HEADER
    ========================================================== -->

    <div class="user-form-header">

        <h2>
            Add User
        </h2>

        <p>
            Create a new authorized account for the system.
        </p>

    </div>



    <!-- =========================================================
         FORM CARD
    ========================================================== -->

    <div class="user-form-card">


        <!-- CARD HEADER -->

        <div class="user-form-card-header">

            <h3>
                User Account Information
            </h3>

            <p>
                Enter the account details below.
            </p>

        </div>



        <!-- FORM -->

        <form
            method="POST"
            action="save.php"
            autocomplete="off"
        >


            <div class="user-form-body">


                <div class="form-grid">


                    <!-- =================================================
                         FULL NAME
                    ================================================== -->

                    <div class="form-group full-width">

                        <label for="full_name">

                            Full Name
                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            class="form-input"
                            placeholder="Enter complete name"
                            maxlength="100"
                            required
                        >

                    </div>



                    <!-- =================================================
                         USERNAME
                    ================================================== -->

                    <div class="form-group">

                        <label for="username">

                            Username
                            <span class="required">*</span>

                        </label>

                        <input
                            type="text"
                            id="username"
                            name="username"
                            class="form-input"
                            placeholder="Enter username"
                            maxlength="50"
                            autocomplete="off"
                            required
                        >

                        <span class="field-note">
                            Username must be unique.
                        </span>

                    </div>



                    <!-- =================================================
                         ROLE
                    ================================================== -->

                    <div class="form-group">

                        <label for="role">

                            Role
                            <span class="required">*</span>

                        </label>

                        <select
                            id="role"
                            name="role"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select role
                            </option>

                            <option value="Staff">
                                Staff
                            </option>

                            <option value="Administrator">
                                Administrator
                            </option>

                        </select>

                    </div>



                    <!-- =================================================
                         PASSWORD
                    ================================================== -->

                    <div class="form-group">

                        <label for="password">

                            Password
                            <span class="required">*</span>

                        </label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-input"
                                placeholder="Enter password"
                                minlength="6"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="togglePassword"
                                aria-label="Show password"
                            >
                                👁
                            </button>

                        </div>

                        <span class="field-note">
                            Minimum of 6 characters.
                        </span>

                    </div>



                    <!-- =================================================
                         CONFIRM PASSWORD
                    ================================================== -->

                    <div class="form-group">

                        <label for="confirm_password">

                            Confirm Password
                            <span class="required">*</span>

                        </label>

                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="confirm_password"
                                name="confirm_password"
                                class="form-input"
                                placeholder="Confirm password"
                                minlength="6"
                                autocomplete="new-password"
                                required
                            >

                            <button
                                type="button"
                                class="password-toggle"
                                id="toggleConfirmPassword"
                                aria-label="Show password"
                            >
                                👁
                            </button>

                        </div>

                    </div>


                </div>

            </div>



            <!-- =========================================================
                 FORM FOOTER
            ========================================================== -->

            <div class="user-form-footer">


                <a
                    href="index.php"
                    class="form-button cancel-button"
                >
                    Cancel
                </a>


                <button
                    type="submit"
                    class="form-button save-button"
                >
                    Create User
                </button>


            </div>


        </form>


    </div>


</div>



<script>

/*
|--------------------------------------------------------------------------
| PASSWORD TOGGLE
|--------------------------------------------------------------------------
*/

var passwordInput =
    document.getElementById("password");

var togglePassword =
    document.getElementById("togglePassword");


if (passwordInput && togglePassword) {

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


/*
|--------------------------------------------------------------------------
| CONFIRM PASSWORD TOGGLE
|--------------------------------------------------------------------------
*/

var confirmPasswordInput =
    document.getElementById("confirm_password");

var toggleConfirmPassword =
    document.getElementById("toggleConfirmPassword");


if (
    confirmPasswordInput &&
    toggleConfirmPassword
) {

    toggleConfirmPassword.addEventListener(
        "click",
        function () {

            if (
                confirmPasswordInput.type ===
                "password"
            ) {

                confirmPasswordInput.type =
                    "text";

                toggleConfirmPassword.innerHTML =
                    "🙈";

                toggleConfirmPassword.setAttribute(
                    "aria-label",
                    "Hide password"
                );

            } else {

                confirmPasswordInput.type =
                    "password";

                toggleConfirmPassword.innerHTML =
                    "👁";

                toggleConfirmPassword.setAttribute(
                    "aria-label",
                    "Show password"
                );

            }

        }
    );

}


/*
|--------------------------------------------------------------------------
| CONFIRM PASSWORD VALIDATION
|--------------------------------------------------------------------------
*/

var userForm =
    document.querySelector("form");


if (userForm) {

    userForm.addEventListener(
        "submit",
        function (event) {

            var password =
                document.getElementById(
                    "password"
                ).value;

            var confirmPassword =
                document.getElementById(
                    "confirm_password"
                ).value;


            if (password !== confirmPassword) {

                event.preventDefault();

                alert(
                    "Password and Confirm Password do not match."
                );

                document.getElementById(
                    "confirm_password"
                ).focus();

                return false;
            }

        }
    );

}

</script>


<?php

include __DIR__ . "/../includes/footer.php";

?>