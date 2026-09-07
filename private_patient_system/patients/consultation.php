<?php

require_once "../config/database.php";


// =========================================================
// PAGE SETTINGS
// =========================================================

$pageTitle = "New Consultation";
$pageSubtitle = "New Consultation";
$basePath = "../";
$activePage = "consultations";


// =========================================================
// GET PATIENT ID
// =========================================================

$patient_id = isset($_GET['patient_id'])
    ? intval($_GET['patient_id'])
    : 0;

if ($patient_id <= 0) {
    die("Invalid patient.");
}


// =========================================================
// GET PATIENT
// =========================================================

$sql = "SELECT * FROM patients WHERE id = ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $patient_id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {

    $stmt->close();

    die("Patient not found.");
}

$patient = $result->fetch_assoc();

$stmt->close();


// =========================================================
// PATIENT STATUS
// =========================================================

$patientStatus = !empty($patient['status'])
    ? $patient['status']
    : 'Active';


// =========================================================
// FULL NAME
// =========================================================

$fullName = $patient['first_name'];

if (!empty($patient['middle_name'])) {
    $fullName .= " " . $patient['middle_name'];
}

$fullName .= " " . $patient['last_name'];


// =========================================================
// CHIEF COMPLAINT LIST
// =========================================================

$chiefComplaints = [

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

];


// =========================================================
// ASSESSMENT LIST
// =========================================================

$assessments = [

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

];


// =========================================================
// PREVENT CONSULTATION FOR DECEASED PATIENT
// =========================================================

if ($patientStatus === 'Deceased') {

    include __DIR__ . "/../includes/header.php";
    include __DIR__ . "/../includes/navigation.php";

    ?>

    <main class="main-container">

        <div class="consultation-unavailable-card">

            <div class="consultation-unavailable-icon">
                !
            </div>

            <h2 class="consultation-unavailable-title">
                Consultation Unavailable
            </h2>

            <div class="consultation-unavailable-name">
                <?php echo htmlspecialchars($fullName); ?>
            </div>

            <div class="consultation-unavailable-id">
                Patient ID:
                <strong>
                    <?php
                    echo htmlspecialchars(
                        $patient['patient_id']
                    );
                    ?>
                </strong>
            </div>

            <div class="consultation-unavailable-alert">

                This patient is marked as
                <strong>Deceased</strong>.

                <br><br>

                A new consultation cannot be created
                for this patient.

            </div>

            <a
                href="view.php?id=<?php echo $patient_id; ?>"
                class="btn btn-primary"
            >
                ← Back to Patient Profile
            </a>

        </div>

    </main>


    <style>

        .consultation-unavailable-card {
            max-width: 620px;
            margin: 40px auto;
            background: #ffffff;
            border: 1px solid #edf0f3;
            border-radius: 10px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .consultation-unavailable-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 18px;
            border-radius: 50%;
            background: #f8d7da;
            color: #842029;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            font-weight: bold;
        }

        .consultation-unavailable-title {
            margin: 0 0 12px;
            color: #842029;
            font-size: 22px;
        }

        .consultation-unavailable-name {
            color: #1f4e78;
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .consultation-unavailable-id {
            color: #777;
            font-size: 13px;
            margin-bottom: 22px;
        }

        .consultation-unavailable-alert {
            background: #f8d7da;
            border: 1px solid #f1b0b7;
            color: #842029;
            border-radius: 6px;
            padding: 15px 18px;
            font-size: 13px;
            line-height: 1.6;
            margin-bottom: 25px;
        }

        @media (max-width: 700px) {

            .consultation-unavailable-card {
                margin: 20px auto;
                padding: 25px 18px;
            }

            .consultation-unavailable-name {
                font-size: 18px;
            }

        }

    </style>

    <?php

    include __DIR__ . "/../includes/footer.php";

    $conn->close();

    exit;
}

?>


<?php

// =========================================================
// SHARED HEADER
// =========================================================

include __DIR__ . "/../includes/header.php";


// =========================================================
// SHARED NAVIGATION
// =========================================================

include __DIR__ . "/../includes/navigation.php";
require_once "../config/auth.php";
?>


<main class="main-container consultation-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="section-header consultation-page-header">

        <div>

            <h2 class="page-title">
                New Consultation
            </h2>

            <p class="page-description">
                Create a new consultation record for this patient.
            </p>

        </div>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION HEADER
    ====================================================== -->

    <div class="card consultation-patient-card">

        <div class="consultation-patient-content">

            <div>

                <div class="consultation-patient-label">
                    Patient
                </div>

                <div class="consultation-patient-name">
                    <?php echo htmlspecialchars($fullName); ?>
                </div>

                <div class="consultation-patient-id">
                    Patient ID:
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $patient['patient_id']
                        );
                        ?>
                    </strong>
                </div>

            </div>


            <div class="consultation-patient-actions">

                <a
                    href="view.php?id=<?php echo $patient_id; ?>"
                    class="btn btn-secondary consultation-blue-btn"
                >
                    ← Patient Profile
                </a>

            </div>

        </div>

    </div>


    <!-- =====================================================
         REQUIRED FIELD NOTICE
    ====================================================== -->

    <div class="consultation-field-legend">

        <span>
            <strong class="required-star">*</strong>
            Required field
        </span>

    </div>


    <!-- =====================================================
         CONSULTATION FORM
    ====================================================== -->

    <form
        action="save_consultation.php"
        method="POST"
        id="consultationForm"
    >

        <input
            type="hidden"
            name="patient_id"
            value="<?php echo $patient_id; ?>"
        >


        <!-- =================================================
             VISIT INFORMATION
        ================================================== -->

        <div class="card consultation-form-card">

            <div class="card-title">
                Visit Information
            </div>

            <div class="form-grid">

                <div class="form-group">

                    <label for="visit_date">
                        Visit Date
                        <span class="required-star">*</span>
                    </label>

                    <input
                        type="date"
                        id="visit_date"
                        name="visit_date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                    <small class="required-help">
                        Required
                    </small>

                </div>

            </div>

        </div>


        <!-- =================================================
             CLINICAL INFORMATION
        ================================================== -->

        <div class="card consultation-form-card">

            <div class="card-title">
                Clinical Information
            </div>

            <div class="form-grid">


                <!-- =================================================
                     CHIEF COMPLAINT
                ================================================== -->

                <div class="form-group full searchable-group">

                    <label for="chief_complaint">
                        Chief Complaint
                        <span class="required-star">*</span>
                    </label>


                    <div class="searchable-wrapper">

                        <input
                            type="text"
                            id="chief_complaint"
                            name="chief_complaint"
                            class="formal-text-field searchable-field"
                            placeholder="Select or search chief complaint..."
                            autocomplete="off"
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
                        style="display: none;"
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
                        >

                        <small class="required-help">
                            Required when "OTHERS" is selected.
                        </small>

                    </div>


                    <small class="required-help">
                        Required
                    </small>

                    <small class="form-help searchable-help">
                        Type to search. Matching complaints will appear below.
                        If not listed, select <strong>OTHERS</strong> and enter
                        the complaint in the separate field.
                    </small>

                </div>


                <!-- =================================================
                     HISTORY OF PRESENT ILLNESS
                ================================================== -->

                <div class="form-group full">

                    <label for="history_illness">
                        History of Present Illness
                        <span class="required-star">*</span>
                    </label>

                    <textarea
                        id="history_illness"
                        name="history_illness"
                        class="formal-text-field"
                        placeholder="Enter history of present illness..."
                        required
                    ></textarea>

                    <small class="required-help">
                        Required
                    </small>

                </div>

            </div>

        </div>


        <!-- =================================================
             VITAL SIGNS
        ================================================== -->

        <div class="card consultation-form-card">

            <div class="card-title">
                Vital Signs
            </div>

            <div class="form-grid-3">


                <!-- BLOOD PRESSURE -->

                <div class="form-group">

                    <label for="blood_pressure">
                        Blood Pressure
                    </label>

                    <input
                        type="text"
                        id="blood_pressure"
                        name="blood_pressure"
                        placeholder="120/80"
                        pattern="[0-9]{2,3}/[0-9]{2,3}"
                        title="Enter blood pressure like 120/80"
                        maxlength="7"
                    >

                </div>


                <!-- TEMPERATURE -->

                <div class="form-group">

                    <label for="temperature">
                        Temperature (°C)
                    </label>

                    <input
                        type="number"
                        id="temperature"
                        name="temperature"
                        step="0.1"
                        min="25"
                        max="45"
                        placeholder="36.5"
                    >

                </div>


                <!-- PULSE RATE -->

                <div class="form-group">

                    <label for="pulse_rate">
                        Pulse Rate (bpm)
                    </label>

                    <input
                        type="number"
                        id="pulse_rate"
                        name="pulse_rate"
                        min="20"
                        max="250"
                        placeholder="72"
                    >

                </div>

            </div>


            <div class="form-grid-3">


                <!-- RESPIRATORY RATE -->

                <div class="form-group">

                    <label for="respiratory_rate">
                        Respiratory Rate (/min)
                    </label>

                    <input
                        type="number"
                        id="respiratory_rate"
                        name="respiratory_rate"
                        min="5"
                        max="80"
                        placeholder="18"
                    >

                </div>


                <!-- OXYGEN SATURATION -->

                <div class="form-group">

                    <label for="oxygen_saturation">
                        SpO₂ (%)
                    </label>

                    <input
                        type="number"
                        id="oxygen_saturation"
                        name="oxygen_saturation"
                        min="50"
                        max="100"
                        placeholder="98"
                    >

                </div>


                <!-- WEIGHT -->

                <div class="form-group">

                    <label for="weight">
                        Weight (kg)
                    </label>

                    <input
                        type="number"
                        id="weight"
                        name="weight"
                        step="0.01"
                        min="0.1"
                        max="500"
                        placeholder="60"
                    >

                </div>

            </div>


            <div class="form-grid">


                <!-- HEIGHT -->

                <div class="form-group">

                    <label for="height">
                        Height (cm)
                    </label>

                    <input
                        type="number"
                        id="height"
                        name="height"
                        step="0.01"
                        min="20"
                        max="250"
                        placeholder="165"
                    >

                </div>

            </div>

        </div>


        <!-- =================================================
             ASSESSMENT & TREATMENT
        ================================================== -->

        <div class="card consultation-form-card">

            <div class="card-title">
                Assessment
            </div>

            <div class="form-grid">


                <!-- =================================================
                     ASSESSMENT
                ================================================== -->

                <div class="form-group full searchable-group">

                    <label for="assessment">
                        Diagnosis
                        <span class="required-star">*</span>
                    </label>


                    <div class="searchable-wrapper">

                        <input
                            type="text"
                            id="assessment"
                            name="assessment"
                            class="formal-text-field searchable-field"
                            placeholder="Search code or diagnosis..."
                            autocomplete="off"
                            required
                        >


                        <div
                            id="assessmentDropdown"
                            class="search-dropdown"
                        ></div>

                    </div>


                    <small class="required-help">
                        Required
                    </small>

                    <small class="form-help searchable-help">
                        Search using the code or diagnosis name.
                        If not listed, you may type your own diagnosis.
                    </small>

                </div>


                <!-- MANAGEMENT -->

                <div class="form-group full">

                    <label for="management">
                        Management / Plan
                    </label>

                    <textarea
                        id="management"
                        name="management"
                        class="formal-text-field"
                        placeholder="Enter management or treatment plan..."
                    ></textarea>

                </div>

            </div>

        </div>


        <!-- =================================================
             FOLLOW-UP & REMARKS
        ================================================== -->

        <div class="card consultation-form-card">

            <div class="card-title">
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
                        id="follow_up_date"
                        name="follow_up_date"
                    >

                    <small class="form-help follow-up-help">
                        No follow-up needed? Select today's date.
                        This will apply to today only.
                    </small>

                </div>


                <!-- REMARKS -->

                <div class="form-group">

                    <label for="remarks">
                        Remarks
                    </label>

                    <textarea
                        id="remarks"
                        name="remarks"
                        class="formal-text-field"
                        placeholder="Additional remarks..."
                    ></textarea>

                </div>

            </div>


            <!-- =================================================
                 FORM ACTIONS
            ================================================== -->

            <div class="form-actions">

                <a
                    href="view.php?id=<?php echo $patient_id; ?>"
                    class="btn btn-secondary consultation-blue-outline"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="btn btn-primary consultation-save-btn"
                >
                    Save Consultation
                </button>

            </div>

        </div>

    </form>

</main>


<!-- =========================================================
     PAGE-SPECIFIC STYLES
========================================================= -->

<style>

    /* =====================================================
       PAGE
    ====================================================== */

    .consultation-page {
        max-width: 1200px;
    }


    .consultation-page-header {
        margin-bottom: 18px;
    }


    /* =====================================================
       PATIENT CARD
    ====================================================== */

    .consultation-patient-card {
        margin-bottom: 14px;
        padding: 20px 24px;
    }


    .consultation-patient-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }


    .consultation-patient-label {
        color: #777;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 4px;
    }


    .consultation-patient-name {
        color: #1f4e78;
        font-size: 21px;
        font-weight: bold;
        line-height: 1.3;
        margin-bottom: 4px;
        text-transform: uppercase;
    }


    .consultation-patient-id {
        color: #666;
        font-size: 13px;
    }


    .consultation-patient-id strong {
        color: #1f4e78;
    }


    .consultation-patient-actions {
        flex-shrink: 0;
    }


    /* =====================================================
       REQUIRED FIELD LEGEND
    ====================================================== */

    .consultation-field-legend {
        display: flex;
        align-items: center;
        margin: 0 0 15px;
        padding: 0 3px;
        color: #777;
        font-size: 11px;
    }


    .consultation-field-legend span {
        display: inline-flex;
        align-items: center;
        gap: 3px;
    }


    .required-star {
        color: #dc3545;
        font-weight: 700;
        font-size: 14px;
        line-height: 1;
    }


    /* =====================================================
       FORM CARDS
    ====================================================== */

    .consultation-form-card {
        margin-bottom: 20px;
    }


    /* =====================================================
       REQUIRED FIELD STATUS
    ====================================================== */

    .required-help {
        display: block;
        margin-top: 5px;
        color: #dc3545;
        font-size: 10px;
        font-weight: 600;
    }


    /* =====================================================
       SEARCHABLE FIELD HELP
    ====================================================== */

    .searchable-help {
        display: block;
        margin-top: 5px;
        color: #777;
        font-size: 10px;
        line-height: 1.4;
    }


    /* =====================================================
       OTHER CHIEF COMPLAINT
    ====================================================== */

    .other-chief-complaint-group {
        margin-top: 12px;
        padding: 14px 15px;
        background: #f8fafc;
        border: 1px solid #dce3ea;
        border-left: 3px solid #1f4e78;
        border-radius: 6px;
    }


    .other-chief-complaint-group label {
        display: block;
        margin-bottom: 6px;
        color: #333;
        font-size: 13px;
        font-weight: 600;
    }


    .other-chief-complaint-group input {
        width: 100%;
        box-sizing: border-box;
    }


    /* =====================================================
       FOLLOW-UP GUIDE
    ====================================================== */

    .follow-up-help {
        display: block;
        margin-top: 6px;
        color: #777;
        font-size: 10px;
        line-height: 1.4;
    }


    /* =====================================================
       FORMAL TEXT FIELDS
    ====================================================== */

    .formal-text-field {
        text-transform: uppercase;
    }


    .formal-text-field::placeholder {
        text-transform: none;
    }


    /* =====================================================
       SEARCHABLE WRAPPER
    ====================================================== */

    .searchable-group {
        position: relative;
    }


    .searchable-wrapper {
        position: relative;
        width: 100%;
    }


    .searchable-field {
        width: 100%;
        box-sizing: border-box;
    }


    /* =====================================================
       CUSTOM SEARCH DROPDOWN
    ====================================================== */

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
            0 4px 12px rgba(0, 0, 0, 0.12);

        z-index: 9999;

        display: none;

        box-sizing: border-box;
    }


    .search-dropdown.show {
        display: block;
    }


    .search-dropdown-item {
        padding: 10px 12px;

        background: #ffffff;

        border-bottom: 1px solid #edf0f3;

        color: #333;

        font-size: 13px;

        line-height: 1.4;

        cursor: pointer;

        transition:
            background-color 0.12s ease,
            color 0.12s ease;
    }


    .search-dropdown-item:last-child {
        border-bottom: none;
    }


    .search-dropdown-item:hover,
    .search-dropdown-item.active {
        background: #f1f5f9;
        color: #1f4e78;
    }


    .search-dropdown-item strong {
        color: #1f4e78;
    }


    /* =====================================================
       NO MATCH
    ====================================================== */

    .search-dropdown-empty {
        padding: 10px 12px;

        color: #888;

        font-size: 12px;

        background: #ffffff;
    }


    /* =====================================================
       BLUE BUTTONS
    ====================================================== */

    .consultation-blue-btn {
        background: #1f4e78 !important;
        color: #ffffff !important;
        border: 1px solid #1f4e78 !important;
    }


    .consultation-blue-btn:hover {
        background: #163a5c !important;
        border-color: #163a5c !important;
    }


    .consultation-save-btn {
        background: #1f4e78 !important;
        color: #ffffff !important;
        border: 1px solid #1f4e78 !important;
        min-width: 160px;
    }


    .consultation-save-btn:hover {
        background: #163a5c !important;
        border-color: #163a5c !important;
    }


    .consultation-blue-outline {
        background: #ffffff !important;
        color: #1f4e78 !important;
        border: 1px solid #1f4e78 !important;
    }


    .consultation-blue-outline:hover {
        background: #1f4e78 !important;
        color: #ffffff !important;
    }


    /* =====================================================
       REQUIRED FIELD VISUAL
    ====================================================== */

    input:required,
    textarea:required {
        border-color: #cfd6dd;
    }


    input:required:focus,
    textarea:required:focus {
        border-color: #1f4e78;
        box-shadow: 0 0 0 2px rgba(31, 78, 120, 0.10);
        outline: none;
    }


    /* =====================================================
       RESPONSIVE
    ====================================================== */

    @media (max-width: 700px) {

        .consultation-patient-card {
            padding: 18px;
        }


        .consultation-patient-content {
            flex-direction: column;
            align-items: flex-start;
        }


        .consultation-patient-actions {
            width: 100%;
        }


        .consultation-patient-actions .btn {
            width: 100%;
        }


        .consultation-patient-name {
            font-size: 19px;
        }


        .consultation-field-legend {
            flex-wrap: wrap;
            gap: 10px 18px;
        }


        .form-actions {
            flex-direction: column;
        }


        .form-actions .btn {
            width: 100%;
        }


        .search-dropdown {
            max-height: 200px;
        }

    }

</style>


<!-- =========================================================
     AUTOMATIC FORMATTING + SEARCH
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    // =====================================================
    // PHP LISTS → JAVASCRIPT
    // =====================================================

    const chiefComplaints =
        <?php echo json_encode(
            $chiefComplaints,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    const assessments =
        <?php echo json_encode(
            $assessments,
            JSON_UNESCAPED_UNICODE |
            JSON_UNESCAPED_SLASHES
        ); ?>;


    // =====================================================
    // FORMAL TEXT FIELDS
    // =====================================================

    const formalFields = document.querySelectorAll(
        ".formal-text-field"
    );


    function uppercaseWhileTyping(value) {

        return value.toUpperCase();

    }


    function cleanFormalText(value) {

        return value
            .replace(/\s+/g, " ")
            .trim()
            .toUpperCase();

    }


    formalFields.forEach(function (field) {

        field.addEventListener(
            "input",
            function () {

                const start =
                    this.selectionStart;

                const end =
                    this.selectionEnd;


                this.value =
                    uppercaseWhileTyping(
                        this.value
                    );


                try {

                    this.setSelectionRange(
                        start,
                        end
                    );

                } catch (e) {}

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

    });


    // =====================================================
    // SEARCH NORMALIZATION
    // =====================================================

    function normalizeSearch(value) {

        return value
            .toUpperCase()
            .replace(/[^A-Z0-9]+/g, " ")
            .replace(/\s+/g, " ")
            .trim();

    }


    // =====================================================
    // FUZZY SEARCH SCORE
    // =====================================================

    function getMatchScore(searchValue, itemValue) {

        const search =
            normalizeSearch(searchValue);

        const item =
            normalizeSearch(itemValue);


        if (!search) {
            return 0;
        }


        if (!item) {
            return 0;
        }


        // =================================================
        // EXACT
        // =================================================

        if (item === search) {
            return 1000;
        }


        // =================================================
        // STARTS WITH
        // =================================================

        if (item.startsWith(search)) {

            return 900 - Math.min(
                item.length - search.length,
                100
            );

        }


        // =================================================
        // CONTAINS
        // =================================================

        if (item.includes(search)) {

            return 800 - Math.min(
                item.indexOf(search),
                100
            );

        }


        // =================================================
        // WORD STARTS WITH SEARCH
        // =================================================

        const words =
            item.split(" ");


        for (let i = 0; i < words.length; i++) {

            if (words[i].startsWith(search)) {

                return 750 - Math.min(
                    i * 10,
                    100
                );

            }

        }


        // =================================================
        // FUZZY SPELLING
        // =================================================

        const compactSearch =
            search.replace(/\s/g, "");

        const compactItem =
            item.replace(/\s/g, "");


        if (
            compactSearch.length >= 2 &&
            compactItem.length >= 2
        ) {

            const distance =
                levenshteinDistance(
                    compactSearch,
                    compactItem
                );


            const maxLength =
                Math.max(
                    compactSearch.length,
                    compactItem.length
                );


            const similarity =
                1 -
                (distance / maxLength);


            if (similarity >= 0.45) {

                return Math.round(
                    500 * similarity
                );

            }

        }


        return 0;

    }


    // =====================================================
    // LEVENSHTEIN DISTANCE
    // =====================================================

    function levenshteinDistance(a, b) {

        const matrix = [];


        const aLength =
            a.length;

        const bLength =
            b.length;


        if (aLength === 0) {
            return bLength;
        }


        if (bLength === 0) {
            return aLength;
        }


        for (
            let i = 0;
            i <= bLength;
            i++
        ) {

            matrix[i] = [i];

        }


        for (
            let j = 0;
            j <= aLength;
            j++
        ) {

            matrix[0][j] = j;

        }


        for (
            let i = 1;
            i <= bLength;
            i++
        ) {

            for (
                let j = 1;
                j <= aLength;
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

                            matrix[i - 1][j - 1] + 1,

                            matrix[i][j - 1] + 1,

                            matrix[i - 1][j] + 1

                        );

                }

            }

        }


        return matrix[bLength][aLength];

    }


    // =====================================================
    // CREATE SEARCHABLE DROPDOWN
    // =====================================================

    function setupSearchableField(
        inputId,
        dropdownId,
        data
    ) {

        const input =
            document.getElementById(inputId);


        const dropdown =
            document.getElementById(dropdownId);


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


        function showResults() {

            const searchValue =
                normalizeSearch(
                    input.value
                );


            if (!searchValue) {

                hideDropdown();

                return;

            }


            const scoredResults =
                data
                    .map(function (item) {

                        return {

                            value: item,

                            score:
                                getMatchScore(
                                    searchValue,
                                    item
                                )

                        };

                    })
                    .filter(function (result) {

                        return result.score > 0;

                    })
                    .sort(function (a, b) {

                        return b.score - a.score;

                    });


            const results =
                scoredResults.slice(
                    0,
                    8
                );


            if (results.length === 0) {

                hideDropdown();

                return;

            }


            dropdown.innerHTML = "";


            results.forEach(
                function (result, index) {

                    const item =
                        document.createElement(
                            "div"
                        );


                    item.className =
                        "search-dropdown-item";


                    item.dataset.value =
                        result.value;


                    item.textContent =
                        result.value;


                    item.addEventListener(
                        "mousedown",
                        function (event) {

                            event.preventDefault();


                            input.value =
                                result.value;


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


                            try {

                                input.setSelectionRange(
                                    input.value.length,
                                    input.value.length
                                );

                            } catch (e) {}

                        }
                    );


                    dropdown.appendChild(
                        item
                    );

                }
            );


            activeIndex = -1;


            dropdown.classList.add(
                "show"
            );

        }


        input.addEventListener(
            "input",
            function () {

                const start =
                    this.selectionStart;

                const end =
                    this.selectionEnd;


                this.value =
                    uppercaseWhileTyping(
                        this.value
                    );


                try {

                    this.setSelectionRange(
                        start,
                        end
                    );

                } catch (e) {}


                showResults();

            }
        );


        input.addEventListener(
            "focus",
            function () {

                if (
                    this.value.trim() !== ""
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
                    )
                ) {

                    return;

                }


                if (
                    event.key === "ArrowDown"
                ) {

                    event.preventDefault();


                    if (items.length === 0) {
                        return;
                    }


                    activeIndex++;


                    if (
                        activeIndex >=
                        items.length
                    ) {

                        activeIndex = 0;

                    }


                    updateActiveItem(
                        items
                    );

                }


                else if (
                    event.key === "ArrowUp"
                ) {

                    event.preventDefault();


                    if (items.length === 0) {
                        return;
                    }


                    activeIndex--;


                    if (
                        activeIndex < 0
                    ) {

                        activeIndex =
                            items.length - 1;

                    }


                    updateActiveItem(
                        items
                    );

                }


                else if (
                    event.key === "Enter"
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
                                "mousedown",
                                {
                                    bubbles: true
                                }
                            )
                        );

                    }

                }


                else if (
                    event.key === "Escape"
                ) {

                    hideDropdown();

                }

            }
        );


        function updateActiveItem(items) {

            items.forEach(
                function (item, index) {

                    item.classList.toggle(
                        "active",
                        index === activeIndex
                    );

                }
            );


            if (
                activeIndex >= 0 &&
                activeIndex < items.length
            ) {

                items[
                    activeIndex
                ].scrollIntoView({
                    block: "nearest"
                });

            }

        }


        input.addEventListener(
            "blur",
            function () {

                setTimeout(
                    function () {

                        input.value =
                            cleanFormalText(
                                input.value
                            );

                        hideDropdown();

                    },
                    150
                );

            }
        );


        document.addEventListener(
            "mousedown",
            function (event) {

                if (
                    !input.contains(event.target) &&
                    !dropdown.contains(event.target)
                ) {

                    hideDropdown();

                }

            }
        );

    }


    // =====================================================
    // SETUP CHIEF COMPLAINT SEARCH
    // =====================================================

    setupSearchableField(
        "chief_complaint",
        "chiefComplaintDropdown",
        chiefComplaints
    );


    // =====================================================
    // SETUP ASSESSMENT SEARCH
    // =====================================================

    setupSearchableField(
        "assessment",
        "assessmentDropdown",
        assessments
    );


    // =====================================================
    // OTHERS → SEPARATE CHIEF COMPLAINT FIELD
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


        if (value === "OTHERS") {

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
            updateOtherChiefComplaint
        );


        chiefComplaint.addEventListener(
            "change",
            updateOtherChiefComplaint
        );

    }


    // =====================================================
    // BLOOD PRESSURE
    // =====================================================

    const bloodPressure =
        document.getElementById(
            "blood_pressure"
        );


    if (bloodPressure) {

        bloodPressure.addEventListener(
            "input",
            function () {

                let value =
                    this.value.replace(
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


                if (
                    bpParts.length === 2
                ) {

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


                this.value = value;

            }
        );

    }


    // =====================================================
    // FOLLOW-UP DATE
    // =====================================================

    const followUpDate =
        document.getElementById(
            "follow_up_date"
        );


    // No automatic date is placed here.


    // =====================================================
    // FINAL FORM CLEANUP
    // =====================================================

    const form =
        document.getElementById(
            "consultationForm"
        );


    if (form) {

        form.addEventListener(
            "submit",
            function (event) {


                // =========================================
                // FORMAT TEXT FIELDS
                // =========================================

                formalFields.forEach(
                    function (field) {

                        field.value =
                            cleanFormalText(
                                field.value
                            );

                    }
                );


                // =========================================
                // CHECK OTHERS
                // =========================================

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

                        if (otherChiefComplaint) {

                            otherChiefComplaint.focus();

                        }

                        return;

                    }


                    /*
                     * IMPORTANT:
                     *
                     * Instead of saving "OTHERS",
                     * save the actual complaint entered
                     * in the separate field.
                     */

                    chiefComplaint.value =
                        cleanFormalText(
                            otherChiefComplaint.value
                        );

                }


                // =========================================
                // FORMAT BLOOD PRESSURE
                // =========================================

                if (bloodPressure) {

                    bloodPressure.value =
                        bloodPressure.value
                            .replace(
                                /[^0-9/]/g,
                                ""
                            )
                            .trim();

                }


                // =========================================
                // CHIEF COMPLAINT VALIDATION
                // =========================================

                if (
                    chiefComplaint &&
                    chiefComplaint.value.trim() === ""
                ) {

                    event.preventDefault();

                    alert(
                        "Please select or enter a Chief Complaint."
                    );

                    chiefComplaint.focus();

                    return;

                }


                // =========================================
                // ASSESSMENT VALIDATION
                // =========================================

                const assessment =
                    document.getElementById(
                        "assessment"
                    );


                if (
                    assessment &&
                    assessment.value.trim() === ""
                ) {

                    event.preventDefault();

                    alert(
                        "Please select or enter an Assessment / Diagnosis."
                    );

                    assessment.focus();

                    return;

                }

            }
        );

    }

});

</script>


<?php

// =========================================================
// SHARED FOOTER
// =========================================================

include __DIR__ . "/../includes/footer.php";


// =========================================================
// CLOSE DATABASE
// =========================================================

$conn->close();

?>