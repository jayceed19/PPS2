<?php

require_once __DIR__ . "/config/database.php";


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Dashboard";
$pageSubtitle = "Dashboard";

$basePath = "";
$activePage = "dashboard";


/* =========================================================
   PATIENT COUNTS
========================================================= */

$sql = "SELECT COUNT(*) AS total FROM patients";

$result = $conn->query($sql);

$totalPatients = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $totalPatients = (int)$row['total'];

}


/* =========================================================
   ACTIVE PATIENTS
========================================================= */

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(status)) = 'ACTIVE'
";

$result = $conn->query($sql);

$activePatients = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $activePatients = (int)$row['total'];

}


/* =========================================================
   INACTIVE PATIENTS
========================================================= */

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(status)) = 'INACTIVE'
";

$result = $conn->query($sql);

$inactivePatients = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $inactivePatients = (int)$row['total'];

}


/* =========================================================
   DECEASED PATIENTS
========================================================= */

$sql = "
    SELECT COUNT(*) AS total
    FROM patients
    WHERE UPPER(TRIM(status)) = 'DECEASED'
";

$result = $conn->query($sql);

$deceasedPatients = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $deceasedPatients = (int)$row['total'];

}


/* =========================================================
   CONSULTATIONS TODAY
========================================================= */

$today = date('Y-m-d');

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations
    WHERE visit_date = ?
";

$stmt = $conn->prepare($sql);

$consultationsToday = 0;

if ($stmt) {

    $stmt->bind_param("s", $today);

    $stmt->execute();

    $result = $stmt->get_result();

    if ($result) {

        $row = $result->fetch_assoc();

        $consultationsToday = (int)$row['total'];

    }

    $stmt->close();

}


/* =========================================================
   FOLLOW-UP - OVERDUE
========================================================= */

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c
    INNER JOIN patients p
        ON p.id = c.patient_id

    WHERE c.follow_up_date IS NOT NULL

    AND c.follow_up_date < CURDATE()

    AND (
        UPPER(TRIM(c.follow_up_status)) = 'PENDING'
        OR UPPER(TRIM(c.follow_up_status)) = 'ACTIVE'
    )

    AND UPPER(TRIM(p.status)) != 'DECEASED'
";

$result = $conn->query($sql);

$overdue = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $overdue = (int)$row['total'];

}


/* =========================================================
   FOLLOW-UP - DUE SOON
========================================================= */

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c
    INNER JOIN patients p
        ON p.id = c.patient_id

    WHERE c.follow_up_date IS NOT NULL

    AND c.follow_up_date >= CURDATE()

    AND c.follow_up_date <= DATE_ADD(
        CURDATE(),
        INTERVAL 7 DAY
    )

    AND (
        UPPER(TRIM(c.follow_up_status)) = 'PENDING'
        OR UPPER(TRIM(c.follow_up_status)) = 'ACTIVE'
    )

    AND UPPER(TRIM(p.status)) != 'DECEASED'
";

$result = $conn->query($sql);

$dueSoon = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $dueSoon = (int)$row['total'];

}


/* =========================================================
   FOLLOW-UP - SCHEDULED
========================================================= */

$sql = "
    SELECT COUNT(*) AS total
    FROM consultations c
    INNER JOIN patients p
        ON p.id = c.patient_id

    WHERE c.follow_up_date IS NOT NULL

    AND c.follow_up_date > DATE_ADD(
        CURDATE(),
        INTERVAL 7 DAY
    )

    AND (
        UPPER(TRIM(c.follow_up_status)) = 'PENDING'
        OR UPPER(TRIM(c.follow_up_status)) = 'ACTIVE'
    )

    AND UPPER(TRIM(p.status)) != 'DECEASED'
";

$result = $conn->query($sql);

$scheduled = 0;

if ($result) {

    $row = $result->fetch_assoc();

    $scheduled = (int)$row['total'];

}


/* =========================================================
   MONTHLY CONSULTATIONS
========================================================= */

$currentYear = date('Y');

$monthlyConsultations = array_fill(1, 12, 0);

$sql = "
    SELECT
        MONTH(visit_date) AS month_number,
        COUNT(*) AS total

    FROM consultations

    WHERE YEAR(visit_date) = ?

    GROUP BY MONTH(visit_date)

    ORDER BY MONTH(visit_date)
";

$stmt = $conn->prepare($sql);

if ($stmt) {

    $stmt->bind_param("i", $currentYear);

    $stmt->execute();

    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {

        $monthNumber = (int)$row['month_number'];

        $monthlyConsultations[$monthNumber] =
            (int)$row['total'];

    }

    $stmt->close();

}


/* =========================================================
   MONTH NAMES
========================================================= */

$monthNames = array(

    1  => 'Jan',
    2  => 'Feb',
    3  => 'Mar',
    4  => 'Apr',
    5  => 'May',
    6  => 'Jun',
    7  => 'Jul',
    8  => 'Aug',
    9  => 'Sep',
    10 => 'Oct',
    11 => 'Nov',
    12 => 'Dec'

);


/* =========================================================
   MAX MONTHLY CONSULTATION
========================================================= */

$maxMonthly = max($monthlyConsultations);

if ($maxMonthly < 1) {

    $maxMonthly = 1;

}


/* =========================================================
   PATIENT STATUS
========================================================= */

$statusTotal =
    $activePatients +
    $inactivePatients +
    $deceasedPatients;


$activeDeg = 0;
$inactiveDeg = 0;
$deceasedDeg = 0;


if ($statusTotal > 0) {

    $activeDeg =
        ($activePatients / $statusTotal) * 360;

    $inactiveDeg =
        ($inactivePatients / $statusTotal) * 360;

    $deceasedDeg =
        ($deceasedPatients / $statusTotal) * 360;

}


/* =========================================================
   LINKS
========================================================= */

$todayConsultationsLink =
    "patients/todays_consultationdays.php";

$overdueLink =
    "patients/follow_up.php?type=overdue";

$dueSoonLink =
    "patients/follow_up.php?type=due";

$scheduledLink =
    "patients/follow_up.php?type=scheduled";


/* =========================================================
   HEADER
========================================================= */

include __DIR__ . "/includes/header.php";


/* =========================================================
   NAVIGATION
========================================================= */

include __DIR__ . "/includes/navigation.php";

?>


<main class="main-container">


    <!-- =====================================================
         PAGE TITLE
    ====================================================== -->

    <div class="page-title">

        <h2>Dashboard</h2>

        <p>
            Overview of patient records, consultations,
            and follow-up monitoring.
        </p>

    </div>


    <!-- =====================================================
         PATIENT STATISTICS
    ====================================================== -->

    <div class="dashboard-grid">


        <!-- TOTAL -->

        <div class="dashboard-card">

            <h3>Total Patients</h3>

            <div class="number">

                <?php
                echo $totalPatients;
                ?>

            </div>

            <div class="description">
                All registered patients
            </div>

        </div>


        <!-- ACTIVE -->

        <div class="dashboard-card active">

            <h3>Active Patients</h3>

            <div class="number">

                <?php
                echo $activePatients;
                ?>

            </div>

            <div class="description">
                Currently active
            </div>

        </div>


        <!-- INACTIVE -->

        <div class="dashboard-card inactive">

            <h3>Inactive Patients</h3>

            <div class="number">

                <?php
                echo $inactivePatients;
                ?>

            </div>

            <div class="description">
                Currently inactive
            </div>

        </div>


        <!-- DECEASED -->

        <div class="dashboard-card deceased">

            <h3>Deceased Patients</h3>

            <div class="number">

                <?php
                echo $deceasedPatients;
                ?>

            </div>

            <div class="description">
                Patient records marked deceased
            </div>

        </div>


    </div>


    <!-- =====================================================
         CONSULTATIONS TODAY
    ====================================================== -->

    <div class="card">

        <div class="section-header">

            <div>

                <h3 class="card-title">
                    Consultations Today
                </h3>

                <div class="today-number">

                    <?php
                    echo $consultationsToday;
                    ?>

                </div>

                <div class="description">
                    Consultations recorded today
                </div>

            </div>


            <a
                href="<?php
                    echo htmlspecialchars(
                        $todayConsultationsLink
                    );
                ?>"
                class="btn btn-primary"
            >
                View Today's Consultations
            </a>

        </div>

    </div>


    <!-- =====================================================
         CHARTS
    ====================================================== -->

    <div class="dashboard-row">


        <!-- =================================================
             PATIENT STATUS
        ================================================== -->

        <div class="card">

            <div class="card-title">
                Patient Status
            </div>

            <div class="card-subtitle">
                Current patient distribution
            </div>


            <div class="status-chart-area">


                <div
                    class="donut"
                    style="
                        <?php

                        if ($statusTotal > 0) {

                            echo "background: conic-gradient(";

                            echo "#198754 0deg ";
                            echo $activeDeg . "deg, ";

                            echo "#6c757d ";
                            echo $activeDeg . "deg ";
                            echo ($activeDeg + $inactiveDeg) . "deg, ";

                            echo "#dc3545 ";
                            echo ($activeDeg + $inactiveDeg) . "deg 360deg";

                            echo ");";

                        } else {

                            echo "background:#e9ecef;";

                        }

                        ?>
                    "
                >

                    <div class="donut-center">

                        <div class="donut-total">

                            <?php
                            echo $statusTotal;
                            ?>

                        </div>

                        <div class="donut-label">
                            Patients
                        </div>

                    </div>

                </div>


                <!-- LEGEND -->

                <div class="legend">


                    <div class="legend-item">

                        <span class="legend-dot legend-active"></span>

                        <span class="legend-name">
                            Active
                        </span>

                        <span class="legend-value">

                            <?php
                            echo $activePatients;
                            ?>

                        </span>

                    </div>


                    <div class="legend-item">

                        <span class="legend-dot legend-inactive"></span>

                        <span class="legend-name">
                            Inactive
                        </span>

                        <span class="legend-value">

                            <?php
                            echo $inactivePatients;
                            ?>

                        </span>

                    </div>


                    <div class="legend-item">

                        <span class="legend-dot legend-deceased"></span>

                        <span class="legend-name">
                            Deceased
                        </span>

                        <span class="legend-value">

                            <?php
                            echo $deceasedPatients;
                            ?>

                        </span>

                    </div>


                </div>

            </div>

        </div>


        <!-- =================================================
             MONTHLY CONSULTATIONS
        ================================================== -->

        <div class="card">

            <div class="card-title">
                Monthly Consultations
            </div>

            <div class="card-subtitle">

                Consultation activity for
                <?php echo $currentYear; ?>

            </div>


            <div class="monthly-chart">

                <?php

                for (
                    $month = 1;
                    $month <= 12;
                    $month++
                ) {

                    $count =
                        $monthlyConsultations[$month];

                    $height =
                        ($count / $maxMonthly) * 175;

                ?>

                    <div class="month-column">

                        <div class="bar-value">

                            <?php
                            echo $count;
                            ?>

                        </div>


                        <div
                            class="bar"
                            style="
                                height:
                                <?php
                                echo max(2, $height);
                                ?>px;
                            "
                            title="<?php
                                echo $monthNames[$month];
                            ?>:
                            <?php
                                echo $count;
                            ?> consultations"
                        ></div>


                        <div class="month-label">

                            <?php
                            echo $monthNames[$month];
                            ?>

                        </div>

                    </div>

                <?php

                }

                ?>

            </div>

        </div>


    </div>


    <!-- =====================================================
         FOLLOW-UP MONITORING
    ====================================================== -->

    <div class="card">

        <div class="card-title">
            Follow-up Monitoring
        </div>

        <div class="card-subtitle">
            Patients requiring follow-up attention
        </div>


        <div class="follow-up-grid">


            <!-- =================================================
                 OVERDUE
            ================================================== -->

            <a
                href="<?php
                    echo htmlspecialchars($overdueLink);
                ?>"
                class="follow-up-card overdue"
            >

                <h3>
                    Overdue
                </h3>

                <div class="number">

                    <?php
                    echo $overdue;
                    ?>

                </div>

                <div class="description">
                    Follow-up date has passed →
                </div>

            </a>


            <!-- =================================================
                 DUE SOON
            ================================================== -->

            <a
                href="<?php
                    echo htmlspecialchars($dueSoonLink);
                ?>"
                class="follow-up-card due"
            >

                <h3>
                    Due Soon
                </h3>

                <div class="number">

                    <?php
                    echo $dueSoon;
                    ?>

                </div>

                <div class="description">
                    Due within the next 7 days →
                </div>

            </a>


            <!-- =================================================
                 SCHEDULED
            ================================================== -->

            <a
                href="<?php
                    echo htmlspecialchars($scheduledLink);
                ?>"
                class="follow-up-card scheduled"
            >

                <h3>
                    Scheduled
                </h3>

                <div class="number">

                    <?php
                    echo $scheduled;
                    ?>

                </div>

                <div class="description">
                    Scheduled beyond 7 days →
                </div>

            </a>


        </div>

    </div>


</main>


<?php

/* =========================================================
   FOOTER
========================================================= */

include __DIR__ . "/includes/footer.php";


$conn->close();

?>  