<?php

require_once "../config/database.php";


/* =========================================================
   GET PRESCRIPTION HEADER ID
========================================================= */

$headerId = isset($_GET["prescription_header_id"])
    ? (int) $_GET["prescription_header_id"]
    : 0;

if ($headerId <= 0) {
    die("Invalid prescription ID.");
}


/* =========================================================
   GET PRESCRIPTION HEADER + PATIENT
========================================================= */

$sql = "
    SELECT
        ph.id AS prescription_header_id,
        ph.patient_id,
        ph.prescribed_date,

        p.id AS patient_database_id,
        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name,
        p.birthdate,
        p.sex,
        p.address,
        p.contact_no

    FROM prescription_headers ph

    INNER JOIN patients p
        ON ph.patient_id = p.id

    WHERE ph.id = ?

    LIMIT 1
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


$stmt->bind_param(
    "i",
    $headerId
);


$stmt->execute();


$result = $stmt->get_result();


if ($result->num_rows == 0) {

    $stmt->close();
    $conn->close();

    die("Prescription not found.");
}


$prescription = $result->fetch_assoc();


$stmt->close();


/* =========================================================
   PATIENT INFORMATION
========================================================= */

$patientId =
    (int) $prescription["patient_id"];


$fullName =
    $prescription["first_name"];


if (
    !empty(
        $prescription["middle_name"]
    )
) {

    $fullName .=
        " " .
        $prescription["middle_name"];

}


$fullName .=
    " " .
    $prescription["last_name"];


$fullName =
    trim($fullName);


/* =========================================================
   AGE
========================================================= */

$age = "";


if (
    !empty(
        $prescription["birthdate"]
    )
) {

    try {

        $birthDate =
            new DateTime(
                $prescription["birthdate"]
            );

        $prescriptionDate =
            new DateTime(
                $prescription["prescribed_date"]
            );

        $age =
            $birthDate
                ->diff(
                    $prescriptionDate
                )
                ->y;

    } catch (Exception $e) {

        $age = "";

    }

}


/* =========================================================
   FORMAT DATE
========================================================= */

$prescribedDateFormatted = "-";


if (
    !empty(
        $prescription["prescribed_date"]
    )
) {

    $prescribedDateFormatted =
        date(
            "F d, Y",
            strtotime(
                $prescription["prescribed_date"]
            )
        );

}


/* =========================================================
   GET MEDICINES
========================================================= */

$medicineSql = "
    SELECT
        id,
        consultation_id,
        medicine_name,
        strength,
        quantity,
        breakfast,
        lunch,
        dinner,
        prescribed_date

    FROM prescriptions

    WHERE prescription_header_id = ?

    ORDER BY id ASC
";


$medicineStmt =
    $conn->prepare(
        $medicineSql
    );


if (!$medicineStmt) {
    die(
        "Database error: " .
        $conn->error
    );
}


$medicineStmt->bind_param(
    "i",
    $headerId
);


$medicineStmt->execute();


$medicineResult =
    $medicineStmt->get_result();


$medicines = array();


while (
    $medicine =
    $medicineResult->fetch_assoc()
) {

    $medicines[] =
        $medicine;

}


$medicineStmt->close();


/* =========================================================
   PAGE SETTINGS
========================================================= */

$pageTitle =
    "Manage Prescription";

$pageSubtitle =
    "Prescription Management";

$basePath =
    "../";

$activePage =
    "patients";


include "../includes/header.php";

include "../includes/navigation.php";
require_once "../config/auth.php";
?>

<style>

/* =========================================================
   MAIN CONTAINER
========================================================= */

.manage-prescription-page {

    max-width: 1200px;

    margin: 30px auto;

    padding:
        0 20px 40px;

}


/* =========================================================
   TOP
========================================================= */

.page-top {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 20px;

    flex-wrap: wrap;

}


.page-top h2 {

    margin: 0;

    color: #1f4e78;

    font-size: 26px;

}


.page-top p {

    margin:
        5px 0 0;

    color: #666;

    font-size: 14px;

}


/* =========================================================
   BUTTONS
========================================================= */

.btn {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    padding:
        9px 15px;

    border-radius:
        6px;

    text-decoration:
        none;

    border:
        none;

    cursor:
        pointer;

    font-size:
        13px;

    font-weight:
        600;

    transition:
        0.2s ease;

}


.btn-secondary {

    background:
        #6c757d;

    color:
        white;

}


.btn-secondary:hover {

    background:
        #5a6268;

}


.btn-primary {

    background:
        #1f4e78;

    color:
        white;

}


.btn-primary:hover {

    background:
        #173a5c;

}


.btn-warning {

    background:
        #f0ad4e;

    color:
        white;

}


.btn-warning:hover {

    background:
        #ec971f;

}


.btn-danger {

    background:
        #dc3545;

    color:
        white;

}


.btn-danger:hover {

    background:
        #bb2d3b;

}


/* =========================================================
   PATIENT CARD
========================================================= */

.patient-card {

    background:
        white;

    border:
        1px solid #d9e2ec;

    border-radius:
        10px;

    padding:
        22px;

    margin-bottom:
        20px;

    box-shadow:
        0 2px 8px
        rgba(0,0,0,0.05);

}


.patient-card-title {

    color:
        #1f4e78;

    font-size:
        18px;

    font-weight:
        700;

    margin-bottom:
        15px;

    border-bottom:
        2px solid #1f4e78;

    padding-bottom:
        8px;

}


.patient-grid {

    display:
        grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap:
        15px 20px;

}


.info-item {

    min-width:
        0;

}


.info-label {

    display:
        block;

    font-size:
        11px;

    color:
        #777;

    margin-bottom:
        4px;

    font-weight:
        600;

    text-transform:
        uppercase;

}


.info-value {

    font-size:
        14px;

    color:
        #222;

    word-break:
        break-word;

}


/* =========================================================
   PRESCRIPTION CARD
========================================================= */

.prescription-card {

    background:
        white;

    border:
        1px solid #d9e2ec;

    border-radius:
        10px;

    padding:
        22px;

    box-shadow:
        0 2px 8px
        rgba(0,0,0,0.05);

}


.prescription-header {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        15px;

    margin-bottom:
        20px;

    padding-bottom:
        15px;

    border-bottom:
        1px solid #e1e5e9;

}


.prescription-title {

    margin:
        0;

    color:
        #1f4e78;

    font-size:
        20px;

    font-weight:
        700;

}


.prescription-date {

    color:
        #666;

    font-size:
        13px;

}


/* =========================================================
   NOTICE
========================================================= */

.notice {

    background:
        #eef6ff;

    border-left:
        4px solid #1f4e78;

    padding:
        12px 15px;

    border-radius:
        5px;

    color:
        #444;

    font-size:
        13px;

    margin-bottom:
        20px;

    line-height:
        1.5;

}


/* =========================================================
   MEDICINE TABLE
========================================================= */

.table-container {

    width:
        100%;

    overflow-x:
        auto;

    border:
        1px solid #d9e2ec;

    border-radius:
        8px;

}


.medicine-table {

    width:
        100%;

    border-collapse:
        collapse;

    min-width:
        900px;

}


.medicine-table th {

    padding:
        12px 10px;

    background:
        #f5f7f9;

    border-bottom:
        1px solid #dfe4e8;

    color:
        #5f6972;

    font-size:
        11px;

    font-weight:
        700;

    text-transform:
        uppercase;

    text-align:
        left;

    white-space:
        nowrap;

}


.medicine-table td {

    padding:
        12px 10px;

    border-bottom:
        1px solid #edf0f2;

    color:
        #333;

    font-size:
        13px;

    vertical-align:
        middle;

}


.medicine-table tbody tr:last-child td {

    border-bottom:
        none;

}


.medicine-table tbody tr:hover {

    background:
        #fafbfd;

}


/* =========================================================
   MEDICINE NAME
========================================================= */

.medicine-name {

    color:
        #1f4e78;

    font-weight:
        700;

}


.medicine-strength {

    color:
        #555;

    font-weight:
        600;

}


.quantity {

    text-align:
        center;

    font-weight:
        700;

}


.meal-dose {

    text-align:
        center;

    white-space:
        nowrap;

}


/* =========================================================
   ACTIONS
========================================================= */

.action-buttons {

    display:
        flex;

    align-items:
        center;

    gap:
        6px;

    flex-wrap:
        nowrap;

}


.action-btn {

    display:
        inline-flex;

    align-items:
        center;

    justify-content:
        center;

    height:
        32px;

    padding:
        0 10px;

    border-radius:
        5px;

    text-decoration:
        none;

    border:
        none;

    cursor:
        pointer;

    font-size:
        12px;

    font-weight:
        600;

    white-space:
        nowrap;

}


.action-edit {

    background:
        #f0ad4e;

    color:
        white;

}


.action-edit:hover {

    background:
        #ec971f;

}


.action-delete {

    background:
        #dc3545;

    color:
        white;

}


.action-delete:hover {

    background:
        #bb2d3b;

}


/* =========================================================
   EMPTY
========================================================= */

.empty-message {

    text-align:
        center;

    padding:
        40px 20px;

    color:
        #777;

    font-size:
        14px;

    background:
        #fafbfc;

    border:
        1px dashed #d1d8de;

    border-radius:
        7px;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 900px) {

    .patient-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 600px) {

    .manage-prescription-page {

        padding:
            0 12px 30px;

    }


    .patient-grid {

        grid-template-columns:
            1fr;

    }


    .prescription-header {

        align-items:
            flex-start;

        flex-direction:
            column;

    }


    .prescription-header .btn {

        width:
            100%;

    }

}

</style>


<main class="main-container">

    <div class="manage-prescription-page">


        <!-- =====================================================
             PAGE TOP
        ====================================================== -->

        <div class="page-top">

            <div>

                <h2>
                    Manage Prescription
                </h2>

                <p>
                    Add, edit, or remove medicines from this prescription.
                </p>

            </div>


            <div>

                <a
                    href="history.php?id=<?php echo $patientId; ?>"
                    class="btn btn-secondary"
                >
                    ← Back to Prescription History
                </a>

            </div>

        </div>


        <!-- =====================================================
             PATIENT INFORMATION
        ====================================================== -->

        <div class="patient-card">

            <div class="patient-card-title">

                Patient Information

            </div>


            <div class="patient-grid">


                <div class="info-item">

                    <span class="info-label">
                        Patient ID
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $prescription["patient_number"]
                        );

                        ?>

                    </div>

                </div>


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


                <div class="info-item">

                    <span class="info-label">
                        Age / Sex
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $age
                        );


                        if (
                            $age !== ""
                            &&
                            !empty(
                                $prescription["sex"]
                            )
                        ) {

                            echo " / ";

                        }


                        echo htmlspecialchars(
                            $prescription["sex"]
                        );

                        ?>

                    </div>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Prescribed Date
                    </span>

                    <div class="info-value">

                        <?php

                        echo htmlspecialchars(
                            $prescribedDateFormatted
                        );

                        ?>

                    </div>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Contact No.
                    </span>

                    <div class="info-value">

                        <?php

                        echo !empty(
                            $prescription["contact_no"]
                        )
                            ? htmlspecialchars(
                                $prescription["contact_no"]
                            )
                            : "-";

                        ?>

                    </div>

                </div>


                <div class="info-item">

                    <span class="info-label">
                        Address
                    </span>

                    <div class="info-value">

                        <?php

                        echo !empty(
                            $prescription["address"]
                        )
                            ? htmlspecialchars(
                                $prescription["address"]
                            )
                            : "-";

                        ?>

                    </div>

                </div>


            </div>

        </div>


        <!-- =====================================================
             PRESCRIPTION
        ====================================================== -->

        <div class="prescription-card">


            <div class="prescription-header">

                <div>

                    <h3 class="prescription-title">

                        Prescription

                    </h3>


                    <div class="prescription-date">

                        RX-<?php

                        echo str_pad(
                            $headerId,
                            6,
                            "0",
                            STR_PAD_LEFT
                        );

                        ?>

                        &nbsp; • &nbsp;

                        <?php

                        echo htmlspecialchars(
                            $prescribedDateFormatted
                        );

                        ?>

                    </div>

                </div>


                <div>

                    <a
                        href="print_prescription.php?prescription_header_id=<?php echo $headerId; ?>&from_history=1"
                        class="btn btn-primary"
                        target="_blank"
                    >
                        🖨 View / Print
                    </a>

                </div>

            </div>


            <div class="notice">

                <strong>
                    1 Patient + 1 Day = 1 Prescription
                </strong>

                <br>

                Any medicine added here will remain part of this same prescription.

            </div>


            <?php if (count($medicines) > 0) { ?>


                <div class="table-container">


                    <table class="medicine-table">


                        <thead>

                            <tr>

                                <th>
                                    Medicine
                                </th>

                                <th>
                                    Qty
                                </th>

                                <th>
                                    Breakfast
                                </th>

                                <th>
                                    Lunch
                                </th>

                                <th>
                                    Dinner
                                </th>

                                <th>
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                            <?php foreach (
                                $medicines
                                as $medicine
                            ) { ?>


                                <tr>


                                    <!-- MEDICINE -->

                                    <td>

                                        <div class="medicine-name">

                                            <?php

                                            echo htmlspecialchars(
                                                $medicine[
                                                    "medicine_name"
                                                ]
                                            );

                                            ?>

                                        </div>


                                        <?php if (
                                            !empty(
                                                $medicine["strength"]
                                            )
                                        ) { ?>

                                            <div class="medicine-strength">

                                                <?php

                                                echo htmlspecialchars(
                                                    $medicine[
                                                        "strength"
                                                    ]
                                                );

                                                ?>

                                            </div>

                                        <?php } ?>


                                    </td>


                                    <!-- QTY -->

                                    <td class="quantity">

                                        <?php

                                        if (
                                            isset(
                                                $medicine["quantity"]
                                            )
                                            &&
                                            $medicine["quantity"] !== null
                                            &&
                                            $medicine["quantity"] !== ""
                                        ) {

                                            echo htmlspecialchars(
                                                $medicine["quantity"]
                                            );

                                        } else {

                                            echo "-";

                                        }

                                        ?>

                                    </td>


                                    <!-- BREAKFAST -->

                                    <td class="meal-dose">

                                        <?php

                                        echo (
                                            isset(
                                                $medicine["breakfast"]
                                            )
                                            &&
                                            trim(
                                                $medicine["breakfast"]
                                            ) !== ""
                                        )
                                            ? htmlspecialchars(
                                                $medicine["breakfast"]
                                            )
                                            : "-";

                                        ?>

                                    </td>


                                    <!-- LUNCH -->

                                    <td class="meal-dose">

                                        <?php

                                        echo (
                                            isset(
                                                $medicine["lunch"]
                                            )
                                            &&
                                            trim(
                                                $medicine["lunch"]
                                            ) !== ""
                                        )
                                            ? htmlspecialchars(
                                                $medicine["lunch"]
                                            )
                                            : "-";

                                        ?>

                                    </td>


                                    <!-- DINNER -->

                                    <td class="meal-dose">

                                        <?php

                                        echo (
                                            isset(
                                                $medicine["dinner"]
                                            )
                                            &&
                                            trim(
                                                $medicine["dinner"]
                                            ) !== ""
                                        )
                                            ? htmlspecialchars(
                                                $medicine["dinner"]
                                            )
                                            : "-";

                                        ?>

                                    </td>


                                    <!-- ACTION -->

                                    <td>

                                        <div class="action-buttons">


                                            <a
                                                href="edit_medicine.php?id=<?php echo (int)$medicine["id"]; ?>"
                                                class="action-btn action-edit"
                                            >
                                                ✏ Edit
                                            </a>


                                            <a
                                                href="delete_medicine.php?id=<?php echo (int)$medicine["id"]; ?>&prescription_header_id=<?php echo $headerId; ?>"
                                                class="action-btn action-delete"
                                                onclick="return confirm('Are you sure you want to delete this medicine?');"
                                            >
                                                🗑 Delete
                                            </a>


                                        </div>

                                    </td>


                                </tr>


                            <?php } ?>


                        </tbody>


                    </table>


                </div>


            <?php } else { ?>


                <div class="empty-message">

                    No medicines are currently recorded in this prescription.

                </div>


            <?php } ?>


        </div>


    </div>

</main>


<?php

$conn->close();

include "../includes/footer.php";

?>