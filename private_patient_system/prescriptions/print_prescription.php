<?php

require_once "../config/database.php";
require_once "../config/database.php";
/* =========================================================
   GET PARAMETERS
========================================================= */

$prescriptionHeaderId = isset($_GET["prescription_header_id"])
    ? (int) $_GET["prescription_header_id"]
    : 0;

$fromHistory = isset($_GET["from_history"])
    ? (int) $_GET["from_history"]
    : 0;

if ($prescriptionHeaderId <= 0) {
    die("Invalid prescription ID.");
}

/* =========================================================
   GET PRESCRIPTION HEADER + PATIENT
========================================================= */

$sql = "
    SELECT
        ph.id AS prescription_header_id,
        ph.patient_id,
        ph.prescribed_date,
        ph.created_at,
        p.id AS patient_database_id,
        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.address,
        p.contact_no
    FROM prescription_headers ph
    INNER JOIN patients p
        ON ph.patient_id = p.id
    WHERE ph.id = ?
    LIMIT 1
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "i",
    $prescriptionHeaderId
);

$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $stmt->close();
    $conn->close();
    die("Prescription not found.");
}

$prescriptionHeader = $result->fetch_assoc();

$stmt->close();

/* =========================================================
   GET CONSULTATION CONNECTED TO THIS PRESCRIPTION
========================================================= */

$consultationId = 0;
$visitDateRaw = "";
$followUpDateRaw = "";
$consultationNumber = 0;

$consultationSql = "
    SELECT
        c.id AS consultation_id,
        c.visit_date,
        c.follow_up_date
    FROM prescriptions pr
    INNER JOIN consultations c
        ON pr.consultation_id = c.id
    WHERE pr.prescription_header_id = ?
      AND c.patient_id = ?
    ORDER BY
        c.visit_date DESC,
        c.id DESC,
        pr.id DESC
    LIMIT 1
";

$consultationStmt = $conn->prepare($consultationSql);

if (!$consultationStmt) {
    die("Database error: " . $conn->error);
}

$consultationStmt->bind_param(
    "ii",
    $prescriptionHeaderId,
    $prescriptionHeader["patient_id"]
);

$consultationStmt->execute();

$consultationResult = $consultationStmt->get_result();

if ($consultationResult->num_rows > 0) {

    $consultation = $consultationResult->fetch_assoc();

    $consultationId = (int) $consultation["consultation_id"];

    $visitDateRaw = $consultation["visit_date"];

    $followUpDateRaw = $consultation["follow_up_date"];
}

$consultationStmt->close();

/* =========================================================
   CONSULTATION NUMBER
========================================================= */

if ($consultationId > 0) {

    $consultationNumberSql = "
        SELECT
            COUNT(*) AS consultation_number
        FROM consultations c2
        WHERE c2.patient_id = ?
          AND (
                c2.visit_date < ?
                OR (
                    c2.visit_date = ?
                    AND c2.id <= ?
                )
              )
    ";

    $consultationNumberStmt = $conn->prepare(
        $consultationNumberSql
    );

    if (!$consultationNumberStmt) {
        die("Database error: " . $conn->error);
    }

    $consultationNumberStmt->bind_param(
        "issi",
        $prescriptionHeader["patient_id"],
        $visitDateRaw,
        $visitDateRaw,
        $consultationId
    );

    $consultationNumberStmt->execute();

    $consultationNumberResult =
        $consultationNumberStmt->get_result();

    $consultationNumberRow =
        $consultationNumberResult->fetch_assoc();

    $consultationNumber =
        !empty($consultationNumberRow["consultation_number"])
            ? (int) $consultationNumberRow["consultation_number"]
            : 0;

    $consultationNumberStmt->close();
}

/* =========================================================
   PATIENT NAME
========================================================= */

$middleName = trim(
    $prescriptionHeader["middle_name"]
);

$fullName = trim(
    $prescriptionHeader["first_name"]
    . " "
    . (
        $middleName !== ""
            ? $middleName . " "
            : ""
    )
    . $prescriptionHeader["last_name"]
);

/* =========================================================
   AGE
========================================================= */

$age = "";

if (!empty($prescriptionHeader["birthdate"])) {

    try {

        $birthDate = new DateTime(
            $prescriptionHeader["birthdate"]
        );

        $today = new DateTime();

        $age = $birthDate->diff($today)->y;

    } catch (Exception $e) {

        $age = "";
    }
}

/* =========================================================
   PRESCRIPTION DATE
========================================================= */

$prescriptionDate = "";

if (!empty($prescriptionHeader["prescribed_date"])) {

    $prescriptionDate = date(
        "m/d/Y",
        strtotime(
            $prescriptionHeader["prescribed_date"]
        )
    );
}

/* =========================================================
   FOLLOW-UP DATE
========================================================= */

$followUpDate = "";

if (!empty($followUpDateRaw)) {

    $followUpDate = date(
        "m/d/Y",
        strtotime($followUpDateRaw)
    );
}

/* =========================================================
   GET MEDICINES
========================================================= */

$prescriptionSql = "
    SELECT
        id,
        medicine_name,
        strength,
        quantity,
        breakfast,
        lunch,
        dinner
    FROM prescriptions
    WHERE prescription_header_id = ?
    ORDER BY id ASC
";

$prescriptionStmt = $conn->prepare(
    $prescriptionSql
);

if (!$prescriptionStmt) {
    die("Database error: " . $conn->error);
}

$prescriptionStmt->bind_param(
    "i",
    $prescriptionHeaderId
);

$prescriptionStmt->execute();

$prescriptionResult =
    $prescriptionStmt->get_result();

$prescriptions = array();

while (
    $row =
    $prescriptionResult->fetch_assoc()
) {

    $prescriptions[] = $row;
}

$prescriptionStmt->close();

$conn->close();

/* =========================================================
   HTML ESCAPE
========================================================= */

function h($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}

/* =========================================================
   CAPSLOCK + FORMAL OUTPUT
========================================================= */

function hUpper($value)
{
    $value = trim(
        preg_replace(
            '/\s+/',
            ' ',
            (string) $value
        )
    );

    return htmlspecialchars(
        strtoupper($value),
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Prescription - <?php echo hUpper($fullName); ?>
    </title>

    <link
        rel="icon"
        type="image/png"
        href="../asset/images/DCMDLOGO.png?v=1"
    >

    <style>

        /* =====================================================
           GENERAL
        ===================================================== */

        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {

            background: #eef1f4;

            color: #222;

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 12px;
        }


        /* =====================================================
           MAIN CONTAINER
        ===================================================== */

        .print-container {

            width: 100%;

            max-width: 900px;

            margin: 25px auto;
        }


        /* =====================================================
           TOOLBAR
        ===================================================== */

        .toolbar {

            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 10px;

            margin-bottom: 15px;
        }

        .toolbar-left {

            display: flex;

            align-items: center;

            gap: 8px;
        }

        .btn {

            display: inline-flex;

            align-items: center;

            justify-content: center;

            min-height: 38px;

            padding: 8px 14px;

            border: none;

            border-radius: 5px;

            text-decoration: none;

            cursor: pointer;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 12px;

            font-weight: 600;
        }

        .btn-back {

            background: #6c757d;

            color: #fff;
        }

        .btn-print {

            background: #1f4e78;

            color: #fff;
        }


        /* =====================================================
           PAPER
        ===================================================== */

        .prescription-paper {

            width: 100%;

            min-height: 700px;

            background: #fff;

            padding:
                25px
                40px
                30px;

            box-shadow:
                0 2px 12px
                rgba(0, 0, 0, 0.12);
        }


        /* =====================================================
           CLINIC HEADER
        ===================================================== */

        .clinic-header {

            width: 100%;

            display: grid;

            grid-template-columns:
                105px
                1fr
                105px;

            align-items: center;

            column-gap: 12px;

            margin-bottom: 6px;
        }

        .clinic-logo {

            width: 94px;

            height: 94px;

            object-fit: contain;

            display: block;

            margin: 0 auto;
        }

        .clinic-title {

            text-align: center;

            line-height: 1.15;
        }

        .clinic-title h1 {

            margin: 0;

            color: #1f3f6e;

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 23px;

            font-weight: 700;

            letter-spacing: 0.2px;

            white-space: nowrap;
        }

        .clinic-title p {

            margin: 3px 0 0;

            color: #1f3f6e;

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 11.5px;

            font-weight: 600;
        }

        .clinic-title .member-line {

            margin-top: 2px;

            font-size: 10.5px;
        }

        .clinic-title .address-line {

            margin-top: 2px;

            font-size: 10px;

            font-weight: 600;
        }


        /* =====================================================
           PRESCRIPTION TITLE
        ===================================================== */

        .rx-heading {

            margin:
                8px 0
                12px;

            text-align: center;

            color: #1f3f6e;

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 22px;

            font-weight: 700;

            letter-spacing: 0.3px;
        }


        /* =====================================================
           PATIENT INFORMATION
        ===================================================== */

        .patient-info-table {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;

            margin-bottom: 12px;
        }

        .patient-info-table td {

            height: 31px;

            border: 1px solid #555;

            padding:
                5px 8px;

            vertical-align: middle;

            line-height: 1.15;

            word-break: normal;
        }

        .patient-label {

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 11.5px;

            font-weight: 700;

            white-space: nowrap;

            color: #222;
        }

        .patient-value {

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 11.5px;

            padding-left: 10px !important;

            overflow-wrap: anywhere;

            font-weight: 600;
        }


        /* =====================================================
           MEDICATION TABLE
        ===================================================== */

        .medicine-table {

            width: 100%;

            border-collapse: collapse;

            table-layout: fixed;
        }

        .medicine-table th {

            height: 38px;

            padding:
                6px 7px;

            border: 1px solid #444;

            background: #39aee8;

            color: #fff;

            text-align: center;

            vertical-align: middle;

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 13px;

            font-weight: 700;
        }

        .medicine-table td {

            height: 38px;

            padding:
                6px 9px;

            border: 1px solid #555;

            vertical-align: middle;

            font-family:
                "Times New Roman",
                Times,
                serif;

            font-size: 11px;

            line-height: 1.2;
        }


        /* =====================================================
           TABLE WIDTHS
        ===================================================== */

        .medicine-col {
            width: 38%;
        }

        .qty-col {
            width: 12%;
        }

        .meal-col {
            width: 16.666%;
        }


        /* =====================================================
           MEDICINE DISPLAY
        ===================================================== */

        .medicine-name {

            display: block;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 11px;

            font-weight: 700;

            line-height: 1.2;

            white-space: normal;

            text-transform: uppercase;
        }


        /* =====================================================
           QUANTITY
        ===================================================== */

        .qty-value {

            text-align: center;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 11px;

            font-weight: 600;
        }


        /* =====================================================
           MEAL DOSE
        ===================================================== */

        .meal-value {

            text-align: center;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            font-size: 10.5px;

            font-weight: 600;

            white-space: normal;

            overflow-wrap: anywhere;

            text-transform: uppercase;
        }


        /* =====================================================
           EMPTY ROWS
        ===================================================== */

        .empty-row td {
            height: 38px;
        }


        /* =====================================================
           BOTTOM SECTION
        ===================================================== */

        .bottom-section {

            width: 100%;

            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            margin-top: 25px;

            min-height: 150px;
        }


        /* =====================================================
           DOCTOR / SIGNATURE
        ===================================================== */

        .doctor-info {

            width: 300px;

            min-height: 150px;

            font-family:
                "Times New Roman",
                Times,
                serif;

            display: flex;

            flex-direction: column;

            justify-content: flex-start;
        }


        /*
           BLANK SPACE FOR SIGNATURE

           This gives Dr. Darwin enough room
           to physically sign above the line.
        */

        .signature-space {

            height: 55px;

            flex-shrink: 0;
        }


        .doctor-line {

            width: 195px;

            border-top: 1px solid #333;

            margin-bottom: 5px;
        }

        .doctor-name {

            font-size: 13px;

            font-weight: 700;

            margin-bottom: 2px;
        }

        .doctor-detail {

            font-size: 11px;

            line-height: 1.4;
        }


        /* =====================================================
           FOLLOW-UP AREA
        ===================================================== */

        .follow-up-area {

            display: flex;

            align-items: flex-start;

            justify-content: flex-end;

            gap: 12px;

            margin-top: 55px;

            font-family:
                "Times New Roman",
                Times,
                serif;
        }

        .follow-up {

            font-size: 12px;

            white-space: nowrap;

            font-weight: 600;

            display: flex;

            align-items: center;

            height: 20px;
        }

        .follow-up-line {

            display: inline-flex;

            align-items: flex-end;

            justify-content: center;

            min-width: 125px;

            height: 20px;

            margin-left: 6px;

            padding:
                0 5px
                3px;

            border-bottom: 1px solid #555;

            text-align: center;
        }

        .follow-up-logo {

            width: 82px;

            height: 82px;

            object-fit: contain;

            display: block;

            margin-top: -32px;
        }


        /* =====================================================
           MOBILE
        ===================================================== */

        @media (max-width: 700px) {

            body {
                background: #fff;
            }

            .print-container {
                margin: 0;
            }

            .toolbar {

                padding: 10px;

                flex-direction: column;

                align-items: stretch;
            }

            .toolbar-left {

                width: 100%;

                flex-direction: column;
            }

            .toolbar .btn {
                width: 100%;
            }

            .prescription-paper {

                box-shadow: none;

                padding: 20px;

                overflow-x: auto;
            }

            .clinic-header {

                grid-template-columns:
                    65px
                    1fr
                    65px;
            }

            .clinic-logo {

                width: 60px;

                height: 60px;
            }

            .clinic-title h1 {

                font-size: 14px;

                white-space: normal;
            }

            .clinic-title p {
                font-size: 7.5px;
            }

            .clinic-title .member-line,
            .clinic-title .address-line {
                font-size: 7px;
            }

            .patient-info-table,
            .medicine-table {
                min-width: 650px;
            }

            .bottom-section {
                min-width: 650px;
            }

            .follow-up-area {
                right: 10px;
            }

            .follow-up-logo {

                width: 60px;

                height: 60px;
            }
        }


        /* =====================================================
           PRINT
        ===================================================== */

        @media print {

            @page {

                size: A4 portrait;

                margin: 0;
            }

            html,
            body {

                width: 210mm;

                height: 148.5mm;

                min-height: 148.5mm;

                max-height: 148.5mm;

                background: #fff;

                margin: 0;

                padding: 0;

                overflow: hidden;
            }

            body {

                font-family:
                    "Times New Roman",
                    Times,
                    serif;
            }


            /* =================================================
               CONTAINER
            ================================================= */

            .print-container {

                width: 210mm;

                height: 148.5mm;

                max-width: none;

                margin: 0;

                padding: 0;
            }


            /* =================================================
               HIDE TOOLBAR
            ================================================= */

            .toolbar {
                display: none !important;
            }


            /* =================================================
               HALF-A4 PAPER
            ================================================= */

            .prescription-paper {

                width: 210mm;

                height: 148.5mm;

                min-height: 148.5mm;

                max-height: 148.5mm;

                margin: 0;

                padding:
                    4.5mm
                    6mm
                    3mm;

                box-shadow: none;

                overflow: hidden;
            }


            /* =================================================
               CLINIC HEADER
            ================================================= */

            .clinic-header {

                grid-template-columns:
                    25mm
                    1fr
                    25mm;

                column-gap: 2mm;

                margin-bottom: 0.5mm;
            }

            .clinic-logo {

                width: 20mm;

                height: 20mm;
            }

            .clinic-title h1 {

                font-size: 15.5pt;

                line-height: 1;

                white-space: nowrap;
            }

            .clinic-title p {

                margin-top: 0.6mm;

                font-size: 7.1pt;

                line-height: 1;
            }

            .clinic-title .member-line {

                font-size: 6.5pt;

                margin-top: 0.3mm;
            }

            .clinic-title .address-line {

                font-size: 6.2pt;

                margin-top: 0.3mm;
            }


            /* =================================================
               TITLE
            ================================================= */

            .rx-heading {

                margin:
                    1mm
                    0
                    1.5mm;

                font-size: 12.5pt;

                line-height: 1;
            }


            /* =================================================
               PATIENT INFORMATION
            ================================================= */

            .patient-info-table {

                margin-bottom: 2mm;
            }

            .patient-info-table td {

                height: 6.5mm;

                padding:
                    0.7mm
                    1.3mm;

                font-size: 7.5pt;

                line-height: 1;
            }

            .patient-label {
                font-size: 7.5pt;
            }

            .patient-value {

                font-size: 7.5pt;

                padding-left: 2mm !important;
            }


            /* =================================================
               MEDICATION TABLE HEADER
            ================================================= */

            .medicine-table th {

                height: 7mm;

                padding:
                    0.7mm
                    1mm;

                font-size: 8pt;

                line-height: 1;
            }


            /* =================================================
               MEDICATION TABLE BODY
            ================================================= */

            .medicine-table td {

                height: 7.7mm;

                padding:
                    0.7mm
                    1.3mm;

                font-size: 7.2pt;

                line-height: 1;
            }

            .medicine-name {

                font-size: 7.5pt;

                line-height: 1;
            }

            .qty-value {

                font-size: 7.5pt;

                line-height: 1;
            }

            .meal-value {

                font-size: 7.5pt;

                line-height: 1;
            }

            .empty-row td {
                height: 7.7mm;
            }


            /* =================================================
               BOTTOM SECTION
            ================================================= */

            .bottom-section {

                width: 100%;

                height: 28mm;

                min-height: 28mm;

                margin-top: 4mm;

                display: flex;

                justify-content: space-between;

                align-items: flex-start;
            }


            /* =================================================
               DOCTOR / SIGNATURE
            ================================================= */

            .doctor-info {

                width: 55mm;

                height: 28mm;

                min-height: 28mm;

                display: flex;

                flex-direction: column;

                justify-content: flex-start;
            }


            /*
               SIGNATURE SPACE

               12mm blank space above signature line.
               This is where the doctor can sign.
            */

            .signature-space {

                height: 12mm;

                min-height: 12mm;

                flex-shrink: 0;
            }


            .doctor-line {

                width: 43mm;

                border-top:
                    0.25mm solid #333;

                margin-top: 0;

                margin-bottom: 1mm;
            }

            .doctor-name {

                font-size: 8pt;

                line-height: 1;

                margin-bottom: 0.7mm;

                font-weight: 700;
            }

            .doctor-detail {

                font-size: 6.5pt;

                line-height: 1.25;

                margin: 0;
            }


            /* =================================================
               FOLLOW-UP

               SAME HORIZONTAL LEVEL AS SIGNATURE LINE
            ================================================= */

            .follow-up-area {

                height: 28mm;

                display: flex;

                align-items: flex-start;

                justify-content: flex-end;

                gap: 3mm;

                margin-top: 12mm;
            }

            .follow-up {

                font-size: 7.5pt;

                line-height: 1;

                height: 5mm;

                display: flex;

                align-items: center;

                padding-top: 0;
            }

            .follow-up-line {

                min-width: 31mm;

                height: 5mm;

                margin-left: 1mm;

                padding:
                    0
                    1mm
                    0.5mm;

                border-bottom:
                    0.25mm solid #555;

                display: inline-flex;

                align-items: flex-end;

                justify-content: center;

                font-size: 7.5pt;

                line-height: 1;
            }

            .follow-up-logo {

                width: 20mm;

                height: 20mm;

                object-fit: contain;

                display: block;

                margin-top: -8mm;
            }
        }

    </style>

</head>

<body>

<div class="print-container">


    <!-- =====================================================
         TOOLBAR
    ====================================================== -->

    <div class="toolbar">

        <div class="toolbar-left">

            <?php if ($fromHistory === 1): ?>

                <a
                    href="history.php?id=<?php echo (int) $prescriptionHeader["patient_database_id"]; ?>"
                    class="btn btn-back"
                >
                    ← Back to Prescription History
                </a>

            <?php else: ?>

                <?php if ($consultationId > 0): ?>

                    <a
                        href="prescription.php?consultation_id=<?php echo $consultationId; ?>"
                        class="btn btn-back"
                    >
                        Back to Prescription
                    </a>

                <?php else: ?>

                    <a
                        href="../patients/view.php?id=<?php echo (int) $prescriptionHeader["patient_database_id"]; ?>"
                        class="btn btn-back"
                    >
                        Back to Patient
                    </a>

                <?php endif; ?>

            <?php endif; ?>


            <?php if ($consultationId > 0): ?>

                <a
                    href="../patients/consultation_view.php?id=<?php echo $consultationId; ?>"
                    class="btn btn-back"
                >
                    Consultation
                </a>

            <?php endif; ?>

        </div>


        <button
            type="button"
            class="btn btn-print"
            onclick="window.print();"
        >
            Print Prescription
        </button>

    </div>


    <!-- =====================================================
         PRESCRIPTION PAPER
    ====================================================== -->

    <div class="prescription-paper">


        <!-- =================================================
             CLINIC HEADER
        ================================================== -->

        <div class="clinic-header">

            <img
                src="../asset/images/2.png?v=1"
                alt="DCMD Clinic Logo"
                class="clinic-logo"
            >


            <div class="clinic-title">

                <h1>
                    DCMD DIABETES AND HYPERTENSION CLINIC
                </h1>

                <p>
                    ALUMNI, INSTITUTE FOR STUDIES ON DIABETES FOUNDATION
                </p>

                <p class="member-line">
                    MEMBER, PHILIPPINE ASSOCIATION OF DIABETES EDUCATORS
                </p>

                <p class="address-line">
                    ROOM 124 ROSARIO MEMORIAL HOSPITAL, SN. ROQUE, GUAGUA, PAMPANGA
                </p>

            </div>


            <img
                src="../asset/images/1.png?v=1"
                alt="Institute Logo"
                class="clinic-logo"
            >

        </div>


        <!-- =================================================
             TITLE
        ================================================== -->

        <div class="rx-heading">
            PRESCRIPTIONS
        </div>


        <!-- =================================================
             PATIENT INFORMATION
        ================================================== -->

        <table class="patient-info-table">

            <colgroup>

                <col style="width: 9%;">

                <col style="width: 41%;">

                <col style="width: 10%;">

                <col style="width: 14%;">

                <col style="width: 7%;">

                <col style="width: 19%;">

            </colgroup>


            <tr>

                <td class="patient-label">
                    NAME:
                </td>

                <td class="patient-value">
                    <?php echo hUpper($fullName); ?>
                </td>

                <td class="patient-label">
                    AGE/SEX:
                </td>

                <td class="patient-value">

                    <?php
                    echo $age !== ""
                        ? h($age)
                        : "N/A";
                    ?>

                    /

                    <?php
                    echo hUpper(
                        $prescriptionHeader["sex"]
                    );
                    ?>

                </td>

                <td class="patient-label">
                    DATE:
                </td>

                <td class="patient-value">
                    <?php echo h($prescriptionDate); ?>
                </td>

            </tr>


            <tr>

                <td class="patient-label">
                    ADDRESS:
                </td>

                <td class="patient-value">

                    <?php

                    echo !empty(
                        $prescriptionHeader["address"]
                    )
                        ? hUpper(
                            $prescriptionHeader["address"]
                        )
                        : "";

                    ?>

                </td>


                <td class="patient-label">
                    TEL NO.:
                </td>

                <td
                    class="patient-value"
                    colspan="3"
                >

                    <?php

                    echo !empty(
                        $prescriptionHeader["contact_no"]
                    )
                        ? h(
                            $prescriptionHeader["contact_no"]
                        )
                        : "";

                    ?>

                </td>

            </tr>

        </table>


        <!-- =================================================
             MEDICATION TABLE
        ================================================== -->

        <table class="medicine-table">

            <colgroup>

                <col class="medicine-col">

                <col class="qty-col">

                <col class="meal-col">

                <col class="meal-col">

                <col class="meal-col">

            </colgroup>


            <thead>

                <tr>

                    <th>
                        MEDICATIONS:
                    </th>

                    <th>
                        QTY:
                    </th>

                    <th>
                        BREAKFAST:
                    </th>

                    <th>
                        LUNCH:
                    </th>

                    <th>
                        DINNER:
                    </th>

                </tr>

            </thead>


            <tbody>

                <?php if (count($prescriptions) > 0): ?>

                    <?php foreach (
                        $prescriptions
                        as $prescription
                    ): ?>

                        <?php

                        $medicineDisplay =
                            trim(
                                $prescription["medicine_name"]
                                . " "
                                . $prescription["strength"]
                            );

                        ?>


                        <tr>

                            <td>

                                <span class="medicine-name">

                                    <?php

                                    echo hUpper(
                                        $medicineDisplay
                                    );

                                    ?>

                                </span>

                            </td>


                            <td class="qty-value">

                                <?php

                                if (
                                    $prescription["quantity"] !== null
                                    &&
                                    $prescription["quantity"] !== ""
                                ) {

                                    echo h(
                                        $prescription["quantity"]
                                    );

                                }

                                ?>

                            </td>


                            <td class="meal-value">

                                <?php

                                echo !empty(
                                    $prescription["breakfast"]
                                )
                                    ? hUpper(
                                        $prescription["breakfast"]
                                    )
                                    : "";

                                ?>

                            </td>


                            <td class="meal-value">

                                <?php

                                echo !empty(
                                    $prescription["lunch"]
                                )
                                    ? hUpper(
                                        $prescription["lunch"]
                                    )
                                    : "";

                                ?>

                            </td>


                            <td class="meal-value">

                                <?php

                                echo !empty(
                                    $prescription["dinner"]
                                )
                                    ? hUpper(
                                        $prescription["dinner"]
                                    )
                                    : "";

                                ?>

                            </td>

                        </tr>

                    <?php endforeach; ?>


                    <?php

                    /*
                     * Keep exactly 9 medication rows
                     * for the half-A4 layout.
                     */

                    $minimumRows = 9;

                    $remainingRows =
                        $minimumRows -
                        count($prescriptions);

                    if ($remainingRows > 0):

                        for (
                            $i = 0;
                            $i < $remainingRows;
                            $i++
                        ):

                    ?>

                        <tr class="empty-row">

                            <td></td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td></td>

                        </tr>

                    <?php

                        endfor;

                    endif;

                    ?>


                <?php else: ?>


                    <?php for (
                        $i = 0;
                        $i < 9;
                        $i++
                    ): ?>

                        <tr class="empty-row">

                            <td></td>

                            <td></td>

                            <td></td>

                            <td></td>

                            <td></td>

                        </tr>

                    <?php endfor; ?>


                <?php endif; ?>

            </tbody>

        </table>


        <!-- =================================================
             BOTTOM SECTION
        ================================================== -->

        <div class="bottom-section">


            <!-- =============================================
                 DOCTOR / SIGNATURE
            ============================================== -->

            <div class="doctor-info">

                <!-- BLANK SIGNATURE SPACE -->

                <div class="signature-space"></div>


                <!-- SIGNATURE LINE -->

                <div class="doctor-line"></div>


                <!-- DOCTOR NAME -->

                <div class="doctor-name">
                    DARWIN G. CRUZ, MD
                </div>


                <!-- LICENSE -->

                <div class="doctor-detail">
                    LIC. NO: 119102
                </div>


                <!-- PTR -->

                <div class="doctor-detail">
                    PTR:
                </div>

            </div>


            <!-- =============================================
                 FOLLOW-UP + LOGO
            ============================================== -->

            <div class="follow-up-area">

                <div class="follow-up">

                    FOLLOW UP DATE ON:

                    <span class="follow-up-line">

                        <?php

                        echo $followUpDate !== ""
                            ? h($followUpDate)
                            : "";

                        ?>

                    </span>

                </div>


                <img
                    src="../asset/images/DCMD1.JPG?v=1"
                    alt="DCMD Logo"
                    class="follow-up-logo"
                >

            </div>


        </div>

    </div>

</div>

</body>

</html>