<?php

require_once "../config/database.php";


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Consultations";
$pageSubtitle = "Consultation Records";
$basePath = "../";
$activePage = "consultations";


/* =========================================================
   SEARCH
========================================================= */

$search = isset($_GET['search'])
    ? trim($_GET['search'])
    : "";


/* =========================================================
   GET ALL CONSULTATION RECORDS
========================================================= */

if ($search !== "") {

    $searchValue = "%" . $search . "%";

    $sql = "
        SELECT
            c.id,
            c.patient_id,
            c.visit_date,
            c.chief_complaint,
            c.assessment,
            c.follow_up_date,
            c.follow_up_status,

            p.patient_id AS patient_number,
            p.first_name,
            p.middle_name,
            p.last_name

        FROM consultations c

        INNER JOIN patients p
            ON c.patient_id = p.id

        WHERE
            p.patient_id LIKE ?
            OR p.first_name LIKE ?
            OR p.middle_name LIKE ?
            OR p.last_name LIKE ?
            OR c.chief_complaint LIKE ?
            OR c.assessment LIKE ?

        ORDER BY
            c.visit_date DESC,
            c.id DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }

    $stmt->bind_param(
        "ssssss",
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue,
        $searchValue
    );

} else {

    $sql = "
        SELECT
            c.id,
            c.patient_id,
            c.visit_date,
            c.chief_complaint,
            c.assessment,
            c.follow_up_date,
            c.follow_up_status,

            p.patient_id AS patient_number,
            p.first_name,
            p.middle_name,
            p.last_name

        FROM consultations c

        INNER JOIN patients p
            ON c.patient_id = p.id

        ORDER BY
            c.visit_date DESC,
            c.id DESC
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
}


$stmt->execute();

$result = $stmt->get_result();

$totalConsultations = $result->num_rows;


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

<main class="main-container consultations-page">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="section-header consultations-page-header">

        <div>

            <h2 class="page-title">
                Consultation Records
            </h2>

            <p class="page-description">
                View and manage all consultation records.
            </p>

        </div>

    </div>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="card consultations-summary-card">

        <div class="consultations-summary-content">

            <div>

                <div class="summary-label">
                    Total Consultation Records
                </div>

                <div class="summary-number">
                    <?php echo $totalConsultations; ?>
                </div>

            </div>


            <div class="consultations-summary-label">

                <?php

                if ($search !== "") {
                    echo "Search results";
                } else {
                    echo "All records";
                }

                ?>

            </div>

        </div>

    </div>


    <!-- =====================================================
         SEARCH
    ====================================================== -->

    <div class="card consultations-search-card">

        <form
            method="GET"
            action="consultations.php"
            class="consultations-search-form"
        >

            <input
                type="text"
                name="search"
                value="<?php echo htmlspecialchars($search); ?>"
                placeholder="Search Patient ID, name, complaint, or assessment..."
                autocomplete="off"
            >


            <button
                type="submit"
                class="btn btn-primary"
            >
                Search
            </button>


            <?php if ($search !== "") { ?>

                <a
                    href="consultations.php"
                    class="btn btn-secondary"
                >
                    Clear
                </a>

            <?php } ?>

        </form>

    </div>


    <!-- =====================================================
         CONSULTATION TABLE
    ====================================================== -->

    <div class="card consultations-table-card">


        <?php if ($totalConsultations > 0) { ?>


            <div class="table-container consultations-table-container">

                <table class="consultations-table">

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

                        </tr>

                    </thead>


                    <tbody>


                    <?php while ($row = $result->fetch_assoc()) { ?>


                        <?php

                        /* =====================================
                           FULL NAME
                        ====================================== */

                        $fullName = $row['first_name'];


                        if (!empty($row['middle_name'])) {

                            $fullName .=
                                " " .
                                $row['middle_name'];

                        }


                        $fullName .=
                            " " .
                            $row['last_name'];


                        /* =====================================
                           ACTUAL FOLLOW-UP STATUS
                        ====================================== */

                        $followUpStatus = strtoupper(
                            trim(
                                isset($row['follow_up_status'])
                                    ? $row['follow_up_status']
                                    : ""
                            )
                        );


                        /*
                         * Kapag walang value sa database,
                         * default sa PENDING.
                         */

                        if ($followUpStatus == "") {

                            $followUpStatus = "PENDING";

                        }


                        /*
                         * Default status.
                         */

                        $followUpClass = "status-pending";


                        if ($followUpStatus == "CONFIRMED") {

                            $followUpClass = "status-confirmed";

                        } elseif ($followUpStatus == "CANCELLED") {

                            $followUpClass = "status-cancelled";

                        } elseif ($followUpStatus == "COMPLETED") {

                            $followUpClass = "status-completed";

                        } elseif ($followUpStatus == "NO SHOW") {

                            $followUpClass = "status-no-show";

                        } elseif ($followUpStatus == "PENDING") {

                            $followUpClass = "status-pending";

                        } elseif ($followUpStatus == "ACTIVE") {

                            /*
                             * Compatibility para sa mga
                             * lumang records.
                             */

                            $followUpClass = "status-pending";

                        }


                        /* =====================================
                           MONITORING
                        ====================================== */

                        $monitoring = "—";

                        $monitoringClass = "status-active";


                        /*
                         * Walang follow-up date.
                         */

                        if (empty($row['follow_up_date'])) {

                            $monitoring = "NO FOLLOW-UP";

                            $monitoringClass = "status-active";

                        }


                        /*
                         * PENDING lamang ang mino-monitor
                         * base sa date.
                         */

                        elseif (
                            $followUpStatus == "PENDING" ||
                            $followUpStatus == "ACTIVE"
                        ) {

                            $todayDate = strtotime(
                                date("Y-m-d")
                            );


                            $followUpDate = strtotime(
                                $row['follow_up_date']
                            );


                            if ($followUpDate < $todayDate) {

                                $monitoring = "OVERDUE";

                                $monitoringClass = "status-overdue";

                            } elseif (
                                $followUpDate <=
                                strtotime("+7 days")
                            ) {

                                $monitoring = "DUE SOON";

                                $monitoringClass = "status-due";

                            } else {

                                $monitoring = "SCHEDULED";

                                $monitoringClass = "status-scheduled";

                            }

                        }


                        /*
                         * CONFIRMED
                         */

                        elseif ($followUpStatus == "CONFIRMED") {

                            $monitoring = "CONFIRMED";

                            $monitoringClass = "status-confirmed";

                        }


                        /*
                         * CANCELLED
                         */

                        elseif ($followUpStatus == "CANCELLED") {

                            $monitoring = "CANCELLED";

                            $monitoringClass = "status-cancelled";

                        }


                        /*
                         * COMPLETED
                         */

                        elseif ($followUpStatus == "COMPLETED") {

                            $monitoring = "COMPLETED";

                            $monitoringClass = "status-completed";

                        }


                        /*
                         * NO SHOW
                         */

                        elseif ($followUpStatus == "NO SHOW") {

                            $monitoring = "NO SHOW";

                            $monitoringClass = "status-no-show";

                        }

                        ?>


                        <tr>


                            <!-- PATIENT ID -->

                            <td>

                                <span class="patient-id">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['patient_number']
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- PATIENT NAME -->

                            <td>

                                <span class="patient-name">

                                    <?php

                                    echo htmlspecialchars(
                                        $fullName
                                    );

                                    ?>

                                </span>

                            </td>


                            <!-- VISIT DATE -->

                            <td>

                                <span class="consultation-record-date">

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


                            <!-- CHIEF COMPLAINT -->

                            <td class="consultation-record-text">

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


                            <!-- ASSESSMENT -->

                            <td class="consultation-record-text">

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


                            <!-- FOLLOW-UP DATE -->

                            <td>

                                <?php

                                if (
                                    !empty(
                                        $row['follow_up_date']
                                    )
                                ) {

                                    echo date(
                                        "M d, Y",
                                        strtotime(
                                            $row['follow_up_date']
                                        )
                                    );

                                } else {

                                    echo "—";

                                }

                                ?>

                            </td>


                            <!-- FOLLOW-UP STATUS -->

                            <td>

                                <span
                                    class="status <?php echo $followUpClass; ?>"
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
                                    class="status <?php echo $monitoringClass; ?>"
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

                                <a
                                    href="consultation_view.php?id=<?php echo urlencode($row['id']); ?>"
                                    class="btn btn-primary consultation-record-view-btn"
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
                 EMPTY STATE
            ================================================== -->

            <div class="empty-state consultations-empty-state">

                <div class="empty-icon">
                    ✓
                </div>


                <div class="empty-title">

                    <?php

                    if ($search !== "") {

                        echo "No Consultation Records Found";

                    } else {

                        echo "No Consultation Records";

                    }

                    ?>

                </div>


                <div class="empty-description">

                    <?php

                    if ($search !== "") {

                        echo
                            "No consultation records matched your search.";

                    } else {

                        echo
                            "There are currently no consultation records.";

                    }

                    ?>

                </div>

            </div>


        <?php } ?>


    </div>


</main>


<!-- =========================================================
     PAGE-SPECIFIC STYLES
========================================================= -->

<style>

    .consultations-page {
        max-width: 1400px;
    }


    .consultations-page-header {
        margin-bottom: 18px;
    }


    /* =====================================================
       SUMMARY
    ====================================================== */

    .consultations-summary-card {
        margin-bottom: 20px;
        padding: 18px 22px;
    }


    .consultations-summary-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 20px;
    }


    .consultations-summary-label {
        color: #777;
        font-size: 12px;
        font-weight: 600;
        background: #f4f6f9;
        border: 1px solid #e3e7eb;
        border-radius: 5px;
        padding: 7px 11px;
        white-space: nowrap;
    }


    /* =====================================================
       SEARCH
    ====================================================== */

    .consultations-search-card {
        margin-bottom: 20px;
        padding: 18px 22px;
    }


    .consultations-search-form {
        display: flex;
        align-items: center;
        gap: 10px;
        width: 100%;
    }


    .consultations-search-form input[type="text"] {

        flex: 1;

        min-width: 0;

        height: 40px;

        padding: 0 13px;

        border: 1px solid #ced4da;

        border-radius: 5px;

        background: #ffffff;

        color: #333;

        font-family:
            Arial,
            Helvetica,
            sans-serif;

        font-size: 14px;

    }


    .consultations-search-form input[type="text"]:focus {

        outline: none;

        border-color: #1f4e78;

        box-shadow:
            0 0 0 2px
            rgba(31, 78, 120, 0.08);

    }


    .consultations-search-form .btn {

        height: 40px;

        min-height: 40px;

        flex-shrink: 0;

    }


    /* =====================================================
       TABLE
    ====================================================== */

    .consultations-table-card {

        padding: 0;

        overflow: hidden;

    }


    .consultations-table-container {
        width: 100%;
        overflow-x: hidden;
    }

    .consultations-table {
        width: 100%;
        border-collapse: collapse;
    }


    .consultations-table th {

        background: #f4f6f9;

        color: #1f4e78;

        text-align: left;

        padding: 13px 12px;

        font-size: 12px;

        font-weight: 700;

        border-bottom:
            2px solid #dfe3e7;

        white-space: nowrap;

    }


    .consultations-table td {

        padding: 13px 12px;

        font-size: 13px;

        border-bottom:
            1px solid #eee;

        vertical-align: middle;

    }


    .consultations-table tbody tr:hover {

        background: #fafafa;

    }


    .consultations-table tbody tr:last-child td {

        border-bottom: none;

    }


    .consultation-record-date {

        color: #555;

        font-weight: 600;

        white-space: nowrap;

    }


    .consultation-record-text {

        max-width: 260px;

        line-height: 1.45;

        word-break: break-word;

    }


    .consultation-record-view-btn {

        min-height: 36px;

        padding: 8px 14px;

        font-size: 12px;

    }


    /* =====================================================
       FOLLOW-UP STATUS
    ====================================================== */

    .consultations-table td .status {

        display: inline-block;

        margin-top: 0;

        font-size: 10px;

        padding: 4px 8px;

        white-space: nowrap;

    }


    /* =====================================================
       STATUS COLORS
    ====================================================== */

    .status-pending {

        background: #fff3cd;

        color: #856404;

    }


    .status-confirmed {

        background: #cfe2ff;

        color: #084298;

    }


    .status-cancelled {

        background: #f8d7da;

        color: #842029;

    }


    .status-completed {

        background: #d1e7dd;

        color: #0f5132;

    }


    .status-no-show {

        background: #e2e3e5;

        color: #41464b;

    }


    .status-overdue {

        background: #f8d7da;

        color: #842029;

    }


    .status-due {

        background: #fff3cd;

        color: #856404;

    }


    .status-scheduled {

        background: #cfe2ff;

        color: #084298;

    }


    .status-active {

        background: #d1e7dd;

        color: #0f5132;

    }


    /* =====================================================
       EMPTY STATE
    ====================================================== */

    .consultations-empty-state {

        padding: 55px 20px;

    }


    /* =====================================================
       RESPONSIVE
    ====================================================== */

    @media (max-width: 700px) {


        .consultations-summary-content {

            align-items: flex-start;

            flex-direction: column;

        }


        .consultations-summary-label {

            width: 100%;

            text-align: center;

        }


        .consultations-search-form {

            flex-direction: column;

            align-items: stretch;

        }


        .consultations-search-form input[type="text"] {

            width: 100%;

        }


        .consultations-search-form .btn {

            width: 100%;

        }


        .consultations-table-card {

            border-radius: 8px;

        }

    }

</style>


<?php

/* =========================================================
   SHARED FOOTER
========================================================= */

include __DIR__ . "/../includes/footer.php";


/* =========================================================
   CLOSE DATABASE
========================================================= */

$stmt->close();

$conn->close();

?>