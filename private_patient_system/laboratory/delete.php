<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   POST ONLY
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    die("Invalid request.");

}


/* =========================================================
   GET LABORATORY ID
========================================================= */

$id = isset($_POST["id"])
    ? intval($_POST["id"])
    : 0;


if ($id <= 0) {

    die("Invalid laboratory result ID.");

}


/* =========================================================
   GET EXISTING LABORATORY RECORD
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        patient_id,
        test_date
    FROM laboratory
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Database error: " .
        $conn->error
    );

}


$stmt->bind_param(
    "i",
    $id
);


$stmt->execute();


$result =
    $stmt->get_result();


if ($result->num_rows === 0) {

    $stmt->close();
    $conn->close();

    die(
        "Laboratory result not found."
    );

}


$lab =
    $result->fetch_assoc();


$patientId =
    (int)$lab["patient_id"];


$testDate =
    $lab["test_date"];


$stmt->close();


/* =========================================================
   BEGIN TRANSACTION
========================================================= */

$conn->begin_transaction();


try {


    /* =====================================================
       CHECK HOW MANY RESULTS EXIST FOR THIS DATE
    ===================================================== */

    $countStmt = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM laboratory
        WHERE patient_id = ?
        AND test_date = ?
    ");


    if (!$countStmt) {

        throw new Exception(
            "Database error: " .
            $conn->error
        );

    }


    $countStmt->bind_param(
        "is",
        $patientId,
        $testDate
    );


    if (!$countStmt->execute()) {

        throw new Exception(
            "Failed to check laboratory results."
        );

    }


    $countResult =
        $countStmt->get_result();


    $countRow =
        $countResult->fetch_assoc();


    $totalResults =
        (int)$countRow["total"];


    $countStmt->close();


    /* =====================================================
       DELETE SELECTED LABORATORY RESULT
    ===================================================== */

    $deleteStmt = $conn->prepare("
        DELETE FROM laboratory
        WHERE id = ?
        LIMIT 1
    ");


    if (!$deleteStmt) {

        throw new Exception(
            "Database error: " .
            $conn->error
        );

    }


    $deleteStmt->bind_param(
        "i",
        $id
    );


    if (!$deleteStmt->execute()) {

        throw new Exception(
            "Failed to delete laboratory result: " .
            $deleteStmt->error
        );

    }


    /* =====================================================
       MAKE SURE RECORD WAS ACTUALLY DELETED
    ===================================================== */

    if ($deleteStmt->affected_rows !== 1) {

        $deleteStmt->close();

        throw new Exception(
            "Laboratory result could not be deleted."
        );

    }


    $deleteStmt->close();


    /* =====================================================
       COMMIT
    ===================================================== */

    $conn->commit();


    /* =====================================================
       REDIRECT
    ===================================================== */

    /*
       If this was the last result for the date,
       return to Laboratory History.
    */

    if ($totalResults <= 1) {

        header(
            "Location: index.php?id=" .
            $patientId .
            "&deleted=1"
        );

        exit;

    }


    /*
       Otherwise, remain on the same laboratory date.
    */

    header(
        "Location: view.php?id=" .
        $patientId .
        "&date=" .
        urlencode($testDate) .
        "&deleted=1"
    );

    exit;


}
catch (Exception $e) {


    /* =====================================================
       ROLLBACK
    ===================================================== */

    $conn->rollback();

    $conn->close();


    /* =====================================================
       ERROR
    ===================================================== */

    die(
        "Unable to delete laboratory result." .
        "<br><br>" .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}

?>