<?php

require_once "../config/database.php";


/* =========================================================
   GET PARAMETERS
========================================================= */

$patientId = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

$testDate = isset($_GET['date'])
    ? trim($_GET['date'])
    : "";


if ($patientId <= 0) {
    die("Invalid patient ID.");
}


if ($testDate === "") {
    die("Laboratory date is required.");
}


/* =========================================================
   VALIDATE DATE
========================================================= */

$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $testDate
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $testDate
) {
    die("Invalid laboratory date.");
}


/* =========================================================
   GET PATIENT
========================================================= */

$patientSql = "
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
";

$patientStmt = $conn->prepare($patientSql);

if (!$patientStmt) {
    die("Database error: " . $conn->error);
}

$patientStmt->bind_param(
    "i",
    $patientId
);

$patientStmt->execute();

$patientResult = $patientStmt->get_result();

$patient = $patientResult->fetch_assoc();

$patientStmt->close();


if (!$patient) {
    die("Patient not found.");
}


/* =========================================================
   PATIENT NAME
========================================================= */

$fullName = strtoupper(
    trim($patient['last_name'])
    . ", "
    . trim($patient['first_name'])
);

if (!empty($patient['middle_name'])) {

    $fullName .= " "
        . strtoupper(
            trim($patient['middle_name'])
        );
}


/* =========================================================
   AGE
========================================================= */

$age = "";

if (!empty($patient['birthdate'])) {

    $birthDate = new DateTime(
        $patient['birthdate']
    );

    $today = new DateTime();

    $age = $birthDate->diff($today)->y;
}


/* =========================================================
   GET LABORATORY RESULTS
========================================================= */

$labSql = "
    SELECT
        id,
        test_name,
        result,
        unit,
        reference_range
    FROM laboratory
    WHERE patient_id = ?
      AND test_date = ?
    ORDER BY id ASC
";

$labStmt = $conn->prepare($labSql);

if (!$labStmt) {
    die("Database error: " . $conn->error);
}

$labStmt->bind_param(
    "is",
    $patientId,
    $testDate
);

$labStmt->execute();

$labResult = $labStmt->get_result();

$laboratoryResults = array();

while ($row = $labResult->fetch_assoc()) {

    $laboratoryResults[] = $row;

}

$labStmt->close();


/* =========================================================
   CHECK RESULTS
========================================================= */

if (count($laboratoryResults) === 0) {
    die("No laboratory results found for this date.");
}


/* =========================================================
   FORMAT DATE
========================================================= */

$formattedDate = date(
    "F d, Y",
    strtotime($testDate)
);


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Laboratory Results";
$pageSubtitle = "Laboratory Results";

$basePath = "../";
$activePage = "patients";

include "../includes/header.php";
include "../includes/navigation.php";
require_once "../config/auth.php";
?>

<style>

/* =========================================================
   PAGE
========================================================= */

.lab-record-page {
    max-width: 1180px;
    margin: 24px auto 50px;
    padding: 0 20px;
}


/* =========================================================
   PAGE TOP
========================================================= */

.lab-page-top {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 14px;
}

.lab-page-heading h2 {
    margin: 0;
    color: #172033;
    font-size: 24px;
    font-weight: 700;
    letter-spacing: -0.3px;
}

.lab-page-heading p {
    margin: 3px 0 0;
    color: #718096;
    font-size: 12px;
}


/* =========================================================
   TOP ACTION
========================================================= */

.lab-page-actions {
    display: flex;
    gap: 8px;
}

.lab-top-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 34px;
    padding: 0 13px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    border: 1px solid #d1d5db;
    transition: 0.2s ease;
}

.lab-back-btn {
    background: #ffffff;
    color: #475569;
}

.lab-back-btn:hover {
    background: #f1f5f9;
}


/* =========================================================
   MAIN RECORD CARD
========================================================= */

.lab-record-card {
    background: #ffffff;
    border: 1px solid #d8dee7;
    border-radius: 7px;
    overflow: hidden;
}


/* =========================================================
   RECORD HEADER
========================================================= */

.lab-record-header {
    padding: 15px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #d8dee7;
}

.record-header-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
}

.record-title {
    color: #172033;
    font-size: 16px;
    font-weight: 700;
    margin: 0;
}

.record-subtitle {
    margin-top: 3px;
    color: #64748b;
    font-size: 11px;
}

.record-status {
    display: inline-flex;
    align-items: center;
    min-height: 26px;
    padding: 0 10px;
    border-radius: 5px;
    background: #eef6ff;
    color: #145b91;
    border: 1px solid #cfe4f7;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}


/* =========================================================
   PATIENT INFORMATION
========================================================= */

.patient-information {
    padding: 16px 22px;
    border-bottom: 1px solid #e5e7eb;
}

.patient-info-title {
    margin-bottom: 11px;
    color: #64748b;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.8px;
}

.patient-grid {
    display: grid;
    grid-template-columns: 2fr 0.85fr 0.85fr 2.4fr 1.35fr;
    gap: 0;
}

.info-item {
    min-width: 0;
    padding: 0 17px;
    border-right: 1px solid #e5e7eb;
}

.info-item:first-child {
    padding-left: 0;
}

.info-item:last-child {
    padding-right: 0;
    border-right: none;
}

.info-label {
    display: block;
    margin-bottom: 4px;
    color: #8993a2;
    font-size: 8px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-value {
    color: #1f2937;
    font-size: 12px;
    font-weight: 600;
    line-height: 1.4;
    word-break: break-word;
}


/* =========================================================
   TABLE CONTAINER
========================================================= */

.table-container {
    width: 100%;
    overflow-x: auto;
}


/* =========================================================
   RESULTS TABLE
========================================================= */

.results-table {
    width: 100%;
    min-width: 900px;
    border-collapse: collapse;
}

.results-table thead th {
    padding: 11px 15px;
    background: #f1f5f9;
    color: #475569;
    border-top: 1px solid #dbe3ea;
    border-bottom: 1px solid #cbd5e1;
    text-align: left;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    white-space: nowrap;
}

.results-table tbody tr {
    border-bottom: 1px solid #e5e7eb;
}

.results-table tbody tr:last-child {
    border-bottom: none;
}

.results-table tbody tr:hover {
    background: #f8fafc;
}

.results-table td {
    padding: 13px 15px;
    color: #374151;
    font-size: 12px;
    vertical-align: middle;
}

.test-name {
    color: #1f2937;
    font-weight: 700;
}

.result-value {
    color: #111827;
    font-weight: 700;
}

.unit-value {
    color: #475569;
    font-weight: 600;
}

.reference-value {
    color: #64748b;
}

.result-badge {
    display: inline-block;
    min-width: 55px;
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.action-buttons {
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}

.edit-result-btn,
.delete-result-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 30px;
    padding: 0 10px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
    transition: 0.2s ease;
}

.edit-result-btn {
    background: #ffffff;
    color: #475569;
    border: 1px solid #cbd5e1;
}

.edit-result-btn:hover {
    background: #f1f5f9;
    color: #1e293b;
}

.delete-result-btn {
    background: #fff7f7;
    color: #b42318;
    border: 1px solid #f1c4c0;
    cursor: pointer;
}

.delete-result-btn:hover {
    background: #feecec;
    border-color: #e5a8a3;
}


/* =========================================================
   RECORD FOOTER
========================================================= */

.record-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 12px 22px;
    background: #f8fafc;
    border-top: 1px solid #e5e7eb;
}

.footer-note {
    color: #64748b;
    font-size: 10px;
}

.footer-count {
    color: #334155;
    font-size: 10px;
    font-weight: 700;
}


/* =========================================================
   BOTTOM ACTION
========================================================= */

.bottom-actions {
    display: flex;
    justify-content: flex-end;
    margin-top: 13px;
}

.add-new-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 36px;
    padding: 0 15px;
    background: #145f91;
    color: #ffffff;
    border: 1px solid #145f91;
    border-radius: 5px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    transition: 0.2s ease;
}

.add-new-btn:hover {
    background: #0f4f79;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 850px) {

    .lab-record-page {
        padding: 0 14px;
        margin-top: 20px;
    }

    .lab-page-top {
        flex-direction: column;
        align-items: flex-start;
    }

    .lab-page-actions {
        width: 100%;
    }

    .lab-top-btn {
        flex: 1;
    }

    .patient-grid {
        grid-template-columns: 1fr 1fr;
        gap: 17px;
    }

    .info-item {
        padding: 0;
        border-right: none;
    }

}


@media (max-width: 550px) {

    .patient-grid {
        grid-template-columns: 1fr;
    }

    .lab-page-actions {
        flex-direction: column;
    }

    .lab-top-btn {
        width: 100%;
    }

    .record-header-main {
        align-items: flex-start;
        flex-direction: column;
    }

    .record-footer {
        flex-direction: column;
        align-items: flex-start;
    }

}


/* =========================================================
   PRINT
========================================================= */

@media print {

    @page {
        size: A4 portrait;
        margin: 12mm;
    }

    body {
        background: #ffffff !important;
    }

    .header,
    .navigation,
    .footer,
    .lab-page-top,
    .bottom-actions,
    .action-column,
    .action-buttons {
        display: none !important;
    }

    .lab-record-page {
        max-width: none;
        margin: 0;
        padding: 0;
    }

    .lab-record-card {
        border: none;
        box-shadow: none;
    }

    .lab-record-header {
        background: #ffffff !important;
        padding: 0 0 12px;
        border-bottom: 2px solid #172033;
    }

    .patient-information {
        padding: 14px 0;
    }

    .results-table {
        min-width: 0;
    }

    .results-table thead th {
        background: #eeeeee !important;
    }

    .record-footer {
        padding: 12px 0;
    }

}

</style>


<div class="lab-record-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="lab-page-top">

        <div class="lab-page-heading">

            <h2>
                Laboratory Results
            </h2>

            <p>
                Patient laboratory records on file
            </p>

        </div>


        <div class="lab-page-actions">

            <a
                href="index.php?id=<?php echo $patientId; ?>"
                class="lab-top-btn lab-back-btn"
            >
                ← Back to Patient
            </a>

        </div>

    </div>


    <!-- =====================================================
         MAIN RECORD CARD
    ====================================================== -->

    <div class="lab-record-card">


        <!-- =================================================
             RECORD HEADER
        ================================================== -->

        <div class="lab-record-header">

            <div class="record-header-main">

                <div>

                    <div class="record-title">
                        Laboratory Examination Record
                    </div>

                    <div class="record-subtitle">
                        Laboratory results submitted for patient record
                    </div>

                </div>


                <div class="record-status">
                    Record on File
                </div>

            </div>

        </div>


        <!-- =================================================
             PATIENT INFORMATION
        ================================================== -->

        <div class="patient-information">

            <div class="patient-info-title">
                Patient Information
            </div>


            <div class="patient-grid">


                <!-- PATIENT NAME -->

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


                <!-- PATIENT ID -->

                <div class="info-item">

                    <span class="info-label">
                        Patient ID
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $patient['patient_id']
                        );

                        ?>

                    </div>

                </div>


                <!-- AGE / SEX -->

                <div class="info-item">

                    <span class="info-label">
                        Age / Sex
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $age
                        );

                        echo " / ";

                        echo htmlspecialchars(
                            strtoupper(
                                $patient['sex']
                            )
                        );

                        ?>

                    </div>

                </div>


                <!-- ADDRESS -->

                <div class="info-item">

                    <span class="info-label">
                        Address
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $patient['address']
                        );

                        ?>

                    </div>

                </div>


                <!-- LABORATORY DATE -->

                <div class="info-item">

                    <span class="info-label">
                        Laboratory Date
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $formattedDate
                        );

                        ?>

                    </div>

                </div>


            </div>

        </div>


        <!-- =================================================
             RESULTS TABLE
        ================================================== -->

        <div class="table-container">

            <table class="results-table">

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

                        <th class="action-column">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach (
                        $laboratoryResults
                        as $lab
                    ): ?>

                        <tr>


                            <!-- TEST NAME -->

                            <td class="test-name">

                                <?php

                                echo htmlspecialchars(
                                    strtoupper(
                                        $lab['test_name']
                                    )
                                );

                                ?>

                            </td>


                            <!-- RESULT -->

                            <td class="result-value">

                                <span class="result-badge">

                                    <?php

                                    echo htmlspecialchars(
                                        $lab['result']
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- UNIT -->

                            <td class="unit-value">

                                <?php

                                if (
                                    trim(
                                        $lab['unit']
                                    ) !== ""
                                ) {

                                    echo htmlspecialchars(
                                        strtoupper(
                                            $lab['unit']
                                        )
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- REFERENCE RANGE -->

                            <td class="reference-value">

                                <?php

                                if (
                                    trim(
                                        $lab['reference_range']
                                    ) !== ""
                                ) {

                                    echo htmlspecialchars(
                                        strtoupper(
                                            $lab['reference_range']
                                        )
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- ACTION -->

                            <td class="action-column">

                                <div class="action-buttons">


                                    <!-- EDIT -->

                                    <a
                                        href="edit.php?id=<?php
                                            echo (int)$lab['id'];
                                        ?>"
                                        class="edit-result-btn"
                                    >
                                        Edit
                                    </a>


                                    <!-- DELETE -->

                                    <form
                                        method="POST"
                                        action="delete.php"
                                        style="margin:0;"
                                        onsubmit="return confirmDelete();"
                                    >

                                        <input
                                            type="hidden"
                                            name="id"
                                            value="<?php
                                                echo (int)$lab['id'];
                                            ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="delete-result-btn"
                                        >
                                            Delete
                                        </button>

                                    </form>


                                </div>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>


        <!-- =================================================
             RECORD FOOTER
        ================================================== -->

        <div class="record-footer">

            <div class="footer-note">
                Laboratory results submitted for clinic record purposes.
            </div>

            <div class="footer-count">

                <?php

                echo count(
                    $laboratoryResults
                );

                ?>

                Laboratory Test(s)

            </div>

        </div>


    </div>


    <!-- =====================================================
         BOTTOM ACTION
    ====================================================== -->

    <div class="bottom-actions">

        <a
            href="add.php?id=<?php echo $patientId; ?>"
            class="add-new-btn"
        >
            + Add Laboratory Result
        </a>

    </div>


</div>


<script>

/* =========================================================
   DELETE CONFIRMATION
========================================================= */

function confirmDelete() {

    return confirm(
        "Are you sure you want to delete this laboratory result?\n\n" +
        "This action cannot be undone."
    );

}

</script>


<?php include "../includes/footer.php"; ?>
