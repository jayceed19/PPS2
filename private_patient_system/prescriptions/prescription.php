<?php

require_once "../config/database.php";

$consultationId = isset($_GET["consultation_id"])
    ? (int) $_GET["consultation_id"]
    : 0;

if ($consultationId <= 0) {
    die("Invalid consultation ID.");
}


/*
|--------------------------------------------------------------------------
| GET CONSULTATION + PATIENT
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        c.id AS consultation_id,
        c.patient_id,
        c.visit_date,
        c.chief_complaint,
        c.follow_up_date,
        p.id AS patient_database_id,
        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.address,
        p.contact_no
    FROM consultations c
    INNER JOIN patients p
        ON c.patient_id = p.id
    WHERE c.id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $consultationId);
$stmt->execute();

$result = $stmt->get_result();
$consultation = $result->fetch_assoc();

$stmt->close();


if (!$consultation) {
    die("Consultation not found.");
}


/*
|--------------------------------------------------------------------------
| PATIENT INFORMATION
|--------------------------------------------------------------------------
*/

$patientId = (int) $consultation["patient_id"];


$fullName = trim(
    $consultation["first_name"] . " " .
    $consultation["middle_name"] . " " .
    $consultation["last_name"]
);

$fullName = preg_replace('/\s+/', ' ', $fullName);


/*
|--------------------------------------------------------------------------
| AGE
|--------------------------------------------------------------------------
*/

$age = "";

if (!empty($consultation["birthdate"])) {

    try {

        $birthDate = new DateTime(
            $consultation["birthdate"]
        );

        $today = new DateTime();

        $age = $birthDate->diff($today)->y;

    } catch (Exception $e) {

        $age = "";

    }

}


/*
|--------------------------------------------------------------------------
| OTHER PATIENT INFORMATION
|--------------------------------------------------------------------------
*/

$sex = !empty($consultation["sex"])
    ? $consultation["sex"]
    : "";

$address = !empty($consultation["address"])
    ? $consultation["address"]
    : "";

$contactNo = !empty($consultation["contact_no"])
    ? $consultation["contact_no"]
    : "";

$visitDate = !empty($consultation["visit_date"])
    ? date(
        "F j, Y",
        strtotime($consultation["visit_date"])
    )
    : "";

$chiefComplaint = !empty(
    $consultation["chief_complaint"]
)
    ? $consultation["chief_complaint"]
    : "";


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

$pageTitle = "New Prescription";
$pageSubtitle = "Prescription Management";
$basePath = "../";
$activePage = "consultations";

include "../includes/header.php";
include "../includes/navigation.php";
require_once "../config/auth.php";
?>

<style>

/* =========================================================
   MAIN CONTAINER
========================================================= */

.prescription-container {
    max-width: 1200px;
    margin: 30px auto;
    padding: 0 20px 40px;
}


/* =========================================================
   PAGE TOP
========================================================= */

.page-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.page-top h2 {
    margin: 0;
    color: #1f4e78;
    font-size: 26px;
}

.page-top p {
    margin: 5px 0 0;
    color: #666;
    font-size: 14px;
}


/* =========================================================
   BUTTONS
========================================================= */

.btn {
    display: inline-block;
    padding: 10px 16px;
    border-radius: 6px;
    text-decoration: none;
    border: none;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background: #5a6268;
}

.btn-primary {
    background: #1f4e78;
    color: white;
}

.btn-primary:hover {
    background: #173a5c;
}

.btn-success {
    background: #198754;
    color: white;
}

.btn-success:hover {
    background: #157347;
}

.btn-danger {
    background: #dc3545;
    color: white;
}

.btn-danger:hover {
    background: #bb2d3b;
}


/* =========================================================
   PATIENT CARD
========================================================= */

.patient-card {
    background: white;
    border: 1px solid #d9e2ec;
    border-radius: 10px;
    padding: 22px;
    margin-bottom: 25px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.patient-card-title {
    color: #1f4e78;
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 15px;
    border-bottom: 2px solid #1f4e78;
    padding-bottom: 8px;
}

.patient-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px 25px;
}

.info-item {
    min-width: 0;
}

.info-label {
    display: block;
    font-size: 12px;
    color: #777;
    margin-bottom: 4px;
    font-weight: 600;
    text-transform: uppercase;
}

.info-value {
    font-size: 15px;
    color: #222;
    word-break: break-word;
}


/* =========================================================
   PRESCRIPTION CARD
========================================================= */

.prescription-card {
    background: white;
    border: 1px solid #d9e2ec;
    border-radius: 10px;
    padding: 22px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.section-title {
    color: #1f4e78;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 18px;
}


/* =========================================================
   NOTICE
========================================================= */

.notice {
    background: #eef6ff;
    border-left: 4px solid #1f4e78;
    padding: 13px 15px;
    border-radius: 5px;
    color: #444;
    font-size: 13px;
    margin-bottom: 20px;
    line-height: 1.5;
}


/* =========================================================
   MEDICINE ROW
========================================================= */

.medicine-row {
    border: 1px solid #d7dee7;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 18px;
    background: #fafcff;
}

.medicine-row-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 18px;
    gap: 10px;
}

.medicine-number {
    font-size: 16px;
    font-weight: 700;
    color: #1f4e78;
}


/* =========================================================
   MEDICINE + STRENGTH + QUANTITY
========================================================= */

.form-grid {
    display: grid;
    grid-template-columns: 2fr 1.2fr 1fr;
    gap: 15px;
    margin-bottom: 20px;
}


/* =========================================================
   MEAL GRID
========================================================= */

.meal-title {
    color: #1f4e78;
    font-size: 15px;
    font-weight: 700;
    margin: 5px 0 10px;
}

.meal-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 10px;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-size: 13px;
    font-weight: 700;
    color: #444;
    margin-bottom: 6px;
}

.form-group input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 11px;
    border: 1px solid #cbd5df;
    border-radius: 6px;
    font-size: 14px;
    background: white;
    outline: none;
}

.form-group input:focus {
    border-color: #1f4e78;
    box-shadow:
        0 0 0 2px
        rgba(31,78,120,0.10);
}

.meal-help {
    font-size: 12px;
    color: #777;
    margin-top: 5px;
}


/* =========================================================
   REMOVE BUTTON
========================================================= */

.remove-button {
    padding: 7px 11px;
    font-size: 13px;
}


/* =========================================================
   ADD MEDICINE
========================================================= */

.add-medicine-container {
    margin-top: 5px;
    margin-bottom: 25px;
}


/* =========================================================
   FORM ACTIONS
========================================================= */

.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
    flex-wrap: wrap;
}

.form-actions-left,
.form-actions-right {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 900px) {

    .patient-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .form-grid {
        grid-template-columns:
            repeat(2, 1fr);
    }

    .meal-grid {
        grid-template-columns: 1fr;
    }

}


@media (max-width: 600px) {

    .prescription-container {
        padding:
            0 12px 30px;
    }

    .patient-grid,
    .form-grid,
    .meal-grid {
        grid-template-columns: 1fr;
    }

    .medicine-row {
        padding: 15px;
    }

    .page-top {
        align-items: flex-start;
    }

    .form-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .form-actions-left,
    .form-actions-right {
        width: 100%;
    }

    .form-actions .btn {
        width: 100%;
        text-align: center;
    }

}

</style>


<div class="prescription-container">


    <!-- =====================================================
         PAGE TOP
    ====================================================== -->

    <div class="page-top">

        <div>

            <h2>
                New Prescription
            </h2>

            <p>
                Create prescription for this consultation.
            </p>

        </div>


        <div>

            <a
                href="../patients/consultation_view.php?id=<?php echo $consultationId; ?>"
                class="btn btn-secondary"
            >
                ← Back to Consultation
            </a>

        </div>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="patient-card">

        <div class="patient-card-title">
            Patient Information
        </div>


        <div class="patient-grid">


            <div class="info-item">

                <span class="info-label">
                    Patient ID
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $consultation["patient_number"]
                    );

                    ?>

                </div>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Patient Name
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $fullName
                    );

                    ?>

                </div>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Age / Sex
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars($age);

                    if (
                        $age !== ""
                        &&
                        $sex !== ""
                    ) {

                        echo " / ";

                    }

                    echo htmlspecialchars($sex);

                    ?>

                </div>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Visit Date
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $visitDate
                    );

                    ?>

                </div>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Contact No.
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $contactNo
                    );

                    ?>

                </div>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Address
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $address
                    );

                    ?>

                </div>

            </div>


            <div class="info-item">

                <span class="info-label">
                    Chief Complaint
                </span>

                <div class="info-value">

                    <?php

                    echo htmlspecialchars(
                        $chiefComplaint
                    );

                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         PRESCRIPTION FORM
    ====================================================== -->

    <div class="prescription-card">


        <div class="section-title">
            Prescription Details
        </div>


        <div class="notice">

            <strong>Prescription Rule:</strong>

            Ang lahat ng medicines na ise-save sa
            parehong patient at parehong date ay
            mapupunta sa iisang prescription.

            <br>
            <br>

            <strong>Dose per Meal:</strong>

            Ilagay mismo ang dose na iinumin sa bawat meal.

            Halimbawa:

            <strong>1 TAB</strong>,
            <strong>1 CAP</strong>,
            <strong>5 ML</strong>.

            Iwanang blanko kung walang dose sa meal na iyon.

        </div>


        <form
            method="POST"
            action="save_prescription.php"
            id="prescriptionForm"
        >


            <!-- PATIENT -->

            <input
                type="hidden"
                name="consultation_id"
                value="<?php echo $consultationId; ?>"
            >


            <input
                type="hidden"
                name="patient_id"
                value="<?php echo $patientId; ?>"
            >


            <div id="medicineContainer">


                <!-- =================================================
                     MEDICINE #1
                ================================================== -->

                <div class="medicine-row">


                    <div class="medicine-row-header">

                        <div class="medicine-number">
                            Medicine #1
                        </div>

                    </div>


                    <!-- MEDICINE / STRENGTH / QUANTITY -->

                    <div class="form-grid">


                        <div class="form-group">

                            <label>
                                Medicine Name
                            </label>

                            <input
                                type="text"
                                name="medicine_name[]"
                                class="uppercase-field"
                                placeholder="e.g. CETIRIZINE"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Strength
                            </label>

                            <input
                                type="text"
                                name="strength[]"
                                class="uppercase-field"
                                placeholder="e.g. 10 MG"
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Quantity
                            </label>

                            <input
                                type="number"
                                name="quantity[]"
                                placeholder="e.g. 10"
                                min="1"
                                step="1"
                                inputmode="numeric"
                            >

                        </div>


                    </div>


                    <!-- =================================================
                         DOSE PER MEAL
                    ================================================== -->

                    <div class="meal-title">
                        Dose per Meal
                    </div>


                    <div class="meal-grid">


                        <!-- BREAKFAST -->

                        <div class="form-group">

                            <label>
                                Breakfast
                            </label>

                            <input
                                type="text"
                                name="breakfast[]"
                                class="uppercase-field"
                                placeholder="e.g. 1 TAB"
                            >

                            <div class="meal-help">
                                Halimbawa:
                                1 TAB
                            </div>

                        </div>


                        <!-- LUNCH -->

                        <div class="form-group">

                            <label>
                                Lunch
                            </label>

                            <input
                                type="text"
                                name="lunch[]"
                                class="uppercase-field"
                                placeholder="e.g. 1 TAB"
                            >

                            <div class="meal-help">
                                Halimbawa:
                                1 TAB
                            </div>

                        </div>


                        <!-- DINNER -->

                        <div class="form-group">

                            <label>
                                Dinner
                            </label>

                            <input
                                type="text"
                                name="dinner[]"
                                class="uppercase-field"
                                placeholder="e.g. 1 TAB"
                            >

                            <div class="meal-help">
                                Halimbawa:
                                1 TAB
                            </div>

                        </div>


                    </div>


                </div>


            </div>


            <!-- =====================================================
                 ADD MEDICINE
            ====================================================== -->

            <div class="add-medicine-container">

                <button
                    type="button"
                    class="btn btn-success"
                    id="addMedicineBtn"
                >
                    + Add Medicine
                </button>

            </div>


            <!-- =====================================================
                 ACTIONS
            ====================================================== -->

            <div class="form-actions">


                <div class="form-actions-left">

                    <a
                        href="../patients/consultation_view.php?id=<?php echo $consultationId; ?>"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>


                <div class="form-actions-right">

                    <button
                        type="submit"
                        class="btn btn-primary"
                    >
                        Save Prescription
                    </button>

                </div>


            </div>


        </form>


    </div>


</div>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const container =
            document.getElementById(
                "medicineContainer"
            );


        const addButton =
            document.getElementById(
                "addMedicineBtn"
            );


        /*
        |--------------------------------------------------------------------------
        | UPPERCASE TEXT FIELDS
        |--------------------------------------------------------------------------
        */

        function applyUppercase(field) {

            if (!field) {
                return;
            }

            field.addEventListener(
                "input",
                function () {

                    const start =
                        field.selectionStart;

                    const end =
                        field.selectionEnd;

                    field.value =
                        field.value.toUpperCase();

                    /*
                        Restore cursor position
                    */

                    try {

                        field.setSelectionRange(
                            start,
                            end
                        );

                    } catch (e) {

                    }

                }
            );

        }


        function applyUppercaseToRow(row) {

            const fields =
                row.querySelectorAll(
                    ".uppercase-field"
                );

            fields.forEach(
                function (field) {

                    applyUppercase(field);

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | QUANTITY NUMBERS ONLY
        |--------------------------------------------------------------------------
        */

        function applyQuantityValidation(row) {

            const quantityInputs =
                row.querySelectorAll(
                    'input[name="quantity[]"]'
                );

            quantityInputs.forEach(
                function (input) {

                    input.addEventListener(
                        "input",
                        function () {

                            input.value =
                                input.value.replace(
                                    /[^0-9]/g,
                                    ""
                                );

                        }
                    );

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | UPDATE MEDICINE NUMBERS
        |--------------------------------------------------------------------------
        */

        function updateMedicineNumbers() {

            const rows =
                container.querySelectorAll(
                    ".medicine-row"
                );


            rows.forEach(
                function (row, index) {

                    const numberLabel =
                        row.querySelector(
                            ".medicine-number"
                        );


                    if (numberLabel) {

                        numberLabel.textContent =
                            "Medicine #" +
                            (index + 1);

                    }

                }
            );

        }


        /*
        |--------------------------------------------------------------------------
        | ADD MEDICINE ROW
        |--------------------------------------------------------------------------
        */

        function addMedicineRow() {


            const rows =
                container.querySelectorAll(
                    ".medicine-row"
                );


            if (rows.length === 0) {
                return;
            }


            const firstRow =
                rows[0];


            const newRow =
                firstRow.cloneNode(true);


            /*
            |--------------------------------------------------------------------------
            | CLEAR INPUTS
            |--------------------------------------------------------------------------
            */

            newRow.querySelectorAll(
                "input"
            ).forEach(
                function (input) {

                    input.value = "";

                }
            );


            /*
            |--------------------------------------------------------------------------
            | ADD REMOVE BUTTON
            |--------------------------------------------------------------------------
            */

            const header =
                newRow.querySelector(
                    ".medicine-row-header"
                );


            if (header) {


                const removeButton =
                    document.createElement(
                        "button"
                    );


                removeButton.type =
                    "button";


                removeButton.className =
                    "btn btn-danger remove-button";


                removeButton.textContent =
                    "Remove";


                removeButton.addEventListener(
                    "click",
                    function () {


                        const currentRows =
                            container.querySelectorAll(
                                ".medicine-row"
                            );


                        if (
                            currentRows.length > 1
                        ) {

                            newRow.remove();

                            updateMedicineNumbers();

                        }

                    }
                );


                header.appendChild(
                    removeButton
                );

            }


            container.appendChild(
                newRow
            );


            /*
            |--------------------------------------------------------------------------
            | APPLY INPUT FUNCTIONS TO NEW ROW
            |--------------------------------------------------------------------------
            */

            applyUppercaseToRow(
                newRow
            );

            applyQuantityValidation(
                newRow
            );


            updateMedicineNumbers();

        }


        /*
        |--------------------------------------------------------------------------
        | ADD MEDICINE BUTTON
        |--------------------------------------------------------------------------
        */

        addButton.addEventListener(
            "click",
            function () {

                addMedicineRow();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | INITIAL ROW FUNCTIONS
        |--------------------------------------------------------------------------
        */

        const firstRow =
            container.querySelector(
                ".medicine-row"
            );


        if (firstRow) {

            applyUppercaseToRow(
                firstRow
            );

            applyQuantityValidation(
                firstRow
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FORM VALIDATION
        |--------------------------------------------------------------------------
        */

        document
            .getElementById(
                "prescriptionForm"
            )
            .addEventListener(
                "submit",
                function (event) {


                    const medicineNames =
                        container.querySelectorAll(
                            'input[name="medicine_name[]"]'
                        );


                    let hasMedicine =
                        false;


                    medicineNames.forEach(
                        function (input) {


                            if (
                                input.value.trim()
                                !== ""
                            ) {

                                hasMedicine =
                                    true;

                            }

                        }
                    );


                    if (!hasMedicine) {

                        event.preventDefault();

                        alert(
                            "Please enter at least one medicine."
                        );

                        return;

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | VALIDATE EACH MEDICINE
                    |--------------------------------------------------------------------------
                    */

                    const rows =
                        container.querySelectorAll(
                            ".medicine-row"
                        );


                    let invalidRow =
                        false;


                    rows.forEach(
                        function (row) {


                            const medicineInput =
                                row.querySelector(
                                    'input[name="medicine_name[]"]'
                                );


                            if (
                                medicineInput
                                &&
                                medicineInput.value.trim()
                                === ""
                            ) {

                                invalidRow =
                                    true;

                            }


                            /*
                            |--------------------------------------------------------------------------
                            | FINAL UPPERCASE
                            |--------------------------------------------------------------------------
                            */

                            row.querySelectorAll(
                                ".uppercase-field"
                            ).forEach(
                                function (field) {

                                    field.value =
                                        field.value
                                            .trim()
                                            .replace(
                                                /\s+/g,
                                                " "
                                            )
                                            .toUpperCase();

                                }
                            );


                            /*
                            |--------------------------------------------------------------------------
                            | FINAL QUANTITY CLEANING
                            |--------------------------------------------------------------------------
                            */

                            const quantityInput =
                                row.querySelector(
                                    'input[name="quantity[]"]'
                                );


                            if (quantityInput) {

                                quantityInput.value =
                                    quantityInput.value.replace(
                                        /[^0-9]/g,
                                        ""
                                    );

                            }

                        }
                    );


                    if (invalidRow) {

                        event.preventDefault();

                        alert(
                            "Please enter the Medicine Name for every medicine row."
                        );

                        return;

                    }

                }
            );


        /*
        |--------------------------------------------------------------------------
        | INITIAL NUMBER
        |--------------------------------------------------------------------------
        */

        updateMedicineNumbers();

    }

);

</script>


<?php

include "../includes/footer.php";

?>