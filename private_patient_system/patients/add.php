<?php

require_once "../config/database.php";


// =========================================================
// PAGE SETTINGS
// =========================================================

$pageTitle = "New Patient";
$pageSubtitle = "Patient Registration";
$basePath = "../";
$activePage = "patients";


// =========================================================
// SHARED HEADER
// =========================================================

require_once "../includes/header.php";


// =========================================================
// SHARED NAVIGATION
// =========================================================

require_once "../includes/navigation.php";
require_once "../config/auth.php";
?>


<style>

/* =========================================================
   NEW PATIENT PAGE
========================================================= */

.patient-form-card {

    max-width: 1050px;

    margin: 0 auto;

}


/* =========================================================
   CARD HEADER
========================================================= */

.patient-form-card .card-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding-bottom: 18px;

    margin-bottom: 5px;

    border-bottom: 1px solid #e5e9ee;

}


.patient-form-card .card-header h2 {

    margin: 0;

    color: #1f4e78;

    font-size: 23px;

    font-weight: 700;

    line-height: 1.3;

}


.patient-form-card .card-header p {

    margin: 5px 0 0;

    color: #6c757d;

    font-size: 13px;

}


/* =========================================================
   SECTION TITLE
========================================================= */

.patient-form-card .section-title {

    margin-top: 28px;

    margin-bottom: 17px;

    padding: 9px 12px;

    background: #f1f5f9;

    border-left: 4px solid #1f4e78;

    border-bottom: 1px solid #e1e6eb;

    color: #1f4e78;

    font-size: 15px;

    font-weight: 700;

    letter-spacing: 0.1px;

}


/* =========================================================
   FORM GRID
========================================================= */

.patient-form-card .form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px 22px;

}


/* =========================================================
   FORM GROUP
========================================================= */

.patient-form-card .form-group {

    display: flex;

    flex-direction: column;

}


/* =========================================================
   FULL WIDTH
========================================================= */

.patient-form-card .form-group-full {

    grid-column: 1 / -1;

}


/* =========================================================
   LABEL
========================================================= */

.patient-form-card .form-group label {

    margin-bottom: 7px;

    color: #343a40;

    font-size: 13px;

    font-weight: 600;

}


/* =========================================================
   REQUIRED ASTERISK
========================================================= */

.patient-form-card .required {

    color: #dc3545;

    font-weight: 700;

}


/* =========================================================
   REQUIRED HELP
========================================================= */

.patient-form-card .required-help {

    display: block;

    margin-top: 5px;

    color: #dc3545;

    font-size: 10px;

    font-weight: 600;

}


/* =========================================================
   INPUT / SELECT / TEXTAREA
========================================================= */

.patient-form-card input,
.patient-form-card select,
.patient-form-card textarea {

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

.patient-form-card input:focus,
.patient-form-card select:focus,
.patient-form-card textarea:focus {

    border-color: #1f4e78;

    box-shadow:
        0 0 0 2px rgba(31, 78, 120, 0.10);

}


/* =========================================================
   READONLY
========================================================= */

.patient-form-card input[readonly] {

    background: #f1f3f5;

    color: #6c757d;

    cursor: not-allowed;

}


/* =========================================================
   TEXTAREA
========================================================= */

.patient-form-card textarea {

    min-height: 85px;

    resize: vertical;

    line-height: 1.5;

}


/* =========================================================
   UPPERCASE FIELDS
========================================================= */

.patient-form-card .uppercase-field {

    text-transform: uppercase;

}


.patient-form-card .uppercase-field::placeholder {

    text-transform: none;

}


/* =========================================================
   FORM ACTIONS
========================================================= */

.patient-form-card .form-actions {

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

.patient-form-card .form-actions .btn {

    min-height: 38px;

    padding: 9px 17px;

    border-radius: 5px;

    font-size: 13px;

    font-weight: 600;

}


/* =========================================================
   BACK / SECONDARY
========================================================= */

.patient-form-card .btn-secondary {

    background: #6c757d;

    color: #ffffff;

}


.patient-form-card .btn-secondary:hover {

    background: #5c636a;

}


/* =========================================================
   CLEAR / LIGHT
========================================================= */

.patient-form-card .btn-light {

    background: #e9ecef;

    color: #343a40;

    border: 1px solid #d5d9dd;

}


.patient-form-card .btn-light:hover {

    background: #dee2e6;

}


/* =========================================================
   SAVE / PRIMARY
========================================================= */

.patient-form-card .btn-primary {

    background: #1f4e78;

    color: #ffffff;

}


.patient-form-card .btn-primary:hover {

    background: #173a5c;

}


/* =========================================================
   DATE INPUT
========================================================= */

.patient-form-card input[type="date"] {

    cursor: pointer;

}


/* =========================================================
   SELECT
========================================================= */

.patient-form-card select {

    cursor: pointer;

}


/* =========================================================
   REQUIRED FIELD VISUAL
========================================================= */

.patient-form-card input:required,
.patient-form-card select:required,
.patient-form-card textarea:required {

    border-color: #cfd6dd;

}


.patient-form-card input:required:focus,
.patient-form-card select:required:focus,
.patient-form-card textarea:required:focus {

    border-color: #1f4e78;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .patient-form-card .form-grid {

        grid-template-columns: 1fr;

        gap: 16px;

    }


    .patient-form-card .form-group-full {

        grid-column: auto;

    }


    .patient-form-card .card-header {

        padding-bottom: 15px;

    }


    .patient-form-card .card-header h2 {

        font-size: 20px;

    }


    .patient-form-card .form-actions {

        justify-content: stretch;

        flex-wrap: wrap;

    }


    .patient-form-card .form-actions .btn {

        flex: 1;

        min-width: 100px;

    }

}

</style>


<main class="main-container">


    <div class="card patient-form-card">


        <!-- =====================================================
             CARD HEADER
        ====================================================== -->

        <div class="card-header">

            <div>

                <h2>
                    New Patient Registration
                </h2>

                <p>
                    Fields marked with
                    <span class="required">*</span>
                    are required.
                </p>

            </div>

        </div>


        <form
            action="save.php"
            method="POST"
            id="patientForm"
        >


            <!-- =================================================
                 PATIENT INFORMATION
            ================================================== -->

            <div class="section-title">
                Patient Information
            </div>


            <div class="form-grid">


                <!-- PATIENT ID -->

                <div class="form-group">

                    <label>
                        Patient ID
                    </label>

                    <input
                        type="text"
                        value="AUTO"
                        readonly
                    >

                </div>


                <!-- DATE REGISTERED -->

                <div class="form-group">

                    <label>
                        Date Registered
                    </label>

                    <input
                        type="date"
                        name="date_registered"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                    <small class="required-help">
                        Required
                    </small>

                </div>


                <!-- LAST NAME -->

                <div class="form-group">

                    <label>

                        Last Name

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        name="last_name"
                        class="uppercase-field"
                        autocomplete="family-name"
                        required
                    >

                    <small class="required-help">
                        Required
                    </small>

                </div>


                <!-- FIRST NAME -->

                <div class="form-group">

                    <label>

                        First Name

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="text"
                        name="first_name"
                        class="uppercase-field"
                        autocomplete="given-name"
                        required
                    >

                    <small class="required-help">
                        Required
                    </small>

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
                    >

                </div>


                <!-- SEX -->

                <div class="form-group">

                    <label>

                        Sex

                        <span class="required">
                            *
                        </span>

                    </label>

                    <select
                        name="sex"
                        required
                    >

                        <option value="">
                            Select Sex
                        </option>

                        <option value="Male">
                            Male
                        </option>

                        <option value="Female">
                            Female
                        </option>

                    </select>

                    <small class="required-help">
                        Required
                    </small>

                </div>


                <!-- BIRTHDATE -->

                <div class="form-group">

                    <label>

                        Birthdate

                        <span class="required">
                            *
                        </span>

                    </label>

                    <input
                        type="date"
                        id="birthdate"
                        name="birthdate"
                        required
                        onchange="calculateAge()"
                    >

                    <small class="required-help">
                        Required
                    </small>

                </div>


                <!-- AGE -->

                <div class="form-group">

                    <label>
                        Age
                    </label>

                    <input
                        type="text"
                        id="age"
                        readonly
                    >

                </div>


                <!-- ADDRESS -->

                <div class="form-group form-group-full">

                    <label>

                        Address

                        <span class="required">
                            *
                        </span>

                    </label>

                    <textarea
                        name="address"
                        id="address"
                        class="uppercase-field"
                        placeholder="Enter address or - if none..."
                        required
                    ></textarea>

                    <small class="required-help">
                        Required — enter <strong>-</strong> if no address is available.
                    </small>

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
                        oninput="
                            this.value =
                            this.value
                            .replace(/[^0-9]/g, '')
                            .slice(0, 11);
                        "
                    >

                </div>


            </div>


            <!-- =================================================
                 FORM BUTTONS
            ================================================== -->

            <div class="form-actions">


                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back
                </a>


                <button
                    type="reset"
                    class="btn btn-light"
                >
                    Clear
                </button>


                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Save Patient
                </button>


            </div>


        </form>


    </div>

</main>


<!-- =========================================================
     PROFESSIONAL FORMATTING
========================================================= -->

<script>


/* =========================================================
   AUTOMATIC UPPERCASE
========================================================= */

/*
 * Uppercase fields will become uppercase while typing.
 *
 * Spaces are NOT removed while typing.
 *
 * Example:
 *
 * JUAN DELA CRUZ
 *
 * can be typed normally.
 *
 * Extra spaces are cleaned only when:
 *
 * 1. Leaving the field
 * 2. Submitting the form
 */

document
    .querySelectorAll(".uppercase-field")
    .forEach(function(field) {


        field.addEventListener(
            "input",
            function() {

                const start =
                    this.selectionStart;

                const end =
                    this.selectionEnd;


                this.value =
                    this.value.toUpperCase();


                /*
                 * Keep cursor position.
                 */

                try {

                    this.setSelectionRange(
                        start,
                        end
                    );

                } catch (e) {}

            }
        );


        /*
         * Clean extra spaces when
         * leaving the field.
         */

        field.addEventListener(
            "blur",
            function() {

                this.value =
                    cleanText(
                        this.value
                    );

            }
        );

    });


/* =========================================================
   CLEAN TEXT
========================================================= */

function cleanText(value) {

    return value
        .replace(/\s+/g, " ")
        .trim()
        .toUpperCase();

}


/* =========================================================
   AGE CALCULATION
========================================================= */

function calculateAge() {


    var birthdate =
        document.getElementById(
            "birthdate"
        ).value;


    var ageField =
        document.getElementById(
            "age"
        );


    if (!birthdate) {

        ageField.value = "";

        return;

    }


    var birthDate =
        new Date(
            birthdate + "T00:00:00"
        );


    var today =
        new Date();


    var age =
        today.getFullYear() -
        birthDate.getFullYear();


    var monthDifference =
        today.getMonth() -
        birthDate.getMonth();


    if (
        monthDifference < 0 ||
        (
            monthDifference === 0 &&
            today.getDate() <
            birthDate.getDate()
        )
    ) {

        age--;

    }


    ageField.value = age;

}


/* =========================================================
   PREVENT FUTURE BIRTHDATE
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    function() {

        var birthdate =
            document.getElementById(
                "birthdate"
            );


        if (birthdate) {

            var today =
                new Date()
                .toISOString()
                .split("T")[0];


            birthdate.max = today;

        }


        /*
         * Automatically calculate age
         * if a birthdate already exists.
         */

        calculateAge();

    }
);


/* =========================================================
   FORM SUBMIT CLEANUP
========================================================= */

document
    .getElementById("patientForm")
    .addEventListener(
        "submit",
        function() {


            /*
             * Clean all uppercase fields.
             */

            document
                .querySelectorAll(
                    ".uppercase-field"
                )
                .forEach(
                    function(field) {

                        field.value =
                            cleanText(
                                field.value
                            );

                    }
                );


            /*
             * Address:
             *
             * If the user enters only spaces,
             * automatically save "-"
             * instead of an empty value.
             *
             * This is an extra protection
             * because Address is required.
             */

            var address =
                document.getElementById(
                    "address"
                );


            if (address) {

                address.value =
                    cleanText(
                        address.value
                    );


                if (
                    address.value === ""
                ) {

                    address.value = "-";

                }

            }

        }
    );


/* =========================================================
   CLEAR FORM
========================================================= */

document
    .getElementById("patientForm")
    .addEventListener(
        "reset",
        function() {

            /*
             * Allow the browser to reset first.
             * Then restore today's date.
             */

            setTimeout(
                function() {

                    var dateRegistered =
                        document.querySelector(
                            'input[name="date_registered"]'
                        );


                    if (dateRegistered) {

                        dateRegistered.value =
                            "<?php echo date('Y-m-d'); ?>";

                    }


                    var ageField =
                        document.getElementById(
                            "age"
                        );


                    if (ageField) {

                        ageField.value = "";

                    }

                },
                0
            );

        }
    );


</script>


<?php

// =========================================================
// SHARED FOOTER
// =========================================================

require_once "../includes/footer.php";

?>