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
   GET USER ID
========================================================= */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid user ID.");
}


/* =========================================================
   GET USER DATA
========================================================= */

$sql = "
    SELECT id, username, full_name, role, is_active
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error.");
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    die("User not found.");
}

$user = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   HEADER & NAVIGATION
========================================================= */

require_once "../includes/header.php";
require_once "../includes/navigation.php";

?>


<style>

/* =========================================================
   USER EDIT PAGE
========================================================= */

.user-edit-wrapper {
    max-width: 900px;
    margin: 0 auto;
    padding: 28px 20px 40px;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.user-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.user-page-title {
    margin: 0;
    font-size: 23px;
    font-weight: 700;
    color: #1f2937;
}

.user-page-subtitle {
    margin-top: 4px;
    color: #6b7280;
    font-size: 13px;
}


/* =========================================================
   BACK BUTTON
========================================================= */

.user-back-btn {
    font-size: 12px;
    font-weight: 600;
    border-radius: 6px;
    padding: 8px 14px;
}


/* =========================================================
   MAIN CARD
========================================================= */

.user-edit-card {
    background: #ffffff;
    border: 1px solid #e2e6eb;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    overflow: hidden;
}


/* =========================================================
   CARD HEADER
========================================================= */

.user-edit-header {
    padding: 18px 24px;
    background: #fafbfc;
    border-bottom: 1px solid #e5e7eb;

    display: flex;
    align-items: center;
    gap: 13px;
}

.user-icon {
    width: 42px;
    height: 42px;

    border-radius: 8px;

    background: #eef5ff;
    color: #2563eb;

    display: flex;
    align-items: center;
    justify-content: center;

    font-size: 18px;
}

.user-edit-header-title {
    font-size: 15px;
    font-weight: 700;
    color: #1f2937;
}

.user-edit-header-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}


/* =========================================================
   CARD BODY
========================================================= */

.user-edit-body {
    padding: 26px 28px 24px;
}


/* =========================================================
   FORM SECTION
========================================================= */

.form-section {
    margin-bottom: 24px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.form-section-title {
    font-size: 13px;
    font-weight: 700;
    color: #374151;

    padding-bottom: 9px;
    margin-bottom: 16px;

    border-bottom: 1px solid #edf0f3;
}


/* =========================================================
   FORM LABEL
========================================================= */

.user-edit-body .form-label {
    font-size: 12px !important;
    font-weight: 600 !important;
    color: #374151 !important;

    margin-bottom: 6px !important;
}


/* =========================================================
   INPUTS
========================================================= */

.user-edit-body .form-control,
.user-edit-body .form-select {

    min-height: 40px;

    border: 1px solid #d1d5db !important;

    border-radius: 6px !important;

    font-size: 13px !important;

    color: #374151;

    background-color: #ffffff;

    box-shadow: none !important;

    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease;
}

.user-edit-body .form-control:focus,
.user-edit-body .form-select:focus {

    border-color: #80aee8 !important;

    box-shadow:
        0 0 0 3px rgba(37, 99, 235, 0.08) !important;
}

.user-edit-body .form-control::placeholder {

    color: #9ca3af;

    font-size: 12px;
}


/* =========================================================
   PASSWORD FIELD
========================================================= */

.password-field {
    position: relative;
    width: 100%;
}

.password-input {
    padding-right: 45px !important;
}


/* =========================================================
   PASSWORD EYE
========================================================= */

.password-eye {

    position: absolute;

    right: 0;
    top: 0;

    width: 42px;
    height: 40px;

    border: 0;

    background: transparent;

    color: #6b7280;

    display: flex;
    align-items: center;
    justify-content: center;

    cursor: pointer;

    font-size: 17px;

    padding: 0;

    z-index: 5;

    transition:
        color 0.15s ease,
        background-color 0.15s ease;
}

.password-eye:hover {
    color: #2563eb;
}

.password-eye:focus {
    outline: none;
}

.password-eye.active {
    color: #2563eb;
}


/* =========================================================
   PASSWORD NOTE
========================================================= */

.password-note {

    font-size: 11px;

    color: #6b7280;

    margin-top: 6px;

    line-height: 1.4;
}


/* =========================================================
   ACTION AREA
========================================================= */

.user-form-actions {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 9px;

    padding-top: 20px;

    margin-top: 24px;

    border-top: 1px solid #e5e7eb;
}

.user-form-actions .btn {

    min-width: 110px;

    height: 38px;

    font-size: 13px;

    font-weight: 600;

    border-radius: 6px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .user-edit-wrapper {

        padding: 20px 12px 30px;

    }

    .user-page-header {

        align-items: flex-start;

        gap: 12px;

    }

    .user-page-title {

        font-size: 20px;

    }

    .user-edit-body {

        padding: 22px 18px;

    }

    .user-edit-header {

        padding: 16px 18px;

    }

}


@media (max-width: 576px) {

    .user-page-header {

        flex-direction: column;

        align-items: flex-start;

    }

    .user-back-btn {

        width: 100%;

        text-align: center;

    }

    .user-form-actions {

        flex-direction: column-reverse;

        align-items: stretch;

    }

    .user-form-actions .btn {

        width: 100%;

    }

}

</style>


<!-- =========================================================
     PAGE
========================================================= -->

<div class="user-edit-wrapper">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="user-page-header">

        <div>

            <h2 class="user-page-title">
                Edit User
            </h2>

            <div class="user-page-subtitle">
                Manage user account information and access settings
            </div>

        </div>


        <a
            href="index.php"
            class="btn btn-outline-secondary user-back-btn"
        >
            ← Back
        </a>

    </div>



    <!-- =====================================================
         MAIN CARD
    ====================================================== -->

    <div class="user-edit-card">


        <!-- =================================================
             CARD HEADER
        ================================================== -->

        <div class="user-edit-header">

            <div class="user-icon">
                👤
            </div>

            <div>

                <div class="user-edit-header-title">
                    User Account
                </div>

                <div class="user-edit-header-subtitle">
                    Update the information associated with this account
                </div>

            </div>

        </div>



        <!-- =================================================
             CARD BODY
        ================================================== -->

        <div class="user-edit-body">

            <form
                method="POST"
                action="update.php"
                id="editUserForm"
            >

                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $user['id']; ?>"
                >


                <!-- =================================================
                     ACCOUNT INFORMATION
                ================================================== -->

                <div class="form-section">

                    <div class="form-section-title">
                        Account Information
                    </div>


                    <div class="row g-3">


                        <!-- FULL NAME -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($user['full_name']); ?>"
                                placeholder="Enter full name"
                                required
                            >

                        </div>


                        <!-- USERNAME -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Username
                            </label>

                            <input
                                type="text"
                                name="username"
                                class="form-control"
                                value="<?php echo htmlspecialchars($user['username']); ?>"
                                placeholder="Enter username"
                                required
                            >

                        </div>


                        <!-- ROLE -->

                        <div class="col-md-6">

                            <label class="form-label">
                                User Role
                            </label>

                            <select
                                name="role"
                                class="form-select"
                                required
                            >

                                <option
                                    value="Staff"
                                    <?php
                                    echo ($user['role'] === 'Staff')
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Staff
                                </option>

                                <option
                                    value="Administrator"
                                    <?php
                                    echo ($user['role'] === 'Administrator')
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Administrator
                                </option>

                            </select>

                        </div>


                        <!-- ACCOUNT STATUS -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Account Status
                            </label>

                            <select
                                name="is_active"
                                class="form-select"
                                required
                            >

                                <option
                                    value="1"
                                    <?php
                                    echo ($user['is_active'] == 1)
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Active
                                </option>

                                <option
                                    value="0"
                                    <?php
                                    echo ($user['is_active'] == 0)
                                        ? 'selected'
                                        : '';
                                    ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     CHANGE PASSWORD
                ================================================== -->

                <div class="form-section">

                    <div class="form-section-title">
                        Change Password
                    </div>


                    <div class="row g-3">


                        <!-- NEW PASSWORD -->

                        <div class="col-md-6">

                            <label class="form-label">
                                New Password
                            </label>


                            <div class="password-field">

                                <input
                                    type="password"
                                    name="password"
                                    id="password"
                                    class="form-control password-input"
                                    minlength="6"
                                    placeholder="Enter new password"
                                >


                                <button
                                    type="button"
                                    class="password-eye"
                                    id="passwordToggle"
                                    aria-label="Show password"
                                    title="Show password"
                                >
                                    👁
                                </button>

                            </div>


                            <div class="password-note">
                                Leave blank to keep the current password.
                                Minimum 6 characters.
                            </div>

                        </div>



                        <!-- CONFIRM PASSWORD -->

                        <div class="col-md-6">

                            <label class="form-label">
                                Confirm New Password
                            </label>


                            <div class="password-field">

                                <input
                                    type="password"
                                    name="confirm_password"
                                    id="confirm_password"
                                    class="form-control password-input"
                                    minlength="6"
                                    placeholder="Re-enter new password"
                                >


                                <button
                                    type="button"
                                    class="password-eye"
                                    id="confirmPasswordToggle"
                                    aria-label="Show password"
                                    title="Show password"
                                >
                                    👁
                                </button>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- =================================================
                     ACTION BUTTONS
                ================================================== -->

                <div class="user-form-actions">

                    <a
                        href="index.php"
                        class="btn btn-light border"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Changes
                    </button>

                </div>

            </form>

        </div>

    </div>

</div>



<script>

/* =========================================================
   PASSWORD TOGGLE FUNCTION
========================================================= */

function setupPasswordToggle(buttonId, inputId) {

    var button =
        document.getElementById(buttonId);

    var input =
        document.getElementById(inputId);


    if (!button || !input) {
        return;
    }


    button.addEventListener("click", function () {

        if (input.type === "password") {

            input.type = "text";

            button.classList.add("active");

            button.innerHTML = "🙈";

            button.setAttribute(
                "aria-label",
                "Hide password"
            );

            button.setAttribute(
                "title",
                "Hide password"
            );

        } else {

            input.type = "password";

            button.classList.remove("active");

            button.innerHTML = "👁";

            button.setAttribute(
                "aria-label",
                "Show password"
            );

            button.setAttribute(
                "title",
                "Show password"
            );

        }

    });

}


/* =========================================================
   NEW PASSWORD TOGGLE
========================================================= */

setupPasswordToggle(
    "passwordToggle",
    "password"
);


/* =========================================================
   CONFIRM PASSWORD TOGGLE
========================================================= */

setupPasswordToggle(
    "confirmPasswordToggle",
    "confirm_password"
);


/* =========================================================
   FORM VALIDATION
========================================================= */

var editUserForm =
    document.getElementById("editUserForm");


if (editUserForm) {

    editUserForm.addEventListener(
        "submit",
        function (event) {

            var password =
                document.getElementById("password").value;

            var confirmPassword =
                document.getElementById("confirm_password").value;


            /* =============================================
               PASSWORD LENGTH
            ============================================= */

            if (
                password !== "" &&
                password.length < 6
            ) {

                alert(
                    "Password must be at least 6 characters."
                );

                event.preventDefault();

                return false;
            }


            /* =============================================
               PASSWORD CONFIRMATION
            ============================================= */

            if (
                password !== "" &&
                password !== confirmPassword
            ) {

                alert(
                    "New Password and Confirm New Password do not match."
                );

                event.preventDefault();

                document
                    .getElementById("confirm_password")
                    .focus();

                return false;
            }

        }
    );

}

</script>


<?php

require_once "../includes/footer.php";

?>