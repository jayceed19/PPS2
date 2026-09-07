<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   GET LABORATORY ID
========================================================= */

$labId = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($labId <= 0) {
    die("Invalid laboratory ID.");
}


/* =========================================================
   GET LABORATORY RESULT + PATIENT
========================================================= */

$sql = "
    SELECT
        l.id,
        l.patient_id,
        l.test_date,
        l.test_name,
        l.result,
        l.unit,
        l.reference_range,

        p.patient_id AS patient_code,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.address

    FROM laboratory l

    INNER JOIN patients p
        ON p.id = l.patient_id

    WHERE l.id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $labId
);

$stmt->execute();

$result = $stmt->get_result();

$lab = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   CHECK RECORD
========================================================= */

if (!$lab) {
    die("Laboratory result not found.");
}


/* =========================================================
   PATIENT NAME
========================================================= */

$fullName =
    trim($lab['first_name']);

if (!empty($lab['middle_name'])) {

    $fullName .= " "
        . trim($lab['middle_name']);
}

$fullName .= " "
    . trim($lab['last_name']);


/* =========================================================
   AGE
========================================================= */

$age = "-";

if (!empty($lab['birthdate'])) {

    $birthDate = new DateTime(
        $lab['birthdate']
    );

    $today = new DateTime();

    $age = $today->diff(
        $birthDate
    )->y;
}


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Edit Laboratory";
$pageSubtitle = "Edit Laboratory Result";

$basePath = "../";
$activePage = "patients";


/* =========================================================
   SHARED HEADER
========================================================= */

include __DIR__ . "/../includes/header.php";


/* =========================================================
   SHARED NAVIGATION
========================================================= */

include __DIR__ . "/../includes/navigation.php";
require_once "../config/auth.php";
?>

<style>

/* =========================================================
   PAGE
========================================================= */

.lab-edit-page {

    max-width: 900px;

    margin: 25px auto 50px;

    padding: 0 20px;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.lab-edit-header {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 20px;

    margin-bottom: 20px;
}

.lab-edit-title h2 {

    margin: 0;

    color: #1f2937;

    font-size: 28px;

    font-weight: 700;
}

.lab-edit-title p {

    margin: 6px 0 0;

    color: #6b7280;

    font-size: 14px;
}


/* =========================================================
   HEADER ACTIONS
========================================================= */

.lab-edit-actions {

    display: flex;

    gap: 8px;

    flex-wrap: wrap;
}

.lab-action-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

    min-height: 40px;

    padding: 0 15px;

    border-radius: 7px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;

    border: 1px solid transparent;

    cursor: pointer;
}

.back-btn {

    background: #f3f4f6;

    color: #374151;

    border-color: #d1d5db;
}

.back-btn:hover {

    background: #e5e7eb;
}


/* =========================================================
   MAIN CARD
========================================================= */

.edit-card {

    background: #ffffff;

    border: 1px solid #dbe3ea;

    border-radius: 10px;

    overflow: hidden;

    box-shadow:
        0 2px 8px rgba(0,0,0,0.04);
}


/* =========================================================
   CARD HEADER
========================================================= */

.edit-card-header {

    background: #0f5f9f;

    color: #ffffff;

    padding: 18px 22px;
}

.edit-card-header-title {

    font-size: 18px;

    font-weight: 700;

    margin-bottom: 4px;
}

.edit-card-header-subtitle {

    font-size: 12px;

    opacity: 0.9;
}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-information {

    padding: 20px 22px;

    border-bottom: 1px solid #e5e7eb;

    background: #ffffff;
}

.patient-grid {

    display: grid;

    grid-template-columns:
        1.6fr
        0.8fr
        0.8fr;

    gap: 20px;
}

.info-label {

    display: block;

    font-size: 10px;

    font-weight: 700;

    color: #6b7280;

    text-transform: uppercase;

    letter-spacing: 0.4px;

    margin-bottom: 5px;
}

.info-value {

    font-size: 14px;

    font-weight: 600;

    color: #111827;

    word-break: break-word;
}


/* =========================================================
   LAB DATE
========================================================= */

.lab-date-box {

    margin: 20px 22px;

    padding: 14px 16px;

    background: #f8fafc;

    border: 1px solid #dbe3ea;

    border-left: 4px solid #0f5f9f;

    border-radius: 7px;
}

.lab-date-label {

    color: #64748b;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    margin-bottom: 3px;
}

.lab-date-value {

    color: #111827;

    font-size: 16px;

    font-weight: 700;
}


/* =========================================================
   FORM
========================================================= */

.edit-form {

    padding: 5px 22px 25px;
}

.form-group {

    margin-bottom: 18px;
}

.form-label {

    display: block;

    margin-bottom: 7px;

    color: #374151;

    font-size: 12px;

    font-weight: 700;
}

.required {

    color: #dc2626;
}


/* =========================================================
   INPUTS
========================================================= */

.form-input {

    width: 100%;

    min-height: 42px;

    padding: 9px 12px;

    box-sizing: border-box;

    border: 1px solid #cbd5e1;

    border-radius: 6px;

    background: #ffffff;

    color: #111827;

    font-size: 13px;

    outline: none;

    transition: 0.2s ease;
}

.form-input:focus {

    border-color: #0f5f9f;

    box-shadow:
        0 0 0 2px rgba(15,95,159,0.10);
}

textarea.form-input {

    min-height: 42px;

    resize: vertical;
}


/* =========================================================
   TWO COLUMN INPUTS
========================================================= */

.form-grid {

    display: grid;

    grid-template-columns:
        1fr
        1fr;

    gap: 18px;
}


/* =========================================================
   TEST NAME
========================================================= */

.test-name-input {

    font-weight: 700;

    text-transform: uppercase;
}


/* =========================================================
   RESULT
========================================================= */

.result-input {

    font-weight: 700;

    text-transform: uppercase;
}


/* =========================================================
   BUTTONS
========================================================= */

.form-actions {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    margin-top: 25px;

    padding-top: 18px;

    border-top: 1px solid #e5e7eb;
}

.cancel-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-height: 40px;

    padding: 0 17px;

    background: #ffffff;

    color: #374151;

    border: 1px solid #d1d5db;

    border-radius: 6px;

    text-decoration: none;

    font-size: 13px;

    font-weight: 700;
}

.cancel-btn:hover {

    background: #f3f4f6;
}

.save-btn {

    min-height: 40px;

    padding: 0 20px;

    background: #0f5f9f;

    color: #ffffff;

    border: 1px solid #0f5f9f;

    border-radius: 6px;

    font-size: 13px;

    font-weight: 700;

    cursor: pointer;
}

.save-btn:hover {

    background: #0b4f85;

    border-color: #0b4f85;
}


/* =========================================================
   HELP TEXT
========================================================= */

.form-help {

    margin-top: 5px;

    color: #64748b;

    font-size: 11px;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .lab-edit-page {

        padding: 0 12px;

        margin-top: 18px;
    }

    .lab-edit-header {

        flex-direction: column;
    }

    .lab-edit-actions {

        width: 100%;
    }

    .lab-action-btn {

        flex: 1;
    }

    .patient-grid {

        grid-template-columns: 1fr 1fr;

        gap: 15px;
    }

    .form-grid {

        grid-template-columns: 1fr;

        gap: 0;
    }

}


@media (max-width: 520px) {

    .patient-grid {

        grid-template-columns: 1fr;
    }

    .lab-edit-actions {

        flex-direction: column;
    }

    .lab-action-btn {

        width: 100%;
    }

    .form-actions {

        flex-direction: column;
    }

    .cancel-btn,
    .save-btn {

        width: 100%;
    }

}

</style>


<main class="main-container">

    <div class="lab-edit-page">


        <!-- =================================================
             PAGE HEADER
        ================================================== -->

        <div class="lab-edit-header">

            <div class="lab-edit-title">

                <h2>
                    Edit Laboratory Result
                </h2>

                <p>
                    Update the selected laboratory examination result.
                </p>

            </div>


            <div class="lab-edit-actions">

                <a
                    href="view.php?id=<?php
                        echo (int)$lab['patient_id'];
                    ?>&date=<?php
                        echo urlencode(
                            $lab['test_date']
                        );
                    ?>"
                    class="lab-action-btn back-btn"
                >
                    ← Back to Results
                </a>

            </div>

        </div>


        <!-- =================================================
             MAIN CARD
        ================================================== -->

        <div class="edit-card">


            <!-- CARD HEADER -->

            <div class="edit-card-header">

                <div class="edit-card-header-title">
                    Laboratory Examination
                </div>

                <div class="edit-card-header-subtitle">
                    Edit laboratory result information
                </div>

            </div>


            <!-- =================================================
                 PATIENT INFORMATION
            ================================================== -->

            <div class="patient-information">

                <div class="patient-grid">


                    <div>

                        <span class="info-label">
                            Patient Name
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                strtoupper(
                                    $fullName
                                )
                            );
                            ?>

                        </div>

                    </div>


                    <div>

                        <span class="info-label">
                            Patient ID
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                $lab['patient_code']
                            );
                            ?>

                        </div>

                    </div>


                    <div>

                        <span class="info-label">
                            Age / Sex
                        </span>

                        <div class="info-value">

                            <?php
                            echo htmlspecialchars(
                                $age
                            );
                            ?>

                            /

                            <?php
                            echo htmlspecialchars(
                                strtoupper(
                                    $lab['sex']
                                )
                            );
                            ?>

                        </div>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 LAB DATE
            ================================================== -->

            <div class="lab-date-box">

                <div class="lab-date-label">
                    Laboratory Date
                </div>

                <div class="lab-date-value">

                    <?php
                    echo htmlspecialchars(
                        date(
                            "F d, Y",
                            strtotime(
                                $lab['test_date']
                            )
                        )
                    );
                    ?>

                </div>

            </div>


            <!-- =================================================
                 EDIT FORM
            ================================================== -->

            <form
                action="update.php"
                method="POST"
                class="edit-form"
                onsubmit="return validateLaboratoryForm();"
            >


                <!-- LABORATORY ID -->

                <input
                    type="hidden"
                    name="id"
                    value="<?php
                        echo (int)$lab['id'];
                    ?>"
                >


                <!-- =================================================
                     TEST NAME
                ================================================== -->

                <div class="form-group">

                    <label
                        for="test_name"
                        class="form-label"
                    >
                        Laboratory Test
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="test_name"
                        name="test_name"
                        class="form-input test-name-input"
                        value="<?php
                            echo htmlspecialchars(
                                $lab['test_name']
                            );
                        ?>"
                        required
                        autocomplete="off"
                    >

                </div>


                <!-- =================================================
                     RESULT
                ================================================== -->

                <div class="form-group">

                    <label
                        for="result"
                        class="form-label"
                    >
                        Result
                        <span class="required">*</span>
                    </label>

                    <input
                        type="text"
                        id="result"
                        name="result"
                        class="form-input result-input"
                        value="<?php
                            echo htmlspecialchars(
                                $lab['result']
                            );
                        ?>"
                        required
                        autocomplete="off"
                    >

                </div>


                <!-- =================================================
                     UNIT + REFERENCE RANGE
                ================================================== -->

                <div class="form-grid">


                    <div class="form-group">

                        <label
                            for="unit"
                            class="form-label"
                        >
                            Unit
                        </label>

                        <input
                            type="text"
                            id="unit"
                            name="unit"
                            class="form-input"
                            value="<?php
                                echo htmlspecialchars(
                                    $lab['unit']
                                );
                            ?>"
                            autocomplete="off"
                        >

                    </div>


                    <div class="form-group">

                        <label
                            for="reference_range"
                            class="form-label"
                        >
                            Reference Range
                        </label>

                        <input
                            type="text"
                            id="reference_range"
                            name="reference_range"
                            class="form-input"
                            value="<?php
                                echo htmlspecialchars(
                                    $lab['reference_range']
                                );
                            ?>"
                            autocomplete="off"
                        >

                    </div>


                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div class="form-actions">

                    <a
                        href="view.php?id=<?php
                            echo (int)$lab['patient_id'];
                        ?>&date=<?php
                            echo urlencode(
                                $lab['test_date']
                            );
                        ?>"
                        class="cancel-btn"
                    >
                        Cancel
                    </a>


                    <button
                        type="submit"
                        class="save-btn"
                    >
                        Save Changes
                    </button>

                </div>


            </form>


        </div>


    </div>

</main>


<script>

/* =========================================================
   UPPERCASE TEXT
========================================================= */

function makeUppercase(element) {

    element.addEventListener(
        "input",
        function () {

            this.value =
                this.value.toUpperCase();

        }
    );

}


makeUppercase(
    document.getElementById("test_name")
);

makeUppercase(
    document.getElementById("result")
);

makeUppercase(
    document.getElementById("unit")
);

makeUppercase(
    document.getElementById("reference_range")
);


/* =========================================================
   VALIDATION
========================================================= */

function validateLaboratoryForm() {

    var testName =
        document
            .getElementById("test_name")
            .value
            .trim();

    var result =
        document
            .getElementById("result")
            .value
            .trim();


    if (testName === "") {

        alert(
            "Laboratory test name is required."
        );

        document
            .getElementById("test_name")
            .focus();

        return false;
    }


    if (result === "") {

        alert(
            "Laboratory result is required."
        );

        document
            .getElementById("result")
            .focus();

        return false;
    }


    return true;
}

</script>


<?php

$conn->close();

include __DIR__ . "/../includes/footer.php";

?>