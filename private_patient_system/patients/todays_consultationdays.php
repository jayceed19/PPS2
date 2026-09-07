<?php

require_once "../config/database.php";


// =========================================================
// PAGE SETTINGS
// =========================================================

$pageTitle = "Today's Consultations";
$pageSubtitle = "Consultation Monitoring";
$basePath = "../";
$activePage = "consultations";


// =========================================================
// TODAY'S DATE
// =========================================================

$today = date("Y-m-d");


// =========================================================
// GET TODAY'S CONSULTATIONS
// =========================================================

$sql = "
    SELECT
        c.id,
        c.patient_id,
        c.visit_date,
        c.chief_complaint,
        c.assessment,

        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name

    FROM consultations c

    INNER JOIN patients p
        ON c.patient_id = p.id

    WHERE c.visit_date = ?

    ORDER BY c.id DESC
";


$stmt = $conn->prepare($sql);

if (!$stmt) {

    die("Database error: " . $conn->error);

}


$stmt->bind_param(
    "s",
    $today
);


$stmt->execute();


$result = $stmt->get_result();


$totalToday = $result->num_rows;


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

<main class="main-container consultation-days-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="section-header consultation-days-header">

        <div>

            <h2 class="page-title">
                Today's Consultations
            </h2>

            <p class="page-description">

                Consultation records created today —
                <?php echo date("F d, Y"); ?>

            </p>

        </div>


        <a
            href="../dashboard.php"
            class="btn btn-secondary"
        >
            ← Back to Dashboard
        </a>

    </div>


    <!-- =====================================================
         SUMMARY CARD
    ====================================================== -->

    <div class="card consultation-count-card">

        <div class="consultation-count-content">

            <div>

                <div class="summary-label">
                    Consultations Today
                </div>

                <div class="summary-number">
                    <?php echo $totalToday; ?>
                </div>

            </div>


            <div class="consultation-count-label">
                <?php echo date("M d, Y"); ?>
            </div>

        </div>

    </div>


    <!-- =====================================================
         CONSULTATION TABLE
    ====================================================== -->

    <div class="card consultation-days-table-card">


        <?php if ($totalToday > 0) { ?>


            <div class="table-container consultation-days-table-container">


                <table class="consultation-days-table">


                    <thead>

                        <tr>

                            <th>
                                Patient ID
                            </th>

                            <th>
                                Patient Name
                            </th>

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
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($row = $result->fetch_assoc()) { ?>


                        <?php

                        // =====================================
                        // FULL NAME
                        // =====================================

                        $fullName =
                            $row['first_name'];


                        if (!empty($row['middle_name'])) {

                            $fullName .=
                                " " .
                                $row['middle_name'];

                        }


                        $fullName .=
                            " " .
                            $row['last_name'];

                        ?>


                        <tr>


                            <!-- =================================
                                 PATIENT ID
                            ================================== -->

                            <td>

                                <span class="patient-id">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['patient_number']
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- =================================
                                 PATIENT NAME
                            ================================== -->

                            <td>

                                <span class="patient-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $fullName
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- =================================
                                 VISIT DATE
                            ================================== -->

                            <td>

                                <span class="consultation-date">

                                    <?php

                                    echo date(
                                        "M d, Y",
                                        strtotime(
                                            $row['visit_date']
                                        )
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- =================================
                                 CHIEF COMPLAINT
                            ================================== -->

                            <td class="consultation-text-cell">

                                <?php

                                if (
                                    !empty(
                                        $row['chief_complaint']
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        $row['chief_complaint']
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- =================================
                                 ASSESSMENT
                            ================================== -->

                            <td class="consultation-text-cell">

                                <?php

                                if (
                                    !empty(
                                        $row['assessment']
                                    )
                                ) {

                                    echo htmlspecialchars(
                                        $row['assessment']
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- =================================
                                 ACTION
                            ================================== -->

                            <td>

                                <a
                                    href="consultation_view.php?id=<?php echo urlencode($row['id']); ?>"
                                    class="btn btn-primary consultation-view-btn"
                                >
                                    View Consultation
                                </a>

                            </td>


                        </tr>


                    <?php } ?>


                    </tbody>


                </table>


            </div>


        <?php } else { ?>


            <!-- =================================================
                 EMPTY STATE
            ================================================== -->

            <div class="empty-state consultation-empty-state">

                <div class="empty-icon">
                    ✓
                </div>

                <div class="empty-title">
                    No Consultations Today
                </div>

                <div class="empty-description">
                    There are no consultation records for today.
                </div>

            </div>


        <?php } ?>


    </div>


</main>


<!-- =========================================================
     PAGE-SPECIFIC STYLES
========================================================= -->

<style>

    .consultation-days-page {
        max-width: 1400px;
    }


    .consultation-days-header {
        margin-bottom: 18px;
    }


    .consultation-count-card {
        margin-bottom: 20px;
        padding: 18px 22px;
    }


    .consultation-count-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }


    .consultation-count-label {
        color: #777;
        font-size: 12px;
        font-weight: 600;
        background: #f4f6f9;
        border: 1px solid #e3e7eb;
        border-radius: 5px;
        padding: 7px 11px;
        white-space: nowrap;
    }


    .consultation-days-table-card {
        padding: 0;
        overflow: hidden;
    }


    .consultation-days-table-container {
        width: 100%;
        overflow-x: auto;
    }


    .consultation-days-table {
        width: 100%;
        min-width: 950px;
        border-collapse: collapse;
    }


    .consultation-days-table th {
        background: #f4f6f9;
        color: #1f4e78;
        text-align: left;
        padding: 13px 12px;
        font-size: 12px;
        font-weight: 700;
        border-bottom: 2px solid #dfe3e7;
        white-space: nowrap;
    }


    .consultation-days-table td {
        padding: 13px 12px;
        font-size: 13px;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }


    .consultation-days-table tbody tr:hover {
        background: #fafafa;
    }


    .consultation-days-table tbody tr:last-child td {
        border-bottom: none;
    }


    .consultation-date {
        font-weight: 600;
        color: #555;
        white-space: nowrap;
    }


    .consultation-text-cell {
        max-width: 280px;
        line-height: 1.45;
    }


    .consultation-view-btn {
        min-height: 36px;
        padding: 8px 13px;
        font-size: 12px;
    }


    .consultation-empty-state {
        padding: 55px 20px;
    }


    @media (max-width: 700px) {

        .consultation-count-content {
            align-items: flex-start;
            flex-direction: column;
        }


        .consultation-count-label {
            width: 100%;
            text-align: center;
        }


        .consultation-days-table-card {
            border-radius: 8px;
        }

    }

</style>


<?php

// =========================================================
// SHARED FOOTER
// =========================================================

include __DIR__ . "/../includes/footer.php";


// =========================================================
// CLOSE DATABASE
// =========================================================

$stmt->close();

$conn->close();

?>