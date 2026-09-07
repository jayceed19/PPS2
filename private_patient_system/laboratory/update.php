<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   POST ONLY
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    die("Invalid request.");

}


/* =========================================================
   GET POST DATA
========================================================= */

$labId = isset($_POST['id'])
    ? intval($_POST['id'])
    : 0;

$testName = isset($_POST['test_name'])
    ? trim($_POST['test_name'])
    : "";

$result = isset($_POST['result'])
    ? trim($_POST['result'])
    : "";

$unit = isset($_POST['unit'])
    ? trim($_POST['unit'])
    : "";

$referenceRange = isset($_POST['reference_range'])
    ? trim($_POST['reference_range'])
    : "";


/* =========================================================
   VALIDATE ID
========================================================= */

if ($labId <= 0) {

    die("Invalid laboratory ID.");

}


/* =========================================================
   VALIDATE REQUIRED FIELDS
========================================================= */

if ($testName === "") {

    die("Laboratory test name is required.");

}


if ($result === "") {

    die("Laboratory result is required.");

}


/* =========================================================
   CLEAN TEXT
========================================================= */

function cleanText($value)
{

    $value = trim($value);

    $value = preg_replace(
        '/\s+/',
        ' ',
        $value
    );

    return strtoupper($value);

}


$testName = cleanText($testName);

$result = cleanText($result);

$unit = cleanText($unit);

$referenceRange = cleanText(
    $referenceRange
);


/* =========================================================
   GET EXISTING LABORATORY RECORD
========================================================= */

$checkSql = "
    SELECT
        id,
        patient_id,
        test_date
    FROM laboratory
    WHERE id = ?
    LIMIT 1
";


$checkStmt =
    $conn->prepare($checkSql);


if (!$checkStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$checkStmt->bind_param(
    "i",
    $labId
);


$checkStmt->execute();


$checkResult =
    $checkStmt->get_result();


$existingLab =
    $checkResult->fetch_assoc();


$checkStmt->close();


/* =========================================================
   CHECK RECORD
========================================================= */

if (!$existingLab) {

    $conn->close();

    die(
        "Laboratory result not found."
    );

}


$patientId =
    (int)$existingLab['patient_id'];


$testDate =
    $existingLab['test_date'];


/* =========================================================
   UPDATE LABORATORY RESULT
========================================================= */

$updateSql = "
    UPDATE laboratory
    SET
        test_name = ?,
        result = ?,
        unit = ?,
        reference_range = ?
    WHERE id = ?
    LIMIT 1
";


$updateStmt =
    $conn->prepare($updateSql);


if (!$updateStmt) {

    $conn->close();

    die(
        "Database error: " .
        $conn->error
    );

}


$updateStmt->bind_param(
    "ssssi",
    $testName,
    $result,
    $unit,
    $referenceRange,
    $labId
);


/* =========================================================
   EXECUTE UPDATE
========================================================= */

if (!$updateStmt->execute()) {

    $errorMessage =
        $updateStmt->error;

    $updateStmt->close();

    $conn->close();

    die(
        "Failed to update laboratory result: " .
        htmlspecialchars(
            $errorMessage
        )
    );

}


$updateStmt->close();

$conn->close();


/* =========================================================
   REDIRECT BACK TO RESULTS
========================================================= */

header(
    "Location: view.php?id=" .
    $patientId .
    "&date=" .
    urlencode($testDate) .
    "&updated=1"
);

exit;

?>