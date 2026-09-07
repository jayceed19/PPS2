<?php

require_once "../config/database.php";
require_once "../config/database.php";
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid consultation ID.");
}


/* =========================================================
   GET CONSULTATION + PATIENT
========================================================= */

$sql = "
    SELECT
        c.*,
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
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param("i", $id);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows == 0) {
    $stmt->close();
    die("Consultation not found.");
}

$data = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   FULL NAME
========================================================= */

$fullName = $data['first_name'];

if (!empty($data['middle_name'])) {
    $fullName .= " " . $data['middle_name'];
}

$fullName .= " " . $data['last_name'];


/* =========================================================
   AGE AT VISIT
========================================================= */

$age = "-";

if (!empty($data['birthdate'])) {

    $birthDate = new DateTime($data['birthdate']);
    $visitDate = new DateTime($data['visit_date']);

    $age = $visitDate->diff($birthDate)->y;
}


/* =========================================================
   FORMAT DATES
========================================================= */

$visitDateFormatted = date(
    "F d, Y",
    strtotime($data['visit_date'])
);

$followUpFormatted = "-";

if (!empty($data['follow_up_date'])) {

    $followUpFormatted = date(
        "F d, Y",
        strtotime($data['follow_up_date'])
    );
}


/* =========================================================
   GET PRESCRIPTION MEDICINES
========================================================= */

$prescriptionMedicines = array();

$prescriptionSql = "
    SELECT
        id,
        medicine_name,
        strength,
        quantity,
        breakfast,
        lunch,
        dinner,
        prescribed_date
    FROM prescriptions
    WHERE consultation_id = ?
    ORDER BY id ASC
";

$prescriptionStmt = $conn->prepare($prescriptionSql);

if ($prescriptionStmt) {

    $prescriptionStmt->bind_param(
        "i",
        $id
    );

    if ($prescriptionStmt->execute()) {

        $prescriptionResult =
            $prescriptionStmt->get_result();

        while (
            $prescriptionRow =
            $prescriptionResult->fetch_assoc()
        ) {

            $prescriptionMedicines[] =
                $prescriptionRow;
        }
    }

    $prescriptionStmt->close();
}
require_once "../config/auth.php";
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<link
    rel="icon"
    type="image/png"
    href="../asset/images/DCMDLOGO.png?v=1"
>

<title>
Consultation Record -
<?php echo htmlspecialchars($fullName); ?>
</title>


<style>

/* =========================================================
   PAGE SETUP
========================================================= */

@page {
    size: A4 portrait;
    margin: 8mm;
}

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    padding: 0;
}

body {
    background: #f4f6f9;
    color: #222;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    font-size: 11px;
}


/* =========================================================
   MAIN PRINT CONTAINER
========================================================= */

.print-container {

    width: 100%;

    max-width: 194mm;

    min-height: 281mm;

    margin: 0 auto;

    background: #fff;

    padding: 6mm 7mm;

    box-shadow:
        0 2px 10px
        rgba(0,0,0,0.08);
}


/* =========================================================
   HEADER
========================================================= */

.header {

    border-bottom: 2px solid #1f4e78;

    padding-bottom: 8px;

    margin-bottom: 10px;
}

.header-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;
}


/* =========================================================
   LOGO + CLINIC INFORMATION
========================================================= */

.header-left {

    display: flex;

    align-items: center;

    gap: 12px;

    min-width: 0;
}

.clinic-logo {

    width: 52px;

    height: 52px;

    object-fit: contain;

    flex-shrink: 0;
}

.clinic-information {

    min-width: 0;
}

.clinic-name {

    font-size: 18px;

    font-weight: bold;

    color: #1f4e78;

    letter-spacing: 0.3px;

    line-height: 1.15;
}

.clinic-subtitle {

    margin-top: 3px;

    font-size: 9px;

    color: #555;

    font-weight: 600;

    line-height: 1.25;
}

.document-title {

    margin-top: 5px;

    font-size: 10px;

    font-weight: bold;

    color: #555;

    letter-spacing: 1px;
}

.document-label {

    text-align: right;

    font-size: 9px;

    color: #777;

    flex-shrink: 0;
}

.document-label strong {

    display: block;

    color: #222;

    font-size: 11px;

    margin-top: 3px;
}


/* =========================================================
   PATIENT SUMMARY
========================================================= */

.patient-summary {

    border: 1px solid #cfd6dc;

    border-radius: 3px;

    padding: 9px 11px;

    margin-bottom: 10px;

    background: #fafbfc;
}

.patient-main {

    display: grid;

    grid-template-columns:
        2.3fr
        0.8fr
        0.8fr;

    gap: 15px;

    align-items: center;
}

.patient-name {

    font-size: 19px;

    font-weight: bold;

    color: #1f4e78;
}

.patient-id {

    margin-top: 3px;

    font-size: 10px;

    color: #666;
}

.summary-item {

    font-size: 9px;

    color: #777;
}

.summary-item strong {

    display: block;

    color: #222;

    font-size: 11px;

    margin-top: 3px;
}


/* =========================================================
   SECTIONS
========================================================= */

.section {

    margin-bottom: 9px;

    page-break-inside: avoid;
}

.section-title {

    background: #1f4e78;

    color: white;

    font-size: 10.5px;

    font-weight: bold;

    letter-spacing: 0.5px;

    padding: 6px 8px;

    margin-bottom: 6px;
}


/* =========================================================
   INFORMATION GRID
========================================================= */

.info-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 5px 14px;
}

.info-item {

    min-width: 0;

    padding: 5px 6px;

    border-bottom:
        1px solid #dfe4e8;
}

.info-item.full {

    grid-column: 1 / -1;
}


/* =========================================================
   LABELS
========================================================= */

.label {

    font-size: 8.5px;

    font-weight: bold;

    color: #666;

    text-transform: uppercase;

    margin-bottom: 3px;
}


/* =========================================================
   VALUES
========================================================= */

.value {

    font-size: 12px;

    font-weight: normal;

    line-height: 1.35;

    white-space: pre-line;

    overflow-wrap: break-word;
}

.patient-summary .patient-name {

    font-size: 19px;
}

.patient-summary .patient-id {

    font-size: 10px;
}

.info-item .value {

    font-size: 12px;
}


/* =========================================================
   CLINICAL INFORMATION
========================================================= */

.clinical-item {

    border: 1px solid #d1d8de;

    border-radius: 3px;

    padding: 7px 8px;

    margin-bottom: 5px;

    min-height: 34px;
}

.clinical-item:last-child {

    margin-bottom: 0;
}

.clinical-item .label {

    margin-bottom: 4px;
}

.clinical-item .value {

    font-size: 11.5px;

    line-height: 1.35;
}


/* =========================================================
   VITAL SIGNS
========================================================= */

.vitals {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    border: 1px solid #cfd6dc;
}

.vital {

    text-align: center;

    padding: 7px 4px;

    border-right:
        1px solid #d8dde1;

    border-bottom:
        1px solid #d8dde1;

    min-height: 43px;
}


/* Right borders */

.vital:nth-child(4),
.vital:nth-child(8) {

    border-right: none;
}


/* Bottom borders */

.vital:nth-child(5),
.vital:nth-child(6),
.vital:nth-child(7),
.vital:nth-child(8) {

    border-bottom: none;
}

.vital-label {

    font-size: 7.5px;

    color: #666;

    font-weight: bold;

    margin-bottom: 3px;
}

.vital-value {

    font-size: 12px;

    font-weight: bold;

    color: #222;
}


/* =========================================================
   PRESCRIPTION / MEDICATION
========================================================= */

.prescription-wrapper {

    width: 100%;

    border: 1px solid #cfd6dc;

    border-radius: 3px;

    overflow: hidden;
}

.prescription-table {

    width: 100%;

    border-collapse: collapse;

    table-layout: fixed;
}

.prescription-table th {

    background: #f1f4f7;

    color: #4f5b66;

    border-bottom:
        1px solid #cfd6dc;

    border-right:
        1px solid #d8dde1;

    padding: 6px 5px;

    font-size: 8px;

    font-weight: bold;

    text-transform: uppercase;

    text-align: center;

    vertical-align: middle;
}

.prescription-table th:last-child {

    border-right: none;
}

.prescription-table td {

    border-right:
        1px solid #d8dde1;

    border-bottom:
        1px solid #d8dde1;

    padding: 6px 6px;

    font-size: 9.5px;

    color: #222;

    vertical-align: middle;

    line-height: 1.25;

    word-break: break-word;
}

.prescription-table td:last-child {

    border-right: none;
}

.prescription-table tr:last-child td {

    border-bottom: none;
}

.prescription-table .medicine-cell {

    width: 34%;

    font-weight: bold;

    color: #1f4e78;
}

.prescription-table .qty-cell {

    width: 8%;

    text-align: center;

    font-weight: bold;
}

.prescription-table .dose-cell {

    width: 14.5%;

    text-align: center;
}

.prescription-empty {

    border: 1px solid #d1d8de;

    border-radius: 3px;

    padding: 10px;

    color: #777;

    font-size: 10px;

    text-align: center;

    background: #fafbfc;
}


/* =========================================================
   FOLLOW-UP
========================================================= */

.followup-status {

    display: inline-block;

    padding: 4px 8px;

    border-radius: 12px;

    background: #fff4cc;

    color: #856404;

    font-size: 9px;

    font-weight: bold;
}


/* =========================================================
   PRINT BUTTONS
========================================================= */

.buttons {

    text-align: center;

    margin-top: 12px;
}

.btn {

    display: inline-block;

    padding: 9px 17px;

    margin: 4px;

    border-radius: 5px;

    text-decoration: none;

    font-size: 11px;

    font-weight: bold;

    border: none;

    cursor: pointer;
}

.btn-print {

    background: #1f4e78;

    color: white;
}

.btn-back {

    background: #ddd;

    color: #333;
}


/* =========================================================
   PRINT MODE
========================================================= */

@media print {

    @page {

        size: A4 portrait;

        margin: 8mm;
    }

    html,
    body {

        width: 210mm;

        min-height: 297mm;

        background: white;
    }

    body {

        margin: 0;

        padding: 0;

        font-size: 11px;
    }

    .print-container {

        width: 194mm;

        max-width: 194mm;

        min-height: 281mm;

        margin: 0 auto;

        padding: 5mm 6mm;

        box-shadow: none;

        background: white;
    }


    /* -----------------------------------------------------
       HEADER
    ----------------------------------------------------- */

    .clinic-logo {

        width: 50px;

        height: 50px;
    }

    .clinic-name {

        font-size: 18px;
    }

    .clinic-subtitle {

        font-size: 8.5px;
    }

    .document-title {

        font-size: 10px;
    }


    /* -----------------------------------------------------
       PATIENT INFORMATION
    ----------------------------------------------------- */

    .patient-summary {

        padding: 9px 11px;
    }

    .patient-name {

        font-size: 19px;
    }

    .patient-id {

        font-size: 10px;
    }

    .summary-item {

        font-size: 9px;
    }

    .summary-item strong {

        font-size: 11px;
    }

    .info-item .value {

        font-size: 12px;
    }


    /* -----------------------------------------------------
       SECTION TITLES
    ----------------------------------------------------- */

    .section-title {

        font-size: 10.5px;

        padding: 6px 8px;
    }


    /* -----------------------------------------------------
       LABELS
    ----------------------------------------------------- */

    .label {

        font-size: 8.5px;
    }


    /* -----------------------------------------------------
       CLINICAL INFORMATION
    ----------------------------------------------------- */

    .clinical-item {

        padding: 7px 8px;

        min-height: 34px;
    }

    .clinical-item .value {

        font-size: 11.5px;

        line-height: 1.35;
    }


    /* -----------------------------------------------------
       VITALS
    ----------------------------------------------------- */

    .vital {

        min-height: 43px;

        padding: 7px 4px;
    }

    .vital-label {

        font-size: 7.5px;
    }

    .vital-value {

        font-size: 12px;
    }


    /* -----------------------------------------------------
       PRESCRIPTION
    ----------------------------------------------------- */

    .prescription-table th {

        font-size: 8px;

        padding: 6px 4px;
    }

    .prescription-table td {

        font-size: 9.5px;

        padding: 6px 5px;
    }


    /* -----------------------------------------------------
       HIDE BUTTONS
    ----------------------------------------------------- */

    .buttons {

        display: none !important;
    }


    /* -----------------------------------------------------
       PREVENT PAGE BREAKS
    ----------------------------------------------------- */

    .section,
    .patient-summary,
    .vitals,
    .clinical-item,
    .info-item,
    .prescription-wrapper {

        page-break-inside: avoid;

        break-inside: avoid;
    }

}


/* =========================================================
   SCREEN VIEW
========================================================= */

@media screen {

    .print-container {

        margin-top: 20px;

        margin-bottom: 20px;
    }
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    body {

        padding: 10px;
    }

    .print-container {

        padding: 20px;
    }

    .header-top {

        align-items: flex-start;
    }

    .header-left {

        align-items: flex-start;
    }

    .clinic-logo {

        width: 45px;

        height: 45px;
    }

    .clinic-name {

        font-size: 16px;
    }

    .clinic-subtitle {

        font-size: 8px;
    }

    .patient-main {

        grid-template-columns: 1fr;

        gap: 8px;
    }

    .info-grid {

        grid-template-columns: 1fr;
    }

    .info-item.full {

        grid-column: auto;
    }

    .vitals {

        grid-template-columns:
            repeat(2, 1fr);
    }

    .vital:nth-child(4),
    .vital:nth-child(8) {

        border-right:
            1px solid #d8dde1;
    }

    .vital:nth-child(2),
    .vital:nth-child(4),
    .vital:nth-child(6),
    .vital:nth-child(8) {

        border-right: none;
    }

    .vital:nth-child(5),
    .vital:nth-child(6) {

        border-bottom:
            1px solid #d8dde1;
    }

    .vital:nth-child(7),
    .vital:nth-child(8) {

        border-bottom: none;
    }

    .prescription-table {

        min-width: 650px;
    }

}


/* =========================================================
   PREVENT BAD PAGE BREAKS
========================================================= */

.section-title,
.clinical-item,
.vital,
.patient-summary,
.info-item,
.prescription-wrapper,
.prescription-table tr {

    break-inside: avoid;
}

</style>

</head>


<body>


<div class="print-container">


    <!-- =====================================================
         HEADER
    ====================================================== -->

    <div class="header">

        <div class="header-top">


            <!-- LEFT: LOGO + CLINIC -->

            <div class="header-left">

                <img
                    src="../asset/images/DCMD.png?v=1"
                    alt="DCMD Logo"
                    class="clinic-logo"
                >


                <div class="clinic-information">

                    <div class="clinic-name">
                        PRIVATE PATIENT SYSTEM
                    </div>

                    <div class="clinic-subtitle">
                        DCMD DIABETES AND HYPERTENSION CLINIC
                    </div>

                    <div class="document-title">
                        CONSULTATION RECORD
                    </div>

                </div>

            </div>


            <!-- RIGHT: CONSULTATION NUMBER -->

            <div class="document-label">

                CONSULTATION NO.

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $data['id']
                    );
                    ?>
                </strong>

            </div>


        </div>

    </div>


    <!-- =====================================================
         PATIENT SUMMARY
    ====================================================== -->

    <div class="patient-summary">

        <div class="patient-main">


            <!-- PATIENT -->

            <div>

                <div class="patient-name">

                    <?php
                    echo htmlspecialchars(
                        $fullName
                    );
                    ?>

                </div>

                <div class="patient-id">

                    Patient ID:

                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $data['patient_number']
                        );
                        ?>
                    </strong>

                </div>

            </div>


            <!-- VISIT DATE -->

            <div class="summary-item">

                VISIT DATE

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $visitDateFormatted
                    );
                    ?>
                </strong>

            </div>


            <!-- AGE -->

            <div class="summary-item">

                AGE

                <strong>
                    <?php
                    echo htmlspecialchars(
                        $age
                    );
                    ?>
                    years old
                </strong>

            </div>


        </div>

    </div>


    <!-- =====================================================
         PATIENT INFORMATION
    ====================================================== -->

    <div class="section">

        <div class="section-title">
            PATIENT INFORMATION
        </div>


        <div class="info-grid">


            <!-- FULL NAME -->

            <div class="info-item">

                <div class="label">
                    FULL NAME
                </div>

                <div class="value">
                    <?php
                    echo htmlspecialchars(
                        $fullName
                    );
                    ?>
                </div>

            </div>


            <!-- SEX -->

            <div class="info-item">

                <div class="label">
                    SEX
                </div>

                <div class="value">
                    <?php
                    echo htmlspecialchars(
                        $data['sex']
                    );
                    ?>
                </div>

            </div>


            <!-- CONTACT -->

            <div class="info-item">

                <div class="label">
                    CONTACT NUMBER
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['contact_no']
                    )
                        ? htmlspecialchars(
                            $data['contact_no']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- BIRTHDATE -->

            <div class="info-item">

                <div class="label">
                    BIRTHDATE
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['birthdate']
                    )
                        ? date(
                            "F d, Y",
                            strtotime(
                                $data['birthdate']
                            )
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- ADDRESS -->

            <div class="info-item full">

                <div class="label">
                    ADDRESS
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['address']
                    )
                        ? htmlspecialchars(
                            $data['address']
                        )
                        : "-";

                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         CLINICAL INFORMATION
    ====================================================== -->

    <div class="section">

        <div class="section-title">
            CLINICAL INFORMATION
        </div>


        <!-- CHIEF COMPLAINT -->

        <div class="clinical-item">

            <div class="label">
                CHIEF COMPLAINT
            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['chief_complaint']
                )
                    ? htmlspecialchars(
                        $data['chief_complaint']
                    )
                    : "-";

                ?>

            </div>

        </div>


        <!-- HISTORY -->

        <div class="clinical-item">

            <div class="label">
                HISTORY OF PRESENT ILLNESS
            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['history_illness']
                )
                    ? htmlspecialchars(
                        $data['history_illness']
                    )
                    : "-";

                ?>

            </div>

        </div>


    </div>


    <!-- =====================================================
         VITAL SIGNS
    ====================================================== -->

    <div class="section">

        <div class="section-title">
            VITAL SIGNS
        </div>


        <div class="vitals">


            <!-- BLOOD PRESSURE -->

            <div class="vital">

                <div class="vital-label">
                    BLOOD PRESSURE
                </div>

                <div class="vital-value">

                    <?php

                    echo !empty(
                        $data['blood_pressure']
                    )
                        ? htmlspecialchars(
                            $data['blood_pressure']
                        )
                        : "-";

                    ?>

                </div>

            </div>


            <!-- TEMPERATURE -->

            <div class="vital">

                <div class="vital-label">
                    TEMPERATURE
                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['temperature'] !== null &&
                        $data['temperature'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['temperature']
                        ) . " °C"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- PULSE -->

            <div class="vital">

                <div class="vital-label">
                    PULSE RATE
                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['pulse_rate'] !== null &&
                        $data['pulse_rate'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['pulse_rate']
                        ) . " bpm"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- RESPIRATORY -->

            <div class="vital">

                <div class="vital-label">
                    RESPIRATORY RATE
                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['respiratory_rate'] !== null &&
                        $data['respiratory_rate'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['respiratory_rate']
                        ) . " /min"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- SPO2 -->

            <div class="vital">

                <div class="vital-label">
                    SpO₂
                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['oxygen_saturation'] !== null &&
                        $data['oxygen_saturation'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['oxygen_saturation']
                        ) . " %"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- WEIGHT -->

            <div class="vital">

                <div class="vital-label">
                    WEIGHT
                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['weight'] !== null &&
                        $data['weight'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['weight']
                        ) . " kg"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- HEIGHT -->

            <div class="vital">

                <div class="vital-label">
                    HEIGHT
                </div>

                <div class="vital-value">

                    <?php

                    echo (
                        $data['height'] !== null &&
                        $data['height'] !== ''
                    )
                        ? htmlspecialchars(
                            $data['height']
                        ) . " cm"
                        : "-";

                    ?>

                </div>

            </div>


            <!-- VISIT DATE -->

            <div class="vital">

                <div class="vital-label">
                    VISIT DATE
                </div>

                <div class="vital-value">

                    <?php
                    echo htmlspecialchars(
                        $visitDateFormatted
                    );
                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         ASSESSMENT & TREATMENT
    ====================================================== -->

    <div class="section">

        <div class="section-title">
            ASSESSMENT &amp; TREATMENT
        </div>


        <!-- ASSESSMENT -->

        <div class="clinical-item">

            <div class="label">
                ASSESSMENT / DIAGNOSIS
            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['assessment']
                )
                    ? htmlspecialchars(
                        $data['assessment']
                    )
                    : "-";

                ?>

            </div>

        </div>


        <!-- MANAGEMENT -->

        <div class="clinical-item">

            <div class="label">
                MANAGEMENT / PLAN
            </div>

            <div class="value">

                <?php

                echo !empty(
                    $data['management']
                )
                    ? htmlspecialchars(
                        $data['management']
                    )
                    : "-";

                ?>

            </div>

        </div>


    </div>


    <!-- =====================================================
         MEDICATION / PRESCRIPTION
    ====================================================== -->

    <div class="section">

        <div class="section-title">
            MEDICATION / PRESCRIPTION
        </div>


        <?php if (count($prescriptionMedicines) > 0) { ?>


            <div class="prescription-wrapper">

                <table class="prescription-table">

                    <thead>

                        <tr>

                            <th style="width: 34%;">
                                MEDICATION
                            </th>

                            <th style="width: 8%;">
                                QTY
                            </th>

                            <th style="width: 14.5%;">
                                BREAKFAST
                            </th>

                            <th style="width: 14.5%;">
                                LUNCH
                            </th>

                            <th style="width: 14.5%;">
                                DINNER
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        <?php

                        foreach (
                            $prescriptionMedicines
                            as $medicine
                        ) {

                            $medicineDisplay =
                                trim(
                                    $medicine['medicine_name']
                                );

                            if (
                                !empty(
                                    $medicine['strength']
                                )
                            ) {

                                $medicineDisplay .=
                                    " " .
                                    trim(
                                        $medicine['strength']
                                    );
                            }

                        ?>

                            <tr>


                                <!-- MEDICINE -->

                                <td class="medicine-cell">

                                    <?php
                                    echo htmlspecialchars(
                                        $medicineDisplay
                                    );
                                    ?>

                                </td>


                                <!-- QTY -->

                                <td class="qty-cell">

                                    <?php

                                    if (
                                        isset(
                                            $medicine['quantity']
                                        ) &&
                                        $medicine['quantity'] !== null &&
                                        $medicine['quantity'] !== ''
                                    ) {

                                        echo htmlspecialchars(
                                            $medicine['quantity']
                                        );

                                    } else {

                                        echo "-";

                                    }

                                    ?>

                                </td>


                                <!-- BREAKFAST -->

                                <td class="dose-cell">

                                    <?php

                                    if (
                                        isset(
                                            $medicine['breakfast']
                                        ) &&
                                        trim(
                                            $medicine['breakfast']
                                        ) !== ""
                                    ) {

                                        echo htmlspecialchars(
                                            $medicine['breakfast']
                                        );

                                    } else {

                                        echo "-";

                                    }

                                    ?>

                                </td>


                                <!-- LUNCH -->

                                <td class="dose-cell">

                                    <?php

                                    if (
                                        isset(
                                            $medicine['lunch']
                                        ) &&
                                        trim(
                                            $medicine['lunch']
                                        ) !== ""
                                    ) {

                                        echo htmlspecialchars(
                                            $medicine['lunch']
                                        );

                                    } else {

                                        echo "-";

                                    }

                                    ?>

                                </td>


                                <!-- DINNER -->

                                <td class="dose-cell">

                                    <?php

                                    if (
                                        isset(
                                            $medicine['dinner']
                                        ) &&
                                        trim(
                                            $medicine['dinner']
                                        ) !== ""
                                    ) {

                                        echo htmlspecialchars(
                                            $medicine['dinner']
                                        );

                                    } else {

                                        echo "-";

                                    }

                                    ?>

                                </td>


                            </tr>

                        <?php

                        }

                        ?>

                    </tbody>

                </table>

            </div>


        <?php } else { ?>


            <div class="prescription-empty">

                No prescription recorded for this consultation.

            </div>


        <?php } ?>


    </div>


    <!-- =====================================================
         FOLLOW-UP & REMARKS
    ====================================================== -->

    <div class="section">

        <div class="section-title">
            FOLLOW-UP &amp; REMARKS
        </div>


        <div class="info-grid">


            <!-- FOLLOW-UP DATE -->

            <div class="info-item">

                <div class="label">
                    FOLLOW-UP DATE
                </div>

                <div class="value">

                    <?php

                    echo htmlspecialchars(
                        $followUpFormatted
                    );

                    ?>

                </div>

            </div>


            <!-- REMARKS -->

            <div class="info-item">

                <div class="label">
                    REMARKS
                </div>

                <div class="value">

                    <?php

                    echo !empty(
                        $data['remarks']
                    )
                        ? htmlspecialchars(
                            $data['remarks']
                        )
                        : "-";

                    ?>

                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         BUTTONS
    ====================================================== -->

    <div class="buttons">


        <button
            type="button"
            class="btn btn-print"
            onclick="window.print();"
        >
            🖨 Print Consultation
        </button>


        <a
            href="consultation_view.php?id=<?php echo $id; ?>"
            class="btn btn-back"
        >
            ← Back
        </a>


    </div>


</div>


</body>

</html>


<?php

$conn->close();

?>