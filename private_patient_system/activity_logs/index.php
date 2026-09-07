<?php

require_once "../config/auth.php";
require_once "../config/database.php";


/* =========================================================
   ADMINISTRATOR ONLY
========================================================= */

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Administrator') {

    header("Location: ../dashboard.php");
    exit;
}


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle = "Activity Log";
$pageSubtitle = "System activity and user actions";

$basePath = "../";
$activePage = "activity_logs";


/* =========================================================
   FILTER VALUES
========================================================= */

$filterDate = isset($_GET['date'])
    ? trim($_GET['date'])
    : '';

$filterUser = isset($_GET['user_id'])
    ? intval($_GET['user_id'])
    : 0;

$filterAction = isset($_GET['action'])
    ? trim($_GET['action'])
    : '';

$filterSearch = isset($_GET['search'])
    ? trim($_GET['search'])
    : '';


/* =========================================================
   PAGINATION
========================================================= */

$recordsPerPage = 50;

$page = isset($_GET['page'])
    ? intval($_GET['page'])
    : 1;

if ($page < 1) {
    $page = 1;
}

$offset = ($page - 1) * $recordsPerPage;


/* =========================================================
   GET USERS FOR FILTER
========================================================= */

$users = array();

$userQuery = $conn->query("
    SELECT DISTINCT
        user_id,
        username,
        full_name
    FROM activity_logs
    WHERE user_id IS NOT NULL
    ORDER BY full_name ASC
");

if ($userQuery) {

    while ($userRow = $userQuery->fetch_assoc()) {

        $users[] = $userRow;

    }

}


/* =========================================================
   ACTION OPTIONS
========================================================= */

$actionOptions = array(
    "LOGIN",
    "LOGOUT",
    "ADD_PATIENT",
    "EDIT_PATIENT",
    "DELETE_PATIENT",
    "CONSULTATION",
    "LABORATORY",
    "PRESCRIPTION",
    "FOLLOW_UP",
    "USER_MANAGEMENT"
);


/* =========================================================
   BUILD WHERE CONDITION
========================================================= */

$where = array();
$params = array();
$types = "";


/* DATE */

if ($filterDate !== '') {

    $where[] = "DATE(created_at) = ?";
    $params[] = $filterDate;
    $types .= "s";

}


/* USER */

if ($filterUser > 0) {

    $where[] = "user_id = ?";
    $params[] = $filterUser;
    $types .= "i";

}


/* ACTION */

if ($filterAction !== '') {

    $where[] = "action = ?";
    $params[] = $filterAction;
    $types .= "s";

}


/* SEARCH */

if ($filterSearch !== '') {

    $where[] = "
        (
            username LIKE ?
            OR full_name LIKE ?
            OR description LIKE ?
            OR action LIKE ?
        )
    ";

    $searchValue = "%" . $filterSearch . "%";

    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;
    $params[] = $searchValue;

    $types .= "ssss";

}


/* =========================================================
   WHERE SQL
========================================================= */

$whereSql = "";

if (count($where) > 0) {

    $whereSql = "WHERE " . implode(" AND ", $where);

}


/* =========================================================
   GET TOTAL RECORDS
========================================================= */

$countSql = "
    SELECT COUNT(*) AS total
    FROM activity_logs
    $whereSql
";

$countStmt = $conn->prepare($countSql);

$totalRecords = 0;

if ($countStmt) {

    if (!empty($params)) {

        $bindParams = array();

        $bindParams[] = $types;

        foreach ($params as $key => $value) {
            $bindParams[] = &$params[$key];
        }

        call_user_func_array(
            array($countStmt, 'bind_param'),
            $bindParams
        );

    }

    $countStmt->execute();

    $countResult = $countStmt->get_result();

    if ($countResult) {

        $countRow = $countResult->fetch_assoc();

        $totalRecords = intval($countRow['total']);

    }

    $countStmt->close();

}


/* =========================================================
   PAGINATION CALCULATION
========================================================= */

$totalPages = ceil($totalRecords / $recordsPerPage);

if ($totalPages > 0 && $page > $totalPages) {

    $page = $totalPages;

    $offset = ($page - 1) * $recordsPerPage;

}


/* =========================================================
   GET ACTIVITY LOGS
========================================================= */

$sql = "
    SELECT
        id,
        user_id,
        username,
        full_name,
        role,
        action,
        description,
        created_at
    FROM activity_logs
    $whereSql
    ORDER BY created_at DESC
    LIMIT ?, ?
";


$stmt = $conn->prepare($sql);

$result = false;

if ($stmt) {

    $dataParams = $params;

    $dataTypes = $types . "ii";

    $dataParams[] = $offset;
    $dataParams[] = $recordsPerPage;

    $bindParams = array();

    $bindParams[] = $dataTypes;

    foreach ($dataParams as $key => $value) {
        $bindParams[] = &$dataParams[$key];
    }

    call_user_func_array(
        array($stmt, 'bind_param'),
        $bindParams
    );

    $stmt->execute();

    $result = $stmt->get_result();

}


/* =========================================================
   PAGINATION URL
========================================================= */

function buildPageUrl($pageNumber)
{
    global $filterDate;
    global $filterUser;
    global $filterAction;
    global $filterSearch;

    $query = array();

    if ($filterDate !== '') {
        $query['date'] = $filterDate;
    }

    if ($filterUser > 0) {
        $query['user_id'] = $filterUser;
    }

    if ($filterAction !== '') {
        $query['action'] = $filterAction;
    }

    if ($filterSearch !== '') {
        $query['search'] = $filterSearch;
    }

    $query['page'] = $pageNumber;

    return '?' . http_build_query($query);
}


/* =========================================================
   HEADER / NAVIGATION
========================================================= */

require_once "../includes/header.php";
require_once "../includes/navigation.php";

?>


<style>

/* =========================================================
   ACTIVITY LOG
========================================================= */

.activity-wrapper {
    max-width: 1250px;
    margin: 0 auto;
    padding: 28px 20px 40px;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.activity-page-header {
    margin-bottom: 20px;
}

.activity-page-title {
    margin: 0;
    font-size: 23px;
    font-weight: 700;
    color: #1f2937;
}

.activity-page-subtitle {
    margin-top: 4px;
    color: #6b7280;
    font-size: 13px;
}


/* =========================================================
   FILTER CARD
========================================================= */

.activity-filter-card {
    background: #ffffff;
    border: 1px solid #e2e6eb;
    border-radius: 10px;
    padding: 18px 20px;
    margin-bottom: 18px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.04);
}

.activity-filter-title {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
    margin-bottom: 14px;
}

.activity-filter-row {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    align-items: flex-end;
}

.activity-filter-group {
    flex: 1;
    min-width: 160px;
}

.activity-filter-group.search-group {
    flex: 2;
}

.activity-filter-label {
    display: block;
    font-size: 11px;
    font-weight: 700;
    color: #4b5563;
    margin-bottom: 5px;
    text-transform: uppercase;
}

.activity-filter-input,
.activity-filter-select {
    width: 100%;
    height: 38px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    padding: 0 10px;
    font-size: 13px;
    color: #374151;
    background: #ffffff;
}

.activity-filter-input:focus,
.activity-filter-select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.08);
}


/* =========================================================
   BUTTONS
========================================================= */

.activity-filter-btn {
    height: 38px;
    padding: 0 16px;
    border-radius: 6px;
    border: none;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
}

.activity-filter-btn.primary {
    background: #2563eb;
    color: #ffffff;
}

.activity-filter-btn.primary:hover {
    background: #1d4ed8;
}

.activity-filter-btn.clear {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #f3f4f6;
    color: #4b5563;
    text-decoration: none;
}

.activity-filter-btn.clear:hover {
    background: #e5e7eb;
}


/* =========================================================
   LOG CARD
========================================================= */

.activity-card {
    background: #ffffff;
    border: 1px solid #e2e6eb;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}

.activity-card-header {
    padding: 16px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e5e7eb;

    display: flex;
    justify-content: space-between;
    align-items: center;
}

.activity-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
}

.activity-card-count {
    font-size: 12px;
    color: #6b7280;
}


/* =========================================================
   TABLE
========================================================= */

.activity-table {
    margin-bottom: 0;
}

.activity-table thead th {
    background: #f8f9fa;
    color: #4b5563;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    padding: 12px 14px;
    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

.activity-table tbody td {
    padding: 12px 14px;
    font-size: 12px;
    color: #374151;
    vertical-align: middle;
    border-bottom: 1px solid #f0f1f3;
}

.activity-table tbody tr:last-child td {
    border-bottom: none;
}

.activity-table tbody tr:hover {
    background: #fafcff;
}


/* =========================================================
   DATE
========================================================= */

.activity-date {
    white-space: nowrap;
    font-size: 12px;
    color: #4b5563;
}


/* =========================================================
   USER
========================================================= */

.activity-user-name {
    font-weight: 600;
    color: #1f2937;
}

.activity-user-username {
    font-size: 11px;
    color: #9ca3af;
    margin-top: 2px;
}


/* =========================================================
   ROLE
========================================================= */

.activity-role {
    font-size: 11px;
    color: #6b7280;
}


/* =========================================================
   ACTION BADGES
========================================================= */

.activity-action {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 5px;
    background: #eef2ff;
    color: #4338ca;
    font-size: 10px;
    font-weight: 700;
    white-space: nowrap;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.activity-description {
    max-width: 450px;
    line-height: 1.5;
    color: #4b5563;
}


/* =========================================================
   EMPTY
========================================================= */

.activity-empty {
    padding: 45px 20px !important;
    text-align: center;
    color: #9ca3af !important;
}


/* =========================================================
   PAGINATION
========================================================= */

.activity-pagination {
    padding: 15px 20px;
    border-top: 1px solid #e5e7eb;

    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
}

.activity-pagination-info {
    font-size: 12px;
    color: #6b7280;
}

.activity-pagination-links {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.activity-page-link {
    min-width: 32px;
    height: 32px;
    padding: 0 9px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border: 1px solid #d1d5db;
    border-radius: 5px;

    text-decoration: none;

    font-size: 12px;
    color: #374151;
    background: #ffffff;
}

.activity-page-link:hover {
    background: #f3f4f6;
}

.activity-page-link.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #ffffff;
}

.activity-page-link.disabled {
    color: #9ca3af;
    background: #f9fafb;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .activity-wrapper {
        padding: 20px 12px 30px;
    }

    .activity-filter-group,
    .activity-filter-group.search-group {
        flex: 1 1 100%;
    }

    .activity-card {
        overflow-x: auto;
    }

    .activity-table {
        min-width: 950px;
    }

    .activity-pagination {
        flex-direction: column;
        align-items: flex-start;
    }

}

</style>


<div class="activity-wrapper">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="activity-page-header">

        <h2 class="activity-page-title">
            Activity Log
        </h2>

        <div class="activity-page-subtitle">
            System activity and user actions
        </div>

    </div>



    <!-- =====================================================
         FILTERS
    ====================================================== -->

    <div class="activity-filter-card">

        <div class="activity-filter-title">
            Filter Activity
        </div>


        <form method="GET" action="">


            <div class="activity-filter-row">


                <!-- DATE -->

                <div class="activity-filter-group">

                    <label class="activity-filter-label">
                        Date
                    </label>

                    <input
                        type="date"
                        name="date"
                        class="activity-filter-input"
                        value="<?php echo htmlspecialchars($filterDate); ?>"
                    >

                </div>


                <!-- USER -->

                <div class="activity-filter-group">

                    <label class="activity-filter-label">
                        User
                    </label>

                    <select
                        name="user_id"
                        class="activity-filter-select"
                    >

                        <option value="0">
                            All Users
                        </option>

                        <?php foreach ($users as $user): ?>

                            <option
                                value="<?php echo intval($user['user_id']); ?>"
                                <?php
                                echo (
                                    $filterUser == intval($user['user_id'])
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $user['full_name']
                                );
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- ACTION -->

                <div class="activity-filter-group">

                    <label class="activity-filter-label">
                        Action
                    </label>

                    <select
                        name="action"
                        class="activity-filter-select"
                    >

                        <option value="">
                            All Actions
                        </option>

                        <?php foreach ($actionOptions as $action): ?>

                            <option
                                value="<?php echo htmlspecialchars($action); ?>"
                                <?php
                                echo (
                                    $filterAction === $action
                                )
                                ? 'selected'
                                : '';
                                ?>
                            >

                                <?php
                                echo htmlspecialchars($action);
                                ?>

                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <!-- SEARCH -->

                <div class="activity-filter-group search-group">

                    <label class="activity-filter-label">
                        Search
                    </label>

                    <input
                        type="text"
                        name="search"
                        class="activity-filter-input"
                        placeholder="Search user, action or description..."
                        value="<?php echo htmlspecialchars($filterSearch); ?>"
                    >

                </div>


                <!-- FILTER -->

                <div>

                    <button
                        type="submit"
                        class="activity-filter-btn primary"
                    >
                        Filter
                    </button>

                </div>


                <!-- CLEAR -->

                <div>

                    <a
                        href="index.php"
                        class="activity-filter-btn clear"
                    >
                        Clear
                    </a>

                </div>


            </div>


        </form>

    </div>



    <!-- =====================================================
         ACTIVITY TABLE
    ====================================================== -->

    <div class="activity-card">


        <div class="activity-card-header">

            <div class="activity-card-title">
                System Activities
            </div>

            <div class="activity-card-count">

                <?php
                echo number_format($totalRecords);
                ?>

                record<?php echo ($totalRecords == 1) ? '' : 's'; ?>

            </div>

        </div>


        <div class="table-responsive">

            <table class="table activity-table">

                <thead>

                    <tr>

                        <th>
                            Date & Time
                        </th>

                        <th>
                            User
                        </th>

                        <th>
                            Role
                        </th>

                        <th>
                            Action
                        </th>

                        <th>
                            Description
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result && $result->num_rows > 0): ?>


                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>


                            <!-- DATE -->

                            <td>

                                <div class="activity-date">

                                    <?php
                                    echo date(
                                        "M d, Y h:i A",
                                        strtotime($row['created_at'])
                                    );
                                    ?>

                                </div>

                            </td>


                            <!-- USER -->

                            <td>

                                <div class="activity-user-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['full_name']
                                    );
                                    ?>

                                </div>

                                <div class="activity-user-username">

                                    @<?php
                                    echo htmlspecialchars(
                                        $row['username']
                                    );
                                    ?>

                                </div>

                            </td>


                            <!-- ROLE -->

                            <td>

                                <div class="activity-role">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['role']
                                    );
                                    ?>

                                </div>

                            </td>


                            <!-- ACTION -->

                            <td>

                                <span class="activity-action">

                                    <?php
                                    echo htmlspecialchars(
                                        $row['action']
                                    );
                                    ?>

                                </span>

                            </td>


                            <!-- DESCRIPTION -->

                            <td>

                                <div class="activity-description">

                                    <?php

                                    echo htmlspecialchars(
                                        $row['description']
                                    );

                                    ?>

                                </div>

                            </td>


                        </tr>

                    <?php endwhile; ?>


                <?php else: ?>

                    <tr>

                        <td
                            colspan="5"
                            class="activity-empty"
                        >

                            No activity records found.

                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>



        <!-- =================================================
             PAGINATION
        ================================================== -->

        <?php if ($totalPages > 1): ?>

            <div class="activity-pagination">


                <div class="activity-pagination-info">

                    Showing

                    <?php
                    echo number_format($offset + 1);
                    ?>

                    to

                    <?php
                    echo number_format(
                        min(
                            $offset + $recordsPerPage,
                            $totalRecords
                        )
                    );
                    ?>

                    of

                    <?php
                    echo number_format($totalRecords);
                    ?>

                    records

                </div>


                <div class="activity-pagination-links">


                    <!-- PREVIOUS -->

                    <?php if ($page > 1): ?>

                        <a
                            href="<?php echo buildPageUrl($page - 1); ?>"
                            class="activity-page-link"
                        >
                            &laquo;
                        </a>

                    <?php else: ?>

                        <span class="activity-page-link disabled">
                            &laquo;
                        </span>

                    <?php endif; ?>


                    <!-- PAGE NUMBERS -->

                    <?php

                    $startPage = max(1, $page - 2);
                    $endPage = min($totalPages, $page + 2);

                    ?>


                    <?php if ($startPage > 1): ?>

                        <a
                            href="<?php echo buildPageUrl(1); ?>"
                            class="activity-page-link"
                        >
                            1
                        </a>

                        <?php if ($startPage > 2): ?>

                            <span class="activity-page-link disabled">
                                ...
                            </span>

                        <?php endif; ?>

                    <?php endif; ?>


                    <?php for ($i = $startPage; $i <= $endPage; $i++): ?>

                        <a
                            href="<?php echo buildPageUrl($i); ?>"
                            class="activity-page-link <?php echo ($i == $page) ? 'active' : ''; ?>"
                        >
                            <?php echo $i; ?>
                        </a>

                    <?php endfor; ?>


                    <?php if ($endPage < $totalPages): ?>

                        <?php if ($endPage < $totalPages - 1): ?>

                            <span class="activity-page-link disabled">
                                ...
                            </span>

                        <?php endif; ?>

                        <a
                            href="<?php echo buildPageUrl($totalPages); ?>"
                            class="activity-page-link"
                        >
                            <?php echo $totalPages; ?>
                        </a>

                    <?php endif; ?>


                    <!-- NEXT -->

                    <?php if ($page < $totalPages): ?>

                        <a
                            href="<?php echo buildPageUrl($page + 1); ?>"
                            class="activity-page-link"
                        >
                            &raquo;
                        </a>

                    <?php else: ?>

                        <span class="activity-page-link disabled">
                            &raquo;
                        </span>

                    <?php endif; ?>


                </div>

            </div>

        <?php endif; ?>


    </div>

</div>


<?php

require_once "../includes/footer.php";

?>