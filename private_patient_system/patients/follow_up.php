<?php

require_once "../config/database.php";

/* =========================================================
   FOLLOW-UP TYPE
========================================================= */

$type = isset($_GET["type"]) ? trim($_GET["type"]) : "due";

$allowedTypes = array(
    "overdue",
    "due",
    "scheduled",
    "confirmed",
    "cancelled",
    "completed",
    "no_show"
);

if (!in_array($type, $allowedTypes)) {
    $type = "due";
}


/* =========================================================
   PAGE SETTINGS
========================================================= */

if ($type == "overdue") {

    $pageTitle = "Overdue Follow-up";
    $pageSubtitle = "Follow-up Monitoring";
    $pageDescription = "Patients with pending follow-ups whose date has already passed.";
    $emptyMessage = "No overdue follow-ups found.";

} elseif ($type == "scheduled") {

    $pageTitle = "Scheduled Follow-up";
    $pageSubtitle = "Follow-up Monitoring";
    $pageDescription = "Patients with pending follow-up dates more than 7 days from today.";
    $emptyMessage = "No scheduled follow-ups found.";

} elseif ($type == "confirmed") {

    $pageTitle = "Confirmed Follow-up";
    $pageSubtitle = "Follow-up Monitoring";
    $pageDescription = "Patients whose follow-up has been confirmed.";
    $emptyMessage = "No confirmed follow-ups found.";

} elseif ($type == "cancelled") {

    $pageTitle = "Cancelled Follow-up";
    $pageSubtitle = "Follow-up Monitoring";
    $pageDescription = "Patients whose follow-up has been cancelled.";
    $emptyMessage = "No cancelled follow-ups found.";

} elseif ($type == "completed") {

    $pageTitle = "Completed Follow-up";
    $pageSubtitle = "Follow-up Monitoring";
    $pageDescription = "Patients whose follow-up has been completed.";
    $emptyMessage = "No completed follow-ups found.";

} elseif ($type == "no_show") {

    $pageTitle = "No Show Follow-up";
    $pageSubtitle = "Follow-up Monitoring";
    $pageDescription = "Patients who were scheduled or confirmed but did not attend.";
    $emptyMessage = "No No Show follow-ups found.";

} else {

    $pageTitle = "Due Follow-up";
    $pageSubtitle = "Follow-up Monitoring";
    $pageDescription = "Patients with pending follow-up dates within the next 7 days.";
    $emptyMessage = "No follow-ups due within the next 7 days.";

}


/* =========================================================
   SHARED SETTINGS
========================================================= */

$basePath = "../";
$activePage = "follow_up";


/* =========================================================
   QUERY CONDITION
========================================================= */

if ($type == "overdue") {

    $condition = "
        (
            UPPER(TRIM(c.follow_up_status)) = 'PENDING'
            OR UPPER(TRIM(c.follow_up_status)) = 'ACTIVE'
        )
        AND c.follow_up_date < CURDATE()
    ";

} elseif ($type == "due") {

    $condition = "
        (
            UPPER(TRIM(c.follow_up_status)) = 'PENDING'
            OR UPPER(TRIM(c.follow_up_status)) = 'ACTIVE'
        )
        AND c.follow_up_date >= CURDATE()
        AND c.follow_up_date <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ";

} elseif ($type == "scheduled") {

    $condition = "
        (
            UPPER(TRIM(c.follow_up_status)) = 'PENDING'
            OR UPPER(TRIM(c.follow_up_status)) = 'ACTIVE'
        )
        AND c.follow_up_date > DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    ";

} elseif ($type == "confirmed") {

    $condition = "
        UPPER(TRIM(c.follow_up_status)) = 'CONFIRMED'
    ";

} elseif ($type == "cancelled") {

    $condition = "
        UPPER(TRIM(c.follow_up_status)) = 'CANCELLED'
    ";

} elseif ($type == "completed") {

    $condition = "
        UPPER(TRIM(c.follow_up_status)) = 'COMPLETED'
    ";

} elseif ($type == "no_show") {

    $condition = "
        UPPER(TRIM(c.follow_up_status)) = 'NO SHOW'
    ";

} else {

    $condition = "
        UPPER(TRIM(c.follow_up_status)) = 'PENDING'
    ";

}


/* =========================================================
   GET FOLLOW-UP RECORDS
========================================================= */

$sql = "
    SELECT
        c.id AS consultation_id,
        c.visit_date,
        c.follow_up_date,
        c.chief_complaint,
        c.assessment,
        c.follow_up_status,
        p.id AS patient_db_id,
        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.status AS patient_status
    FROM consultations c
    INNER JOIN patients p
        ON c.patient_id = p.id
    WHERE
        UPPER(TRIM(p.status)) != 'DECEASED'
        AND c.follow_up_date IS NOT NULL
        AND " . $condition . "
    ORDER BY
        c.follow_up_date ASC,
        c.id DESC
";

$result = $conn->query($sql);

$count = 0;

if ($result) {
    $count = $result->num_rows;
}


/* =========================================================
   HELPER FUNCTIONS
========================================================= */

function getPatientName($row)
{
    $name = "";

    if (!empty($row["last_name"])) {
        $name .= $row["last_name"];
    }

    if (!empty($row["first_name"])) {

        if ($name != "") {
            $name .= ", ";
        }

        $name .= $row["first_name"];
    }

    if (!empty($row["middle_name"])) {

        $middle = trim($row["middle_name"]);

        if ($middle != "") {
            $name .= " " . $middle;
        }
    }

    return $name;
}


/* =========================================================
   FORMAT DATE
========================================================= */

function formatDateDisplay($date)
{
    if (empty($date) || $date == "0000-00-00") {
        return "-";
    }

    return date("M d, Y", strtotime($date));
}


/* =========================================================
   FOLLOW-UP STATUS TEXT
========================================================= */

function getFollowUpStatusText($status)
{
    $status = strtoupper(trim($status));

    if ($status == "" || $status == "ACTIVE") {
        return "PENDING";
    }

    return $status;
}


/* =========================================================
   FOLLOW-UP STATUS CLASS
========================================================= */

function getFollowUpStatusClass($status)
{
    $status = strtoupper(trim($status));

    if ($status == "CONFIRMED") {
        return "status-confirmed";
    }

    if ($status == "CANCELLED") {
        return "status-cancelled";
    }

    if ($status == "COMPLETED") {
        return "status-completed";
    }

    if ($status == "NO SHOW") {
        return "status-no-show";
    }

    return "status-pending";
}


/* =========================================================
   DATE MONITORING
========================================================= */

function getDateStatus($followUpDate)
{
    if (empty($followUpDate)) {
        return "";
    }

    $today = strtotime(date("Y-m-d"));
    $date = strtotime($followUpDate);

    if ($date < $today) {
        return "OVERDUE";
    }

    $sevenDays = strtotime("+7 days", $today);

    if ($date <= $sevenDays) {
        return "DUE SOON";
    }

    return "SCHEDULED";
}


/* =========================================================
   SHARED HEADER
========================================================= */

require_once "../includes/header.php";


/* =========================================================
   SHARED NAVIGATION
========================================================= */

require_once "../includes/navigation.php";
require_once "../config/auth.php";
?>

<main class="main-container">

    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="page-header">

        <div>

            <h2>
                <?php
                echo htmlspecialchars($pageTitle);
                ?>
            </h2>

            <p class="page-description">
                <?php
                echo htmlspecialchars($pageDescription);
                ?>
            </p>

        </div>

    </div>


    <!-- =====================================================
         FOLLOW-UP TABS
    ====================================================== -->

    <div class="follow-up-tabs">

        <a
            href="follow_up.php?type=overdue"
            class="follow-up-tab <?php
                echo ($type == "overdue") ? "active" : "";
            ?>"
        >
            Overdue
        </a>


        <a
            href="follow_up.php?type=due"
            class="follow-up-tab <?php
                echo ($type == "due") ? "active" : "";
            ?>"
        >
            Due Soon
        </a>


        <a
            href="follow_up.php?type=scheduled"
            class="follow-up-tab <?php
                echo ($type == "scheduled") ? "active" : "";
            ?>"
        >
            Scheduled
        </a>


        <a
            href="follow_up.php?type=confirmed"
            class="follow-up-tab <?php
                echo ($type == "confirmed") ? "active" : "";
            ?>"
        >
            Confirmed
        </a>


        <a
            href="follow_up.php?type=cancelled"
            class="follow-up-tab <?php
                echo ($type == "cancelled") ? "active" : "";
            ?>"
        >
            Cancelled
        </a>


        <a
            href="follow_up.php?type=completed"
            class="follow-up-tab <?php
                echo ($type == "completed") ? "active" : "";
            ?>"
        >
            Completed
        </a>


        <a
            href="follow_up.php?type=no_show"
            class="follow-up-tab <?php
                echo ($type == "no_show") ? "active" : "";
            ?>"
        >
            No Show
        </a>

    </div>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="summary-card">

        <div class="summary-label">
            <?php
            echo htmlspecialchars($pageTitle);
            ?>
        </div>

        <div class="summary-number">
            <?php
            echo $count;
            ?>
        </div>

        <div class="summary-text">

            Follow-up record<?php
            echo ($count == 1) ? "" : "s";
            ?>

        </div>

    </div>


    <!-- =====================================================
         RECORDS
    ====================================================== -->

    <?php if ($count > 0) { ?>

        <div class="card">

            <div class="table-wrapper">

                <table class="data-table">

                    <thead>

                        <tr>

                            <th>Patient ID</th>

                            <th>Patient Name</th>

                            <th>Follow-up Date</th>

                            <th>Last Visit</th>

                            <th>Chief Complaint</th>

                            <th>Follow-up Status</th>

                            <th>Monitoring</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($row = $result->fetch_assoc()) { ?>

                        <?php

                        /* =====================================
                           PATIENT NAME
                        ====================================== */

                        $patientName = getPatientName($row);


                        /* =====================================
                           ACTUAL STATUS
                        ====================================== */

                        $actualStatus = getFollowUpStatusText(
                            $row["follow_up_status"]
                        );


                        $statusClass = getFollowUpStatusClass(
                            $actualStatus
                        );


                        /* =====================================
                           DATE MONITORING
                        ====================================== */

                        $dateStatus = getDateStatus(
                            $row["follow_up_date"]
                        );


                        /* =====================================
                           DEFAULT ACTION
                        ====================================== */

                        $action = "NO ACTION";
                        $actionClass = "action-none";


                        /* =====================================
                           ACTION BASED ON STATUS
                        ====================================== */

                        if ($actualStatus == "PENDING") {

                            if ($dateStatus == "OVERDUE") {

                                $action = "PRIORITY CALL";
                                $actionClass = "action-priority";

                            } elseif ($dateStatus == "DUE SOON") {

                                $action = "FOLLOW-UP CALL";
                                $actionClass = "action-followup";

                            } else {

                                $action = "NO ACTION";
                                $actionClass = "action-none";

                            }

                        } elseif ($actualStatus == "CONFIRMED") {

                            $action = "CONFIRMED";
                            $actionClass = "action-confirmed";

                        } elseif ($actualStatus == "CANCELLED") {

                            $action = "CANCELLED";
                            $actionClass = "action-cancelled";

                        } elseif ($actualStatus == "COMPLETED") {

                            $action = "COMPLETED";
                            $actionClass = "action-completed";

                        } elseif ($actualStatus == "NO SHOW") {

                            $action = "FOLLOW-UP NEEDED";
                            $actionClass = "action-priority";

                        }


                        /* =====================================
                           MONITORING DISPLAY
                        ====================================== */

                        $monitoring = $dateStatus;
                        $monitoringClass = "monitor-scheduled";


                        if ($actualStatus == "CONFIRMED") {

                            $monitoring = "CONFIRMED";
                            $monitoringClass = "monitor-confirmed";

                        } elseif ($actualStatus == "CANCELLED") {

                            $monitoring = "CANCELLED";
                            $monitoringClass = "monitor-cancelled";

                        } elseif ($actualStatus == "COMPLETED") {

                            $monitoring = "COMPLETED";
                            $monitoringClass = "monitor-completed";

                        } elseif ($actualStatus == "NO SHOW") {

                            $monitoring = "NO SHOW";
                            $monitoringClass = "monitor-no-show";

                        } elseif ($dateStatus == "OVERDUE") {

                            $monitoring = "OVERDUE";
                            $monitoringClass = "monitor-overdue";

                        } elseif ($dateStatus == "DUE SOON") {

                            $monitoring = "DUE SOON";
                            $monitoringClass = "monitor-due";

                        } else {

                            $monitoring = "SCHEDULED";
                            $monitoringClass = "monitor-scheduled";

                        }

                        ?>

                        <tr>

                            <!-- PATIENT ID -->

                            <td>

                                <strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $row["patient_number"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <!-- PATIENT NAME -->

                            <td>

                                <?php
                                echo htmlspecialchars($patientName);
                                ?>

                            </td>


                            <!-- FOLLOW-UP DATE -->

                            <td>

                                <strong>

                                    <?php
                                    echo formatDateDisplay(
                                        $row["follow_up_date"]
                                    );
                                    ?>

                                </strong>

                            </td>


                            <!-- LAST VISIT -->

                            <td>

                                <?php
                                echo formatDateDisplay(
                                    $row["visit_date"]
                                );
                                ?>

                            </td>


                            <!-- CHIEF COMPLAINT -->

                            <td>

                                <?php

                                if (!empty($row["chief_complaint"])) {

                                    echo htmlspecialchars(
                                        $row["chief_complaint"]
                                    );

                                } else {

                                    echo "-";

                                }

                                ?>

                            </td>


                            <!-- FOLLOW-UP STATUS -->

                            <td>

                                <span
                                    class="follow-status <?php
                                        echo htmlspecialchars(
                                            $statusClass
                                        );
                                    ?>"
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $actualStatus
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- MONITORING -->

                            <td>

                                <span
                                    class="monitor-status <?php
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

                                <div class="follow-actions">


                                    <!-- VIEW -->

                                    <a
                                        href="view.php?id=<?php
                                            echo intval(
                                                $row["patient_db_id"]
                                            );
                                        ?>"
                                        class="btn btn-view"
                                    >
                                        View
                                    </a>


                                    <!-- =================================
                                         PENDING ACTIONS
                                    ================================== -->

                                    <?php if ($actualStatus == "PENDING") { ?>


                                        <!-- CONFIRM -->

                                        <form
                                            method="POST"
                                            action="update_follow_up_status.php"
                                            class="status-form"
                                            onsubmit="return confirm('Confirm this follow-up?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="consultation_id"
                                                value="<?php
                                                    echo intval(
                                                        $row["consultation_id"]
                                                    );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="confirm"
                                            >

                                            <input
                                                type="hidden"
                                                name="type"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $type
                                                    );
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-confirm"
                                            >
                                                Confirm
                                            </button>

                                        </form>


                                        <!-- CANCEL -->

                                        <form
                                            method="POST"
                                            action="update_follow_up_status.php"
                                            class="status-form"
                                            onsubmit="return confirm('Cancel this follow-up? The follow-up date will remain in the record.');"
                                        >

                                            <input
                                                type="hidden"
                                                name="consultation_id"
                                                value="<?php
                                                    echo intval(
                                                        $row["consultation_id"]
                                                    );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="cancel"
                                            >

                                            <input
                                                type="hidden"
                                                name="type"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $type
                                                    );
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-cancel"
                                            >
                                                Cancel
                                            </button>

                                        </form>


                                    <?php } ?>


                                    <!-- =================================
                                         CONFIRMED ACTIONS
                                    ================================== -->

                                    <?php if ($actualStatus == "CONFIRMED") { ?>


                                        <!-- COMPLETE -->

                                        <form
                                            method="POST"
                                            action="update_follow_up_status.php"
                                            class="status-form"
                                            onsubmit="return confirm('Mark this follow-up as completed?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="consultation_id"
                                                value="<?php
                                                    echo intval(
                                                        $row["consultation_id"]
                                                    );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="complete"
                                            >

                                            <input
                                                type="hidden"
                                                name="type"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $type
                                                    );
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-complete"
                                            >
                                                Complete
                                            </button>

                                        </form>


                                        <!-- NO SHOW -->

                                        <form
                                            method="POST"
                                            action="update_follow_up_status.php"
                                            class="status-form"
                                            onsubmit="return confirm('Mark this patient as No Show?');"
                                        >

                                            <input
                                                type="hidden"
                                                name="consultation_id"
                                                value="<?php
                                                    echo intval(
                                                        $row["consultation_id"]
                                                    );
                                                ?>"
                                            >

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="no_show"
                                            >

                                            <input
                                                type="hidden"
                                                name="type"
                                                value="<?php
                                                    echo htmlspecialchars(
                                                        $type
                                                    );
                                                ?>"
                                            >

                                            <button
                                                type="submit"
                                                class="btn btn-no-show"
                                            >
                                                No Show
                                            </button>

                                        </form>


                                    <?php } ?>


                                </div>

                            </td>

                        </tr>

                    <?php } ?>

                    </tbody>

                </table>

            </div>

        </div>


    <?php } else { ?>


        <!-- =====================================================
             EMPTY STATE
        ====================================================== -->

        <div class="empty-state">

            <div class="empty-icon">
                ✓
            </div>

            <h3>

                <?php
                echo htmlspecialchars($emptyMessage);
                ?>

            </h3>

            <p>
                There are currently no patients under this
                follow-up category.
            </p>

        </div>


    <?php } ?>


</main>


<style>

/* =========================================================
   FOLLOW-UP TABS
========================================================= */

.follow-up-tabs {
    display: flex;
    gap: 8px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.follow-up-tab {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 38px;
    padding: 9px 18px;
    background: #ffffff;
    color: #495057;
    border: 1px solid #d9dee3;
    border-radius: 5px;
    text-decoration: none;
    font-size: 13px;
    font-weight: 600;
    transition: 0.2s ease;
}

.follow-up-tab:hover {
    background: #f1f4f7;
}

.follow-up-tab.active {
    background: #1f4e78;
    color: #ffffff;
    border-color: #1f4e78;
}


/* =========================================================
   SUMMARY CARD
========================================================= */

.summary-card {
    background: #ffffff;
    border: 1px solid #e0e4e8;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    width: 220px;
    box-shadow:
        0 2px 6px rgba(0, 0, 0, 0.04);
}

.summary-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.summary-number {
    font-size: 32px;
    font-weight: 700;
    color: #1f4e78;
    line-height: 1.2;
}

.summary-text {
    font-size: 12px;
    color: #777;
    margin-top: 4px;
}


/* =========================================================
   FOLLOW-UP STATUS
========================================================= */

.follow-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 90px;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    text-align: center;
    white-space: nowrap;
}

.status-pending {
    background: #fff4cc;
    color: #856404;
}

.status-confirmed {
    background: #e6f4ea;
    color: #287d3c;
}

.status-cancelled {
    background: #fce8e8;
    color: #b42318;
}

.status-completed {
    background: #dff3ea;
    color: #176b4d;
}

.status-no-show {
    background: #fff0d9;
    color: #9a5b00;
}


/* =========================================================
   MONITORING
========================================================= */

.monitor-status {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 80px;
    padding: 5px 9px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 700;
    white-space: nowrap;
}

.monitor-overdue {
    background: #fce8e8;
    color: #b42318;
}

.monitor-due {
    background: #fff4cc;
    color: #856404;
}

.monitor-scheduled {
    background: #e9f2fb;
    color: #1f4e78;
}

.monitor-confirmed {
    background: #e6f4ea;
    color: #287d3c;
}

.monitor-cancelled {
    background: #fce8e8;
    color: #b42318;
}

.monitor-completed {
    background: #dff3ea;
    color: #176b4d;
}

.monitor-no-show {
    background: #fff0d9;
    color: #9a5b00;
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
    color: #b42318;
    font-size: 11px;
    font-weight: 700;
}

.action-confirmed {
    color: #287d3c;
    font-size: 11px;
    font-weight: 700;
}

.action-cancelled {
    color: #b42318;
    font-size: 11px;
    font-weight: 700;
}

.action-completed {
    color: #176b4d;
    font-size: 11px;
    font-weight: 700;
}


/* =========================================================
   ACTION BUTTONS
========================================================= */

.follow-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    align-items: center;
}

.status-form {
    margin: 0;
    padding: 0;
}

.follow-actions .btn {
    min-height: 32px;
    padding: 6px 10px;
    font-size: 11px;
    border-radius: 4px;
    border: none;
    cursor: pointer;
    text-decoration: none;
    font-weight: 600;
}

.btn-view {
    background: #1f4e78;
    color: #ffffff;
}

.btn-view:hover {
    background: #163a5c;
}

.btn-confirm {
    background: #2e7d32;
    color: #ffffff;
}

.btn-confirm:hover {
    background: #256628;
}

.btn-cancel {
    background: #c0392b;
    color: #ffffff;
}

.btn-cancel:hover {
    background: #a93226;
}

.btn-complete {
    background: #287d5a;
    color: #ffffff;
}

.btn-complete:hover {
    background: #206347;
}

.btn-no-show {
    background: #b7791f;
    color: #ffffff;
}

.btn-no-show:hover {
    background: #986515;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.empty-state {
    background: #ffffff;
    border: 1px solid #e0e4e8;
    border-radius: 8px;
    text-align: center;
    padding: 50px 20px;
    box-shadow:
        0 2px 6px rgba(0, 0, 0, 0.03);
}

.empty-icon {
    width: 50px;
    height: 50px;
    margin: 0 auto 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #e6f4ea;
    color: #287d3c;
    font-size: 22px;
    font-weight: bold;
}

.empty-state h3 {
    margin: 0 0 7px;
    color: #343a40;
    font-size: 17px;
}

.empty-state p {
    margin: 0;
    color: #777;
    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 1000px) {

    .table-wrapper {
        overflow-x: auto;
    }

    .data-table {
        min-width: 1050px;
    }

}

@media (max-width: 600px) {

    .follow-up-tabs {
        width: 100%;
    }

    .follow-up-tab {
        flex: 1;
    }

    .summary-card {
        width: 100%;
    }

}

</style>


<?php

$conn->close();

require_once "../includes/footer.php";

?>