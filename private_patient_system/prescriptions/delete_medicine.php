<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   GET FORM VALUES
   Supports POST from the Delete form.
========================================================= */

$medicineId = 0;
$headerId = 0;
$patientId = 0;


/* =========================================================
   GET VALUES FROM POST
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $medicineId = isset($_POST["id"])
        ? (int) $_POST["id"]
        : 0;

    $headerId = isset($_POST["prescription_header_id"])
        ? (int) $_POST["prescription_header_id"]
        : 0;

    $patientId = isset($_POST["patient_id"])
        ? (int) $_POST["patient_id"]
        : 0;

}


/* =========================================================
   ALSO SUPPORT GET
   This prevents the page from going to dashboard if the
   current Manage page is still using an old link.
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "GET") {

    $medicineId = isset($_GET["id"])
        ? (int) $_GET["id"]
        : 0;

    $headerId = isset($_GET["prescription_header_id"])
        ? (int) $_GET["prescription_header_id"]
        : 0;

    $patientId = isset($_GET["patient_id"])
        ? (int) $_GET["patient_id"]
        : 0;

}


/* =========================================================
   VALIDATE MEDICINE ID
========================================================= */

if ($medicineId <= 0) {

    die(
        "Invalid medicine ID."
    );

}


/* =========================================================
   FIND THE MEDICINE
   We get the real header and patient directly from the
   database so the delete is tied to the correct record.
========================================================= */

$findSql = "
    SELECT
        id,
        prescription_header_id,
        patient_id

    FROM prescriptions

    WHERE id = ?

    LIMIT 1
";


$findStmt =
    $conn->prepare(
        $findSql
    );


if (!$findStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$findStmt->bind_param(
    "i",
    $medicineId
);


$findStmt->execute();


$findResult =
    $findStmt->get_result();


if ($findResult->num_rows == 0) {

    $findStmt->close();
    $conn->close();

    die(
        "Medicine record not found."
    );

}


$medicineData =
    $findResult->fetch_assoc();


$findStmt->close();


/* =========================================================
   USE ACTUAL DATABASE VALUES
========================================================= */

$realHeaderId =
    (int) $medicineData["prescription_header_id"];

$realPatientId =
    (int) $medicineData["patient_id"];


/*
    If the values from the page are available,
    they must match the actual record.
*/

if (
    $headerId > 0 &&
    $headerId != $realHeaderId
) {

    $conn->close();

    die(
        "Invalid prescription information."
    );

}


if (
    $patientId > 0 &&
    $patientId != $realPatientId
) {

    $conn->close();

    die(
        "Invalid patient information."
    );

}


/* =========================================================
   USE ACTUAL VALUES
========================================================= */

$headerId =
    $realHeaderId;

$patientId =
    $realPatientId;


/* =========================================================
   DELETE MEDICINE
========================================================= */

$deleteSql = "
    DELETE FROM prescriptions

    WHERE id = ?

    AND prescription_header_id = ?

    AND patient_id = ?

    LIMIT 1
";


$deleteStmt =
    $conn->prepare(
        $deleteSql
    );


if (!$deleteStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$deleteStmt->bind_param(
    "iii",
    $medicineId,
    $headerId,
    $patientId
);


if (!$deleteStmt->execute()) {

    $error =
        $deleteStmt->error;

    $deleteStmt->close();
    $conn->close();

    die(
        "Unable to delete medicine: " .
        htmlspecialchars($error)
    );

}


$deleteStmt->close();


/* =========================================================
   CHECK IF THERE ARE STILL MEDICINES
========================================================= */

$countSql = "
    SELECT
        COUNT(*) AS medicine_count

    FROM prescriptions

    WHERE prescription_header_id = ?
";


$countStmt =
    $conn->prepare(
        $countSql
    );


$medicineCount = 0;


if ($countStmt) {

    $countStmt->bind_param(
        "i",
        $headerId
    );

    $countStmt->execute();

    $countResult =
        $countStmt->get_result();

    $countData =
        $countResult->fetch_assoc();

    if (isset($countData["medicine_count"])) {

        $medicineCount =
            (int) $countData["medicine_count"];

    }

    $countStmt->close();

}


/* =========================================================
   IF NO MEDICINES REMAIN
   DELETE EMPTY PRESCRIPTION HEADER
========================================================= */

if ($medicineCount === 0) {

    $deleteHeaderSql = "
        DELETE FROM prescription_headers

        WHERE id = ?

        AND patient_id = ?

        LIMIT 1
    ";


    $deleteHeaderStmt =
        $conn->prepare(
            $deleteHeaderSql
        );


    if ($deleteHeaderStmt) {

        $deleteHeaderStmt->bind_param(
            "ii",
            $headerId,
            $patientId
        );


        $deleteHeaderStmt->execute();


        $deleteHeaderStmt->close();

    }


    /*
        Since the prescription no longer has medicines,
        return to Prescription History.
    */

    $conn->close();


    header(
        "Location: history.php?id=" .
        $patientId
    );

    exit;

}


/* =========================================================
   CLOSE DATABASE
========================================================= */

$conn->close();


/* =========================================================
   RETURN TO THE SAME MANAGE PRESCRIPTION PAGE
========================================================= */

header(
    "Location: manage_prescription.php?prescription_header_id=" .
    $headerId
);

exit;

?>