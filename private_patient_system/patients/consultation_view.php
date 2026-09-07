<?php
require_once "../config/database.php";

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Consultation Details";
$pageSubtitle = "Consultation Details";
$basePath = "../";
$activePage = "consultations";


/* =========================================================
   GET CONSULTATION ID
========================================================= */

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
        p.sex
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
    $conn->close();
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
   AGE
========================================================= */

$age = "-";

if (!empty($data['birthdate'])) {
    $birthDate = new DateTime($data['birthdate']);
    $today = new DateTime();

    $age = $today->diff($birthDate)->y;
}


/* =========================================================
   FOLLOW-UP STATUS
========================================================= */

$followUpStatus = "PENDING";

if (
    isset($data['follow_up_status']) &&
    trim($data['follow_up_status']) != ""
) {
    $followUpStatus = strtoupper(
        trim($data['follow_up_status'])
    );
}


/* =========================================================
   FOLLOW-UP STATUS CLASS
========================================================= */

$followUpStatusClass = "status-pending";

if ($followUpStatus == "CONFIRMED") {

    $followUpStatusClass = "status-confirmed";

} elseif ($followUpStatus == "CANCELLED") {

    $followUpStatusClass = "status-cancelled";

} elseif ($followUpStatus == "COMPLETED") {

    $followUpStatusClass = "status-completed";

} elseif ($followUpStatus == "NO SHOW") {

    $followUpStatusClass = "status-no-show";

} elseif ($followUpStatus == "PENDING") {

    $followUpStatusClass = "status-pending";

}


/* =========================================================
   GET PRESCRIPTION MEDICINES FOR THIS CONSULTATION
========================================================= */

$prescriptionMedicines = array();

$prescriptionSql = "
    SELECT
        pr.id,
        pr.medicine_name,
        pr.strength,
        pr.quantity,
        pr.breakfast,
        pr.lunch,
        pr.dinner,
        pr.prescribed_date
    FROM prescriptions pr
    WHERE pr.consultation_id = ?
    ORDER BY pr.id ASC
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


/* =========================================================
   HELPER FUNCTION
========================================================= */

function displayValue($value)
{
    if ($value === null || $value === '') {
        return "-";
    }

    return nl2br(
        htmlspecialchars($value)
    );
}

?>


<?php
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
   CONSULTATION VIEW PAGE
========================================================= */

.consultation-page {
    max-width: 1200px;
    margin: 0 auto;
}


/* =========================================================
   BACK BUTTON
========================================================= */

.consultation-back {
    display: inline-flex;
    align-items: center;
    min-height: 40px;
    padding: 0 16px;
    margin-bottom: 18px;

    background: #ffffff;
    color: #1f4e78;

    border: 1px solid #d5dbe1;
    border-radius: 6px;

    text-decoration: none;
    font-size: 13px;
    font-weight: 600;

    transition: 0.2s ease;
}

.consultation-back:hover {
    background: #f4f7fa;
    border-color: #1f4e78;
}


/* =========================================================
   MAIN CARDS
========================================================= */

.consultation-card {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    margin-bottom: 20px;
    overflow: hidden;
}

.consultation-card-header {
    padding: 18px 22px;
    border-bottom: 1px solid #e5e8eb;
    background: #ffffff;
}

.consultation-card-title {
    margin: 0;
    color: #1f4e78;
    font-size: 17px;
    font-weight: 700;
}

.consultation-card-body {
    padding: 22px;
}


/* =========================================================
   PATIENT / VISIT HEADER
========================================================= */

.consultation-patient-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 20px;
}

.consultation-patient-name {
    font-size: 23px;
    font-weight: 700;
    color: #222;
    margin-bottom: 5px;
}

.consultation-patient-id {
    font-size: 13px;
    color: #777;
}

.consultation-patient-id strong {
    color: #1f4e78;
}

.consultation-visit {
    text-align: right;
    min-width: 150px;
}

.consultation-visit-label {
    font-size: 11px;
    color: #888;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}

.consultation-visit-date {
    margin-top: 3px;
    color: #1f4e78;
    font-size: 16px;
    font-weight: 700;
}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

.patient-info-box {
    padding: 15px 16px;
    background: #f8fafc;
    border: 1px solid #e1e6eb;
    border-radius: 6px;
}

.patient-info-label {
    display: block;
    margin-bottom: 5px;

    color: #777;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.patient-info-value {
    display: block;

    color: #222;
    font-size: 14px;
    font-weight: 600;
}


/* =========================================================
   CLINICAL INFORMATION
========================================================= */

.clinical-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.clinical-box {
    border: 1px solid #e1e6eb;
    border-radius: 7px;
    overflow: hidden;
    background: #fff;
}

.clinical-label {
    display: block;
    padding: 10px 14px;

    background: #f5f7f9;
    border-bottom: 1px solid #e1e6eb;

    color: #5d6872;
    font-size: 12px;
    font-weight: 700;
}

.clinical-value {
    display: block;
    min-height: 44px;
    padding: 13px 14px;

    color: #222;
    font-size: 14px;
    line-height: 1.6;

    word-break: break-word;
}


/* =========================================================
   VITAL SIGNS
========================================================= */

.vitals-clean-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

.vital-clean-box {
    min-height: 88px;
    padding: 15px 17px;

    background: #f8fafc;
    border: 1px solid #dfe5ea;
    border-radius: 7px;

    display: flex;
    flex-direction: column;
    justify-content: center;
}

.vital-clean-label {
    margin-bottom: 7px;

    color: #707982;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.25px;
}

.vital-clean-value {
    color: #1f4e78;
    font-size: 19px;
    font-weight: 700;
}


/* =========================================================
   ASSESSMENT & TREATMENT
========================================================= */

.treatment-list {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.treatment-box {
    border: 1px solid #dfe5ea;
    border-radius: 7px;
    background: #ffffff;
    overflow: hidden;
}

.treatment-label {
    display: block;

    padding: 10px 14px;

    background: #f5f7f9;
    border-bottom: 1px solid #dfe5ea;

    color: #5d6872;
    font-size: 12px;
    font-weight: 700;
}

.treatment-value {
    display: block;

    padding: 13px 14px;

    min-height: 43px;

    color: #222;
    font-size: 14px;
    line-height: 1.6;

    word-break: break-word;
}


/* =========================================================
   PRESCRIPTION
========================================================= */

.prescription-table-wrapper {
    width: 100%;
    overflow-x: auto;
}

.prescription-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 720px;
}

.prescription-table th,
.prescription-table td {
    border: 1px solid #dfe5ea;
    padding: 11px 12px;
    font-size: 13px;
    vertical-align: middle;
}

.prescription-table th {
    background: #f5f7f9;
    color: #5d6872;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.25px;
    text-align: center;
}

.prescription-table td {
    color: #222;
    background: #ffffff;
}

.prescription-table td:first-child {
    font-weight: 600;
}

.prescription-table .qty-cell {
    text-align: center;
    width: 70px;
    font-weight: 600;
}

.prescription-table .dose-cell {
    text-align: center;
    width: 120px;
}

.prescription-empty {
    padding: 18px 14px;
    background: #f8fafc;
    border: 1px solid #dfe5ea;
    border-radius: 7px;

    color: #777;
    font-size: 14px;
    text-align: center;
}


/* =========================================================
   FOLLOW-UP
========================================================= */

.followup-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}

.followup-box {
    border: 1px solid #dfe5ea;
    border-radius: 7px;
    background: #ffffff;
    overflow: hidden;
}

.followup-label {
    display: block;

    padding: 10px 14px;

    background: #f5f7f9;
    border-bottom: 1px solid #dfe5ea;

    color: #5d6872;
    font-size: 12px;
    font-weight: 700;
}

.followup-value {
    display: flex;
    align-items: center;

    padding: 13px 14px;

    min-height: 43px;

    color: #222;
    font-size: 14px;
    line-height: 1.6;

    word-break: break-word;
}


/* =========================================================
   FOLLOW-UP STATUS BADGE
========================================================= */

.followup-status {

    display: inline-flex;

    align-items: center;
    justify-content: center;

    min-width: 95px;

    padding: 6px 12px;

    border-radius: 20px;

    font-size: 11px;
    font-weight: 700;

    text-align: center;
}


/* PENDING */

.status-pending {

    background: #fff4cc;
    color: #856404;
}


/* CONFIRMED */

.status-confirmed {

    background: #e6f4ea;
    color: #287d3c;
}


/* CANCELLED */

.status-cancelled {

    background: #fce8e8;
    color: #b42318;
}


/* COMPLETED */

.status-completed {

    background: #dff3ea;
    color: #176b4d;
}


/* NO SHOW */

.status-no-show {

    background: #fff0d9;
    color: #9a5b00;
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.consultation-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 12px;

    margin-top: 6px;
    padding: 18px 0 5px;
}

.consultation-actions-left,
.consultation-actions-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}


/* Make all buttons consistent */

.consultation-actions .btn,
.consultation-actions button {
    height: 40px;
    min-height: 40px;

    padding: 0 16px;

    border-radius: 6px;

    font-size: 13px;
    font-weight: 600;

    white-space: nowrap;
}


/* Secondary */

.consultation-actions .btn-secondary {
    background: #ffffff;
    color: #495057;
    border: 1px solid #cfd5da;
}

.consultation-actions .btn-secondary:hover {
    background: #f4f6f8;
}


/* Print */

.consultation-actions .btn-success {
    background: #198754;
    color: #ffffff;
    border: 1px solid #198754;
}

.consultation-actions .btn-success:hover {
    background: #157347;
}


/* Prescription */

.consultation-actions .btn-prescription {
    background: #1f4e78;
    color: #ffffff;
    border: 1px solid #1f4e78;
}

.consultation-actions .btn-prescription:hover {
    background: #163a5b;
}


/* Edit */

.consultation-actions .btn-warning {
    background: #f0ad00;
    color: #ffffff;
    border: 1px solid #f0ad00;
}

.consultation-actions .btn-warning:hover {
    background: #d99b00;
}


/* Delete */

.consultation-actions .btn-danger {
    background: #dc3545;
    color: #ffffff;
    border: 1px solid #dc3545;
}

.consultation-actions .btn-danger:hover {
    background: #bb2d3b;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .vitals-clean-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .patient-info-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .followup-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}


@media (max-width: 700px) {

    .consultation-patient-header {
        flex-direction: column;
        align-items: flex-start;
    }

    .consultation-visit {
        text-align: left;
    }

    .vitals-clean-grid,
    .patient-info-grid,
    .followup-grid {
        grid-template-columns: 1fr;
    }

    .consultation-card-body {
        padding: 16px;
    }

    .consultation-card-header {
        padding: 16px;
    }

    .consultation-patient-name {
        font-size: 20px;
    }

    .consultation-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .consultation-actions-left,
    .consultation-actions-right {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .consultation-actions .btn,
    .consultation-actions button {
        width: 100%;
    }

}

</style>


<main class="main-container">

    <div class="consultation-page">


        <!-- =====================================================
             BACK TO PATIENT PROFILE
        ====================================================== -->

        <a
            href="view.php?id=<?php echo $data['patient_id']; ?>"
            class="consultation-back"
        >
            ← Back to Patient Profile
        </a>


        <!-- =====================================================
             PATIENT / VISIT HEADER
        ====================================================== -->

        <div class="consultation-card">

            <div class="consultation-card-body">

                <div class="consultation-patient-header">

                    <div>

                        <div class="consultation-patient-name">
                            <?php echo htmlspecialchars($fullName); ?>
                        </div>

                        <div class="consultation-patient-id">
                            Patient ID:
                            <strong>
                                <?php echo htmlspecialchars($data['patient_number']); ?>
                            </strong>
                        </div>

                    </div>


                    <div class="consultation-visit">

                        <div class="consultation-visit-label">
                            Visit Date
                        </div>

                        <div class="consultation-visit-date">
                            <?php
                            echo date(
                                "F d, Y",
                                strtotime($data['visit_date'])
                            );
                            ?>
                        </div>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             PATIENT INFORMATION
        ====================================================== -->

        <div class="consultation-card">

            <div class="consultation-card-header">
                <h2 class="consultation-card-title">
                    Patient Information
                </h2>
            </div>

            <div class="consultation-card-body">

                <div class="patient-info-grid">


                    <!-- FULL NAME -->

                    <div class="patient-info-box">

                        <span class="patient-info-label">
                            Full Name
                        </span>

                        <span class="patient-info-value">
                            <?php echo htmlspecialchars($fullName); ?>
                        </span>

                    </div>


                    <!-- AGE -->

                    <div class="patient-info-box">

                        <span class="patient-info-label">
                            Age
                        </span>

                        <span class="patient-info-value">
                            <?php echo htmlspecialchars($age); ?>
                            years old
                        </span>

                    </div>


                    <!-- SEX -->

                    <div class="patient-info-box">

                        <span class="patient-info-label">
                            Sex
                        </span>

                        <span class="patient-info-value">
                            <?php echo htmlspecialchars($data['sex']); ?>
                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             CLINICAL INFORMATION
        ====================================================== -->

        <div class="consultation-card">

            <div class="consultation-card-header">
                <h2 class="consultation-card-title">
                    Clinical Information
                </h2>
            </div>

            <div class="consultation-card-body">

                <div class="clinical-list">


                    <!-- CHIEF COMPLAINT -->

                    <div class="clinical-box">

                        <span class="clinical-label">
                            Chief Complaint
                        </span>

                        <span class="clinical-value">

                            <?php
                            if (!empty($data['chief_complaint'])) {
                                echo nl2br(
                                    htmlspecialchars(
                                        $data['chief_complaint']
                                    )
                                );
                            } else {
                                echo "-";
                            }
                            ?>

                        </span>

                    </div>


                    <!-- HISTORY OF PRESENT ILLNESS -->

                    <div class="clinical-box">

                        <span class="clinical-label">
                            History of Present Illness
                        </span>

                        <span class="clinical-value">

                            <?php
                            if (!empty($data['history_illness'])) {
                                echo nl2br(
                                    htmlspecialchars(
                                        $data['history_illness']
                                    )
                                );
                            } else {
                                echo "-";
                            }
                            ?>

                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             VITAL SIGNS
        ====================================================== -->

        <div class="consultation-card">

            <div class="consultation-card-header">
                <h2 class="consultation-card-title">
                    Vital Signs
                </h2>
            </div>

            <div class="consultation-card-body">

                <div class="vitals-clean-grid">


                    <!-- BLOOD PRESSURE -->

                    <div class="vital-clean-box">

                        <div class="vital-clean-label">
                            Blood Pressure
                        </div>

                        <div class="vital-clean-value">

                            <?php
                            if (!empty($data['blood_pressure'])) {
                                echo htmlspecialchars(
                                    $data['blood_pressure']
                                );
                            } else {
                                echo "-";
                            }
                            ?>

                        </div>

                    </div>


                    <!-- TEMPERATURE -->

                    <div class="vital-clean-box">

                        <div class="vital-clean-label">
                            Temperature
                        </div>

                        <div class="vital-clean-value">

                            <?php
                            if (
                                $data['temperature'] !== null &&
                                $data['temperature'] !== ''
                            ) {
                                echo htmlspecialchars(
                                    $data['temperature']
                                );
                                echo " °C";
                            } else {
                                echo "-";
                            }
                            ?>

                        </div>

                    </div>


                    <!-- PULSE RATE -->

                    <div class="vital-clean-box">

                        <div class="vital-clean-label">
                            Pulse Rate
                        </div>

                        <div class="vital-clean-value">

                            <?php
                            if (
                                $data['pulse_rate'] !== null &&
                                $data['pulse_rate'] !== ''
                            ) {
                                echo htmlspecialchars(
                                    $data['pulse_rate']
                                );
                                echo " bpm";
                            } else {
                                echo "-";
                            }
                            ?>

                        </div>

                    </div>


                    <!-- RESPIRATORY RATE -->

                    <div class="vital-clean-box">

                        <div class="vital-clean-label">
                            Respiratory Rate
                        </div>

                        <div class="vital-clean-value">

                            <?php
                            if (
                                $data['respiratory_rate'] !== null &&
                                $data['respiratory_rate'] !== ''
                            ) {
                                echo htmlspecialchars(
                                    $data['respiratory_rate']
                                );
                                echo " /min";
                            } else {
                                echo "-";
                            }
                            ?>

                        </div>

                    </div>


                    <!-- SpO2 -->

                    <div class="vital-clean-box">

                        <div class="vital-clean-label">
                            SpO₂
                        </div>

                        <div class="vital-clean-value">

                            <?php
                            if (
                                $data['oxygen_saturation'] !== null &&
                                $data['oxygen_saturation'] !== ''
                            ) {
                                echo htmlspecialchars(
                                    $data['oxygen_saturation']
                                );
                                echo " %";
                            } else {
                                echo "-";
                            }
                            ?>

                        </div>

                    </div>


                    <!-- WEIGHT -->

                    <div class="vital-clean-box">

                        <div class="vital-clean-label">
                            Weight
                        </div>

                        <div class="vital-clean-value">

                            <?php
                            if (
                                $data['weight'] !== null &&
                                $data['weight'] !== ''
                            ) {
                                echo htmlspecialchars(
                                    number_format(
                                        (float)$data['weight'],
                                        2
                                    )
                                );
                                echo " kg";
                            } else {
                                echo "-";
                            }
                            ?>

                        </div>

                    </div>


                    <!-- HEIGHT -->

                    <div class="vital-clean-box">

                        <div class="vital-clean-label">
                            Height
                        </div>

                        <div class="vital-clean-value">

                            <?php
                            if (
                                $data['height'] !== null &&
                                $data['height'] !== ''
                            ) {
                                echo htmlspecialchars(
                                    number_format(
                                        (float)$data['height'],
                                        2
                                    )
                                );
                                echo " cm";
                            } else {
                                echo "-";
                            }
                            ?>

                        </div>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             ASSESSMENT & TREATMENT
        ====================================================== -->

        <div class="consultation-card">

            <div class="consultation-card-header">
                <h2 class="consultation-card-title">
                    Assessment
                </h2>
            </div>

            <div class="consultation-card-body">

                <div class="treatment-list">


                    <!-- ASSESSMENT -->

                    <div class="treatment-box">

                        <span class="treatment-label">
                            Diagnosis
                        </span>

                        <span class="treatment-value">

                            <?php
                            if (!empty($data['assessment'])) {
                                echo nl2br(
                                    htmlspecialchars(
                                        $data['assessment']
                                    )
                                );
                            } else {
                                echo "-";
                            }
                            ?>

                        </span>

                    </div>


                    <!-- MANAGEMENT -->

                    <div class="treatment-box">

                        <span class="treatment-label">
                            Management / Plan
                        </span>

                        <span class="treatment-value">

                            <?php
                            if (!empty($data['management'])) {
                                echo nl2br(
                                    htmlspecialchars(
                                        $data['management']
                                    )
                                );
                            } else {
                                echo "-";
                            }
                            ?>

                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             PRESCRIPTION
             SHOW ONLY
        ====================================================== -->

        <div class="consultation-card">

            <div class="consultation-card-header">

                <h2 class="consultation-card-title">
                    Prescription
                </h2>

            </div>


            <div class="consultation-card-body">


                <?php if (count($prescriptionMedicines) > 0) { ?>


                    <div class="prescription-table-wrapper">

                        <table class="prescription-table">

                            <thead>

                                <tr>

                                    <th>
                                        Medication
                                    </th>

                                    <th>
                                        Qty
                                    </th>

                                    <th>
                                        Breakfast
                                    </th>

                                    <th>
                                        Lunch
                                    </th>

                                    <th>
                                        Dinner
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                <?php
                                foreach (
                                    $prescriptionMedicines
                                    as $medicine
                                ) {
                                ?>

                                    <tr>


                                        <!-- MEDICINE + STRENGTH -->

                                        <td>

                                            <?php

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

                                            echo htmlspecialchars(
                                                $medicineDisplay
                                            );

                                            ?>

                                        </td>


                                        <!-- QUANTITY -->

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

        </div>


        <!-- =====================================================
             FOLLOW-UP & REMARKS
        ====================================================== -->

        <div class="consultation-card">

            <div class="consultation-card-header">
                <h2 class="consultation-card-title">
                    Follow-up &amp; Remarks
                </h2>
            </div>

            <div class="consultation-card-body">

                <div class="followup-grid">


                    <!-- FOLLOW-UP DATE -->

                    <div class="followup-box">

                        <span class="followup-label">
                            Follow-up Date
                        </span>

                        <span class="followup-value">

                            <?php
                            if (!empty($data['follow_up_date'])) {

                                echo date(
                                    "F d, Y",
                                    strtotime(
                                        $data['follow_up_date']
                                    )
                                );

                            } else {
                                echo "-";
                            }
                            ?>

                        </span>

                    </div>


                    <!-- FOLLOW-UP STATUS -->

                    <div class="followup-box">

                        <span class="followup-label">
                            Follow-up Status
                        </span>

                        <span class="followup-value">

                            <span
                                class="followup-status <?php echo $followUpStatusClass; ?>"
                            >
                                <?php
                                echo htmlspecialchars(
                                    $followUpStatus
                                );
                                ?>
                            </span>

                        </span>

                    </div>


                    <!-- REMARKS -->

                    <div class="followup-box">

                        <span class="followup-label">
                            Remarks
                        </span>

                        <span class="followup-value">

                            <?php
                            if (!empty($data['remarks'])) {

                                echo nl2br(
                                    htmlspecialchars(
                                        $data['remarks']
                                    )
                                );

                            } else {
                                echo "-";
                            }
                            ?>

                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             ACTION BUTTONS
        ====================================================== -->

        <div class="consultation-actions">

            <!-- LEFT -->

            <div class="consultation-actions-left">
            </div>


            <!-- RIGHT -->

            <div class="consultation-actions-right">

                <a
                    href="print_consultation.php?id=<?php echo $id; ?>"
                    class="btn btn-success"
                >
                    🖨 Print Consultation
                </a>


                <a
                    href="../prescriptions/prescription.php?consultation_id=<?php echo $id; ?>"
                    class="btn btn-prescription"
                >
                    💊 Prescription
                </a>


                <a
                    href="consultation_edit.php?id=<?php echo $id; ?>"
                    class="btn btn-warning"
                >
                    Edit Consultation
                </a>


                <form
                    action="delete_consultation.php"
                    method="POST"
                    style="margin: 0;"
                    onsubmit="return confirm('Are you sure you want to delete this consultation? This action cannot be undone.');"
                >

                    <input
                        type="hidden"
                        name="consultation_id"
                        value="<?php echo $id; ?>"
                    >

                    <button
                        type="submit"
                        class="btn btn-danger"
                    >
                        Delete Consultation
                    </button>

                </form>

            </div>

        </div>


    </div>

</main>


<?php
/* =========================================================
   SHARED FOOTER
========================================================= */

include __DIR__ . "/../includes/footer.php";


/* =========================================================
   CLOSE DATABASE
========================================================= */

$conn->close();
?>