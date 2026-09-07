<?php
require_once "../config/database.php";

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Patient Profile";
$pageSubtitle = "Patient Profile";
$basePath = "../";
$activePage = "patients";


/* =========================================================
   GET PATIENT ID
========================================================= */

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    die("Invalid patient ID.");
}


/* =========================================================
   GET PATIENT
========================================================= */

$sql = "SELECT * FROM patients WHERE id = ?";

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
    die("Patient not found.");
}

$patient = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   CALCULATE AGE
========================================================= */

$age = "-";

if (!empty($patient['birthdate'])) {

    $birthDate = new DateTime($patient['birthdate']);
    $today = new DateTime();

    $age = $today->diff($birthDate)->y;
}


/* =========================================================
   FULL NAME
========================================================= */

$fullName = $patient['first_name'];

if (!empty($patient['middle_name'])) {
    $fullName .= " " . $patient['middle_name'];
}

$fullName .= " " . $patient['last_name'];


/* =========================================================
   GET CONSULTATION HISTORY
========================================================= */

$consultation_sql = "
    SELECT *
    FROM consultations
    WHERE patient_id = ?
    ORDER BY visit_date DESC, id DESC
";

$consultation_stmt = $conn->prepare($consultation_sql);

if (!$consultation_stmt) {
    die("Database error: " . $conn->error);
}

$consultation_stmt->bind_param("i", $id);
$consultation_stmt->execute();

$consultations = $consultation_stmt->get_result();

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
   PATIENT PROFILE PAGE
========================================================= */

.patient-profile-page {
    max-width: 1200px;
    margin: 0 auto;
}


/* =========================================================
   TOP ACTION BAR
========================================================= */

.patient-top-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 20px;
}

.patient-top-left,
.patient-top-right {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}


/* =========================================================
   BACK BUTTON
========================================================= */

.patient-back {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    height: 40px;
    min-height: 40px;

    padding: 0 16px;

    background: #ffffff;
    color: #1f4e78;

    border: 1px solid #d5dbe1;
    border-radius: 6px;

    text-decoration: none;

    font-size: 13px;
    font-weight: 600;

    transition: 0.2s ease;
}

.patient-back:hover {
    background: #f4f7fa;
    border-color: #1f4e78;
}


/* =========================================================
   PATIENT HEADER CARD
========================================================= */

.patient-header-card {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;

    padding: 22px;

    margin-bottom: 20px;
}

.patient-header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 20px;
}

.patient-header-name {
    margin: 0 0 5px 0;

    color: #1f4e78;

    font-size: 25px;
    font-weight: 700;
}

.patient-header-id {
    color: #777;
    font-size: 13px;
}

.patient-header-id strong {
    color: #1f4e78;
}


/* =========================================================
   STATUS
========================================================= */

.patient-header-status {
    display: flex;
    align-items: center;
    justify-content: flex-end;
}


/* =========================================================
   INFORMATION CARDS
========================================================= */

.patient-profile-card {
    background: #ffffff;
    border: 1px solid #e1e5e9;
    border-radius: 8px;

    margin-bottom: 20px;

    overflow: hidden;
}

.patient-profile-card-header {
    padding: 17px 22px;

    border-bottom: 1px solid #e5e8eb;

    background: #ffffff;
}

.patient-profile-card-title {
    margin: 0;

    color: #1f4e78;

    font-size: 17px;
    font-weight: 700;
}

.patient-profile-card-body {
    padding: 22px;
}


/* =========================================================
   INFORMATION GRID
========================================================= */

.patient-clean-grid {
    display: grid;

    grid-template-columns: repeat(3, 1fr);

    gap: 14px;
}

.patient-clean-box {
    min-height: 76px;

    padding: 14px 16px;

    background: #f8fafc;

    border: 1px solid #dfe5ea;
    border-radius: 7px;

    display: flex;
    flex-direction: column;
    justify-content: center;
}

.patient-clean-label {
    display: block;

    margin-bottom: 6px;

    color: #707982;

    font-size: 11px;
    font-weight: 700;

    text-transform: uppercase;
    letter-spacing: 0.25px;
}

.patient-clean-value {
    display: block;

    color: #222;

    font-size: 14px;
    font-weight: 600;

    word-break: break-word;
}


/* =========================================================
   VISIT HISTORY HEADER
========================================================= */

.visit-history-count {
    margin: 0 0 15px 0;

    color: #777;

    font-size: 13px;
}


/* =========================================================
   VISIT TABLE
========================================================= */

.patient-table-container {
    width: 100%;

    overflow-x: auto;

    border: 1px solid #e1e5e9;
    border-radius: 7px;
}

.patient-table {
    width: 100%;

    border-collapse: collapse;

    min-width: 1050px;

    background: #ffffff;
}

.patient-table th {
    padding: 12px 14px;

    background: #f5f7f9;

    border-bottom: 1px solid #dfe4e8;

    color: #5f6972;

    font-size: 11px;
    font-weight: 700;

    text-transform: uppercase;

    text-align: left;

    white-space: nowrap;
}

.patient-table td {
    padding: 13px 14px;

    border-bottom: 1px solid #edf0f2;

    color: #333;

    font-size: 13px;

    vertical-align: middle;
}

.patient-table tbody tr:last-child td {
    border-bottom: none;
}

.patient-table tbody tr:hover {
    background: #fafbfd;
}


/* =========================================================
   TABLE TEXT
========================================================= */

.visit-date-text {
    color: #1f4e78;
    font-weight: 600;
    white-space: nowrap;
}

.visit-complaint {
    max-width: 180px;
    line-height: 1.4;
}

.visit-assessment {
    max-width: 180px;
    line-height: 1.4;
}

.followup-date-text {
    white-space: nowrap;
    font-weight: 600;
}


/* =========================================================
   FOLLOW-UP STATUS BADGES
========================================================= */

.followup-status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 25px;

    padding: 4px 9px;

    border-radius: 20px;

    font-size: 10px;
    font-weight: 700;

    white-space: nowrap;
}


/* Pending */

.followup-status-pending {
    background: #fff3cd;
    color: #856404;
}


/* Confirmed */

.followup-status-confirmed {
    background: #d1ecf1;
    color: #0c5460;
}


/* Cancelled */

.followup-status-cancelled {
    background: #f8d7da;
    color: #721c24;
}


/* Completed */

.followup-status-completed {
    background: #d4edda;
    color: #155724;
}


/* No Show */

.followup-status-no-show {
    background: #e2e3e5;
    color: #383d41;
}


/* =========================================================
   MONITORING BADGES
========================================================= */

.monitoring-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    min-height: 25px;

    padding: 4px 9px;

    border-radius: 20px;

    font-size: 10px;
    font-weight: 700;

    white-space: nowrap;
}


/* Overdue */

.monitoring-overdue {
    background: #f8d7da;
    color: #721c24;
}


/* Due Soon */

.monitoring-due {
    background: #fff3cd;
    color: #856404;
}


/* Scheduled */

.monitoring-scheduled {
    background: #d1ecf1;
    color: #0c5460;
}


/* Confirmed */

.monitoring-confirmed {
    background: #d1ecf1;
    color: #0c5460;
}


/* Cancelled */

.monitoring-cancelled {
    background: #f8d7da;
    color: #721c24;
}


/* Completed */

.monitoring-completed {
    background: #d4edda;
    color: #155724;
}


/* No Show */

.monitoring-no-show {
    background: #e2e3e5;
    color: #383d41;
}


/* No Follow-up */

.monitoring-none {
    background: #e2e3e5;
    color: #383d41;
}


/* =========================================================
   ACTION TEXT
========================================================= */

.action-none {
    color: #777;
    font-size: 11px;
    font-weight: 600;
}

.action-followup {
    color: #1f4e78;
    font-size: 11px;
    font-weight: 700;
}

.action-priority {
    color: #dc3545;
    font-size: 11px;
    font-weight: 700;
}

.action-confirmed {
    color: #0c5460;
    font-size: 11px;
    font-weight: 700;
}

.action-cancelled {
    color: #721c24;
    font-size: 11px;
    font-weight: 700;
}

.action-completed {
    color: #155724;
    font-size: 11px;
    font-weight: 700;
}


/* =========================================================
   VIEW BUTTON
========================================================= */

.patient-view-btn {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    height: 34px;
    min-height: 34px;

    padding: 0 13px;

    background: #1f4e78;
    color: #ffffff;

    border: 1px solid #1f4e78;
    border-radius: 5px;

    text-decoration: none;

    font-size: 12px;
    font-weight: 600;

    white-space: nowrap;

    transition: 0.2s ease;
}

.patient-view-btn:hover {
    background: #173a5c;
    border-color: #173a5c;
}


/* =========================================================
   NO CONSULTATION
========================================================= */

.no-consultations {
    text-align: center;

    padding: 45px 20px;

    color: #777;

    font-size: 14px;
}

.no-consultations-text {
    margin-bottom: 16px;
}


/* =========================================================
   BUTTONS
========================================================= */

.patient-top-right .btn {
    height: 40px;
    min-height: 40px;

    padding: 0 16px;

    border-radius: 6px;

    font-size: 13px;
    font-weight: 600;

    white-space: nowrap;
}


/* =========================================================
   PRESCRIPTION HISTORY
========================================================= */

.prescription-history-count {
    margin: 0 0 15px 0;
    color: #777;
    font-size: 13px;
}

.prescription-table-container {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e1e5e9;
    border-radius: 7px;
}

.prescription-table {
    width: 100%;
    border-collapse: collapse;
    min-width: 1050px;
    background: #ffffff;
}

.prescription-table th {
    padding: 12px 14px;
    background: #f5f7f9;
    border-bottom: 1px solid #dfe4e8;
    color: #5f6972;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    text-align: left;
    white-space: nowrap;
}

.prescription-table td {
    padding: 13px 14px;
    border-bottom: 1px solid #edf0f2;
    color: #333;
    font-size: 13px;
    vertical-align: middle;
}

.prescription-table tbody tr:last-child td {
    border-bottom: none;
}

.prescription-table tbody tr:hover {
    background: #fafbfd;
}

.prescription-date-text {
    color: #1f4e78;
    font-weight: 600;
    white-space: nowrap;
}

.prescription-medicine {
    color: #222;
    font-weight: 700;
}

.prescription-strength {
    color: #555;
    white-space: nowrap;
}

.prescription-small-text {
    color: #555;
    line-height: 1.4;
}

.prescription-instructions {
    max-width: 260px;
    line-height: 1.4;
}

.prescription-consultation {
    white-space: nowrap;
    color: #555;
}

.prescription-view-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    height: 34px;
    min-height: 34px;
    padding: 0 13px;
    background: #1f4e78;
    color: #ffffff;
    border: 1px solid #1f4e78;
    border-radius: 5px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    transition: 0.2s ease;
}

.prescription-view-btn:hover {
    background: #173a5c;
    border-color: #173a5c;
}

.no-prescriptions {
    text-align: center;
    padding: 45px 20px;
    color: #777;
    font-size: 14px;
}

.no-prescriptions-text {
    margin-bottom: 16px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .patient-clean-grid {
        grid-template-columns: repeat(2, 1fr);
    }

}


@media (max-width: 700px) {

    .patient-top-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .patient-top-left,
    .patient-top-right {
        width: 100%;
        flex-direction: column;
        align-items: stretch;
    }

    .patient-back,
    .patient-top-right .btn {
        width: 100%;
    }

    .patient-header-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .patient-header-status {
        justify-content: flex-start;
    }

    .patient-clean-grid {
        grid-template-columns: 1fr;
    }

    .patient-profile-card-body {
        padding: 16px;
    }

    .patient-profile-card-header {
        padding: 16px;
    }

    .patient-header-card {
        padding: 18px;
    }

    .patient-header-name {
        font-size: 21px;
    }

}

</style>


<main class="main-container">

    <div class="patient-profile-page">


        <!-- =====================================================
             TOP ACTION BAR
        ====================================================== -->

        <div class="patient-top-actions">


            <!-- LEFT -->

            <div class="patient-top-left">

                <a
                    href="index.php"
                    class="patient-back"
                >
                    ← Back to Patient List
                </a>

            </div>


            <!-- RIGHT -->

            <div class="patient-top-right">

                <a
                    href="edit.php?id=<?php echo $id; ?>"
                    class="btn btn-primary"
                >
                    Edit Patient
                </a>


                <a
                    href="consultation.php?patient_id=<?php echo $id; ?>"
                    class="btn btn-primary"
                >
                    + New Consultation
                </a>


                <a
                    href="../prescriptions/history.php?id=<?php echo $id; ?>"
                    class="btn btn-primary"
                >
                    💊 Prescription History
                </a>


                <!-- =================================================
                     LABORATORY
                ================================================== -->

                <a href="../laboratory/index.php?id=<?php echo $id; ?>"
                    class="btn btn-primary">
                    🧪 Laboratory
                </a>

            </div>


        </div>


        <!-- =====================================================
             PATIENT PROFILE HEADER
        ====================================================== -->

        <div class="patient-header-card">

            <div class="patient-header-content">


                <div>

                    <h1 class="patient-header-name">
                        <?php echo htmlspecialchars($fullName); ?>
                    </h1>

                    <div class="patient-header-id">

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


                <div class="patient-header-status">

                    <?php if ($patient['status'] == 'Active') { ?>

                        <span class="status status-active">
                            Active
                        </span>

                    <?php } elseif ($patient['status'] == 'Deceased') { ?>

                        <span class="status status-deceased">
                            Deceased
                        </span>

                    <?php } else { ?>

                        <span class="status status-inactive">
                            Inactive
                        </span>

                    <?php } ?>

                </div>


            </div>

        </div>


        <!-- =====================================================
             PERSONAL INFORMATION
        ====================================================== -->

        <div class="patient-profile-card">

            <div class="patient-profile-card-header">

                <h2 class="patient-profile-card-title">
                    Personal Information
                </h2>

            </div>


            <div class="patient-profile-card-body">

                <div class="patient-clean-grid">


                    <!-- FULL NAME -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Full Name
                        </span>

                        <span class="patient-clean-value">
                            <?php
                            echo htmlspecialchars($fullName);
                            ?>
                        </span>

                    </div>


                    <!-- PATIENT ID -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Patient ID
                        </span>

                        <span class="patient-clean-value">
                            <?php
                            echo htmlspecialchars(
                                $patient['patient_id']
                            );
                            ?>
                        </span>

                    </div>


                    <!-- BIRTHDATE -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Birthdate
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            if (!empty($patient['birthdate'])) {

                                echo htmlspecialchars(
                                    date(
                                        "F d, Y",
                                        strtotime(
                                            $patient['birthdate']
                                        )
                                    )
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </span>

                    </div>


                    <!-- AGE -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Age
                        </span>

                        <span class="patient-clean-value">

                            <?php
                            echo htmlspecialchars($age);
                            ?>

                            <?php if ($age !== "-") { ?>
                                years old
                            <?php } ?>

                        </span>

                    </div>


                    <!-- SEX -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Sex
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            echo !empty($patient['sex'])
                                ? htmlspecialchars(
                                    $patient['sex']
                                )
                                : "-";

                            ?>

                        </span>

                    </div>


                    <!-- DATE REGISTERED -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Date Registered
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            if (
                                !empty(
                                    $patient['date_registered']
                                )
                            ) {

                                echo htmlspecialchars(
                                    date(
                                        "F d, Y",
                                        strtotime(
                                            $patient[
                                                'date_registered'
                                            ]
                                        )
                                    )
                                );

                            } else {

                                echo "-";

                            }

                            ?>

                        </span>

                    </div>


                    <!-- ADDRESS -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Address
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            echo !empty($patient['address'])
                                ? htmlspecialchars(
                                    $patient['address']
                                )
                                : "-";

                            ?>

                        </span>

                    </div>


                    <!-- STATUS -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Patient Status
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            echo !empty($patient['status'])
                                ? htmlspecialchars(
                                    $patient['status']
                                )
                                : "-";

                            ?>

                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             CONTACT INFORMATION
        ====================================================== -->

        <div class="patient-profile-card">

            <div class="patient-profile-card-header">

                <h2 class="patient-profile-card-title">
                    Contact Information
                </h2>

            </div>


            <div class="patient-profile-card-body">

                <div class="patient-clean-grid">


                    <!-- CONTACT NUMBER -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Contact Number
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            echo !empty(
                                $patient['contact_no']
                            )
                                ? htmlspecialchars(
                                    $patient['contact_no']
                                )
                                : "-";

                            ?>

                        </span>

                    </div>


                    <!-- EMERGENCY CONTACT -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Emergency Contact
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            echo !empty(
                                $patient['emergency_contact']
                            )
                                ? htmlspecialchars(
                                    $patient['emergency_contact']
                                )
                                : "-";

                            ?>

                        </span>

                    </div>


                    <!-- EMERGENCY CONTACT NUMBER -->

                    <div class="patient-clean-box">

                        <span class="patient-clean-label">
                            Emergency Contact Number
                        </span>

                        <span class="patient-clean-value">

                            <?php

                            echo !empty(
                                $patient['emergency_contact_no']
                            )
                                ? htmlspecialchars(
                                    $patient['emergency_contact_no']
                                )
                                : "-";

                            ?>

                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =====================================================
             VISIT HISTORY
        ====================================================== -->

        <div class="patient-profile-card">

            <div class="patient-profile-card-header">

                <h2 class="patient-profile-card-title">
                    Visit History
                </h2>

            </div>


            <div class="patient-profile-card-body">


                <?php if ($consultations->num_rows > 0) { ?>


                    <div class="visit-history-count">

                        <?php
                        echo $consultations->num_rows;
                        ?>

                        consultation record(s)

                    </div>


                    <div class="patient-table-container">

                        <table class="patient-table">

                            <thead>

                                <tr>

                                    <th>
                                        Visit Date
                                    </th>

                                    <th>
                                        Chief Complaint
                                    </th>

                                    <th>
                                        Assessment
                                    </th>

                                    <th>
                                        Follow-up Date
                                    </th>

                                    <th>
                                        Follow-up Status
                                    </th>

                                    <th>
                                        Monitoring
                                    </th>

                                    <th>
                                        Action
                                    </th>

                                    <th>
                                        View
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php while (
                                $consultation =
                                $consultations->fetch_assoc()
                            ) { ?>


                                <?php

                                /* =================================================
                                   DATABASE FOLLOW-UP STATUS
                                ================================================= */

                                $followUpStatus = "PENDING";

                                if (
                                    !empty(
                                        $consultation[
                                            'follow_up_status'
                                        ]
                                    )
                                ) {

                                    $followUpStatus =
                                        strtoupper(
                                            trim(
                                                $consultation[
                                                    'follow_up_status'
                                                ]
                                            )
                                        );

                                }


                                /*
                                 * BACKWARD COMPATIBILITY
                                 *
                                 * If an old consultation still has ACTIVE,
                                 * treat it as PENDING.
                                 */

                                if ($followUpStatus == "ACTIVE") {

                                    $followUpStatus = "PENDING";

                                }


                                /* =================================================
                                   STATUS BADGE CLASS
                                ================================================= */

                                $followUpStatusClass =
                                    "followup-status-pending";


                                if (
                                    $followUpStatus ==
                                    "CONFIRMED"
                                ) {

                                    $followUpStatusClass =
                                        "followup-status-confirmed";

                                } elseif (
                                    $followUpStatus ==
                                    "CANCELLED"
                                ) {

                                    $followUpStatusClass =
                                        "followup-status-cancelled";

                                } elseif (
                                    $followUpStatus ==
                                    "COMPLETED"
                                ) {

                                    $followUpStatusClass =
                                        "followup-status-completed";

                                } elseif (
                                    $followUpStatus ==
                                    "NO SHOW"
                                ) {

                                    $followUpStatusClass =
                                        "followup-status-no-show";

                                }


                                /* =================================================
                                   DEFAULT MONITORING
                                ================================================= */

                                $monitoring =
                                    "NO FOLLOW-UP";

                                $monitoringClass =
                                    "monitoring-none";

                                $action =
                                    "NO ACTION";

                                $actionClass =
                                    "action-none";


                                /* =================================================
                                   IMPORTANT:
                                   MONITORING FIRST CHECKS STATUS
                                ================================================= */

                                if (
                                    $followUpStatus ==
                                    "CONFIRMED"
                                ) {

                                    $monitoring =
                                        "CONFIRMED";

                                    $monitoringClass =
                                        "monitoring-confirmed";

                                    $action =
                                        "CONFIRMED";

                                    $actionClass =
                                        "action-confirmed";


                                } elseif (
                                    $followUpStatus ==
                                    "CANCELLED"
                                ) {

                                    $monitoring =
                                        "CANCELLED";

                                    $monitoringClass =
                                        "monitoring-cancelled";

                                    $action =
                                        "CANCELLED";

                                    $actionClass =
                                        "action-cancelled";


                                } elseif (
                                    $followUpStatus ==
                                    "COMPLETED"
                                ) {

                                    $monitoring =
                                        "COMPLETED";

                                    $monitoringClass =
                                        "monitoring-completed";

                                    $action =
                                        "COMPLETED";

                                    $actionClass =
                                        "action-completed";


                                } elseif (
                                    $followUpStatus ==
                                    "NO SHOW"
                                ) {

                                    $monitoring =
                                        "NO SHOW";

                                    $monitoringClass =
                                        "monitoring-no-show";

                                    $action =
                                        "NO SHOW";

                                    $actionClass =
                                        "action-priority";


                                } elseif (
                                    $followUpStatus ==
                                    "PENDING"
                                ) {

                                    if (
                                        !empty(
                                            $consultation[
                                                'follow_up_date'
                                            ]
                                        )
                                    ) {

                                        $today =
                                            new DateTime();

                                        $today->setTime(
                                            0,
                                            0,
                                            0
                                        );


                                        $followUpDate =
                                            new DateTime(
                                                $consultation[
                                                    'follow_up_date'
                                                ]
                                            );

                                        $followUpDate->setTime(
                                            0,
                                            0,
                                            0
                                        );


                                        $daysDifference =
                                            (int)$today->diff(
                                                $followUpDate
                                            )->format("%r%a");


                                        if (
                                            $daysDifference < 0
                                        ) {

                                            $monitoring =
                                                "OVERDUE";

                                            $monitoringClass =
                                                "monitoring-overdue";

                                            $action =
                                                "PRIORITY CALL";

                                            $actionClass =
                                                "action-priority";


                                        } elseif (
                                            $daysDifference <= 7
                                        ) {

                                            $monitoring =
                                                "DUE SOON";

                                            $monitoringClass =
                                                "monitoring-due";

                                            $action =
                                                "FOLLOW-UP CALL";

                                            $actionClass =
                                                "action-followup";


                                        } else {

                                            $monitoring =
                                                "SCHEDULED";

                                            $monitoringClass =
                                                "monitoring-scheduled";

                                            $action =
                                                "NO ACTION";

                                            $actionClass =
                                                "action-none";

                                        }


                                    } else {

                                        $monitoring =
                                            "NO FOLLOW-UP";

                                        $monitoringClass =
                                            "monitoring-none";

                                        $action =
                                            "NO ACTION";

                                        $actionClass =
                                            "action-none";

                                    }

                                }

                                ?>


                                <tr>


                                    <!-- VISIT DATE -->

                                    <td>

                                        <span class="visit-date-text">

                                            <?php

                                            if (
                                                !empty(
                                                    $consultation[
                                                        'visit_date'
                                                    ]
                                                )
                                            ) {

                                                echo htmlspecialchars(
                                                    date(
                                                        "M d, Y",
                                                        strtotime(
                                                            $consultation[
                                                                'visit_date'
                                                            ]
                                                        )
                                                    )
                                                );

                                            } else {

                                                echo "-";

                                            }

                                            ?>

                                        </span>

                                    </td>


                                    <!-- CHIEF COMPLAINT -->

                                    <td>

                                        <div class="visit-complaint">

                                            <?php

                                            echo !empty(
                                                $consultation[
                                                    'chief_complaint'
                                                ]
                                            )
                                                ? htmlspecialchars(
                                                    $consultation[
                                                        'chief_complaint'
                                                    ]
                                                )
                                                : "-";

                                            ?>

                                        </div>

                                    </td>


                                    <!-- ASSESSMENT -->

                                    <td>

                                        <div class="visit-assessment">

                                            <?php

                                            echo !empty(
                                                $consultation[
                                                    'assessment'
                                                ]
                                            )
                                                ? htmlspecialchars(
                                                    $consultation[
                                                        'assessment'
                                                    ]
                                                )
                                                : "-";

                                            ?>

                                        </div>

                                    </td>


                                    <!-- FOLLOW-UP DATE -->

                                    <td>

                                        <span class="followup-date-text">

                                            <?php

                                            if (
                                                !empty(
                                                    $consultation[
                                                        'follow_up_date'
                                                    ]
                                                )
                                            ) {

                                                echo htmlspecialchars(
                                                    date(
                                                        "M d, Y",
                                                        strtotime(
                                                            $consultation[
                                                                'follow_up_date'
                                                            ]
                                                        )
                                                    )
                                                );

                                            } else {

                                                echo "-";

                                            }

                                            ?>

                                        </span>

                                    </td>


                                    <!-- FOLLOW-UP STATUS -->

                                    <td>

                                        <span
                                            class="followup-status-badge <?php
                                                echo htmlspecialchars(
                                                    $followUpStatusClass
                                                );
                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $followUpStatus
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- MONITORING -->

                                    <td>

                                        <span
                                            class="monitoring-badge <?php
                                                echo htmlspecialchars(
                                                    $monitoringClass
                                                );
                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $monitoring
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- ACTION -->

                                    <td>

                                        <span
                                            class="<?php
                                                echo htmlspecialchars(
                                                    $actionClass
                                                );
                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $action
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- VIEW -->

                                    <td>

                                        <a
                                            href="consultation_view.php?id=<?php
                                                echo (int)
                                                    $consultation['id'];
                                            ?>"
                                            class="patient-view-btn"
                                        >
                                            View
                                        </a>

                                    </td>


                                </tr>


                            <?php } ?>


                            </tbody>

                        </table>

                    </div>


                <?php } else { ?>


                    <!-- =================================================
                         NO CONSULTATION
                    ================================================== -->

                    <div class="no-consultations">

                        <div class="no-consultations-text">

                            No consultations recorded yet.

                        </div>


                        <a
                            href="consultation.php?patient_id=<?php
                                echo $id;
                            ?>"
                            class="btn btn-primary"
                        >
                            + Add First Consultation
                        </a>

                    </div>


                <?php } ?>


            </div>

        </div>


    </div>

</main>


<?php

$consultation_stmt->close();

$conn->close();

?>


<?php include __DIR__ . "/../includes/footer.php"; ?>