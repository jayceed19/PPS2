<?php

require_once "../config/database.php";


// =========================================================
// GET CONSULTATION ID
// =========================================================

$consultation_id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($consultation_id <= 0) {
    die("Invalid consultation.");
}


// =========================================================
// GET CONSULTATION + PATIENT
// =========================================================

$sql = "SELECT
            c.id,
            c.patient_id,
            c.visit_date,
            c.chief_complaint,
            c.history_illness,
            c.blood_pressure,
            c.temperature,
            c.pulse_rate,
            c.respiratory_rate,
            c.oxygen_saturation,
            c.weight,
            c.height,
            c.assessment,
            c.management,
            c.follow_up_date,
            c.remarks,

            p.patient_id AS patient_number,
            p.first_name,
            p.middle_name,
            p.last_name

        FROM consultations c

        INNER JOIN patients p
            ON c.patient_id = p.id

        WHERE c.id = ?";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $consultation_id
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    die("Consultation not found.");
}

$consultation = $result->fetch_assoc();

$stmt->close();


// =========================================================
// FULL NAME
// =========================================================

$fullName = $consultation['first_name'];

if (!empty($consultation['middle_name'])) {

    $fullName .=
        " " .
        $consultation['middle_name'];
}

$fullName .=
    " " .
    $consultation['last_name'];


// =========================================================
// PATIENT DATABASE ID
// =========================================================

$patient_database_id =
    (int) $consultation['patient_id'];


// =========================================================
// GET CONSULTATION NUMBER
// =========================================================

$consultationNumber = 1;

$sqlNumber = "
    SELECT COUNT(*) AS consultation_number

    FROM consultations

    WHERE patient_id = ?

    AND (
        visit_date < ?

        OR (
            visit_date = ?

            AND id <= ?
        )
    )
";

$stmtNumber =
    $conn->prepare($sqlNumber);

if ($stmtNumber) {

    $stmtNumber->bind_param(
        "issi",
        $patient_database_id,
        $consultation['visit_date'],
        $consultation['visit_date'],
        $consultation_id
    );

    $stmtNumber->execute();

    $numberResult =
        $stmtNumber->get_result();

    if ($numberResult) {

        $numberRow =
            $numberResult->fetch_assoc();

        if ($numberRow) {

            $consultationNumber =
                (int) $numberRow['consultation_number'];
        }
    }

    $stmtNumber->close();
}


// =========================================================
// GET TOTAL CONSULTATIONS
// =========================================================

$totalConsultations = 0;

$sqlTotal = "
    SELECT COUNT(*) AS total

    FROM consultations

    WHERE patient_id = ?
";

$stmtTotal =
    $conn->prepare($sqlTotal);

if ($stmtTotal) {

    $stmtTotal->bind_param(
        "i",
        $patient_database_id
    );

    $stmtTotal->execute();

    $totalResult =
        $stmtTotal->get_result();

    if ($totalResult) {

        $totalRow =
            $totalResult->fetch_assoc();

        if ($totalRow) {

            $totalConsultations =
                (int) $totalRow['total'];
        }
    }

    $stmtTotal->close();
}


// =========================================================
// CHIEF COMPLAINT LIST
// =========================================================

$chiefComplaints = array(

    "ABDOMINAL CRAMP/PAIN",
    "ALTERED MENTAL SENSORIUM",
    "ANOREXIA",
    "BLEEDING GUMS",
    "BLURRING OF VISION",
    "BODY WEAKNESS",
    "CHEST PAIN/DISCOMFORT",
    "CONSTIPATION",
    "COUGH",
    "DIARRHEA",
    "DIZZINESS",
    "DYSPHAGIA",
    "DYSPNEA",
    "DYSURIA",
    "EPISTAXIS",
    "FEVER",
    "FREQUENCY OF URINATION",
    "HEADACHE",
    "HEMATEMESIS",
    "HEMATURIA",
    "HEMOPTYSIS",
    "IRRITABILITY",
    "JAUNDICE",
    "LOWER EXTREMITY EDEMA",
    "MYALGIA",
    "NAUSEA",
    "ORTHOPNEA",
    "OTHERS",
    "PAIN",
    "PALPITATIONS",
    "SEIZURES",
    "SKIN RASHES",
    "STOOL, BLOODY/BLACK TARRY/MUC",
    "SWEATING",
    "URGENCY",
    "VOMITING/NAUSEA",
    "WEIGHT LOSS"

);


// =========================================================
// ASSESSMENT LIST
// =========================================================

$assessments = array(

    "000 - ESSENTIALLY WELL INDIVIDUAL",
    "B34.9 - SYSTEMIC VIRAL INFECTION (SVI)",
    "E05.9 - HYPERTHYROIDISM",
    "E10 - DM TYPE I",
    "E11.9 - DM TYPE II",
    "E14.9 - DM",
    "E66.9 - OBESITY, UNSPECIFIED",
    "E78.0 - HYPERCHOLESTEROLEMIA",
    "E78.1 - HYPERTRIGLYCERIDAEMIA (PURE)",
    "E78.2 - MIXED HYPERLIPIDEMIA",
    "E78.5 - HYPERLIPIDEMIA",
    "E78.9 - DYSLIPIDEMIA",
    "E79.0 - HYPERURICEMIA",
    "E89.0 - POST PROCEDURAL HYPOTHYROIDISM",
    "G43 - MIGRAINE",
    "G44.1 - VASCULAR HEADACHE",
    "G50.0 - TRIGEMINAL NEURALGIA",
    "H52.7 - DISORDER OF REFRACTION (ERROR OF REFRACTION) UNSPECIFIED",
    "I10.1 - HYPERTENSION STAGE II",
    "I10.9 - HYPERTENSION (HPN)",
    "I51.6 - CARDIOVASCULAR DISEASE (CVD)",
    "I51.7 - CARDIOMEGALY",
    "J06.9 - UPPER RESPIRATORY TRACT INFECTION (URTI)",
    "J22 - LOWER RESPIRATORY TRACT INFECTION",
    "J30.4 - ALLERGIC RHINITIS",
    "J45.0 - ALLERGIC RHINITIS WITH ASTHMA",
    "JO3 - ACUTE TONSILLITIS",
    "K21.9 - GASTROESOPHAGEAL REFLUX DISEASE",
    "K21.9 - GASTROESOPHAGEAL REFLUX DISEASE W/ ESOPHAGITIS (GERD) – ACID REFLUX",
    "K31.8 - HYPER ACIDITY",
    "M10.09 - GOUTY ATHRITIS",
    "M41.9 - SCOLIOSIS UNSPECIFIED",
    "N39.0 - URINARY TRACT INFECTION (UTI)",
    "R42 - VERTIGO",
    "R53 - MALAISE AND FATIGUE (WEAKNESS)",
    "R73 - ELEVATED BLOOD GLUCOSE LEVEL (PRE DIABETES)"

);


// =========================================================
// EXISTING CHIEF COMPLAINT
// =========================================================

$existingChiefComplaint = "";

if (isset($consultation['chief_complaint'])) {

    $existingChiefComplaint =
        strtoupper(
            trim(
                $consultation['chief_complaint']
            )
        );

}


// =========================================================
// DETERMINE IF EXISTING CHIEF COMPLAINT IS STANDARD
// =========================================================

$chiefComplaintIsStandard =
    in_array(
        $existingChiefComplaint,
        $chiefComplaints,
        true
    );


$otherChiefComplaintValue = "";

if (
    !$chiefComplaintIsStandard &&
    $existingChiefComplaint !== ""
) {

    /*
    Existing database record is a custom
    chief complaint.

    Show OTHERS in the main searchable field
    and place the actual complaint in the
    separate Other Chief Complaint field.
    */

    $initialChiefComplaint = "OTHERS";

    $otherChiefComplaintValue =
        $existingChiefComplaint;

} else {

    $initialChiefComplaint =
        $existingChiefComplaint;

}


// =========================================================
// PAGE SETTINGS
// =========================================================

$pageTitle = "Edit Consultation";

$pageSubtitle = "Edit Consultation";

$basePath = "../";

$activePage = "consultations";


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
   EDIT CONSULTATION PAGE
========================================================= */

.consultation-edit-card {

    max-width: 1100px;

    margin: 0 auto 20px;

}


/* =========================================================
   PATIENT HEADER
========================================================= */

.consultation-patient-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding-bottom: 18px;

    margin-bottom: 5px;

    border-bottom: 1px solid #e5e9ee;

}


.consultation-patient-name {

    color: #1f4e78;

    font-size: 22px;

    font-weight: 700;

    line-height: 1.3;

}


.consultation-patient-id {

    margin-top: 5px;

    color: #6c757d;

    font-size: 13px;

}


.consultation-patient-id strong {

    color: #343a40;

}


/* =========================================================
   CONSULTATION BADGE
========================================================= */

.consultation-badge {

    min-width: 135px;

    padding: 10px 14px;

    background: #f1f5f9;

    border: 1px solid #dfe6ed;

    border-radius: 6px;

    text-align: center;

}


.consultation-label {

    color: #6c757d;

    font-size: 10px;

    font-weight: 600;

    letter-spacing: 0.5px;

}


.consultation-number {

    margin-top: 2px;

    color: #1f4e78;

    font-size: 20px;

    font-weight: 700;

}


.consultation-total {

    margin-top: 1px;

    color: #6c757d;

    font-size: 10px;

}


/* =========================================================
   SECTION TITLE
========================================================= */

.consultation-edit-card .section-title {

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


.consultation-edit-card .section-title:first-child {

    margin-top: 0;

}


/* =========================================================
   FORM GRID
========================================================= */

.consultation-edit-card .form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 18px 22px;

}


/* =========================================================
   VITAL SIGNS GRID
========================================================= */

.consultation-edit-card .vital-grid {

    display: grid;

    grid-template-columns:
        repeat(3, minmax(0, 1fr));

    gap: 18px 20px;

}


/* =========================================================
   FORM GROUP
========================================================= */

.consultation-edit-card .form-group {

    display: flex;

    flex-direction: column;

    min-width: 0;

}


/* =========================================================
   FULL WIDTH
========================================================= */

.consultation-edit-card .form-group-full {

    grid-column: 1 / -1;

}


/* =========================================================
   LABEL
========================================================= */

.consultation-edit-card .form-group label {

    margin-bottom: 7px;

    color: #343a40;

    font-size: 13px;

    font-weight: 600;

}


.consultation-edit-card .required {

    color: #dc3545;

    font-weight: 700;

}


/* =========================================================
   INPUT / TEXTAREA
========================================================= */

.consultation-edit-card input,
.consultation-edit-card textarea {

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
        box-shadow 0.15s ease;

    box-sizing: border-box;

}


.consultation-edit-card input:focus,
.consultation-edit-card textarea:focus {

    border-color: #1f4e78;

    box-shadow:
        0 0 0 2px rgba(31, 78, 120, 0.10);

}


/* =========================================================
   TEXTAREA
========================================================= */

.consultation-edit-card textarea {

    min-height: 90px;

    resize: vertical;

    line-height: 1.5;

}


/* =========================================================
   FORMAL TEXT FIELDS
========================================================= */

.formal-text-field {

    text-transform: uppercase;

}


.formal-text-field::placeholder {

    text-transform: none;

}


/* =========================================================
   DATE / NUMBER INPUT
========================================================= */

.consultation-edit-card input[type="date"],
.consultation-edit-card input[type="number"] {

    cursor: pointer;

}


/* =========================================================
   PLACEHOLDER
========================================================= */

.consultation-edit-card input::placeholder,
.consultation-edit-card textarea::placeholder {

    color: #adb5bd;

}


/* =========================================================
   SEARCHABLE FIELD
========================================================= */

.searchable-wrapper {

    position: relative;

    width: 100%;

}


.searchable-field {

    width: 100%;

}


.search-dropdown {

    position: absolute;

    top: calc(100% + 3px);

    left: 0;

    width: 100%;

    max-height: 220px;

    overflow-y: auto;

    background: #ffffff;

    border: 1px solid #cfd6dd;

    border-radius: 6px;

    box-shadow:
        0 6px 18px rgba(0, 0, 0, 0.12);

    z-index: 9999;

    display: none;

}


.search-dropdown.show {

    display: block;

}


.search-dropdown-item {

    padding: 10px 12px;

    color: #343a40;

    font-size: 13px;

    line-height: 1.35;

    cursor: pointer;

    border-bottom: 1px solid #f0f2f4;

    background: #ffffff;

}


.search-dropdown-item:last-child {

    border-bottom: none;

}


.search-dropdown-item:hover,
.search-dropdown-item.active {

    background: #eef4f9;

    color: #1f4e78;

}


.search-dropdown-empty {

    padding: 10px 12px;

    color: #6c757d;

    font-size: 12px;

}


/* =========================================================
   OTHER CHIEF COMPLAINT
========================================================= */

.other-chief-complaint-group {

    margin-top: 15px;

    padding: 13px 14px;

    background: #fafbfd;

    border: 1px solid #dfe5eb;

    border-left: 3px solid #1f4e78;

    border-radius: 5px;

}


.other-chief-complaint-group label {

    display: block;

    margin-bottom: 7px;

    color: #343a40;

    font-size: 13px;

    font-weight: 600;

}


.required-star {

    color: #dc3545;

    font-weight: 700;

}


.required-help {

    display: block;

    margin-top: 6px;

    color: #6c757d;

    font-size: 11px;

}


/* =========================================================
   FOLLOW-UP NOTE
========================================================= */

.follow-up-note {

    margin-top: 6px;

    color: #6c757d;

    font-size: 11px;

    line-height: 1.4;

}


/* =========================================================
   FORM ACTIONS
========================================================= */

.consultation-edit-card .form-actions {

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

.consultation-edit-card .form-actions .btn {

    min-height: 38px;

    padding: 9px 17px;

    border-radius: 5px;

    font-size: 13px;

    font-weight: 600;

}


/* =========================================================
   CANCEL
========================================================= */

.consultation-edit-card .btn-cancel {

    background: #e9ecef;

    color: #343a40;

    border: 1px solid #d5d9dd;

    text-decoration: none;

}


.consultation-edit-card .btn-cancel:hover {

    background: #dee2e6;

}


/* =========================================================
   UPDATE
========================================================= */

.consultation-edit-card .btn-save {

    background: #1f4e78;

    color: #ffffff;

    border: 1px solid #1f4e78;

    cursor: pointer;

}


.consultation-edit-card .btn-save:hover {

    background: #173a5c;

    border-color: #173a5c;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 800px) {

    .consultation-edit-card .vital-grid {

        grid-template-columns: 1fr 1fr;

    }

}


@media (max-width: 700px) {

    .consultation-edit-card .form-grid {

        grid-template-columns: 1fr;

    }


    .consultation-edit-card .vital-grid {

        grid-template-columns: 1fr;

    }


    .consultation-edit-card .form-group-full {

        grid-column: auto;

    }


    .consultation-patient-header {

        flex-direction: column;

        align-items: flex-start;

    }


    .consultation-badge {

        width: 100%;

    }


    .consultation-patient-name {

        font-size: 20px;

    }


    .consultation-edit-card .form-actions {

        justify-content: stretch;

        flex-wrap: wrap;

    }


    .consultation-edit-card .form-actions .btn {

        flex: 1;

        min-width: 120px;

    }

}

</style>


<main class="main-container">


    <div class="card consultation-edit-card">


        <!-- =====================================================
             PATIENT HEADER
        ====================================================== -->

        <div class="consultation-patient-header">


            <div>

                <div class="consultation-patient-name">

                    <?php

                    echo htmlspecialchars(
                        $fullName
                    );

                    ?>

                </div>


                <div class="consultation-patient-id">

                    Patient ID:

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $consultation['patient_number']
                        );

                        ?>

                    </strong>

                </div>

            </div>


            <!-- CONSULTATION NUMBER -->

            <div class="consultation-badge">

                <div class="consultation-label">
                    CONSULTATION
                </div>


                <div class="consultation-number">

                    #<?php

                    echo $consultationNumber;

                    ?>

                </div>


                <div class="consultation-total">

                    of <?php

                    echo $totalConsultations;

                    ?>

                </div>

            </div>


        </div>


        <form
            action="update_consultation.php"
            method="POST"
            id="editConsultationForm"
        >


            <input
                type="hidden"
                name="consultation_id"
                value="<?php
                    echo $consultation_id;
                ?>"
            >


            <!-- =================================================
                 VISIT INFORMATION
            ================================================== -->

            <div class="section-title">

                Visit Information

            </div>


            <div class="form-grid">


                <div class="form-group">

                    <label>

                        Visit Date

                        <span class="required">*</span>

                    </label>


                    <input
                        type="date"
                        name="visit_date"
                        value="<?php

                            echo htmlspecialchars(
                                $consultation['visit_date']
                            );

                        ?>"
                        max="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                </div>


            </div>


            <!-- =================================================
                 CLINICAL INFORMATION
            ================================================== -->

            <div class="section-title">

                Clinical Information

            </div>


            <div class="form-grid">


                <!-- =================================================
                     CHIEF COMPLAINT
                ================================================== -->

                <div class="form-group form-group-full">

                    <label for="chief_complaint">

                        Chief Complaint

                        <span class="required">*</span>

                    </label>


                    <div class="searchable-wrapper">

                        <input
                            type="text"
                            id="chief_complaint"
                            name="chief_complaint"
                            class="formal-text-field searchable-field"
                            placeholder="Select or search chief complaint..."
                            autocomplete="off"
                            value="<?php

                                echo htmlspecialchars(
                                    $initialChiefComplaint
                                );

                            ?>"
                            required
                        >


                        <div
                            id="chiefComplaintDropdown"
                            class="search-dropdown"
                        ></div>

                    </div>


                    <!-- =================================================
                         OTHER CHIEF COMPLAINT
                    ================================================== -->

                    <div
                        id="otherChiefComplaintGroup"
                        class="other-chief-complaint-group"
                        style="<?php

                            echo (
                                $initialChiefComplaint === "OTHERS"
                                ? "display:block;"
                                : "display:none;"
                            );

                        ?>"
                    >

                        <label for="other_chief_complaint">

                            Other Chief Complaint

                            <span class="required-star">*</span>

                        </label>


                        <input
                            type="text"
                            id="other_chief_complaint"
                            name="other_chief_complaint"
                            class="formal-text-field"
                            placeholder="Enter other chief complaint..."
                            autocomplete="off"
                            value="<?php

                                echo htmlspecialchars(
                                    $otherChiefComplaintValue
                                );

                            ?>"
                        >


                        <small class="required-help">

                            Required when "OTHERS" is selected.

                        </small>

                    </div>


                </div>


                <!-- =================================================
                     HISTORY OF PRESENT ILLNESS
                ================================================== -->

                <div class="form-group form-group-full">

                    <label for="history_illness">

                        History of Present Illness

                        <span class="required">*</span>

                    </label>


                    <textarea
                        name="history_illness"
                        id="history_illness"
                        class="formal-text-field"
                        placeholder="Enter history of present illness..."
                        required
                    ><?php

                        echo htmlspecialchars(
                            $consultation['history_illness']
                        );

                    ?></textarea>

                </div>


            </div>


            <!-- =================================================
                 VITAL SIGNS
            ================================================== -->

            <div class="section-title">

                Vital Signs

            </div>


            <div class="vital-grid">


                <!-- BLOOD PRESSURE -->

                <div class="form-group">

                    <label for="blood_pressure">

                        Blood Pressure

                    </label>


                    <input
                        type="text"
                        name="blood_pressure"
                        id="blood_pressure"
                        placeholder="120/80"
                        pattern="[0-9]{2,3}/[0-9]{2,3}"
                        title="Enter blood pressure like 120/80"
                        maxlength="7"
                        inputmode="numeric"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['blood_pressure'])
                                    ? $consultation['blood_pressure']
                                    : ''
                            );

                        ?>"
                    >

                </div>


                <!-- TEMPERATURE -->

                <div class="form-group">

                    <label for="temperature">

                        Temperature (°C)

                    </label>


                    <input
                        type="number"
                        name="temperature"
                        id="temperature"
                        step="0.1"
                        min="25"
                        max="45"
                        placeholder="36.5"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['temperature'])
                                    ? $consultation['temperature']
                                    : ''
                            );

                        ?>"
                    >

                </div>


                <!-- PULSE RATE -->

                <div class="form-group">

                    <label for="pulse_rate">

                        Pulse Rate (bpm)

                    </label>


                    <input
                        type="number"
                        name="pulse_rate"
                        id="pulse_rate"
                        min="20"
                        max="250"
                        placeholder="72"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['pulse_rate'])
                                    ? $consultation['pulse_rate']
                                    : ''
                            );

                        ?>"
                    >

                </div>


                <!-- RESPIRATORY RATE -->

                <div class="form-group">

                    <label for="respiratory_rate">

                        Respiratory Rate (/min)

                    </label>


                    <input
                        type="number"
                        name="respiratory_rate"
                        id="respiratory_rate"
                        min="5"
                        max="80"
                        placeholder="18"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['respiratory_rate'])
                                    ? $consultation['respiratory_rate']
                                    : ''
                            );

                        ?>"
                    >

                </div>


                <!-- OXYGEN SATURATION -->

                <div class="form-group">

                    <label for="oxygen_saturation">

                        SpO₂ (%)

                    </label>


                    <input
                        type="number"
                        name="oxygen_saturation"
                        id="oxygen_saturation"
                        min="50"
                        max="100"
                        placeholder="98"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['oxygen_saturation'])
                                    ? $consultation['oxygen_saturation']
                                    : ''
                            );

                        ?>"
                    >

                </div>


                <!-- WEIGHT -->

                <div class="form-group">

                    <label for="weight">

                        Weight (kg)

                    </label>


                    <input
                        type="number"
                        name="weight"
                        id="weight"
                        step="0.01"
                        min="0.1"
                        max="500"
                        placeholder="60"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['weight'])
                                    ? $consultation['weight']
                                    : ''
                            );

                        ?>"
                    >

                </div>


                <!-- HEIGHT -->

                <div class="form-group">

                    <label for="height">

                        Height (cm)

                    </label>


                    <input
                        type="number"
                        name="height"
                        id="height"
                        step="0.01"
                        min="20"
                        max="250"
                        placeholder="165"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['height'])
                                    ? $consultation['height']
                                    : ''
                            );

                        ?>"
                    >

                </div>


            </div>


            <!-- =================================================
                 ASSESSMENT & TREATMENT
            ================================================== -->

            <div class="section-title">

                Assessment

            </div>


            <div class="form-grid">


                <!-- =================================================
                     ASSESSMENT
                ================================================== -->

                <div class="form-group form-group-full">

                    <label for="assessment">

                        Diagnosis

                        <span class="required">*</span>

                    </label>


                    <div class="searchable-wrapper">

                        <input
                            type="text"
                            id="assessment"
                            name="assessment"
                            class="formal-text-field searchable-field"
                            placeholder="Search code or diagnosis..."
                            autocomplete="off"
                            value="<?php

                                echo htmlspecialchars(
                                    $consultation['assessment']
                                );

                            ?>"
                            required
                        >


                        <div
                            id="assessmentDropdown"
                            class="search-dropdown"
                        ></div>

                    </div>

                </div>


                <!-- MANAGEMENT -->

                <div class="form-group form-group-full">

                    <label for="management">

                        Management / Plan

                    </label>


                    <textarea
                        name="management"
                        id="management"
                        class="formal-text-field"
                        placeholder="Enter management or treatment plan..."
                    ><?php

                        echo htmlspecialchars(
                            isset($consultation['management'])
                                ? $consultation['management']
                                : ''
                        );

                    ?></textarea>

                </div>


            </div>


            <!-- =================================================
                 FOLLOW-UP & REMARKS
            ================================================== -->

            <div class="section-title">

                Follow-up & Remarks

            </div>


            <div class="form-grid">


                <!-- FOLLOW-UP DATE -->

                <div class="form-group">

                    <label for="follow_up_date">

                        Follow-up Date

                    </label>


                    <input
                        type="date"
                        name="follow_up_date"
                        id="follow_up_date"
                        value="<?php

                            echo htmlspecialchars(
                                isset($consultation['follow_up_date'])
                                    ? $consultation['follow_up_date']
                                    : ''
                            );

                        ?>"
                    >


                    <div class="follow-up-note">

                        No follow-up needed? Select today's date.
                        This will apply to today only.

                    </div>

                </div>


                <!-- REMARKS -->

                <div class="form-group">

                    <label for="remarks">

                        Remarks

                    </label>


                    <textarea
                        name="remarks"
                        id="remarks"
                        class="formal-text-field"
                        placeholder="Additional remarks..."
                    ><?php

                        echo htmlspecialchars(
                            isset($consultation['remarks'])
                                ? $consultation['remarks']
                                : ''
                        );

                    ?></textarea>

                </div>


            </div>


            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div class="form-actions">


                <a
                    href="consultation_view.php?id=<?php
                        echo $consultation_id;
                    ?>"
                    class="btn btn-cancel"
                >

                    Cancel

                </a>


                <button
                    type="submit"
                    class="btn btn-save"
                >

                    Update Consultation

                </button>


            </div>


        </form>


    </div>


</main>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        // =====================================================
        // DATA FROM PHP
        // =====================================================

        const chiefComplaints =
            <?php echo json_encode(
                $chiefComplaints,
                JSON_UNESCAPED_UNICODE
            ); ?>;


        const assessments =
            <?php echo json_encode(
                $assessments,
                JSON_UNESCAPED_UNICODE
            ); ?>;


        // =====================================================
        // CLEAN TEXT
        // =====================================================

        function cleanFormalText(value) {

            return String(value || "")
                .replace(/\s+/g, " ")
                .trim()
                .toUpperCase();

        }


        // =====================================================
        // NORMALIZE SEARCH
        // =====================================================

        function normalizeSearch(value) {

            return cleanFormalText(value)
                .replace(/[^A-Z0-9]+/g, " ")
                .replace(/\s+/g, " ")
                .trim();

        }


        // =====================================================
        // LEVENSHTEIN DISTANCE
        // =====================================================

        function levenshteinDistance(a, b) {

            const matrix = [];

            for (
                let i = 0;
                i <= b.length;
                i++
            ) {

                matrix[i] = [i];

            }


            for (
                let j = 0;
                j <= a.length;
                j++
            ) {

                matrix[0][j] = j;

            }


            for (
                let i = 1;
                i <= b.length;
                i++
            ) {

                for (
                    let j = 1;
                    j <= a.length;
                    j++
                ) {

                    if (
                        b.charAt(i - 1) ===
                        a.charAt(j - 1)
                    ) {

                        matrix[i][j] =
                            matrix[i - 1][j - 1];

                    } else {

                        matrix[i][j] =
                            Math.min(

                                matrix[i - 1][j] + 1,

                                matrix[i][j - 1] + 1,

                                matrix[i - 1][j - 1] + 1

                            );

                    }

                }

            }


            return matrix[b.length][a.length];

        }


        // =====================================================
        // MATCH SCORE
        // =====================================================

        function getMatchScore(search, item) {

            const s =
                normalizeSearch(search);

            const i =
                normalizeSearch(item);


            if (!s) {

                return 0;

            }


            if (i === s) {

                return 1000;

            }


            if (i.startsWith(s)) {

                return 900;

            }


            if (i.includes(s)) {

                return 800;

            }


            const words =
                i.split(" ");


            if (
                words.some(
                    function (word) {

                        return word.startsWith(s);

                    }
                )
            ) {

                return 750;

            }


            const distance =
                levenshteinDistance(
                    s,
                    i
                );


            const maxLength =
                Math.max(
                    s.length,
                    i.length
                );


            if (maxLength === 0) {

                return 0;

            }


            const similarity =
                1 -
                (
                    distance /
                    maxLength
                );


            if (similarity >= 0.45) {

                return 500 * similarity;

            }


            return 0;

        }


        // =====================================================
        // SEARCHABLE FIELD SETUP
        // =====================================================

        function setupSearchableField(
            inputId,
            dropdownId,
            data
        ) {

            const input =
                document.getElementById(
                    inputId
                );


            const dropdown =
                document.getElementById(
                    dropdownId
                );


            if (!input || !dropdown) {

                return;

            }


            let activeIndex = -1;


            function hideDropdown() {

                dropdown.classList.remove(
                    "show"
                );

                dropdown.innerHTML = "";

                activeIndex = -1;

            }


            function updateActiveItem() {

                const items =
                    dropdown.querySelectorAll(
                        ".search-dropdown-item"
                    );


                items.forEach(
                    function (item, index) {

                        item.classList.toggle(
                            "active",
                            index === activeIndex
                        );

                    }
                );

            }


            function showResults() {

                const search =
                    cleanFormalText(
                        input.value
                    );


                dropdown.innerHTML = "";

                activeIndex = -1;


                if (!search) {

                    hideDropdown();

                    return;

                }


                const results =
                    data
                        .map(
                            function (item) {

                                return {

                                    item: item,

                                    score:
                                        getMatchScore(
                                            search,
                                            item
                                        )

                                };

                            }
                        )
                        .filter(
                            function (result) {

                                return result.score > 0;

                            }
                        )
                        .sort(
                            function (a, b) {

                                if (
                                    b.score !==
                                    a.score
                                ) {

                                    return (
                                        b.score -
                                        a.score
                                    );

                                }


                                return a.item.localeCompare(
                                    b.item
                                );

                            }
                        )
                        .slice(0, 8);


                if (results.length === 0) {

                    hideDropdown();

                    return;

                }


                results.forEach(
                    function (result) {

                        const item =
                            document.createElement(
                                "div"
                            );


                        item.className =
                            "search-dropdown-item";


                        item.textContent =
                            result.item;


                        item.addEventListener(
                            "mousedown",
                            function (event) {

                                event.preventDefault();


                                input.value =
                                    result.item;


                                input.dispatchEvent(
                                    new Event(
                                        "input",
                                        {
                                            bubbles: true
                                        }
                                    )
                                );


                                hideDropdown();

                                input.focus();

                            }
                        );


                        dropdown.appendChild(
                            item
                        );

                    }
                );


                dropdown.classList.add(
                    "show"
                );

            }


            input.addEventListener(
                "input",
                function () {

                    this.value =
                        this.value.toUpperCase();

                    showResults();

                }
            );


            input.addEventListener(
                "focus",
                function () {

                    if (
                        cleanFormalText(
                            this.value
                        ) !== ""
                    ) {

                        showResults();

                    }

                }
            );


            input.addEventListener(
                "keydown",
                function (event) {

                    const items =
                        dropdown.querySelectorAll(
                            ".search-dropdown-item"
                        );


                    if (
                        !dropdown.classList.contains(
                            "show"
                        ) ||
                        items.length === 0
                    ) {

                        return;

                    }


                    if (
                        event.key ===
                        "ArrowDown"
                    ) {

                        event.preventDefault();


                        activeIndex =
                            Math.min(
                                activeIndex + 1,
                                items.length - 1
                            );


                        updateActiveItem();

                    }


                    else if (
                        event.key ===
                        "ArrowUp"
                    ) {

                        event.preventDefault();


                        activeIndex =
                            Math.max(
                                activeIndex - 1,
                                0
                            );


                        updateActiveItem();

                    }


                    else if (
                        event.key ===
                        "Enter"
                    ) {

                        if (
                            activeIndex >= 0 &&
                            activeIndex < items.length
                        ) {

                            event.preventDefault();


                            items[
                                activeIndex
                            ].dispatchEvent(
                                new MouseEvent(
                                    "mousedown"
                                )
                            );

                        }

                    }


                    else if (
                        event.key ===
                        "Escape"
                    ) {

                        hideDropdown();

                    }

                }
            );


            input.addEventListener(
                "blur",
                function () {

                    this.value =
                        cleanFormalText(
                            this.value
                        );


                    setTimeout(
                        function () {

                            hideDropdown();

                        },
                        150
                    );

                }
            );

        }


        // =====================================================
        // CHIEF COMPLAINT
        // =====================================================

        const chiefComplaint =
            document.getElementById(
                "chief_complaint"
            );


        const otherChiefComplaintGroup =
            document.getElementById(
                "otherChiefComplaintGroup"
            );


        const otherChiefComplaint =
            document.getElementById(
                "other_chief_complaint"
            );


        function updateOtherChiefComplaint() {

            if (
                !chiefComplaint ||
                !otherChiefComplaintGroup ||
                !otherChiefComplaint
            ) {

                return;

            }


            const value =
                cleanFormalText(
                    chiefComplaint.value
                );


            if (
                value === "OTHERS"
            ) {

                otherChiefComplaintGroup.style.display =
                    "block";


                otherChiefComplaint.required =
                    true;

            } else {

                otherChiefComplaintGroup.style.display =
                    "none";


                otherChiefComplaint.required =
                    false;


                otherChiefComplaint.value =
                    "";

            }

        }


        if (chiefComplaint) {

            chiefComplaint.addEventListener(
                "input",
                function () {

                    this.value =
                        this.value.toUpperCase();

                    updateOtherChiefComplaint();

                }
            );


            chiefComplaint.addEventListener(
                "change",
                function () {

                    updateOtherChiefComplaint();

                }
            );

        }


        // =====================================================
        // INITIAL OTHER CHIEF COMPLAINT STATE
        // =====================================================

        if (
            chiefComplaint &&
            otherChiefComplaint &&
            otherChiefComplaintGroup
        ) {

            if (
                cleanFormalText(
                    chiefComplaint.value
                ) === "OTHERS"
            ) {

                otherChiefComplaint.required =
                    true;

                otherChiefComplaintGroup.style.display =
                    "block";

            }

        }


        // =====================================================
        // SETUP SEARCHABLE CHIEF COMPLAINT
        // =====================================================

        setupSearchableField(
            "chief_complaint",
            "chiefComplaintDropdown",
            chiefComplaints
        );


        // =====================================================
        // SETUP SEARCHABLE ASSESSMENT
        // =====================================================

        setupSearchableField(
            "assessment",
            "assessmentDropdown",
            assessments
        );


        // =====================================================
        // FORMAL TEXT FIELDS
        // =====================================================

        const formalFields =
            document.querySelectorAll(
                ".formal-text-field"
            );


        formalFields.forEach(
            function (field) {

                field.value =
                    field.value.toUpperCase();


                field.addEventListener(
                    "input",
                    function () {

                        this.value =
                            this.value.toUpperCase();

                    }
                );


                field.addEventListener(
                    "blur",
                    function () {

                        this.value =
                            cleanFormalText(
                                this.value
                            );

                    }
                );

            }
        );


        // =====================================================
        // BLOOD PRESSURE
        // =====================================================

        const bloodPressure =
            document.getElementById(
                "blood_pressure"
            );


        function formatBloodPressure(value) {

            value =
                value.replace(
                    /[^0-9/]/g,
                    ""
                );


            const parts =
                value.split("/");


            if (parts.length > 2) {

                value =
                    parts[0] +
                    "/" +
                    parts[1];

            }


            const bpParts =
                value.split("/");


            if (bpParts.length === 2) {

                bpParts[0] =
                    bpParts[0].substring(
                        0,
                        3
                    );


                bpParts[1] =
                    bpParts[1].substring(
                        0,
                        3
                    );


                value =
                    bpParts[0] +
                    "/" +
                    bpParts[1];

            } else {

                value =
                    value.substring(
                        0,
                        3
                    );

            }


            return value;

        }


        if (bloodPressure) {

            bloodPressure.value =
                formatBloodPressure(
                    bloodPressure.value
                );


            bloodPressure.addEventListener(
                "input",
                function () {

                    this.value =
                        formatBloodPressure(
                            this.value
                        );

                }
            );

        }


        // =====================================================
        // FORM SUBMIT
        // =====================================================

        const form =
            document.getElementById(
                "editConsultationForm"
            );


        if (form) {

            form.addEventListener(
                "submit",
                function (event) {


                    // -----------------------------------------
                    // CLEAN ALL TEXT FIELDS
                    // -----------------------------------------

                    formalFields.forEach(
                        function (field) {

                            field.value =
                                cleanFormalText(
                                    field.value
                                );

                        }
                    );


                    // -----------------------------------------
                    // OTHER CHIEF COMPLAINT
                    // -----------------------------------------

                    updateOtherChiefComplaint();


                    if (
                        chiefComplaint &&
                        cleanFormalText(
                            chiefComplaint.value
                        ) === "OTHERS"
                    ) {

                        if (
                            !otherChiefComplaint ||
                            cleanFormalText(
                                otherChiefComplaint.value
                            ) === ""
                        ) {

                            event.preventDefault();


                            alert(
                                "Please enter the Other Chief Complaint."
                            );


                            if (
                                otherChiefComplaint
                            ) {

                                otherChiefComplaint.focus();

                            }


                            return;

                        }


                        /*
                        Send the actual custom complaint
                        to update_consultation.php.
                        */

                        chiefComplaint.value =
                            cleanFormalText(
                                otherChiefComplaint.value
                            );

                    }


                    // -----------------------------------------
                    // FORMAT BLOOD PRESSURE
                    // -----------------------------------------

                    if (bloodPressure) {

                        bloodPressure.value =
                            formatBloodPressure(
                                bloodPressure.value
                            );

                    }


                    // -----------------------------------------
                    // CONFIRM UPDATE
                    // -----------------------------------------

                    const confirmed =
                        confirm(
                            "Are you sure you want to update this consultation?"
                        );


                    if (!confirmed) {

                        event.preventDefault();

                    }

                }
            );

        }


        // =====================================================
        // CLICK OUTSIDE SEARCH DROPDOWN
        // =====================================================

        document.addEventListener(
            "click",
            function (event) {

                const dropdowns =
                    document.querySelectorAll(
                        ".search-dropdown"
                    );


                const wrappers =
                    document.querySelectorAll(
                        ".searchable-wrapper"
                    );


                let insideWrapper = false;


                wrappers.forEach(
                    function (wrapper) {

                        if (
                            wrapper.contains(
                                event.target
                            )
                        ) {

                            insideWrapper = true;

                        }

                    }
                );


                if (!insideWrapper) {

                    dropdowns.forEach(
                        function (dropdown) {

                            dropdown.classList.remove(
                                "show"
                            );

                            dropdown.innerHTML =
                                "";

                        }
                    );

                }

            }
        );

    }
);

</script>


<?php

// =========================================================
// SHARED FOOTER
// =========================================================

require_once "../includes/footer.php";


// =========================================================
// CLOSE DATABASE
// =========================================================

$conn->close();

?>