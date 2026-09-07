<?php

require_once "../config/database.php";

/*
|--------------------------------------------------------------------------
| GET PATIENT ID
|--------------------------------------------------------------------------
*/

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid patient ID.");
}

/*
|--------------------------------------------------------------------------
| GET PATIENT DATA
|--------------------------------------------------------------------------
*/

$sql = "SELECT * FROM patients WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Patient not found.");
}

$patient = $result->fetch_assoc();

$stmt->close();

/*
|--------------------------------------------------------------------------
| DEFAULT STATUS
|--------------------------------------------------------------------------
*/

$currentStatus = !empty($patient['status'])
    ? $patient['status']
    : 'Active';

/*
|--------------------------------------------------------------------------
| PAGE SETTINGS
|--------------------------------------------------------------------------
*/

$pageTitle = "Edit Patient";
$pageSubtitle = "Edit Patient Information";
$basePath = "../";
$activePage = "patients";

/*
|--------------------------------------------------------------------------
| SHARED HEADER
|--------------------------------------------------------------------------
*/

require_once "../includes/header.php";

/*
|--------------------------------------------------------------------------
| SHARED NAVIGATION
|--------------------------------------------------------------------------
*/

require_once "../includes/navigation.php";

?>

<style>

/* =========================================================
   EDIT PATIENT PAGE
========================================================= */

.edit-patient-card {
    max-width: 1050px;
    margin: 0 auto 20px;
}

/* =========================================================
   CARD HEADER
========================================================= */

.edit-patient-card .card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding-bottom: 18px;
    margin-bottom: 5px;
    border-bottom: 1px solid #e5e9ee;
}

.edit-patient-card .card-header h2 {
    margin: 0;
    color: #1f4e78;
    font-size: 23px;
    font-weight: 700;
    line-height: 1.3;
}

.edit-patient-card .card-header p {
    margin: 5px 0 0;
    color: #6c757d;
    font-size: 13px;
}

/* =========================================================
   SECTION TITLE
========================================================= */

.edit-patient-card .section-title {
    margin-top: 28px;
    margin-bottom: 17px;
    padding: 9px 12px;
    background: #f1f5f9;
    border-left: 4px solid #1f4e78;
    border-bottom: 1px solid #e1e6eb;
    color: #1f4e78;
    font-size: 15px;
    font-weight: 700;
}

.edit-patient-card .section-title:first-child {
    margin-top: 0;
}

/* =========================================================
   FORM GRID
========================================================= */

.edit-patient-card .form-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 18px 22px;
}

/* =========================================================
   FORM GROUP
========================================================= */

.edit-patient-card .form-group {
    display: flex;
    flex-direction: column;
}

/* =========================================================
   FULL WIDTH
========================================================= */

.edit-patient-card .form-group-full {
    grid-column: 1 / -1;
}

/* =========================================================
   LABEL
========================================================= */

.edit-patient-card .form-group label {
    margin-bottom: 7px;
    color: #343a40;
    font-size: 13px;
    font-weight: 600;
}

.edit-patient-card .required {
    color: #dc3545;
    font-weight: 700;
}

/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.edit-patient-card input,
.edit-patient-card select,
.edit-patient-card textarea {
    width: 100%;
    min-height: 40px;
    padding: 9px 11px;
    background: #ffffff;
    border: 1px solid #cfd6dd;
    border-radius: 5px;
    color: #333;
    font-family:
        Arial,
        Helvetica,
        sans-serif;
    font-size: 13px;
    outline: none;
    transition:
        border-color 0.15s ease,
        box-shadow 0.15s ease,
        background 0.15s ease;
}

/* =========================================================
   INPUT FOCUS
========================================================= */

.edit-patient-card input:focus,
.edit-patient-card select:focus,
.edit-patient-card textarea:focus {
    border-color: #1f4e78;
    box-shadow:
        0 0 0 2px rgba(31, 78, 120, 0.10);
}

/* =========================================================
   TEXTAREA
========================================================= */

.edit-patient-card textarea {
    min-height: 85px;
    resize: vertical;
    line-height: 1.5;
}

/* =========================================================
   SELECT
========================================================= */

.edit-patient-card select {
    cursor: pointer;
}

/* =========================================================
   DATE
========================================================= */

.edit-patient-card input[type="date"] {
    cursor: pointer;
}

/* =========================================================
   PATIENT ID BOX
========================================================= */

.patient-id-display {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 15px 16px;
    background: #f5f7fa;
    border: 1px solid #e1e6eb;
    border-radius: 6px;
    margin-bottom: 8px;
}

.patient-id-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.patient-id-label {
    color: #6c757d;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.patient-id-number {
    color: #1f4e78;
    font-size: 20px;
    font-weight: 700;
}

.patient-id-note {
    color: #6c757d;
    font-size: 12px;
}

/* =========================================================
   FIELD HELP
========================================================= */

.field-note {
    margin-top: 6px;
    color: #6c757d;
    font-size: 11px;
    line-height: 1.4;
}

/* =========================================================
   STATUS NOTE
========================================================= */

.status-note {
    margin-top: 6px;
    color: #6c757d;
    font-size: 11px;
    line-height: 1.4;
}

/* =========================================================
   FORM ACTIONS
========================================================= */

.edit-patient-card .form-actions {
    display: flex;
    justify-content: flex-end;
    align-items: center;
    gap: 9px;
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e5e9ee;
}

/* =========================================================
   BUTTONS
========================================================= */

.edit-patient-card .form-actions .btn {
    min-height: 38px;
    padding: 9px 17px;
    border-radius: 5px;
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
}

/* =========================================================
   CANCEL BUTTON
========================================================= */

.edit-patient-card .btn-cancel {
    background: #e9ecef;
    color: #343a40;
    border: 1px solid #d5d9dd;
}

.edit-patient-card .btn-cancel:hover {
    background: #dee2e6;
}

/* =========================================================
   UPDATE BUTTON
========================================================= */

.edit-patient-card .btn-update {
    background: #1f4e78;
    color: #ffffff;
    border: 1px solid #1f4e78;
    cursor: pointer;
}

.edit-patient-card .btn-update:hover {
    background: #173a5c;
    border-color: #173a5c;
}

/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .edit-patient-card .form-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }

    .edit-patient-card .form-group-full {
        grid-column: auto;
    }

    .edit-patient-card .card-header h2 {
        font-size: 20px;
    }

    .patient-id-display {
        align-items: flex-start;
        flex-direction: column;
        gap: 8px;
    }

    .edit-patient-card .form-actions {
        justify-content: stretch;
        flex-wrap: wrap;
    }

    .edit-patient-card .form-actions .btn {
        flex: 1;
        min-width: 110px;
        text-align: center;
    }
}

</style>


<main class="main-container">

    <div class="card edit-patient-card">

        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <div class="card-header">

            <div>

                <h2>
                    Edit Patient Information
                </h2>

                <p>
                    Update the patient's information below.
                </p>

            </div>

        </div>


        <form
            action="update.php"
            method="POST"
            id="editPatientForm"
        >

            <!-- =================================================
                 HIDDEN DATABASE ID
            ================================================== -->

            <input
                type="hidden"
                name="id"
                value="<?php echo $id; ?>"
            >


            <!-- =================================================
                 PATIENT IDENTIFICATION
            ================================================== -->

            <div class="section-title">
                Patient Identification
            </div>


            <div class="patient-id-display">

                <div class="patient-id-info">

                    <span class="patient-id-label">
                        Patient ID
                    </span>

                    <span class="patient-id-number">
                        <?php
                        echo htmlspecialchars(
                            $patient['patient_id']
                        );
                        ?>
                    </span>

                </div>


                <div class="patient-id-note">
                    Patient ID cannot be changed.
                </div>

            </div>


            <!-- =================================================
                 PERSONAL INFORMATION
            ================================================== -->

            <div class="section-title">
                Personal Information
            </div>


            <div class="form-grid">

                <!-- LAST NAME -->

                <div class="form-group">

                    <label>
                        Last Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="last_name"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['last_name']
                            );
                        ?>"
                        autocomplete="family-name"
                        required
                    >

                </div>


                <!-- FIRST NAME -->

                <div class="form-group">

                    <label>
                        First Name
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        name="first_name"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['first_name']
                            );
                        ?>"
                        autocomplete="given-name"
                        required
                    >

                </div>


                <!-- MIDDLE NAME -->

                <div class="form-group">

                    <label>
                        Middle Name
                    </label>

                    <input
                        type="text"
                        name="middle_name"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['middle_name']
                            );
                        ?>"
                    >

                </div>


                <!-- SEX -->

                <div class="form-group">

                    <label>
                        Sex
                        <span class="required">*</span>
                    </label>

                    <select
                        name="sex"
                        required
                    >

                        <option value="">
                            Select Sex
                        </option>

                        <option
                            value="Male"
                            <?php
                            echo (
                                $patient['sex'] == 'Male'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Male
                        </option>

                        <option
                            value="Female"
                            <?php
                            echo (
                                $patient['sex'] == 'Female'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Female
                        </option>

                    </select>

                </div>


                <!-- BIRTHDATE -->

                <div class="form-group">

                    <label>
                        Birthdate
                        <span class="required">*</span>
                    </label>

                    <input
                        type="date"
                        name="birthdate"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['birthdate']
                            );
                        ?>"
                        max="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>


                <!-- PATIENT STATUS -->

                <div class="form-group">

                    <label>
                        Patient Status
                        <span class="required">*</span>
                    </label>

                    <select
                        name="status"
                        required
                    >

                        <option
                            value="Active"
                            <?php
                            echo (
                                $currentStatus == 'Active'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Active
                        </option>

                        <option
                            value="Inactive"
                            <?php
                            echo (
                                $currentStatus == 'Inactive'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Inactive
                        </option>

                        <option
                            value="Deceased"
                            <?php
                            echo (
                                $currentStatus == 'Deceased'
                            )
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Deceased
                        </option>

                    </select>

                    <div class="status-note">
                        Select Deceased for patients who are no longer living.
                    </div>

                </div>

            </div>


            <!-- =================================================
                 CONTACT INFORMATION
            ================================================== -->

            <div class="section-title">
                Contact Information
            </div>


            <div class="form-grid">

                <!-- CONTACT NUMBER -->

                <div class="form-group">

                    <label>
                        Contact Number
                    </label>

                    <input
                        type="text"
                        name="contact_no"
                        maxlength="11"
                        inputmode="numeric"
                        autocomplete="tel"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['contact_no']
                            );
                        ?>"
                        oninput="
                            this.value =
                            this.value
                            .replace(/[^0-9]/g, '')
                            .slice(0, 11);
                        "
                    >

                </div>


                <!-- EMERGENCY CONTACT -->

                <div class="form-group">

                    <label>
                        Emergency Contact
                    </label>

                    <input
                        type="text"
                        name="emergency_contact"
                        class="uppercase-field"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['emergency_contact']
                            );
                        ?>"
                    >

                </div>


                <!-- EMERGENCY CONTACT NUMBER -->

                <div class="form-group">

                    <label>
                        Emergency Contact Number
                    </label>

                    <input
                        type="text"
                        name="emergency_contact_no"
                        maxlength="11"
                        inputmode="numeric"
                        autocomplete="tel"
                        value="<?php
                            echo htmlspecialchars(
                                $patient['emergency_contact_no']
                            );
                        ?>"
                        oninput="
                            this.value =
                            this.value
                            .replace(/[^0-9]/g, '')
                            .slice(0, 11);
                        "
                    >

                </div>


                <!-- ADDRESS -->

                <div class="form-group form-group-full">

                    <label>
                        Address
                        <span class="required">*</span>
                    </label>

                    <textarea
                        name="address"
                        id="address"
                        class="uppercase-field"
                        required
                    ><?php
                        echo htmlspecialchars(
                            $patient['address']
                        );
                    ?></textarea>

                    <div class="field-note">
                        Required — enter <strong>-</strong> if no address is available.
                    </div>

                </div>

            </div>


            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="form-actions">

                <a
                    href="view.php?id=<?php echo $id; ?>"
                    class="btn btn-cancel"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-update"
                >
                    Update Patient
                </button>

            </div>

        </form>

    </div>

</main>


<!-- =========================================================
     PROFESSIONAL FORMATTING
========================================================= -->

<script>

/*
|--------------------------------------------------------------------------
| UPPERCASE WHILE TYPING
|--------------------------------------------------------------------------
|
| Converts letters to uppercase without aggressively
| changing the spaces while the user is typing.
|
*/

function uppercaseWhileTyping(field) {

    field.value = field.value.toUpperCase();

}


/*
|--------------------------------------------------------------------------
| CLEAN FIELD
|--------------------------------------------------------------------------
|
| Runs when leaving the field and before submitting.
|
| - Removes unnecessary repeated spaces
| - Removes leading/trailing spaces
| - Converts to uppercase
|
*/

function cleanUppercaseField(field) {

    field.value = field.value
        .replace(/\s+/g, " ")
        .trim()
        .toUpperCase();

}


/*
|--------------------------------------------------------------------------
| APPLY UPPERCASE FIELDS
|--------------------------------------------------------------------------
*/

document
    .querySelectorAll(".uppercase-field")
    .forEach(function(field) {

        /*
        ---------------------------------------------------------------
        Format existing database value
        ---------------------------------------------------------------
        */

        field.value = field.value.toUpperCase();


        /*
        ---------------------------------------------------------------
        While typing
        ---------------------------------------------------------------
        */

        field.addEventListener(
            "input",
            function() {

                uppercaseWhileTyping(this);

            }
        );


        /*
        ---------------------------------------------------------------
        When leaving the field
        ---------------------------------------------------------------
        */

        field.addEventListener(
            "blur",
            function() {

                cleanUppercaseField(this);

            }
        );

    });


/*
|--------------------------------------------------------------------------
| FORM SUBMIT
|--------------------------------------------------------------------------
*/

document
    .getElementById("editPatientForm")
    .addEventListener(
        "submit",
        function(event) {

            /*
            -----------------------------------------------------------
            ADDRESS
            -----------------------------------------------------------
            If address is blank, automatically use "-"
            -----------------------------------------------------------
            */

            var address =
                document.getElementById("address");

            if (
                address.value.trim() === ""
            ) {

                address.value = "-";

            }


            /*
            -----------------------------------------------------------
            CLEAN ALL UPPERCASE FIELDS
            -----------------------------------------------------------
            */

            document
                .querySelectorAll(".uppercase-field")
                .forEach(function(field) {

                    field.value = field.value
                        .replace(/\s+/g, " ")
                        .trim()
                        .toUpperCase();

                });

        }
    );

</script>


<?php

/*
|--------------------------------------------------------------------------
| SHARED FOOTER
|--------------------------------------------------------------------------
*/

require_once "../includes/footer.php";
require_once "../config/auth.php";
$conn->close();

?>