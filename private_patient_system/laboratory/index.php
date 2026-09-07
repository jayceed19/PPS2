<?php
require_once "../config/database.php";

/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Laboratory";
$pageSubtitle = "Laboratory History";
$basePath = "../";
$activePage = "patients";


/* =========================================================
   GET PATIENT ID
========================================================= */

$patientId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($patientId <= 0) {
    die("Invalid patient ID.");
}


/* =========================================================
   GET PATIENT
========================================================= */

$patientSql = "
    SELECT *
    FROM patients
    WHERE id = ?
";

$patientStmt = $conn->prepare($patientSql);

if (!$patientStmt) {
    die("Database error: " . $conn->error);
}

$patientStmt->bind_param("i", $patientId);
$patientStmt->execute();

$patientResult = $patientStmt->get_result();

if ($patientResult->num_rows == 0) {
    $patientStmt->close();
    $conn->close();

    die("Patient not found.");
}

$patient = $patientResult->fetch_assoc();

$patientStmt->close();


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
   GET LABORATORY HISTORY
========================================================= */

$labSql = "
    SELECT
        test_date,
        COUNT(*) AS test_count
    FROM laboratory
    WHERE patient_id = ?
    GROUP BY test_date
    ORDER BY test_date DESC
";

$labStmt = $conn->prepare($labSql);

if (!$labStmt) {
    die("Database error: " . $conn->error);
}

$labStmt->bind_param("i", $patientId);
$labStmt->execute();

$labDates = $labStmt->get_result();


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
   LABORATORY PAGE
========================================================= */

.laboratory-page {
    max-width: 1200px;
    margin: 0 auto;
}


/* =========================================================
   TOP ACTION BAR
========================================================= */

.lab-top-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 12px;

    margin-bottom: 20px;
}

.lab-top-left,
.lab-top-right {
    display: flex;
    align-items: center;

    gap: 10px;

    flex-wrap: wrap;
}


/* =========================================================
   BACK BUTTON
========================================================= */

.lab-back-btn {
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

.lab-back-btn:hover {
    background: #f4f7fa;
    border-color: #1f4e78;
}


/* =========================================================
   ADD LAB BUTTON
========================================================= */

.lab-add-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    height: 40px;
    min-height: 40px;

    padding: 0 17px;

    background: #1f4e78;
    color: #ffffff;

    border: 1px solid #1f4e78;
    border-radius: 6px;

    text-decoration: none;

    font-size: 13px;
    font-weight: 600;

    transition: 0.2s ease;
}

.lab-add-btn:hover {
    background: #173a5c;
    border-color: #173a5c;
}


/* =========================================================
   PATIENT CARD
========================================================= */

.lab-patient-card {
    background: #ffffff;

    border: 1px solid #e1e5e9;
    border-radius: 8px;

    padding: 22px;

    margin-bottom: 20px;
}

.lab-patient-content {
    display: flex;

    justify-content: space-between;
    align-items: center;

    gap: 20px;
}

.lab-patient-name {
    margin: 0 0 5px 0;

    color: #1f4e78;

    font-size: 24px;
    font-weight: 700;
}

.lab-patient-id {
    color: #777;

    font-size: 13px;
}

.lab-patient-id strong {
    color: #1f4e78;
}

.lab-patient-age-sex {
    color: #555;

    font-size: 13px;

    margin-top: 4px;
}


/* =========================================================
   MAIN CARD
========================================================= */

.lab-card {
    background: #ffffff;

    border: 1px solid #e1e5e9;
    border-radius: 8px;

    overflow: hidden;

    margin-bottom: 20px;
}

.lab-card-header {
    display: flex;

    justify-content: space-between;
    align-items: center;

    gap: 15px;

    padding: 17px 22px;

    border-bottom: 1px solid #e5e8eb;

    background: #ffffff;
}

.lab-card-title {
    margin: 0;

    color: #1f4e78;

    font-size: 17px;
    font-weight: 700;
}

.lab-card-count {
    color: #777;

    font-size: 12px;
}


/* =========================================================
   LAB DATE GROUP
========================================================= */

.lab-date-group {
    border-bottom: 1px solid #edf0f2;
}

.lab-date-group:last-child {
    border-bottom: none;
}


/* =========================================================
   DATE HEADER
========================================================= */

.lab-date-header {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 15px;

    padding: 15px 22px;

    background: #f8fafc;

    border-bottom: 1px solid #e5e8eb;
}

.lab-date-left {
    display: flex;

    flex-direction: column;

    gap: 4px;
}

.lab-date {
    color: #1f4e78;

    font-size: 15px;
    font-weight: 700;
}

.lab-date-count {
    color: #777;

    font-size: 11px;
}


/* =========================================================
   VIEW RESULTS BUTTON
========================================================= */

.lab-view-btn {
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

.lab-view-btn:hover {
    background: #173a5c;
    border-color: #173a5c;
}


/* =========================================================
   NO LABORATORY
========================================================= */

.no-laboratory {
    text-align: center;

    padding: 55px 20px;

    color: #777;

    font-size: 14px;
}

.no-laboratory-title {
    color: #555;

    font-size: 16px;
    font-weight: 600;

    margin-bottom: 7px;
}

.no-laboratory-text {
    margin-bottom: 18px;

    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 700px) {

    .lab-top-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .lab-top-left,
    .lab-top-right {
        width: 100%;

        flex-direction: column;
        align-items: stretch;
    }

    .lab-back-btn,
    .lab-add-btn {
        width: 100%;
    }

    .lab-patient-content {
        flex-direction: column;
        align-items: flex-start;
    }

    .lab-patient-card {
        padding: 18px;
    }

    .lab-patient-name {
        font-size: 21px;
    }

    .lab-card-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .lab-date-header {
        align-items: flex-start;

        flex-direction: column;
    }

    .lab-view-btn {
        width: 100%;
    }

}

</style>


<main class="main-container">

    <div class="laboratory-page">


        <!-- =====================================================
             TOP ACTION BAR
        ====================================================== -->

        <div class="lab-top-actions">


            <!-- LEFT -->

            <div class="lab-top-left">

                <a
                    href="../patients/view.php?id=<?php echo $patientId; ?>"
                    class="lab-back-btn"
                >
                    ← Back to Patient Profile
                </a>

            </div>


            <!-- RIGHT -->

            <div class="lab-top-right">

                <a
                    href="add.php?id=<?php echo $patientId; ?>"
                    class="lab-add-btn"
                >
                    + Add Laboratory
                </a>

            </div>


        </div>


        <!-- =====================================================
             PATIENT INFORMATION
        ====================================================== -->

        <div class="lab-patient-card">

            <div class="lab-patient-content">


                <div>

                    <h1 class="lab-patient-name">

                        <?php
                        echo htmlspecialchars($fullName);
                        ?>

                    </h1>


                    <div class="lab-patient-id">

                        Patient ID:

                        <strong>

                            <?php
                            echo htmlspecialchars(
                                $patient['patient_id']
                            );
                            ?>

                        </strong>

                    </div>


                    <div class="lab-patient-age-sex">

                        Age:

                        <?php
                        echo htmlspecialchars($age);
                        ?>

                        <?php if ($age !== "-") { ?>
                            years old
                        <?php } ?>

                        &nbsp;&nbsp;|&nbsp;&nbsp;

                        Sex:

                        <?php
                        echo !empty($patient['sex'])
                            ? htmlspecialchars(
                                $patient['sex']
                            )
                            : "-";
                        ?>

                    </div>

                </div>

            </div>

        </div>


        <!-- =====================================================
             LABORATORY HISTORY
        ====================================================== -->

        <div class="lab-card">


            <div class="lab-card-header">

                <h2 class="lab-card-title">
                    Laboratory History
                </h2>


                <div class="lab-card-count">

                    <?php
                    echo $labDates->num_rows;
                    ?>

                    laboratory date(s)

                </div>

            </div>


            <?php if ($labDates->num_rows > 0) { ?>


                <?php while (
                    $labDate =
                    $labDates->fetch_assoc()
                ) { ?>


                    <div class="lab-date-group">


                        <div class="lab-date-header">


                            <div class="lab-date-left">

                                <div class="lab-date">

                                    <?php

                                    echo htmlspecialchars(
                                        date(
                                            "F d, Y",
                                            strtotime(
                                                $labDate[
                                                    'test_date'
                                                ]
                                            )
                                        )
                                    );

                                    ?>

                                </div>


                                <div class="lab-date-count">

                                    <?php
                                    echo (int)
                                        $labDate[
                                            'test_count'
                                        ];
                                    ?>

                                    laboratory test(s)

                                </div>

                            </div>


                            <a
                                href="view.php?id=<?php
                                    echo $patientId;
                                ?>&date=<?php
                                    echo urlencode(
                                        $labDate[
                                            'test_date'
                                        ]
                                    );
                                ?>"
                                class="lab-view-btn"
                            >
                                View Results
                            </a>


                        </div>


                    </div>


                <?php } ?>


            <?php } else { ?>


                <!-- =================================================
                     NO LABORATORY RECORD
                ================================================== -->

                <div class="no-laboratory">

                    <div class="no-laboratory-title">

                        No laboratory records yet.

                    </div>


                    <div class="no-laboratory-text">

                        Add the patient's first laboratory result.

                    </div>


                    <a
                        href="add.php?id=<?php
                            echo $patientId;
                        ?>"
                        class="lab-add-btn"
                    >
                        + Add First Laboratory

                    </a>

                </div>


            <?php } ?>


        </div>


    </div>

</main>


<?php

$labStmt->close();

$conn->close();

?>


<?php include __DIR__ . "/../includes/footer.php"; ?>