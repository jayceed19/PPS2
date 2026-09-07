<?php

require_once "../config/database.php";


/* =========================================================
   GET MEDICINE ID
========================================================= */

$medicineId = isset($_GET["id"])
    ? (int) $_GET["id"]
    : 0;

if ($medicineId <= 0) {
    die("Invalid medicine ID.");
}


/* =========================================================
   GET MEDICINE + PRESCRIPTION + PATIENT
========================================================= */

$sql = "
    SELECT
        pr.id,
        pr.prescription_header_id,
        pr.consultation_id,
        pr.patient_id,
        pr.medicine_name,
        pr.strength,
        pr.quantity,
        pr.breakfast,
        pr.lunch,
        pr.dinner,
        pr.prescribed_date,

        ph.prescribed_date AS header_date,

        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name

    FROM prescriptions pr

    INNER JOIN prescription_headers ph
        ON ph.id = pr.prescription_header_id

    INNER JOIN patients p
        ON p.id = pr.patient_id

    WHERE pr.id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $medicineId
);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows == 0) {

    $stmt->close();
    $conn->close();

    die("Medicine not found.");
}


$medicine = $result->fetch_assoc();


$stmt->close();


/* =========================================================
   VARIABLES
========================================================= */

$headerId =
    (int) $medicine["prescription_header_id"];

$patientId =
    (int) $medicine["patient_id"];


$fullName =
    $medicine["first_name"];


if (
    !empty(
        $medicine["middle_name"]
    )
) {

    $fullName .=
        " " .
        $medicine["middle_name"];

}


$fullName .=
    " " .
    $medicine["last_name"];


$fullName =
    trim($fullName);


/* =========================================================
   FORM VALUES
========================================================= */

$medicineName =
    isset($medicine["medicine_name"])
        ? $medicine["medicine_name"]
        : "";


$strength =
    isset($medicine["strength"])
        ? $medicine["strength"]
        : "";


$quantity =
    isset($medicine["quantity"])
        ? $medicine["quantity"]
        : "";


$breakfast =
    isset($medicine["breakfast"])
        ? $medicine["breakfast"]
        : "";


$lunch =
    isset($medicine["lunch"])
        ? $medicine["lunch"]
        : "";


$dinner =
    isset($medicine["dinner"])
        ? $medicine["dinner"]
        : "";


$prescribedDateFormatted = "-";


if (
    !empty(
        $medicine["header_date"]
    )
) {

    $prescribedDateFormatted =
        date(
            "F d, Y",
            strtotime(
                $medicine["header_date"]
            )
        );

}


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle =
    "Edit Medicine";

$pageSubtitle =
    "Edit Prescription Medicine";

$basePath =
    "../";

$activePage =
    "patients";


include "../includes/header.php";

include "../includes/navigation.php";
require_once "../config/auth.php";
?>

<style>

/* =========================================================
   MAIN
========================================================= */

.edit-medicine-page {

    max-width: 900px;

    margin: 30px auto;

    padding:
        0 20px 40px;

}


/* =========================================================
   PAGE HEADER
========================================================= */

.page-header {

    margin-bottom:
        20px;

}


.page-header h2 {

    margin:
        0;

    color:
        #1f4e78;

    font-size:
        26px;

}


.page-header p {

    margin:
        5px 0 0;

    color:
        #666;

    font-size:
        14px;

}


/* =========================================================
   CARD
========================================================= */

.card {

    background:
        #ffffff;

    border:
        1px solid #d9e2ec;

    border-radius:
        10px;

    padding:
        24px;

    margin-bottom:
        20px;

    box-shadow:
        0 2px 8px
        rgba(0,0,0,0.05);

}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-info {

    display:
        grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap:
        15px;

    padding-bottom:
        20px;

    margin-bottom:
        20px;

    border-bottom:
        1px solid #e1e5e9;

}


.info-label {

    display:
        block;

    margin-bottom:
        4px;

    color:
        #777;

    font-size:
        11px;

    font-weight:
        700;

    text-transform:
        uppercase;

}


.info-value {

    color:
        #222;

    font-size:
        14px;

    font-weight:
        600;

}


/* =========================================================
   SECTION TITLE
========================================================= */

.section-title {

    margin:
        0 0 18px;

    color:
        #1f4e78;

    font-size:
        18px;

    font-weight:
        700;

}


/* =========================================================
   FORM GRID
========================================================= */

.form-grid {

    display:
        grid;

    grid-template-columns:
        2fr 1fr;

    gap:
        18px;

}


.form-group {

    display:
        flex;

    flex-direction:
        column;

}


.form-group.full {

    grid-column:
        1 / -1;

}


.form-group label {

    margin-bottom:
        7px;

    color:
        #444;

    font-size:
        13px;

    font-weight:
        700;

}


.required {

    color:
        #dc3545;

}


.form-control {

    width:
        100%;

    height:
        42px;

    box-sizing:
        border-box;

    padding:
        0 12px;

    border:
        1px solid #cfd6dd;

    border-radius:
        6px;

    background:
        #ffffff;

    color:
        #222;

    font-size:
        14px;

    outline:
        none;

    transition:
        0.2s ease;

}


.form-control:focus {

    border-color:
        #1f4e78;

    box-shadow:
        0 0 0 2px
        rgba(31,78,120,0.10);

}


.form-help {

    margin-top:
        5px;

    color:
        #777;

    font-size:
        11px;

}


/* =========================================================
   MEAL DOSE
========================================================= */

.meal-section {

    margin-top:
        22px;

}


.meal-grid {

    display:
        grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap:
        15px;

}


.meal-card {

    background:
        #f8fafc;

    border:
        1px solid #dfe5ea;

    border-radius:
        7px;

    padding:
        14px;

}


.meal-card label {

    display:
        block;

    margin-bottom:
        7px;

    color:
        #1f4e78;

    font-size:
        12px;

    font-weight:
        700;

    text-transform:
        uppercase;

}


/* =========================================================
   BUTTONS
========================================================= */

.form-actions {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        10px;

    margin-top:
        25px;

    padding-top:
        20px;

    border-top:
        1px solid #e1e5e9;

}


.action-left,
.action-right {

    display:
        flex;

    gap:
        10px;

    flex-wrap:
        wrap;

}


.btn {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    min-height:
        40px;

    padding:
        0 16px;

    border:
        none;

    border-radius:
        6px;

    text-decoration:
        none;

    cursor:
        pointer;

    font-size:
        13px;

    font-weight:
        600;

    box-sizing:
        border-box;

    transition:
        0.2s ease;

}


.btn-secondary {

    background:
        #6c757d;

    color:
        #ffffff;

}


.btn-secondary:hover {

    background:
        #5a6268;

}


.btn-primary {

    background:
        #1f4e78;

    color:
        #ffffff;

}


.btn-primary:hover {

    background:
        #173a5c;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .edit-medicine-page {

        padding:
            0 12px 30px;

    }


    .patient-info {

        grid-template-columns:
            1fr;

    }


    .form-grid {

        grid-template-columns:
            1fr;

    }


    .form-group.full {

        grid-column:
            auto;

    }


    .meal-grid {

        grid-template-columns:
            1fr;

    }


    .form-actions {

        flex-direction:
            column;

        align-items:
            stretch;

    }


    .action-left,
    .action-right {

        width:
            100%;

        flex-direction:
            column;

    }


    .btn {

        width:
            100%;

    }

}

</style>


<main class="main-container">

    <div class="edit-medicine-page">


        <!-- =====================================================
             PAGE HEADER
        ====================================================== -->

        <div class="page-header">

            <h2>
                Edit Medicine
            </h2>

            <p>
                Update the medicine details for this prescription.
            </p>

        </div>


        <!-- =====================================================
             PATIENT INFORMATION
        ====================================================== -->

        <div class="card">


            <div class="patient-info">


                <div>

                    <span class="info-label">
                        Patient ID
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $medicine["patient_number"]
                        );

                        ?>

                    </div>

                </div>


                <div>

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


                <div>

                    <span class="info-label">
                        Prescription
                    </span>

                    <div class="info-value">

                        RX-<?php

                        echo str_pad(
                            $headerId,
                            6,
                            "0",
                            STR_PAD_LEFT
                        );

                        ?>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                action="update_medicine.php"
                id="editMedicineForm"
                autocomplete="off"
            >


                <input
                    type="hidden"
                    name="id"
                    value="<?php echo $medicineId; ?>"
                >


                <input
                    type="hidden"
                    name="prescription_header_id"
                    value="<?php echo $headerId; ?>"
                >


                <input
                    type="hidden"
                    name="patient_id"
                    value="<?php echo $patientId; ?>"
                >


                <div class="section-title">

                    Medicine Information

                </div>


                <div class="form-grid">


                    <!-- MEDICINE NAME -->

                    <div class="form-group">

                        <label for="medicine_name">

                            Medicine Name

                            <span class="required">
                                *
                            </span>

                        </label>

                        <input
                            type="text"
                            id="medicine_name"
                            name="medicine_name"
                            class="form-control"
                            value="<?php echo htmlspecialchars($medicineName); ?>"
                            maxlength="150"
                            required
                        >

                    </div>


                    <!-- STRENGTH -->

                    <div class="form-group">

                        <label for="strength">
                            Strength
                        </label>

                        <input
                            type="text"
                            id="strength"
                            name="strength"
                            class="form-control"
                            value="<?php echo htmlspecialchars($strength); ?>"
                            maxlength="100"
                            placeholder="e.g. 500 mg"
                        >

                    </div>


                    <!-- QUANTITY -->

                    <div class="form-group">

                        <label for="quantity">
                            Quantity
                        </label>

                        <input
                            type="number"
                            id="quantity"
                            name="quantity"
                            class="form-control"
                            value="<?php echo htmlspecialchars($quantity); ?>"
                            min="1"
                            step="1"
                            inputmode="numeric"
                            placeholder="e.g. 10"
                        >

                        <div class="form-help">
                            Enter numbers only.
                        </div>

                    </div>


                </div>


                <!-- =================================================
                     MEAL DOSES
                ================================================== -->

                <div class="meal-section">


                    <div class="section-title">

                        Dose Schedule

                    </div>


                    <div class="meal-grid">


                        <!-- BREAKFAST -->

                        <div class="meal-card">

                            <label for="breakfast">
                                Breakfast
                            </label>

                            <input
                                type="text"
                                id="breakfast"
                                name="breakfast"
                                class="form-control"
                                value="<?php echo htmlspecialchars($breakfast); ?>"
                                maxlength="30"
                                placeholder="e.g. 1 tab"
                            >

                        </div>


                        <!-- LUNCH -->

                        <div class="meal-card">

                            <label for="lunch">
                                Lunch
                            </label>

                            <input
                                type="text"
                                id="lunch"
                                name="lunch"
                                class="form-control"
                                value="<?php echo htmlspecialchars($lunch); ?>"
                                maxlength="30"
                                placeholder="e.g. 1 tab"
                            >

                        </div>


                        <!-- DINNER -->

                        <div class="meal-card">

                            <label for="dinner">
                                Dinner
                            </label>

                            <input
                                type="text"
                                id="dinner"
                                name="dinner"
                                class="form-control"
                                value="<?php echo htmlspecialchars($dinner); ?>"
                                maxlength="30"
                                placeholder="e.g. 1 tab"
                            >

                        </div>


                    </div>


                    <div class="form-help">

                        Example: Breakfast = 1 tab, Lunch = 1 tab, Dinner = 1 tab.

                    </div>


                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="form-actions">


                    <div class="action-left">

                        <a
                            href="manage_prescription.php?prescription_header_id=<?php echo $headerId; ?>"
                            class="btn btn-secondary"
                        >
                            ← Back to Manage Prescription
                        </a>

                    </div>


                    <div class="action-right">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            💾 Save Changes
                        </button>

                    </div>


                </div>


            </form>


        </div>


    </div>

</main>


<script>

/* =========================================================
   UPPERCASE TEXT FIELDS
========================================================= */

function cleanText(value) {

    return value
        .toUpperCase()
        .replace(/\s+/g, " ")
        .trim();

}


var textFields = [
    "medicine_name",
    "strength",
    "breakfast",
    "lunch",
    "dinner"
];


for (
    var i = 0;
    i < textFields.length;
    i++
) {

    var field =
        document.getElementById(
            textFields[i]
        );

    if (!field) {
        continue;
    }


    field.addEventListener(
        "input",
        function() {

            this.value =
                this.value.toUpperCase();

        }
    );


    field.addEventListener(
        "blur",
        function() {

            this.value =
                cleanText(
                    this.value
                );

        }
    );

}


/* =========================================================
   QUANTITY VALIDATION
========================================================= */

var quantity =
    document.getElementById(
        "quantity"
    );


if (quantity) {

    quantity.addEventListener(
        "input",
        function() {

            this.value =
                this.value.replace(
                    /[^0-9]/g,
                    ""
                );

        }
    );

}


/* =========================================================
   FORM SUBMIT
========================================================= */

var form =
    document.getElementById(
        "editMedicineForm"
    );


if (form) {

    form.addEventListener(
        "submit",
        function(event) {


            var medicineName =
                document.getElementById(
                    "medicine_name"
                );


            var qty =
                document.getElementById(
                    "quantity"
                );


            if (
                !medicineName
                ||
                cleanText(
                    medicineName.value
                ) === ""
            ) {

                event.preventDefault();

                alert(
                    "Please enter the medicine name."
                );

                if (medicineName) {
                    medicineName.focus();
                }

                return;

            }


            if (
                qty
                &&
                qty.value !== ""
                &&
                parseInt(
                    qty.value,
                    10
                ) < 1
            ) {

                event.preventDefault();

                alert(
                    "Quantity must be at least 1."
                );

                qty.focus();

                return;

            }


            medicineName.value =
                cleanText(
                    medicineName.value
                );


            var strength =
                document.getElementById(
                    "strength"
                );


            var breakfast =
                document.getElementById(
                    "breakfast"
                );


            var lunch =
                document.getElementById(
                    "lunch"
                );


            var dinner =
                document.getElementById(
                    "dinner"
                );


            if (strength) {

                strength.value =
                    cleanText(
                        strength.value
                    );

            }


            if (breakfast) {

                breakfast.value =
                    cleanText(
                        breakfast.value
                    );

            }


            if (lunch) {

                lunch.value =
                    cleanText(
                        lunch.value
                    );

            }


            if (dinner) {

                dinner.value =
                    cleanText(
                        dinner.value
                    );

            }

        }
    );

}

</script>


<?php

$conn->close();

include "../includes/footer.php";

?>