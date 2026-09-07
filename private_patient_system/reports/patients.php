<?php

require_once "../config/database.php";

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Patient Report";
$pageSubtitle = "Patient Reports";

$basePath = "../";
$activePage = "reports";


/* =========================================================
   DATE FILTER
========================================================= */

$fromDate = isset($_GET["from_date"])
    ? trim($_GET["from_date"])
    : "";

$toDate = isset($_GET["to_date"])
    ? trim($_GET["to_date"])
    : "";


/* =========================================================
   DATE CONDITIONS
========================================================= */

$dateCondition = "";

if ($fromDate != "") {

    $safeFromDate = $conn->real_escape_string($fromDate);

    $dateCondition .=
        " AND date_registered >= '" .
        $safeFromDate .
        "'";
}

if ($toDate != "") {

    $safeToDate = $conn->real_escape_string($toDate);

    $dateCondition .=
        " AND date_registered <= '" .
        $safeToDate .
        "'";
}


/* =========================================================
   TOTAL PATIENTS
========================================================= */

$totalPatients = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE 1=1
    " . $dateCondition;

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $totalPatients = (int)$row["total"];
}


/* =========================================================
   ACTIVE PATIENTS
========================================================= */

$activePatients = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(status)) = 'ACTIVE'
    " . $dateCondition;

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $activePatients = (int)$row["total"];
}


/* =========================================================
   INACTIVE PATIENTS
========================================================= */

$inactivePatients = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(status)) = 'INACTIVE'
    " . $dateCondition;

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $inactivePatients = (int)$row["total"];
}


/* =========================================================
   DECEASED PATIENTS
========================================================= */

$deceasedPatients = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(status)) = 'DECEASED'
    " . $dateCondition;

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $deceasedPatients = (int)$row["total"];
}


/* =========================================================
   MALE PATIENTS
========================================================= */

$malePatients = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(sex)) = 'MALE'
    " . $dateCondition;

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $malePatients = (int)$row["total"];
}


/* =========================================================
   FEMALE PATIENTS
========================================================= */

$femalePatients = 0;

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(sex)) = 'FEMALE'
    " . $dateCondition;

$result = $conn->query($sql);

if ($result) {

    $row = $result->fetch_assoc();

    $femalePatients = (int)$row["total"];
}


/* =========================================================
   AGE GROUPS
========================================================= */

$age0to12 = 0;
$age13to17 = 0;
$age18to30 = 0;
$age31to59 = 0;
$age60plus = 0;


$sql = "
    SELECT birthdate
    FROM patients
    WHERE birthdate IS NOT NULL
    AND birthdate != ''
    " . $dateCondition;

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        if (empty($row["birthdate"])) {
            continue;
        }

        try {

            $birthDate = new DateTime($row["birthdate"]);
            $today = new DateTime();

            $age = $today->diff($birthDate)->y;

            if ($age <= 12) {

                $age0to12++;

            } elseif ($age <= 17) {

                $age13to17++;

            } elseif ($age <= 30) {

                $age18to30++;

            } elseif ($age <= 59) {

                $age31to59++;

            } else {

                $age60plus++;
            }

        } catch (Exception $e) {

            continue;
        }
    }
}


/* =========================================================
   PATIENT RECORDS
========================================================= */

$patients = array();


$sql = "
    SELECT
        id,
        patient_id,
        last_name,
        first_name,
        middle_name,
        birthdate,
        sex,
        contact_no,
        date_registered,
        status

    FROM patients

    WHERE 1=1
    " . $dateCondition . "

    ORDER BY
        date_registered DESC,
        id DESC
";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $patients[] = $row;
    }
}


/* =========================================================
   AGE FUNCTION
========================================================= */

function calculatePatientAge($birthdate)
{
    if (empty($birthdate)) {

        return "-";
    }

    try {

        $birthDate = new DateTime($birthdate);
        $today = new DateTime();

        return $today->diff($birthDate)->y;

    } catch (Exception $e) {

        return "-";
    }
}


/* =========================================================
   DATE FUNCTION
========================================================= */

function displayReportDate($date)
{
    if (empty($date)) {

        return "-";
    }

    $timestamp = strtotime($date);

    if (!$timestamp) {

        return "-";
    }

    return date("M d, Y", $timestamp);
}


/* =========================================================
   HEADER
========================================================= */

include __DIR__ . "/../includes/header.php";


/* =========================================================
   NAVIGATION
========================================================= */

include __DIR__ . "/../includes/navigation.php";

?>


<style>

/* =========================================================
   REPORT FILTER
========================================================= */

.report-filter {
    display: grid;
    grid-template-columns: 1fr 1fr auto auto;
    gap: 12px;
    align-items: end;
}

.report-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.report-field label {
    font-size: 13px;
    font-weight: 600;
    color: #555;
}

.report-field input {
    height: 40px;
    border: 1px solid #d5dbe1;
    border-radius: 5px;
    padding: 0 11px;
    font-size: 14px;
    background: #fff;
}


/* =========================================================
   SUMMARY
========================================================= */

.report-summary-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
}

.report-summary-card {
    border: 1px solid #e2e6ea;
    background: #fff;
    padding: 18px 20px;
    border-radius: 6px;
}

.report-summary-card .label {
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 8px;
}

.report-summary-card .value {
    font-size: 28px;
    font-weight: 700;
    color: #1f4e78;
}

.report-summary-card .small-text {
    margin-top: 5px;
    font-size: 12px;
    color: #777;
}


/* =========================================================
   REPORT SECTIONS
========================================================= */

.report-section-title {
    font-size: 16px;
    font-weight: 700;
    color: #333;
    margin-bottom: 5px;
}

.report-section-subtitle {
    color: #777;
    font-size: 13px;
    margin-bottom: 18px;
}

.report-two-column {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}


/* =========================================================
   STATISTICS
========================================================= */

.report-stat-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.report-stat-row {
    display: grid;
    grid-template-columns: 90px 1fr 50px;
    align-items: center;
    gap: 10px;
}

.report-stat-label {
    font-size: 13px;
    color: #555;
}

.report-stat-bar {
    height: 8px;
    background: #edf0f2;
    border-radius: 10px;
    overflow: hidden;
}

.report-stat-fill {
    height: 100%;
    background: #1f4e78;
    border-radius: 10px;
}

.report-stat-value {
    text-align: right;
    font-size: 13px;
    font-weight: 700;
    color: #333;
}

.report-table-count {
    font-size: 13px;
    color: #777;
}

.print-only {
    display: none;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 900px) {

    .report-summary-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .report-two-column {
        grid-template-columns: 1fr;
    }

    .report-filter {
        grid-template-columns: 1fr 1fr;
    }
}


@media (max-width: 600px) {

    .report-summary-grid {
        grid-template-columns: 1fr;
    }

    .report-filter {
        grid-template-columns: 1fr;
    }
}


/* =========================================================
   PRINT
========================================================= */

@media print {

    @page {
        size: landscape;
        margin: 12mm;
    }

    body {
        background: #fff !important;
    }

    .sidebar,
    .navigation,
    nav,
    .report-filter-card,
    .no-print,
    button,
    a {
        display: none !important;
    }

    .main-container {
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .card {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 15px !important;
        padding: 0 !important;
    }

    .print-only {
        display: block;
    }

    .report-summary-grid {
        grid-template-columns: repeat(4, 1fr);
    }

    .report-two-column {
        grid-template-columns: 1fr 1fr;
    }

    table {
        width: 100% !important;
        border-collapse: collapse !important;
    }

    th,
    td {
        border: 1px solid #999 !important;
        padding: 7px !important;
        font-size: 11px !important;
    }

    th {
        background: #f1f1f1 !important;
    }
}

</style>


<main class="main-container">


    <!-- =====================================================
         PAGE TITLE
    ====================================================== -->

    <div class="page-title">

        <h2>
            Patient Report
        </h2>

        <p>
            Patient registration summary and demographic report.
        </p>

    </div>


    <!-- =====================================================
         FILTER
    ====================================================== -->

    <div class="card report-filter-card">

        <div class="card-title">
            Report Period
        </div>

        <form method="GET" class="report-filter">

            <div class="report-field">

                <label for="from_date">
                    From Date
                </label>

                <input
                    type="date"
                    id="from_date"
                    name="from_date"
                    value="<?php echo htmlspecialchars($fromDate); ?>"
                >

            </div>


            <div class="report-field">

                <label for="to_date">
                    To Date
                </label>

                <input
                    type="date"
                    id="to_date"
                    name="to_date"
                    value="<?php echo htmlspecialchars($toDate); ?>"
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary"
            >
                Generate Report
            </button>


            <?php if ($fromDate != "" || $toDate != "") { ?>

                <a
                    href="patients.php"
                    class="btn btn-secondary"
                >
                    Clear
                </a>

            <?php } ?>

        </form>

    </div>


    <!-- =====================================================
         PRINT HEADER
    ====================================================== -->

    <div class="print-only">

        <h1>
            Patient Report
        </h1>

        <p>

            <?php

            if ($fromDate != "" && $toDate != "") {

                echo "Period: "
                    . displayReportDate($fromDate)
                    . " - "
                    . displayReportDate($toDate);

            } elseif ($fromDate != "") {

                echo "From: "
                    . displayReportDate($fromDate);

            } elseif ($toDate != "") {

                echo "Up to: "
                    . displayReportDate($toDate);

            } else {

                echo "All Patient Records";
            }

            ?>

        </p>

    </div>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="card">

        <div class="report-section-title">
            Patient Summary
        </div>

        <div class="report-section-subtitle">

            <?php

            if ($fromDate != "" && $toDate != "") {

                echo "Patients registered from "
                    . htmlspecialchars(displayReportDate($fromDate))
                    . " to "
                    . htmlspecialchars(displayReportDate($toDate));

            } elseif ($fromDate != "") {

                echo "Patients registered from "
                    . htmlspecialchars(displayReportDate($fromDate));

            } elseif ($toDate != "") {

                echo "Patients registered up to "
                    . htmlspecialchars(displayReportDate($toDate));

            } else {

                echo "All registered patient records";
            }

            ?>

        </div>


        <div class="report-summary-grid">


            <div class="report-summary-card">

                <div class="label">
                    Total Patients
                </div>

                <div class="value">
                    <?php echo $totalPatients; ?>
                </div>

                <div class="small-text">
                    Patient records
                </div>

            </div>


            <div class="report-summary-card">

                <div class="label">
                    Active
                </div>

                <div class="value">
                    <?php echo $activePatients; ?>
                </div>

                <div class="small-text">
                    Active patients
                </div>

            </div>


            <div class="report-summary-card">

                <div class="label">
                    Inactive
                </div>

                <div class="value">
                    <?php echo $inactivePatients; ?>
                </div>

                <div class="small-text">
                    Inactive patients
                </div>

            </div>


            <div class="report-summary-card">

                <div class="label">
                    Deceased
                </div>

                <div class="value">
                    <?php echo $deceasedPatients; ?>
                </div>

                <div class="small-text">
                    Deceased records
                </div>

            </div>


        </div>

    </div>


    <!-- =====================================================
         DEMOGRAPHICS
    ====================================================== -->

    <div class="report-two-column">


        <!-- GENDER -->

        <div class="card">

            <div class="report-section-title">
                Gender Distribution
            </div>

            <div class="report-section-subtitle">
                Patient distribution by sex
            </div>


            <div class="report-stat-list">

                <?php

                $malePercent = 0;

                if ($totalPatients > 0) {

                    $malePercent =
                        ($malePatients / $totalPatients) * 100;
                }

                ?>


                <div class="report-stat-row">

                    <div class="report-stat-label">
                        Male
                    </div>

                    <div class="report-stat-bar">

                        <div
                            class="report-stat-fill"
                            style="width: <?php echo $malePercent; ?>%;"
                        ></div>

                    </div>

                    <div class="report-stat-value">
                        <?php echo $malePatients; ?>
                    </div>

                </div>


                <?php

                $femalePercent = 0;

                if ($totalPatients > 0) {

                    $femalePercent =
                        ($femalePatients / $totalPatients) * 100;
                }

                ?>


                <div class="report-stat-row">

                    <div class="report-stat-label">
                        Female
                    </div>

                    <div class="report-stat-bar">

                        <div
                            class="report-stat-fill"
                            style="width: <?php echo $femalePercent; ?>%;"
                        ></div>

                    </div>

                    <div class="report-stat-value">
                        <?php echo $femalePatients; ?>
                    </div>

                </div>


            </div>

        </div>


        <!-- AGE -->

        <div class="card">

            <div class="report-section-title">
                Age Distribution
            </div>

            <div class="report-section-subtitle">
                Patient distribution by age group
            </div>


            <div class="report-stat-list">


                <?php

                $ageGroups = array(
                    "0-12" => $age0to12,
                    "13-17" => $age13to17,
                    "18-30" => $age18to30,
                    "31-59" => $age31to59,
                    "60+" => $age60plus
                );


                foreach ($ageGroups as $label => $count) {

                    $percentage = 0;

                    if ($totalPatients > 0) {

                        $percentage =
                            ($count / $totalPatients) * 100;
                    }

                ?>


                    <div class="report-stat-row">

                        <div class="report-stat-label">
                            <?php echo $label; ?>
                        </div>

                        <div class="report-stat-bar">

                            <div
                                class="report-stat-fill"
                                style="width: <?php echo $percentage; ?>%;"
                            ></div>

                        </div>

                        <div class="report-stat-value">
                            <?php echo $count; ?>
                        </div>

                    </div>


                <?php

                }

                ?>


            </div>

        </div>


    </div>


    <!-- =====================================================
         PATIENT RECORDS
    ====================================================== -->

    <div class="card">


        <div class="section-header">

            <div>

                <div class="report-section-title">
                    Patient Records
                </div>

                <div class="report-table-count">

                    <?php echo number_format(count($patients)); ?>

                    patient record(s)

                </div>

            </div>


            <button
                type="button"
                class="btn btn-primary no-print"
                onclick="window.print()"
            >
                Print Report
            </button>

        </div>


        <div class="table-container">

            <table>

                <thead>

                    <tr>

                        <th>Patient ID</th>

                        <th>Patient Name</th>

                        <th>Age</th>

                        <th>Sex</th>

                        <th>Contact</th>

                        <th>Date Registered</th>

                        <th>Status</th>

                    </tr>

                </thead>


                <tbody>


                <?php if (count($patients) > 0) { ?>


                    <?php foreach ($patients as $patient) { ?>


                        <?php

                        $age = calculatePatientAge(
                            $patient["birthdate"]
                        );


                        $fullName =
                            $patient["last_name"]
                            . ", "
                            . $patient["first_name"];


                        if (!empty($patient["middle_name"])) {

                            $fullName .=
                                " "
                                . $patient["middle_name"];
                        }


                        $patientStatus =
                            !empty($patient["status"])
                                ? $patient["status"]
                                : "Active";


                        $statusUpper =
                            strtoupper(
                                trim($patientStatus)
                            );


                        if ($statusUpper == "ACTIVE") {

                            $statusClass = "status-active";

                        } elseif ($statusUpper == "INACTIVE") {

                            $statusClass = "status-inactive";

                        } elseif ($statusUpper == "DECEASED") {

                            $statusClass = "status-deceased";

                        } else {

                            $statusClass = "status-inactive";
                        }

                        ?>


                        <tr>


                            <td>

                                <strong style="color:#1f4e78;">

                                    <?php

                                    echo htmlspecialchars(
                                        $patient["patient_id"]
                                    );

                                    ?>

                                </strong>

                            </td>


                            <td>

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $fullName
                                    );

                                    ?>

                                </strong>

                            </td>


                            <td>

                                <?php echo $age; ?>

                            </td>


                            <td>

                                <?php

                                echo htmlspecialchars(
                                    $patient["sex"]
                                );

                                ?>

                            </td>


                            <td>

                                <?php

                                if (!empty($patient["contact_no"])) {

                                    echo htmlspecialchars(
                                        $patient["contact_no"]
                                    );

                                } else {

                                    echo "-";
                                }

                                ?>

                            </td>


                            <td>

                                <?php

                                echo displayReportDate(
                                    $patient["date_registered"]
                                );

                                ?>

                            </td>


                            <td>

                                <span
                                    class="status <?php echo $statusClass; ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $patientStatus
                                    );

                                    ?>

                                </span>

                            </td>


                        </tr>


                    <?php } ?>


                <?php } else { ?>


                    <tr>

                        <td
                            colspan="7"
                            style="
                                text-align:center;
                                padding:45px;
                                color:#777;
                            "
                        >

                            No patient records found
                            for the selected period.

                        </td>

                    </tr>


                <?php } ?>


                </tbody>

            </table>

        </div>

    </div>


</main>


<?php

/* =========================================================
   FOOTER
========================================================= */

include __DIR__ . "/../includes/footer.php";

$conn->close();

?>