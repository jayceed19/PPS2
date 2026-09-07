<?php

require_once "../config/database.php";

$pageTitle = "Prescription History";
$pageSubtitle = "Prescription History";
$basePath = "../";
$activePage = "patients";


// =========================================================
// GET PATIENT ID
// =========================================================

$id = isset($_GET['id'])
    ? intval($_GET['id'])
    : 0;

if ($id <= 0) {
    die("Invalid patient ID.");
}


// =========================================================
// GET PATIENT
// =========================================================

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


// =========================================================
// FULL NAME
// =========================================================

$fullName = $patient['first_name'];

if (!empty($patient['middle_name'])) {
    $fullName .= " " . $patient['middle_name'];
}

$fullName .= " " . $patient['last_name'];


// =========================================================
// GET PRESCRIPTION HISTORY
// =========================================================
//
// FINAL RULE:
//
// 1 PATIENT + 1 DAY = 1 PRESCRIPTION
//
// All medicines saved on the same date for the same
// patient belong to the same prescription header.
//
// =========================================================

$prescription_sql = "
    SELECT
        ph.id AS prescription_header_id,
        ph.patient_id,
        ph.prescribed_date,
        ph.created_at,

        COALESCE(pa.medicine_count, 0) AS medicine_count,

        COALESCE(pa.medicines, '') AS medicines,

        latest_pr.consultation_id,

        c.visit_date,

        CASE

            WHEN c.id IS NOT NULL THEN (

                SELECT COUNT(*)

                FROM consultations c2

                WHERE c2.patient_id = c.patient_id

                AND (
                    c2.visit_date < c.visit_date

                    OR (
                        c2.visit_date = c.visit_date
                        AND c2.id <= c.id
                    )
                )

            )

            ELSE 0

        END AS consultation_number

    FROM prescription_headers ph

    LEFT JOIN (

        SELECT

            prescription_header_id,

            COUNT(*) AS medicine_count,

            GROUP_CONCAT(

                CONCAT(

                    pr.medicine_name,

                    CASE

                        WHEN TRIM(pr.strength) <> ''

                        THEN CONCAT(' ', pr.strength)

                        ELSE ''

                    END

                )

                ORDER BY pr.id ASC

                SEPARATOR ', '

            ) AS medicines,

            MAX(id) AS latest_prescription_row_id

        FROM prescriptions pr

        WHERE pr.prescription_header_id IS NOT NULL

        GROUP BY pr.prescription_header_id

    ) pa

        ON pa.prescription_header_id = ph.id

    LEFT JOIN prescriptions latest_pr

        ON latest_pr.id = pa.latest_prescription_row_id

    LEFT JOIN consultations c

        ON c.id = latest_pr.consultation_id

    WHERE ph.patient_id = ?

    ORDER BY

        ph.prescribed_date DESC,

        ph.id DESC
";


$prescription_stmt = $conn->prepare(
    $prescription_sql
);

if (!$prescription_stmt) {
    die("Database error: " . $conn->error);
}

$prescription_stmt->bind_param(
    "i",
    $id
);

$prescription_stmt->execute();

$prescriptions =
    $prescription_stmt->get_result();


// =========================================================
// TOTAL PRESCRIPTIONS
// =========================================================

$prescriptionCount =
    $prescriptions->num_rows;

?>

<?php

include __DIR__ . "/../includes/header.php";
include __DIR__ . "/../includes/navigation.php";
require_once "../config/auth.php";
?>

<style>

/* =========================================================
   MAIN
========================================================= */

.prescription-history-page {

    max-width: 1200px;

    margin: 0 auto;

}


/* =========================================================
   TOP ACTIONS
========================================================= */

.prescription-top-actions {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 12px;

    margin-bottom: 20px;

}


.prescription-top-left,
.prescription-top-right {

    display: flex;

    align-items: center;

    gap: 10px;

    flex-wrap: wrap;

}


/* =========================================================
   BACK BUTTON
========================================================= */

.prescription-back {

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


.prescription-back:hover {

    background: #f4f7fa;

    border-color: #1f4e78;

}


/* =========================================================
   PATIENT CARD
========================================================= */

.prescription-patient-card {

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 8px;

    padding: 22px;

    margin-bottom: 20px;

}


.prescription-patient-content {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

}


.prescription-patient-name {

    margin: 0 0 5px 0;

    color: #1f4e78;

    font-size: 25px;

    font-weight: 700;

}


.prescription-patient-id {

    color: #777;

    font-size: 13px;

}


.prescription-patient-id strong {

    color: #1f4e78;

}


/* =========================================================
   PRESCRIPTION CARD
========================================================= */

.prescription-card {

    background: #ffffff;

    border: 1px solid #e1e5e9;

    border-radius: 8px;

    overflow: hidden;

}


.prescription-card-header {

    padding: 17px 22px;

    border-bottom: 1px solid #e5e8eb;

    background: #ffffff;

}


.prescription-card-title {

    margin: 0;

    color: #1f4e78;

    font-size: 17px;

    font-weight: 700;

}


.prescription-card-body {

    padding: 22px;

}


/* =========================================================
   COUNT
========================================================= */

.prescription-history-count {

    margin: 0 0 15px 0;

    color: #777;

    font-size: 13px;

}


/* =========================================================
   TABLE CONTAINER
========================================================= */

.prescription-table-container {

    width: 100%;

    overflow-x: auto;

    border: 1px solid #e1e5e9;

    border-radius: 7px;

}


/* =========================================================
   TABLE
========================================================= */

.prescription-table {

    width: 100%;

    border-collapse: collapse;

    min-width: 1020px;

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


/* =========================================================
   PRESCRIPTION NUMBER
========================================================= */

.prescription-number {

    color: #1f4e78;

    font-weight: 700;

    white-space: nowrap;

}


/* =========================================================
   DATE
========================================================= */

.prescription-date-text {

    color: #1f4e78;

    font-weight: 600;

    white-space: nowrap;

}


/* =========================================================
   MEDICINES
========================================================= */

.prescription-medicines {

    max-width: 420px;

    line-height: 1.5;

    color: #333;

    font-weight: 600;

}


/* =========================================================
   MEDICINE COUNT
========================================================= */

.prescription-medicine-count {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 32px;

    height: 28px;

    padding: 0 8px;

    background: #f1f5f8;

    border: 1px solid #dce3e8;

    border-radius: 5px;

    color: #1f4e78;

    font-size: 12px;

    font-weight: 700;

}


/* =========================================================
   CONSULTATION
========================================================= */

.prescription-consultation {

    white-space: nowrap;

    color: #555;

    font-weight: 600;

}


/* =========================================================
   ACTIONS
========================================================= */

.prescription-actions {

    display: flex;

    align-items: center;

    gap: 7px;

    flex-wrap: nowrap;

}


/* =========================================================
   VIEW / PRINT BUTTON
========================================================= */

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


/* =========================================================
   MANAGE BUTTON
========================================================= */

.prescription-manage-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    height: 34px;

    min-height: 34px;

    padding: 0 13px;

    background: #198754;

    color: #ffffff;

    border: 1px solid #198754;

    border-radius: 5px;

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;

    white-space: nowrap;

    transition: 0.2s ease;

}


.prescription-manage-btn:hover {

    background: #157347;

    border-color: #157347;

}


/* =========================================================
   NO PRESCRIPTIONS
========================================================= */

.no-prescriptions {

    text-align: center;

    padding: 45px 20px;

    color: #777;

    font-size: 14px;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 700px) {

    .prescription-top-actions {

        flex-direction: column;

        align-items: stretch;

    }


    .prescription-top-left,
    .prescription-top-right {

        width: 100%;

        flex-direction: column;

        align-items: stretch;

    }


    .prescription-back {

        width: 100%;

    }


    .prescription-patient-content {

        flex-direction: column;

        align-items: flex-start;

    }


    .prescription-patient-card {

        padding: 18px;

    }


    .prescription-card-body {

        padding: 16px;

    }


    .prescription-card-header {

        padding: 16px;

    }


    .prescription-patient-name {

        font-size: 21px;

    }

}

</style>


<main class="main-container">

    <div class="prescription-history-page">


        <!-- =================================================
             TOP ACTIONS
        ================================================== -->

        <div class="prescription-top-actions">

            <div class="prescription-top-left">

                <a
                    href="../patients/view.php?id=<?php echo $id; ?>"
                    class="prescription-back"
                >
                    ← Back to Patient Profile
                </a>

            </div>

        </div>


        <!-- =================================================
             PATIENT INFORMATION
        ================================================== -->

        <div class="prescription-patient-card">

            <div class="prescription-patient-content">

                <div>

                    <h1 class="prescription-patient-name">

                        <?php
                        echo htmlspecialchars($fullName);
                        ?>

                    </h1>


                    <div class="prescription-patient-id">

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

            </div>

        </div>


        <!-- =================================================
             PRESCRIPTION HISTORY
        ================================================== -->

        <div class="prescription-card">


            <div class="prescription-card-header">

                <h2 class="prescription-card-title">

                    Prescription History

                </h2>

            </div>


            <div class="prescription-card-body">


                <?php if ($prescriptionCount > 0) { ?>


                    <div class="prescription-history-count">

                        <?php
                        echo $prescriptionCount;
                        ?>

                        prescription record(s)

                    </div>


                    <div class="prescription-table-container">


                        <table class="prescription-table">


                            <thead>

                                <tr>

                                    <th>
                                        Prescription
                                    </th>

                                    <th>
                                        Prescribed Date
                                    </th>

                                    <th>
                                        Medicines
                                    </th>

                                    <th>
                                        No. of Medicines
                                    </th>

                                    <th>
                                        Consultation
                                    </th>

                                    <th>
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                                <?php while (
                                    $prescription =
                                    $prescriptions->fetch_assoc()
                                ) { ?>


                                    <tr>


                                        <!-- =================================
                                             PRESCRIPTION NUMBER
                                        ================================== -->

                                        <td>

                                            <span class="prescription-number">

                                                <?php

                                                echo "RX-" .
                                                    str_pad(
                                                        (int)$prescription[
                                                            'prescription_header_id'
                                                        ],
                                                        6,
                                                        "0",
                                                        STR_PAD_LEFT
                                                    );

                                                ?>

                                            </span>

                                        </td>


                                        <!-- =================================
                                             PRESCRIBED DATE
                                        ================================== -->

                                        <td>

                                            <span class="prescription-date-text">

                                                <?php

                                                if (
                                                    !empty(
                                                        $prescription[
                                                            'prescribed_date'
                                                        ]
                                                    )
                                                ) {

                                                    echo htmlspecialchars(

                                                        date(

                                                            "M d, Y",

                                                            strtotime(

                                                                $prescription[
                                                                    'prescribed_date'
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


                                        <!-- =================================
                                             MEDICINES
                                        ================================== -->

                                        <td>

                                            <div class="prescription-medicines">

                                                <?php

                                                if (
                                                    !empty(
                                                        $prescription[
                                                            'medicines'
                                                        ]
                                                    )
                                                ) {

                                                    echo htmlspecialchars(
                                                        $prescription[
                                                            'medicines'
                                                        ]
                                                    );

                                                } else {

                                                    echo "-";

                                                }

                                                ?>

                                            </div>

                                        </td>


                                        <!-- =================================
                                             MEDICINE COUNT
                                        ================================== -->

                                        <td>

                                            <span
                                                class="prescription-medicine-count"
                                            >

                                                <?php

                                                echo (int)
                                                    $prescription[
                                                        'medicine_count'
                                                    ];

                                                ?>

                                            </span>

                                        </td>


                                        <!-- =================================
                                             CONSULTATION
                                        ================================== -->

                                        <td>

                                            <span
                                                class="prescription-consultation"
                                            >

                                                <?php

                                                if (
                                                    !empty(
                                                        $prescription[
                                                            'consultation_number'
                                                        ]
                                                    )
                                                ) {

                                                    echo "Consultation #" .
                                                        (int)
                                                        $prescription[
                                                            'consultation_number'
                                                        ];

                                                } else {

                                                    echo "-";

                                                }

                                                ?>

                                            </span>

                                        </td>


                                        <!-- =================================
                                             ACTIONS
                                        ================================== -->

                                        <td>

                                            <div class="prescription-actions">


                                                <!-- MANAGE -->

                                                <a
                                                    href="manage_prescription.php?prescription_header_id=<?php echo (int)$prescription['prescription_header_id']; ?>"
                                                    class="prescription-manage-btn"
                                                >

                                                    🔧 Manage

                                                </a>


                                                <!-- VIEW / PRINT -->

                                                <a
                                                    href="print_prescription.php?prescription_header_id=<?php echo (int)$prescription['prescription_header_id']; ?>&from_history=1"
                                                    class="prescription-view-btn"
                                                    target="_blank"
                                                >

                                                    View / Print

                                                </a>


                                            </div>

                                        </td>


                                    </tr>


                                <?php } ?>


                            </tbody>


                        </table>


                    </div>


                <?php } else { ?>


                    <div class="no-prescriptions">

                        No prescriptions recorded yet.

                    </div>


                <?php } ?>


            </div>


        </div>


    </div>

</main>


<?php

$prescription_stmt->close();

$conn->close();

include __DIR__ . "/../includes/footer.php";

?>