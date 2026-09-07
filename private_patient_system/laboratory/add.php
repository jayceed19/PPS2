<?php

require_once "../config/database.php";

/* =========================================================
   GET PATIENT ID
========================================================= */

$patientId = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($patientId <= 0) {
    die("Invalid patient ID.");
}


/* =========================================================
   GET PATIENT
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        patient_id,
        last_name,
        first_name,
        middle_name,
        birthdate,
        sex,
        address,
        contact_no
    FROM patients
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param("i", $patientId);
$stmt->execute();

$result = $stmt->get_result();
$patient = $result->fetch_assoc();

$stmt->close();

if (!$patient) {
    die("Patient not found.");
}


/* =========================================================
   PATIENT NAME
========================================================= */

$middleInitial = "";

if (!empty($patient['middle_name'])) {

    $middleName = trim($patient['middle_name']);

    $middleInitial =
        " " .
        strtoupper(substr($middleName, 0, 1)) .
        ".";
}

$fullName =
    strtoupper(trim($patient['last_name'])) .
    ", " .
    strtoupper(trim($patient['first_name'])) .
    $middleInitial;


/* =========================================================
   AGE
========================================================= */

$age = "";

if (!empty($patient['birthdate'])) {

    try {

        $birthDate = new DateTime($patient['birthdate']);
        $today = new DateTime();

        $age = $birthDate->diff($today)->y;

    } catch (Exception $e) {

        $age = "";

    }
}


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Add Laboratory";
$pageSubtitle = "Laboratory Results";

$basePath = "../";
$activePage = "patients";

include "../includes/header.php";
include "../includes/navigation.php";
require_once "../config/auth.php";

/* =========================================================
   LABORATORY TEST GROUPS
========================================================= */


/* =========================================================
   1. CBC / HEMATOLOGY
=========================================================

   CBC is now divided into individual parameters.
========================================================= */

$cbcTests = array(

    "HEMOGLOBIN",
    "HEMATOCRIT",
    "RED BLOOD CELL COUNT",
    "WHITE BLOOD CELL COUNT",
    "PLATELET COUNT",
    "MCV",
    "MCH",
    "MCHC",
    "RDW",
    "NEUTROPHILS",
    "LYMPHOCYTES",
    "MONOCYTES",
    "EOSINOPHILS",
    "BASOPHILS"

);


/* =========================================================
   2. BLOOD CHEMISTRY
========================================================= */

$chemistryTests = array(

    "RANDOM BLOOD SUGAR",
    "FASTING BLOOD SUGAR",
    "CREATININE",
    "HBA1C",

    /* -------------------------
       LIPID PROFILE
    ------------------------- */

    "TOTAL CHOLESTEROL",
    "TRIGLYCERIDES",
    "HDL CHOLESTEROL",
    "LDL CHOLESTEROL",

    "ORAL GLUCOSE TOLERANCE TEST"

);


/* =========================================================
   3. IMAGING / CARDIAC
========================================================= */

$imagingTests = array(

    "CHEST X-RAY",
    "ELECTROCARDIOGRAM (ECG)"

);


/* =========================================================
   4. STOOL EXAMINATION
========================================================= */

$stoolTests = array(

    "FECAL OCCULT BLOOD",
    "FECALYSIS"

);


/* =========================================================
   5. URINALYSIS
========================================================= */

$urineTests = array(

    "URINALYSIS"

);


/* =========================================================
   6. OTHER COMMON TESTS
========================================================= */

$otherCommonTests = array(

    "PAP SMEAR",
    "PPD TEST (TUBERCULOSIS)",
    "SPUTUM MICROSCOPY"

);


/* =========================================================
   REFERENCE RANGES
=========================================================

   These are displayed as editable values.
   They can still be changed manually when needed,
   especially for age/sex-specific laboratory ranges.
========================================================= */

$referenceRanges = array(

    /* =========================
       CBC
    ========================== */

    "HEMOGLOBIN" =>
        "MALE: 13.5-17.5 G/DL | FEMALE: 12.0-16.0 G/DL",

    "HEMATOCRIT" =>
        "MALE: 41-53% | FEMALE: 36-46%",

    "RED BLOOD CELL COUNT" =>
        "MALE: 4.5-5.9 M/UL | FEMALE: 4.1-5.1 M/UL",

    "WHITE BLOOD CELL COUNT" =>
        "4.0-11.0 K/UL",

    "PLATELET COUNT" =>
        "150-450 K/UL",

    "MCV" =>
        "80-100 FL",

    "MCH" =>
        "27-33 PG",

    "MCHC" =>
        "32-36 G/DL",

    "RDW" =>
        "11.5-14.5%",

    "NEUTROPHILS" =>
        "40-70%",

    "LYMPHOCYTES" =>
        "20-40%",

    "MONOCYTES" =>
        "2-8%",

    "EOSINOPHILS" =>
        "1-4%",

    "BASOPHILS" =>
        "0-1%",


    /* =========================
       BLOOD CHEMISTRY
    ========================== */

    "RANDOM BLOOD SUGAR" =>
        "70-140 MG/DL",

    "FASTING BLOOD SUGAR" =>
        "70-99 MG/DL",

    "CREATININE" =>
        "MALE: 0.74-1.35 MG/DL | FEMALE: 0.59-1.04 MG/DL",

    "HBA1C" =>
        "<5.7%",


    /* =========================
       LIPID PROFILE
    ========================== */

    "TOTAL CHOLESTEROL" =>
        "<200 MG/DL",

    "TRIGLYCERIDES" =>
        "<150 MG/DL",

    "HDL CHOLESTEROL" =>
        ">=40 MG/DL",

    "LDL CHOLESTEROL" =>
        "<100 MG/DL",


    "ORAL GLUCOSE TOLERANCE TEST" =>
        "SEE OGTT PARAMETERS",


    /* =========================
       IMAGING
    ========================== */

    "CHEST X-RAY" =>
        "SEE RADIOLOGY REPORT",

    "ELECTROCARDIOGRAM (ECG)" =>
        "SEE ECG PARAMETERS",


    /* =========================
       STOOL
    ========================== */

    "FECAL OCCULT BLOOD" =>
        "NEGATIVE",

    "FECALYSIS" =>
        "SEE FECALYSIS PARAMETERS",


    /* =========================
       URINALYSIS
    ========================== */

    "URINALYSIS" =>
        "SEE URINALYSIS PARAMETERS",


    /* =========================
       OTHER COMMON
    ========================== */

    "PAP SMEAR" =>
        "SEE CYTOLOGY REPORT",

    "PPD TEST (TUBERCULOSIS)" =>
        "NEGATIVE",

    "SPUTUM MICROSCOPY" =>
        "NEGATIVE"

);


/* =========================================================
   DEFAULT UNITS
========================================================= */

$defaultUnits = array(

    /* CBC */

    "HEMOGLOBIN" =>
        "G/DL",

    "HEMATOCRIT" =>
        "%",

    "RED BLOOD CELL COUNT" =>
        "M/UL",

    "WHITE BLOOD CELL COUNT" =>
        "K/UL",

    "PLATELET COUNT" =>
        "K/UL",

    "MCV" =>
        "FL",

    "MCH" =>
        "PG",

    "MCHC" =>
        "G/DL",

    "RDW" =>
        "%",

    "NEUTROPHILS" =>
        "%",

    "LYMPHOCYTES" =>
        "%",

    "MONOCYTES" =>
        "%",

    "EOSINOPHILS" =>
        "%",

    "BASOPHILS" =>
        "%",


    /* CHEMISTRY */

    "RANDOM BLOOD SUGAR" =>
        "MG/DL",

    "FASTING BLOOD SUGAR" =>
        "MG/DL",

    "CREATININE" =>
        "MG/DL",

    "HBA1C" =>
        "%",


    /* LIPID */

    "TOTAL CHOLESTEROL" =>
        "MG/DL",

    "TRIGLYCERIDES" =>
        "MG/DL",

    "HDL CHOLESTEROL" =>
        "MG/DL",

    "LDL CHOLESTEROL" =>
        "MG/DL",

    "ORAL GLUCOSE TOLERANCE TEST" =>
        "",


    /* IMAGING */

    "CHEST X-RAY" =>
        "",

    "ELECTROCARDIOGRAM (ECG)" =>
        "",


    /* STOOL */

    "FECAL OCCULT BLOOD" =>
        "",

    "FECALYSIS" =>
        "",


    /* URINE */

    "URINALYSIS" =>
        "",


    /* OTHER COMMON */

    "PAP SMEAR" =>
        "",

    "PPD TEST (TUBERCULOSIS)" =>
        "MM",

    "SPUTUM MICROSCOPY" =>
        ""

);

?>

<style>

/* =========================================================
   PAGE
========================================================= */

.lab-page {
    max-width: 1250px;
    margin: 25px auto 50px;
    padding: 0 20px;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.lab-page-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 20px;
    margin-bottom: 20px;
}

.lab-title h2 {
    margin: 0;
    color: #1f2937;
    font-size: 28px;
    font-weight: 700;
}

.lab-title p {
    margin: 6px 0 0;
    color: #6b7280;
    font-size: 14px;
}

.back-button {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    text-decoration: none;
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
    padding: 9px 15px;
    border-radius: 7px;
    font-size: 14px;
    font-weight: 600;
}

.back-button:hover {
    background: #e5e7eb;
}


/* =========================================================
   PATIENT CARD
========================================================= */

.patient-card {
    background: #ffffff;
    border: 1px solid #dbe3ea;
    border-radius: 10px;
    margin-bottom: 20px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.patient-card-header {
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
    padding: 13px 18px;
    font-size: 14px;
    font-weight: 700;
    color: #374151;
}

.patient-card-body {
    padding: 18px;
}

.patient-grid {
    display: grid;
    grid-template-columns: 1.3fr 0.8fr 0.8fr 1.8fr;
    gap: 18px;
}

.patient-item {
    min-width: 0;
}

.patient-label {
    display: block;
    color: #6b7280;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    margin-bottom: 4px;
    letter-spacing: 0.3px;
}

.patient-value {
    color: #111827;
    font-size: 14px;
    font-weight: 600;
    word-break: break-word;
}


/* =========================================================
   LAB CARD
========================================================= */

.lab-card {
    background: #ffffff;
    border: 1px solid #dbe3ea;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}

.lab-card-header {
    background: #0f5f9f;
    color: #ffffff;
    padding: 15px 18px;
}

.lab-card-title {
    font-size: 16px;
    font-weight: 700;
}

.lab-card-subtitle {
    font-size: 12px;
    opacity: 0.9;
    margin-top: 3px;
}


/* =========================================================
   DATE
========================================================= */

.date-section {
    padding: 18px;
    border-bottom: 1px solid #e5e7eb;
    background: #fafafa;
}

.date-field {
    max-width: 260px;
}

.date-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 6px;
}

.date-field input {
    width: 100%;
    box-sizing: border-box;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 7px;
    background: #ffffff;
    color: #111827;
    font-size: 14px;
    outline: none;
}

.date-field input:focus {
    border-color: #0f5f9f;
    box-shadow: 0 0 0 2px rgba(15,95,159,0.10);
}

.required-note {
    color: #64748b;
    font-size: 11px;
    margin-top: 5px;
}


/* =========================================================
   STEP NAVIGATION
========================================================= */

.step-navigation {
    padding: 16px 18px;
    background: #f8fafc;
    border-bottom: 1px solid #e5e7eb;
}

.step-list {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}

.step-item {
    display: flex;
    align-items: center;
    gap: 7px;
    color: #94a3b8;
    font-size: 11px;
    font-weight: 700;
}

.step-number {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #e2e8f0;
    color: #64748b;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
}

.step-item.active {
    color: #0f5f9f;
}

.step-item.active .step-number {
    background: #0f5f9f;
    color: #ffffff;
}

.step-item.completed {
    color: #15803d;
}

.step-item.completed .step-number {
    background: #dcfce7;
    color: #15803d;
}

.step-line {
    width: 35px;
    height: 1px;
    background: #cbd5e1;
}


/* =========================================================
   FORM STEPS
========================================================= */

.lab-step {
    display: none;
}

.lab-step.active {
    display: block;
}

.step-header {
    padding: 18px;
    background: #ffffff;
    border-bottom: 1px solid #e5e7eb;
}

.step-header h3 {
    margin: 0;
    color: #1f2937;
    font-size: 19px;
}

.step-header p {
    margin: 5px 0 0;
    color: #64748b;
    font-size: 12px;
}


/* =========================================================
   TABLE
========================================================= */

.table-scroll {
    width: 100%;
    overflow-x: auto;
}

.lab-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 850px;
    table-layout: fixed;
}

.lab-table thead th {
    background: #f1f5f9;
    color: #334155;
    border-bottom: 2px solid #cbd5e1;
    padding: 12px 10px;
    text-align: left;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.lab-table thead th:nth-child(1) {
    width: 34%;
}

.lab-table thead th:nth-child(2) {
    width: 22%;
}

.lab-table thead th:nth-child(3) {
    width: 14%;
}

.lab-table thead th:nth-child(4) {
    width: 30%;
}

.lab-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
}

.lab-table tbody tr:nth-child(even) {
    background: #fafafa;
}

.lab-table tbody tr:hover {
    background: #f8fbff;
}

.lab-table td {
    padding: 8px 10px;
    vertical-align: middle;
    box-sizing: border-box;
}

.test-name-cell {
    color: #374151;
    font-size: 13px;
    font-weight: 600;
    word-break: break-word;
}


/* =========================================================
   INPUTS
========================================================= */

.lab-input {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    height: 36px;
    padding: 7px 10px;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    background: #ffffff;
    color: #111827;
    font-size: 13px;
    outline: none;
}

.lab-input:focus {
    border-color: #0f5f9f;
    box-shadow: 0 0 0 2px rgba(15,95,159,0.10);
}

.lab-input::placeholder {
    color: #9ca3af;
    text-transform: none;
}


/* =========================================================
   REFERENCE RANGE
========================================================= */

.reference-input {
    font-size: 10px;
    font-weight: 500;
    background: #f8fafc;
    color: #374151;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.reference-input:focus {
    background: #ffffff;
}


/* =========================================================
   OTHER TESTS
========================================================= */

.other-row td {
    background: #fffdf5;
}

.other-name-input {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    height: 36px;
    padding: 7px 10px;
    border: 1px solid #d6b656;
    border-radius: 6px;
    background: #fffef8;
    color: #111827;
    font-size: 13px;
    outline: none;
}

.other-name-input:focus {
    border-color: #b48b00;
    box-shadow: 0 0 0 2px rgba(180,139,0,0.10);
}


/* =========================================================
   ADD OTHER
========================================================= */

.other-action {
    padding: 14px 18px;
    background: #fffdf5;
    border-top: 1px solid #eee2ad;
}

.add-other-btn {
    border: 1px solid #0f5f9f;
    background: #ffffff;
    color: #0f5f9f;
    border-radius: 7px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
}

.add-other-btn:hover {
    background: #0f5f9f;
    color: #ffffff;
}


/* =========================================================
   REMOVE OTHER
========================================================= */

.remove-other-btn {
    flex: 0 0 30px;
    border: 0;
    background: transparent;
    color: #dc2626;
    font-size: 18px;
    font-weight: 700;
    cursor: pointer;
    width: 30px;
    height: 30px;
    border-radius: 5px;
}

.remove-other-btn:hover {
    background: #fee2e2;
}


/* =========================================================
   HELP
========================================================= */

.lab-help {
    padding: 12px 18px;
    color: #64748b;
    font-size: 12px;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}


/* =========================================================
   STEP BUTTONS
========================================================= */

.step-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 10px;
    padding: 18px;
    border-top: 1px solid #e5e7eb;
    background: #ffffff;
}

.step-left,
.step-right {
    display: flex;
    gap: 10px;
}

.prev-btn,
.next-btn,
.cancel-btn,
.save-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 120px;
    padding: 10px 18px;
    border-radius: 7px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    cursor: pointer;
    box-sizing: border-box;
}

.prev-btn,
.cancel-btn {
    background: #f3f4f6;
    color: #374151;
    border: 1px solid #d1d5db;
}

.prev-btn:hover,
.cancel-btn:hover {
    background: #e5e7eb;
}

.next-btn {
    background: #0f5f9f;
    color: #ffffff;
    border: 1px solid #0f5f9f;
}

.next-btn:hover {
    background: #0b4f85;
}

.save-btn {
    background: #15803d;
    color: #ffffff;
    border: 1px solid #15803d;
}

.save-btn:hover {
    background: #166534;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 800px) {

    .lab-page {
        padding: 0 12px;
        margin-top: 18px;
    }

    .lab-page-header {
        flex-direction: column;
    }

    .patient-grid {
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    .lab-title h2 {
        font-size: 23px;
    }

    .lab-table {
        min-width: 850px;
    }

    .step-list {
        justify-content: flex-start;
        overflow-x: auto;
        flex-wrap: nowrap;
        padding-bottom: 3px;
    }

    .step-item {
        flex: 0 0 auto;
    }

    .step-line {
        flex: 0 0 25px;
    }

}


@media (max-width: 520px) {

    .patient-grid {
        grid-template-columns: 1fr;
    }

    .step-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .step-left,
    .step-right {
        width: 100%;
    }

    .step-left .prev-btn,
    .step-left .cancel-btn,
    .step-right .next-btn,
    .step-right .save-btn {
        width: 100%;
    }

}


/* =========================================================
   OTHER STEP
========================================================= */

.other-step-table {
    min-width: 850px;
}

</style>


<div class="lab-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="lab-page-header">

        <div class="lab-title">

            <h2>Laboratory Results</h2>

            <p>
                Enter laboratory results for this patient.
            </p>

        </div>


        <a
            href="index.php?id=<?php echo $patientId; ?>"
            class="back-button"
        >
            ← Back to Laboratory
        </a>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="patient-card">

        <div class="patient-card-header">
            Patient Information
        </div>

        <div class="patient-card-body">

            <div class="patient-grid">


                <div class="patient-item">

                    <span class="patient-label">
                        Patient Name
                    </span>

                    <div class="patient-value">
                        <?php echo htmlspecialchars($fullName); ?>
                    </div>

                </div>


                <div class="patient-item">

                    <span class="patient-label">
                        Patient ID
                    </span>

                    <div class="patient-value">
                        <?php
                        echo htmlspecialchars(
                            $patient['patient_id']
                        );
                        ?>
                    </div>

                </div>


                <div class="patient-item">

                    <span class="patient-label">
                        Age / Sex
                    </span>

                    <div class="patient-value">

                        <?php
                        echo htmlspecialchars($age);
                        echo " / ";
                        echo htmlspecialchars(
                            strtoupper($patient['sex'])
                        );
                        ?>

                    </div>

                </div>


                <div class="patient-item">

                    <span class="patient-label">
                        Address
                    </span>

                    <div class="patient-value">

                        <?php
                        echo htmlspecialchars(
                            $patient['address']
                        );
                        ?>

                    </div>

                </div>


            </div>

        </div>

    </div>


    <!-- =====================================================
         LABORATORY CARD
    ====================================================== -->

    <div class="lab-card">


        <div class="lab-card-header">

            <div class="lab-card-title">
                Laboratory Examination
            </div>

            <div class="lab-card-subtitle">
                Complete the laboratory results step-by-step.
            </div>

        </div>


        <form
            action="save.php"
            method="POST"
            id="laboratoryForm"
            autocomplete="off"
        >


            <input
                type="hidden"
                name="patient_id"
                value="<?php echo $patientId; ?>"
            >


            <!-- =================================================
                 DATE
            ================================================== -->

            <div class="date-section">

                <div class="date-field">

                    <label for="test_date">
                        Laboratory Date
                    </label>

                    <input
                        type="date"
                        id="test_date"
                        name="test_date"
                        value="<?php echo date('Y-m-d'); ?>"
                        required
                    >

                    <div class="required-note">
                        Date when the laboratory examination was performed.
                    </div>

                </div>

            </div>


            <!-- =================================================
                 STEP NAVIGATION
            ================================================== -->

            <div class="step-navigation">

                <div class="step-list">


                    <?php

                    $stepNames = array(
                        "CBC",
                        "Chemistry",
                        "Imaging",
                        "Stool",
                        "Urinalysis",
                        "Other Common",
                        "Others"
                    );

                    foreach ($stepNames as $index => $stepName):

                    ?>

                        <?php if ($index > 0): ?>

                            <div class="step-line"></div>

                        <?php endif; ?>


                        <div
                            class="step-item <?php echo $index === 0 ? 'active' : ''; ?>"
                            data-step-indicator="<?php echo $index; ?>"
                        >

                            <span class="step-number">
                                <?php echo $index + 1; ?>
                            </span>

                            <?php echo htmlspecialchars($stepName); ?>

                        </div>

                    <?php endforeach; ?>


                </div>

            </div>


            <!-- =================================================
                 STEP 1 - CBC
            ================================================== -->

            <div
                class="lab-step active"
                data-step="0"
            >

                <div class="step-header">

                    <h3>
                        CBC / Hematology
                    </h3>

                    <p>
                        Complete blood count with individual hematology parameters.
                    </p>

                </div>


                <div class="table-scroll">

                    <table class="lab-table">

                        <thead>

                            <tr>

                                <th>
                                    Laboratory Test
                                </th>

                                <th>
                                    Result
                                </th>

                                <th>
                                    Unit
                                </th>

                                <th>
                                    Reference Range
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($cbcTests as $test): ?>

                                <tr>

                                    <td class="test-name-cell">

                                        <?php
                                        echo htmlspecialchars($test);
                                        ?>

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="<?php echo htmlspecialchars($test); ?>"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="result[]"
                                            class="lab-input uppercase-input"
                                            placeholder="Enter result"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="unit[]"
                                            class="lab-input uppercase-input"
                                            value="<?php
                                            echo isset($defaultUnits[$test])
                                                ? htmlspecialchars($defaultUnits[$test])
                                                : "";
                                            ?>"
                                            placeholder="Unit"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="reference_range[]"
                                            class="lab-input reference-input uppercase-input"
                                            value="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                            title="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                        >

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <div class="step-actions">

                    <div class="step-left">

                        <a
                            href="index.php?id=<?php echo $patientId; ?>"
                            class="cancel-btn"
                        >
                            Cancel
                        </a>

                    </div>


                    <div class="step-right">

                        <button
                            type="button"
                            class="next-btn"
                            onclick="nextStep()"
                        >
                            Next →
                        </button>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 STEP 2 - BLOOD CHEMISTRY
            ================================================== -->

            <div
                class="lab-step"
                data-step="1"
            >

                <div class="step-header">

                    <h3>
                        Blood Chemistry
                    </h3>

                    <p>
                        Blood sugar, kidney function, HbA1c and lipid profile examinations.
                    </p>

                </div>


                <div class="table-scroll">

                    <table class="lab-table">

                        <thead>

                            <tr>

                                <th>
                                    Laboratory Test
                                </th>

                                <th>
                                    Result
                                </th>

                                <th>
                                    Unit
                                </th>

                                <th>
                                    Reference Range
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($chemistryTests as $test): ?>

                                <tr>

                                    <td class="test-name-cell">

                                        <?php
                                        echo htmlspecialchars($test);
                                        ?>

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="<?php echo htmlspecialchars($test); ?>"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="result[]"
                                            class="lab-input uppercase-input"
                                            placeholder="Enter result"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="unit[]"
                                            class="lab-input uppercase-input"
                                            value="<?php
                                            echo isset($defaultUnits[$test])
                                                ? htmlspecialchars($defaultUnits[$test])
                                                : "";
                                            ?>"
                                            placeholder="Unit"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="reference_range[]"
                                            class="lab-input reference-input uppercase-input"
                                            value="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                            title="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                        >

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <div class="step-actions">

                    <div class="step-left">

                        <button
                            type="button"
                            class="prev-btn"
                            onclick="previousStep()"
                        >
                            ← Back
                        </button>

                    </div>


                    <div class="step-right">

                        <button
                            type="button"
                            class="next-btn"
                            onclick="nextStep()"
                        >
                            Next →
                        </button>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 STEP 3 - IMAGING
            ================================================== -->

            <div
                class="lab-step"
                data-step="2"
            >

                <div class="step-header">

                    <h3>
                        Imaging / Cardiac
                    </h3>

                    <p>
                        Imaging and cardiac examinations.
                    </p>

                </div>


                <div class="table-scroll">

                    <table class="lab-table">

                        <thead>

                            <tr>

                                <th>Laboratory Test</th>
                                <th>Result</th>
                                <th>Unit</th>
                                <th>Reference Range</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($imagingTests as $test): ?>

                                <tr>

                                    <td class="test-name-cell">

                                        <?php echo htmlspecialchars($test); ?>

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="<?php echo htmlspecialchars($test); ?>"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="result[]"
                                            class="lab-input uppercase-input"
                                            placeholder="Enter result"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="unit[]"
                                            class="lab-input uppercase-input"
                                            value="<?php
                                            echo isset($defaultUnits[$test])
                                                ? htmlspecialchars($defaultUnits[$test])
                                                : "";
                                            ?>"
                                            placeholder="Unit"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="reference_range[]"
                                            class="lab-input reference-input uppercase-input"
                                            value="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                        >

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <div class="step-actions">

                    <div class="step-left">

                        <button
                            type="button"
                            class="prev-btn"
                            onclick="previousStep()"
                        >
                            ← Back
                        </button>

                    </div>


                    <div class="step-right">

                        <button
                            type="button"
                            class="next-btn"
                            onclick="nextStep()"
                        >
                            Next →
                        </button>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 STEP 4 - STOOL
            ================================================== -->

            <div
                class="lab-step"
                data-step="3"
            >

                <div class="step-header">

                    <h3>
                        Stool Examination
                    </h3>

                    <p>
                        Fecal occult blood and fecalysis.
                    </p>

                </div>


                <div class="table-scroll">

                    <table class="lab-table">

                        <thead>

                            <tr>

                                <th>Laboratory Test</th>
                                <th>Result</th>
                                <th>Unit</th>
                                <th>Reference Range</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($stoolTests as $test): ?>

                                <tr>

                                    <td class="test-name-cell">

                                        <?php echo htmlspecialchars($test); ?>

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="<?php echo htmlspecialchars($test); ?>"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="result[]"
                                            class="lab-input uppercase-input"
                                            placeholder="Enter result"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="unit[]"
                                            class="lab-input uppercase-input"
                                            value="<?php
                                            echo isset($defaultUnits[$test])
                                                ? htmlspecialchars($defaultUnits[$test])
                                                : "";
                                            ?>"
                                            placeholder="Unit"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="reference_range[]"
                                            class="lab-input reference-input uppercase-input"
                                            value="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                        >

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <div class="step-actions">

                    <div class="step-left">

                        <button
                            type="button"
                            class="prev-btn"
                            onclick="previousStep()"
                        >
                            ← Back
                        </button>

                    </div>


                    <div class="step-right">

                        <button
                            type="button"
                            class="next-btn"
                            onclick="nextStep()"
                        >
                            Next →
                        </button>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 STEP 5 - URINALYSIS
            ================================================== -->

            <div
                class="lab-step"
                data-step="4"
            >

                <div class="step-header">

                    <h3>
                        Urinalysis
                    </h3>

                    <p>
                        Urine examination.
                    </p>

                </div>


                <div class="table-scroll">

                    <table class="lab-table">

                        <thead>

                            <tr>

                                <th>Laboratory Test</th>
                                <th>Result</th>
                                <th>Unit</th>
                                <th>Reference Range</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($urineTests as $test): ?>

                                <tr>

                                    <td class="test-name-cell">

                                        <?php echo htmlspecialchars($test); ?>

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="<?php echo htmlspecialchars($test); ?>"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="result[]"
                                            class="lab-input uppercase-input"
                                            placeholder="Enter result"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="unit[]"
                                            class="lab-input uppercase-input"
                                            value="<?php
                                            echo isset($defaultUnits[$test])
                                                ? htmlspecialchars($defaultUnits[$test])
                                                : "";
                                            ?>"
                                            placeholder="Unit"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="reference_range[]"
                                            class="lab-input reference-input uppercase-input"
                                            value="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                        >

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <div class="step-actions">

                    <div class="step-left">

                        <button
                            type="button"
                            class="prev-btn"
                            onclick="previousStep()"
                        >
                            ← Back
                        </button>

                    </div>


                    <div class="step-right">

                        <button
                            type="button"
                            class="next-btn"
                            onclick="nextStep()"
                        >
                            Next →
                        </button>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 STEP 6 - OTHER COMMON
            ================================================== -->

            <div
                class="lab-step"
                data-step="5"
            >

                <div class="step-header">

                    <h3>
                        Other Common Laboratory Tests
                    </h3>

                    <p>
                        Other examinations commonly requested in the clinic.
                    </p>

                </div>


                <div class="table-scroll">

                    <table class="lab-table">

                        <thead>

                            <tr>

                                <th>Laboratory Test</th>
                                <th>Result</th>
                                <th>Unit</th>
                                <th>Reference Range</th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php foreach ($otherCommonTests as $test): ?>

                                <tr>

                                    <td class="test-name-cell">

                                        <?php echo htmlspecialchars($test); ?>

                                        <input
                                            type="hidden"
                                            name="test_name[]"
                                            value="<?php echo htmlspecialchars($test); ?>"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="result[]"
                                            class="lab-input uppercase-input"
                                            placeholder="Enter result"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="unit[]"
                                            class="lab-input uppercase-input"
                                            value="<?php
                                            echo isset($defaultUnits[$test])
                                                ? htmlspecialchars($defaultUnits[$test])
                                                : "";
                                            ?>"
                                            placeholder="Unit"
                                        >

                                    </td>


                                    <td>

                                        <input
                                            type="text"
                                            name="reference_range[]"
                                            class="lab-input reference-input uppercase-input"
                                            value="<?php
                                            echo isset($referenceRanges[$test])
                                                ? htmlspecialchars($referenceRanges[$test])
                                                : "";
                                            ?>"
                                        >

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>


                <div class="step-actions">

                    <div class="step-left">

                        <button
                            type="button"
                            class="prev-btn"
                            onclick="previousStep()"
                        >
                            ← Back
                        </button>

                    </div>


                    <div class="step-right">

                        <button
                            type="button"
                            class="next-btn"
                            onclick="nextStep()"
                        >
                            Next →
                        </button>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 STEP 7 - OTHERS
            ================================================== -->

            <div
                class="lab-step"
                data-step="6"
            >

                <div class="step-header">

                    <h3>
                        Other Laboratory Tests
                    </h3>

                    <p>
                        Use this section for tests that are not included in the common list.
                    </p>

                </div>


                <div class="table-scroll">

                    <table class="lab-table other-step-table">

                        <thead>

                            <tr>

                                <th>Laboratory Test</th>
                                <th>Result</th>
                                <th>Unit</th>
                                <th>Reference Range</th>

                            </tr>

                        </thead>


                        <tbody id="otherTestsBody">

                            <tr class="other-row">

                                <td>

                                    <input
                                        type="text"
                                        name="other_test_name[]"
                                        class="other-name-input uppercase-input"
                                        placeholder="Enter other laboratory test"
                                    >

                                </td>


                                <td>

                                    <input
                                        type="text"
                                        name="other_result[]"
                                        class="lab-input uppercase-input"
                                        placeholder="Enter result"
                                    >

                                </td>


                                <td>

                                    <input
                                        type="text"
                                        name="other_unit[]"
                                        class="lab-input uppercase-input"
                                        placeholder="Unit"
                                    >

                                </td>


                                <td>

                                    <div
                                        style="
                                            display:flex;
                                            gap:6px;
                                            align-items:center;
                                            width:100%;
                                        "
                                    >

                                        <input
                                            type="text"
                                            name="other_reference_range[]"
                                            class="lab-input uppercase-input"
                                            placeholder="Reference range"
                                        >


                                        <button
                                            type="button"
                                            class="remove-other-btn"
                                            title="Remove this test"
                                            onclick="removeOtherTest(this)"
                                        >
                                            ×
                                        </button>

                                    </div>

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>


                <div class="other-action">

                    <button
                        type="button"
                        class="add-other-btn"
                        onclick="addOtherTest()"
                    >
                        + Add Other Test
                    </button>

                </div>


                <div class="lab-help">

                    <strong>Tip:</strong>

                    Leave tests blank if they were not performed.

                    Use <strong>+ Add Other Test</strong>
                    if the examination is not included in the common list.

                </div>


                <div class="step-actions">

                    <div class="step-left">

                        <button
                            type="button"
                            class="prev-btn"
                            onclick="previousStep()"
                        >
                            ← Back
                        </button>

                    </div>


                    <div class="step-right">

                        <button
                            type="submit"
                            class="save-btn"
                        >
                            ✓ Save Laboratory Results
                        </button>

                    </div>

                </div>

            </div>


        </form>

    </div>

</div>


<script>

/* =========================================================
   STEP SYSTEM
========================================================= */

var currentStep = 0;

var totalSteps = 7;


/* =========================================================
   SHOW STEP
========================================================= */

function showStep(step) {

    var steps =
        document.querySelectorAll(".lab-step");

    var indicators =
        document.querySelectorAll(".step-item");

    var i;


    if (step < 0) {
        step = 0;
    }


    if (step >= totalSteps) {
        step = totalSteps - 1;
    }


    currentStep = step;


    /* =========================
       HIDE ALL STEPS
    ========================== */

    for (i = 0; i < steps.length; i++) {

        steps[i].classList.remove("active");

    }


    /* =========================
       SHOW CURRENT STEP
    ========================== */

    if (steps[currentStep]) {

        steps[currentStep].classList.add("active");

    }


    /* =========================
       UPDATE INDICATORS
    ========================== */

    for (i = 0; i < indicators.length; i++) {

        indicators[i].classList.remove("active");
        indicators[i].classList.remove("completed");


        if (i === currentStep) {

            indicators[i].classList.add("active");

        }
        else if (i < currentStep) {

            indicators[i].classList.add("completed");

        }

    }


    /* =========================
       SCROLL TOP
    ========================== */

    window.scrollTo({
        top: 0,
        behavior: "smooth"
    });

}


/* =========================================================
   NEXT STEP
========================================================= */

function nextStep() {

    if (currentStep < totalSteps - 1) {

        showStep(currentStep + 1);

    }

}


/* =========================================================
   PREVIOUS STEP
========================================================= */

function previousStep() {

    if (currentStep > 0) {

        showStep(currentStep - 1);

    }

}


/* =========================================================
   UPPERCASE INPUT
========================================================= */

document.addEventListener(
    "input",
    function(event) {

        if (
            event.target.classList.contains(
                "uppercase-input"
            )
        ) {

            var start =
                event.target.selectionStart;

            var end =
                event.target.selectionEnd;


            event.target.value =
                event.target.value.toUpperCase();


            try {

                event.target.setSelectionRange(
                    start,
                    end
                );

            }
            catch (e) {

                /* Ignore cursor errors */

            }

        }

    }
);


/* =========================================================
   ADD OTHER TEST
========================================================= */

function addOtherTest() {

    var body =
        document.getElementById(
            "otherTestsBody"
        );


    var row =
        document.createElement("tr");


    row.className =
        "other-row";


    row.innerHTML =

        '<td>' +

            '<input ' +
                'type="text" ' +
                'name="other_test_name[]" ' +
                'class="other-name-input uppercase-input" ' +
                'placeholder="Enter other laboratory test"' +
            '>' +

        '</td>' +


        '<td>' +

            '<input ' +
                'type="text" ' +
                'name="other_result[]" ' +
                'class="lab-input uppercase-input" ' +
                'placeholder="Enter result"' +
            '>' +

        '</td>' +


        '<td>' +

            '<input ' +
                'type="text" ' +
                'name="other_unit[]" ' +
                'class="lab-input uppercase-input" ' +
                'placeholder="Unit"' +
            '>' +

        '</td>' +


        '<td>' +

            '<div ' +
                'style="display:flex; gap:6px; align-items:center; width:100%;"' +
            '>' +

                '<input ' +
                    'type="text" ' +
                    'name="other_reference_range[]" ' +
                    'class="lab-input uppercase-input" ' +
                    'placeholder="Reference range"' +
                '>' +


                '<button ' +
                    'type="button" ' +
                    'class="remove-other-btn" ' +
                    'title="Remove this test" ' +
                    'onclick="removeOtherTest(this)"' +
                '>' +

                    '×' +

                '</button>' +

            '</div>' +

        '</td>';


    body.appendChild(row);


    /* =========================
       FOCUS NEW TEST
    ========================== */

    var input =
        row.querySelector(
            ".other-name-input"
        );


    if (input) {

        input.focus();

    }

}


/* =========================================================
   REMOVE OTHER TEST
========================================================= */

function removeOtherTest(button) {

    var row =
        button.closest("tr");


    if (!row) {

        return;

    }


    row.remove();

}


/* =========================================================
   FORM VALIDATION
========================================================= */

document
    .getElementById("laboratoryForm")
    .addEventListener(
        "submit",
        function(event) {

            var commonResults =
                document.querySelectorAll(
                    'input[name="result[]"]'
                );


            var otherNames =
                document.querySelectorAll(
                    'input[name="other_test_name[]"]'
                );


            var otherResults =
                document.querySelectorAll(
                    'input[name="other_result[]"]'
                );


            var hasResult = false;

            var i;


            /* =========================
               CHECK COMMON RESULTS
            ========================== */

            for (
                i = 0;
                i < commonResults.length;
                i++
            ) {

                if (
                    commonResults[i]
                        .value
                        .trim() !== ""
                ) {

                    hasResult = true;

                    break;

                }

            }


            /* =========================
               CHECK OTHER RESULTS
            ========================== */

            if (!hasResult) {

                for (
                    i = 0;
                    i < otherResults.length;
                    i++
                ) {

                    if (
                        otherResults[i]
                            .value
                            .trim() !== ""
                    ) {

                        hasResult = true;

                        break;

                    }

                }

            }


            /* =========================
               REQUIRE AT LEAST ONE
            ========================== */

            if (!hasResult) {

                event.preventDefault();

                alert(
                    "Please enter at least one laboratory result before saving."
                );

                return false;

            }


            /* =========================
               CHECK OTHER ROWS
            ========================== */

            for (
                i = 0;
                i < otherResults.length;
                i++
            ) {

                var otherName = "";


                if (otherNames[i]) {

                    otherName =
                        otherNames[i]
                            .value
                            .trim();

                }


                var otherResult =
                    otherResults[i]
                        .value
                        .trim();


                if (
                    otherResult !== "" &&
                    otherName === ""
                ) {

                    event.preventDefault();


                    alert(
                        "Please enter the laboratory test name for every OTHER result."
                    );


                    if (otherNames[i]) {

                        otherNames[i].focus();

                    }


                    return false;

                }

            }


            return true;

        }
    );


/* =========================================================
   INITIAL STEP
========================================================= */

showStep(0);

</script>


<?php

include "../includes/footer.php";

?>