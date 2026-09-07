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
   GET USERS
========================================================= */

$sql = "
    SELECT
        id,
        username,
        full_name,
        role,
        is_active,
        created_at
    FROM users
    ORDER BY full_name ASC
";

$result = $conn->query($sql);

if (!$result) {
    die("Database error.");
}


/* =========================================================
   BASE PATH
========================================================= */

$basePath = "../";

$activePage = "users";


require_once "../includes/header.php";
require_once "../includes/navigation.php";

?>


<style>

/* =========================================================
   USER MANAGEMENT
========================================================= */

.users-wrapper {
    max-width: 1200px;
    margin: 0 auto;
    padding: 28px 20px 40px;
}


/* =========================================================
   PAGE HEADER
========================================================= */

.users-page-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.users-page-title {
    margin: 0;
    font-size: 23px;
    font-weight: 700;
    color: #1f2937;
}

.users-page-subtitle {
    margin-top: 4px;
    color: #6b7280;
    font-size: 13px;
}


/* =========================================================
   ADD BUTTON
========================================================= */

.add-user-btn {
    font-size: 13px;
    font-weight: 600;
    border-radius: 6px;
    padding: 9px 15px;
}


/* =========================================================
   CARD
========================================================= */

.users-card {
    background: #ffffff;
    border: 1px solid #e2e6eb;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    overflow: hidden;
}


/* =========================================================
   TABLE HEADER
========================================================= */

.users-card-header {
    padding: 16px 20px;
    background: #fafbfc;
    border-bottom: 1px solid #e5e7eb;
}

.users-card-title {
    font-size: 14px;
    font-weight: 700;
    color: #374151;
}

.users-card-subtitle {
    font-size: 12px;
    color: #6b7280;
    margin-top: 2px;
}


/* =========================================================
   TABLE
========================================================= */

.users-table {
    margin-bottom: 0;
}

.users-table thead th {
    background: #f8f9fa;
    color: #4b5563;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;

    padding: 12px 16px;

    border-bottom: 1px solid #e5e7eb;
    white-space: nowrap;
}

.users-table tbody td {
    padding: 13px 16px;
    font-size: 13px;
    color: #374151;
    vertical-align: middle;
    border-bottom: 1px solid #f0f1f3;
}

.users-table tbody tr:last-child td {
    border-bottom: none;
}

.users-table tbody tr:hover {
    background: #fafcff;
}


/* =========================================================
   USER NAME
========================================================= */

.user-name {
    font-weight: 600;
    color: #1f2937;
}

.user-username {
    color: #6b7280;
    font-size: 12px;
}


/* =========================================================
   ROLE BADGE
========================================================= */

.role-badge {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 5px;

    font-size: 11px;
    font-weight: 600;
}

.role-staff {
    background: #f1f5f9;
    color: #475569;
}

.role-admin {
    background: #eef5ff;
    color: #2563eb;
}


/* =========================================================
   STATUS BADGE
========================================================= */

.status-badge {
    display: inline-block;
    padding: 4px 9px;
    border-radius: 5px;

    font-size: 11px;
    font-weight: 600;
}

.status-active {
    background: #ecfdf3;
    color: #15803d;
}

.status-inactive {
    background: #fef2f2;
    color: #dc2626;
}


/* =========================================================
   EDIT BUTTON
========================================================= */

.edit-user-btn {
    font-size: 12px;
    font-weight: 600;
    padding: 5px 11px;
    border-radius: 5px;
}


/* =========================================================
   EMPTY STATE
========================================================= */

.users-empty {
    padding: 40px 20px;
    text-align: center;
    color: #6b7280;
    font-size: 13px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .users-wrapper {
        padding: 20px 12px 30px;
    }

    .users-page-header {
        align-items: flex-start;
        gap: 12px;
    }

    .users-page-title {
        font-size: 20px;
    }

    .users-card {
        overflow-x: auto;
    }

    .users-table {
        min-width: 750px;
    }

}

</style>


<div class="users-wrapper">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="users-page-header">

        <div>

            <h2 class="users-page-title">
                User Management
            </h2>

            <div class="users-page-subtitle">
                Manage system users and account access
            </div>

        </div>


        <a
            href="add.php"
            class="btn btn-primary add-user-btn"
        >
            + Add User
        </a>

    </div>



    <!-- =====================================================
         USERS TABLE
    ====================================================== -->

    <div class="users-card">


        <div class="users-card-header">

            <div class="users-card-title">
                System Users
            </div>

            <div class="users-card-subtitle">
                List of registered system accounts
            </div>

        </div>


        <div class="table-responsive">

            <table class="table users-table">

                <thead>

                    <tr>

                        <th>
                            Full Name
                        </th>

                        <th>
                            Username
                        </th>

                        <th>
                            Role
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Created
                        </th>

                        <th class="text-center">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                <?php if ($result->num_rows > 0): ?>

                    <?php while ($row = $result->fetch_assoc()): ?>

                        <tr>

                            <!-- FULL NAME -->

                            <td>

                                <div class="user-name">
                                    <?php
                                    echo htmlspecialchars(
                                        $row['full_name']
                                    );
                                    ?>
                                </div>

                            </td>


                            <!-- USERNAME -->

                            <td>

                                <div class="user-username">
                                    <?php
                                    echo htmlspecialchars(
                                        $row['username']
                                    );
                                    ?>
                                </div>

                            </td>


                            <!-- ROLE -->

                            <td>

                                <?php if ($row['role'] === 'Administrator'): ?>

                                    <span class="role-badge role-admin">
                                        Administrator
                                    </span>

                                <?php else: ?>

                                    <span class="role-badge role-staff">
                                        Staff
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUS -->

                            <td>

                                <?php if ($row['is_active'] == 1): ?>

                                    <span class="status-badge status-active">
                                        Active
                                    </span>

                                <?php else: ?>

                                    <span class="status-badge status-inactive">
                                        Inactive
                                    </span>

                                <?php endif; ?>

                            </td>


                            <!-- CREATED -->

                            <td>

                                <?php
                                echo date(
                                    "M d, Y",
                                    strtotime($row['created_at'])
                                );
                                ?>

                            </td>


                            <!-- ACTION -->

                            <td class="text-center">

                                <a
                                    href="edit.php?id=<?php echo $row['id']; ?>"
                                    class="btn btn-outline-primary edit-user-btn"
                                >
                                    Edit
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                <?php else: ?>

                    <tr>

                        <td
                            colspan="6"
                            class="users-empty"
                        >
                            No user accounts found.
                        </td>

                    </tr>

                <?php endif; ?>

                </tbody>

            </table>

        </div>

    </div>

</div>


<?php

require_once "../includes/footer.php";

?>