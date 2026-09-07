<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/* =========================================================
   ONLY POST REQUEST
========================================================= */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    die("Invalid request.");

}


/* =========================================================
   GET PATIENT ID
========================================================= */

$patientId = isset($_POST['patient_id'])
    ? intval($_POST['patient_id'])
    : 0;

if ($patientId <= 0) {

    die("Invalid patient ID.");

}


/* =========================================================
   GET LABORATORY DATE
========================================================= */

$testDate = isset($_POST['test_date'])
    ? trim($_POST['test_date'])
    : "";

if ($testDate === "") {

    die("Laboratory date is required.");

}


/* =========================================================
   VALIDATE DATE FORMAT
========================================================= */

$dateObject = DateTime::createFromFormat(
    'Y-m-d',
    $testDate
);

if (
    !$dateObject ||
    $dateObject->format('Y-m-d') !== $testDate
) {

    die("Invalid laboratory date.");

}


/* =========================================================
   GET COMMON TEST ARRAYS
========================================================= */

$testNames = isset($_POST['test_name'])
    ? $_POST['test_name']
    : array();

$results = isset($_POST['result'])
    ? $_POST['result']
    : array();

$units = isset($_POST['unit'])
    ? $_POST['unit']
    : array();

$referenceRanges = isset($_POST['reference_range'])
    ? $_POST['reference_range']
    : array();


/* =========================================================
   GET OTHER TEST ARRAYS
========================================================= */

$otherTestNames = isset($_POST['other_test_name'])
    ? $_POST['other_test_name']
    : array();

$otherResults = isset($_POST['other_result'])
    ? $_POST['other_result']
    : array();

$otherUnits = isset($_POST['other_unit'])
    ? $_POST['other_unit']
    : array();

$otherReferenceRanges = isset($_POST['other_reference_range'])
    ? $_POST['other_reference_range']
    : array();


/* =========================================================
   CLEAN TEXT FUNCTION
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


/* =========================================================
   VERIFY PATIENT
========================================================= */

$patientSql = "
    SELECT id
    FROM patients
    WHERE id = ?
    LIMIT 1
";

$patientStmt = $conn->prepare($patientSql);

if (!$patientStmt) {

    die(
        "Database error: " .
        $conn->error
    );

}

$patientStmt->bind_param(
    "i",
    $patientId
);

$patientStmt->execute();

$patientResult =
    $patientStmt->get_result();

if ($patientResult->num_rows === 0) {

    $patientStmt->close();
    $conn->close();

    die("Patient not found.");

}

$patientStmt->close();


/* =========================================================
   BEGIN TRANSACTION
========================================================= */

$conn->begin_transaction();


try {


    /* =====================================================
       INSERT STATEMENT
    ===================================================== */

    $insertSql = "
        INSERT INTO laboratory
        (
            patient_id,
            test_date,
            test_name,
            result,
            unit,
            reference_range,
            remarks
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";


    $insertStmt =
        $conn->prepare($insertSql);


    if (!$insertStmt) {

        throw new Exception(
            "Database error: " .
            $conn->error
        );

    }


    /* =====================================================
       SAVED COUNT
    ===================================================== */

    $savedCount = 0;


    /* =====================================================
       SAVE COMMON LABORATORY TESTS

       Every CBC parameter, lipid parameter,
       chemistry test, imaging test, etc.
       is saved as one record.

       Blank results are skipped.
    ===================================================== */

    if (is_array($testNames)) {


        $totalTests =
            count($testNames);


        for (
            $i = 0;
            $i < $totalTests;
            $i++
        ) {


            /* =============================================
               TEST NAME
            ============================================= */

            $testName =
                isset($testNames[$i])
                    ? cleanText($testNames[$i])
                    : "";


            /* =============================================
               RESULT
            ============================================= */

            $testResult =
                isset($results[$i])
                    ? cleanText($results[$i])
                    : "";


            /* =============================================
               UNIT
            ============================================= */

            $unit =
                isset($units[$i])
                    ? cleanText($units[$i])
                    : "";


            /* =============================================
               REFERENCE RANGE
            ============================================= */

            $referenceRange =
                isset($referenceRanges[$i])
                    ? cleanText($referenceRanges[$i])
                    : "";


            /* =============================================
               SKIP BLANK RESULT
            ============================================= */

            if ($testResult === "") {

                continue;

            }


            /* =============================================
               MAKE SURE TEST NAME EXISTS
            ============================================= */

            if ($testName === "") {

                continue;

            }


            /* =============================================
               REMARKS
            ============================================= */

            $testRemarks = "";


            /* =============================================
               INSERT COMMON TEST
            ============================================= */

            $insertStmt->bind_param(
                "issssss",
                $patientId,
                $testDate,
                $testName,
                $testResult,
                $unit,
                $referenceRange,
                $testRemarks
            );


            if (!$insertStmt->execute()) {

                throw new Exception(
                    "Failed to save laboratory test: " .
                    $testName .
                    "<br><br>" .
                    $insertStmt->error
                );

            }


            $savedCount++;

        }

    }


    /* =====================================================
       SAVE OTHER LABORATORY TESTS

       Multiple OTHER tests are supported.
    ===================================================== */

    if (is_array($otherTestNames)) {


        $totalOtherTests =
            count($otherTestNames);


        for (
            $i = 0;
            $i < $totalOtherTests;
            $i++
        ) {


            /* =============================================
               OTHER TEST NAME
            ============================================= */

            $otherName =
                isset($otherTestNames[$i])
                    ? cleanText($otherTestNames[$i])
                    : "";


            /* =============================================
               OTHER RESULT
            ============================================= */

            $otherResult =
                isset($otherResults[$i])
                    ? cleanText($otherResults[$i])
                    : "";


            /* =============================================
               OTHER UNIT
            ============================================= */

            $otherUnit =
                isset($otherUnits[$i])
                    ? cleanText($otherUnits[$i])
                    : "";


            /* =============================================
               OTHER REFERENCE RANGE
            ============================================= */

            $otherReferenceRange =
                isset($otherReferenceRanges[$i])
                    ? cleanText(
                        $otherReferenceRanges[$i]
                    )
                    : "";


            /* =============================================
               COMPLETELY BLANK ROW

               Do not save.
            ============================================= */

            if (
                $otherName === "" &&
                $otherResult === ""
            ) {

                continue;

            }


            /* =============================================
               RESULT WITHOUT TEST NAME
            ============================================= */

            if (
                $otherResult !== "" &&
                $otherName === ""
            ) {

                throw new Exception(
                    "An OTHER laboratory result was entered without a laboratory test name."
                );

            }


            /* =============================================
               TEST NAME WITHOUT RESULT

               Ignore the row.
            ============================================= */

            if (
                $otherName !== "" &&
                $otherResult === ""
            ) {

                continue;

            }


            /* =============================================
               REMARKS
            ============================================= */

            $otherRemarks = "";


            /* =============================================
               INSERT OTHER TEST
            ============================================= */

            $insertStmt->bind_param(
                "issssss",
                $patientId,
                $testDate,
                $otherName,
                $otherResult,
                $otherUnit,
                $otherReferenceRange,
                $otherRemarks
            );


            if (!$insertStmt->execute()) {

                throw new Exception(
                    "Failed to save OTHER laboratory test: " .
                    $otherName .
                    "<br><br>" .
                    $insertStmt->error
                );

            }


            $savedCount++;

        }

    }


    /* =====================================================
       CHECK IF SOMETHING WAS SAVED
    ===================================================== */

    if ($savedCount === 0) {

        throw new Exception(
            "No laboratory result was entered."
        );

    }


    /* =====================================================
       CLOSE STATEMENT
    ===================================================== */

    $insertStmt->close();


    /* =====================================================
       COMMIT
    ===================================================== */

    $conn->commit();


    /* =====================================================
       REDIRECT TO LABORATORY HISTORY
    ===================================================== */

    header(
        "Location: index.php?id=" .
        $patientId .
        "&saved=1&count=" .
        $savedCount
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
        "Unable to save laboratory results." .
        "<br><br>" .
        htmlspecialchars(
            $e->getMessage()
        )
    );

}

?>