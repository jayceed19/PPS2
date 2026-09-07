<?php

require_once "../config/database.php";
require_once "../config/auth.php";
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../dashboard.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET IDS
|--------------------------------------------------------------------------
*/

$consultationId = isset($_POST["consultation_id"])
    ? (int) $_POST["consultation_id"]
    : 0;

$patientId = isset($_POST["patient_id"])
    ? (int) $_POST["patient_id"]
    : 0;


if ($consultationId <= 0 || $patientId <= 0) {
    die("Invalid consultation or patient ID.");
}


/*
|--------------------------------------------------------------------------
| VERIFY CONSULTATION
|--------------------------------------------------------------------------
*/

$checkSql = "
    SELECT id, patient_id
    FROM consultations
    WHERE id = ?
    LIMIT 1
";

$checkStmt = $conn->prepare($checkSql);

if (!$checkStmt) {
    die("Database error: " . $conn->error);
}

$checkStmt->bind_param(
    "i",
    $consultationId
);

$checkStmt->execute();

$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows === 0) {

    $checkStmt->close();

    die("Consultation not found.");
}

$checkData = $checkResult->fetch_assoc();

$checkStmt->close();


/*
|--------------------------------------------------------------------------
| VERIFY PATIENT
|--------------------------------------------------------------------------
*/

if ((int)$checkData["patient_id"] !== $patientId) {
    die("Patient does not match the selected consultation.");
}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$medicineNames = isset($_POST["medicine_name"])
    ? $_POST["medicine_name"]
    : array();

$strengths = isset($_POST["strength"])
    ? $_POST["strength"]
    : array();

$quantities = isset($_POST["quantity"])
    ? $_POST["quantity"]
    : array();

$breakfasts = isset($_POST["breakfast"])
    ? $_POST["breakfast"]
    : array();

$lunches = isset($_POST["lunch"])
    ? $_POST["lunch"]
    : array();

$dinners = isset($_POST["dinner"])
    ? $_POST["dinner"]
    : array();


/*
|--------------------------------------------------------------------------
| VALIDATE MEDICINE
|--------------------------------------------------------------------------
*/

if (
    !is_array($medicineNames) ||
    count($medicineNames) === 0
) {
    die("Please enter at least one medicine.");
}


/*
|--------------------------------------------------------------------------
| PRESCRIPTION DATE
|--------------------------------------------------------------------------
|
| ONE PATIENT + ONE DATE = ONE PRESCRIPTION
|
|--------------------------------------------------------------------------
*/

$prescribedDate = date("Y-m-d");


/*
|--------------------------------------------------------------------------
| START TRANSACTION
|--------------------------------------------------------------------------
*/

$conn->begin_transaction();

$headerId = 0;
$savedCount = 0;


/*
|--------------------------------------------------------------------------
| FIND EXISTING PRESCRIPTION HEADER
|--------------------------------------------------------------------------
*/

$headerSelectSql = "
    SELECT id
    FROM prescription_headers
    WHERE patient_id = ?
      AND prescribed_date = ?
    LIMIT 1
    FOR UPDATE
";

$headerSelectStmt = $conn->prepare(
    $headerSelectSql
);

if (!$headerSelectStmt) {

    $conn->rollback();

    die(
        "Database error: " .
        $conn->error
    );
}

$headerSelectStmt->bind_param(
    "is",
    $patientId,
    $prescribedDate
);

if (!$headerSelectStmt->execute()) {

    $headerSelectStmt->close();

    $conn->rollback();

    die(
        "Unable to check today's prescription."
    );
}

$headerResult =
    $headerSelectStmt->get_result();


/*
|--------------------------------------------------------------------------
| USE EXISTING HEADER
|--------------------------------------------------------------------------
*/

if ($headerResult->num_rows > 0) {

    $headerData =
        $headerResult->fetch_assoc();

    $headerId =
        (int)$headerData["id"];

}


/*
|--------------------------------------------------------------------------
| CREATE NEW HEADER
|--------------------------------------------------------------------------
*/

else {

    $headerInsertSql = "
        INSERT INTO prescription_headers
        (
            patient_id,
            prescribed_date
        )
        VALUES
        (
            ?,
            ?
        )
    ";

    $headerInsertStmt =
        $conn->prepare(
            $headerInsertSql
        );

    if (!$headerInsertStmt) {

        $headerSelectStmt->close();

        $conn->rollback();

        die(
            "Database error: " .
            $conn->error
        );
    }


    $headerInsertStmt->bind_param(
        "is",
        $patientId,
        $prescribedDate
    );


    if (!$headerInsertStmt->execute()) {

        /*
        |--------------------------------------------------------------------------
        | TRY EXISTING HEADER AGAIN
        |--------------------------------------------------------------------------
        */

        $retrySql = "
            SELECT id
            FROM prescription_headers
            WHERE patient_id = ?
              AND prescribed_date = ?
            LIMIT 1
        ";

        $retryStmt =
            $conn->prepare(
                $retrySql
            );

        if (!$retryStmt) {

            $headerInsertStmt->close();
            $headerSelectStmt->close();

            $conn->rollback();

            die(
                "Unable to find today's prescription."
            );
        }


        $retryStmt->bind_param(
            "is",
            $patientId,
            $prescribedDate
        );


        $retryStmt->execute();

        $retryResult =
            $retryStmt->get_result();


        if ($retryResult->num_rows > 0) {

            $retryData =
                $retryResult->fetch_assoc();

            $headerId =
                (int)$retryData["id"];

        } else {

            $error =
                $headerInsertStmt->error;

            $retryStmt->close();
            $headerInsertStmt->close();
            $headerSelectStmt->close();

            $conn->rollback();

            die(
                "Unable to create prescription: " .
                $error
            );
        }


        $retryStmt->close();

    } else {

        $headerId =
            (int)$conn->insert_id;
    }


    $headerInsertStmt->close();
}


$headerSelectStmt->close();


/*
|--------------------------------------------------------------------------
| VERIFY HEADER ID
|--------------------------------------------------------------------------
*/

if ($headerId <= 0) {

    $conn->rollback();

    die(
        "Unable to create or find today's prescription."
    );
}


/*
|--------------------------------------------------------------------------
| PREPARE MEDICINE INSERT
|--------------------------------------------------------------------------
|
| We only insert the fields used by the new prescription form.
|
| Old columns:
| dosage
| frequency
| duration
| instructions
|
| are NOT included here.
|
| Their database columns can remain untouched.
|
|--------------------------------------------------------------------------
*/

$insertSql = "
    INSERT INTO prescriptions
    (
        prescription_header_id,
        consultation_id,
        patient_id,
        medicine_name,
        strength,
        quantity,
        breakfast,
        lunch,
        dinner,
        prescribed_date
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?
    )
";


$insertStmt = $conn->prepare(
    $insertSql
);

if (!$insertStmt) {

    $conn->rollback();

    die(
        "Database error: " .
        $conn->error
    );
}


/*
|--------------------------------------------------------------------------
| SAVE EACH MEDICINE
|--------------------------------------------------------------------------
*/

for (
    $i = 0;
    $i < count($medicineNames);
    $i++
) {


    /*
    |--------------------------------------------------------------------------
    | MEDICINE NAME
    |--------------------------------------------------------------------------
    */

    $medicineName = isset(
        $medicineNames[$i]
    )
        ? trim($medicineNames[$i])
        : "";


    /*
    |--------------------------------------------------------------------------
    | SKIP EMPTY ROW
    |--------------------------------------------------------------------------
    */

    if ($medicineName === "") {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | STRENGTH
    |--------------------------------------------------------------------------
    */

    $strength = isset(
        $strengths[$i]
    )
        ? trim($strengths[$i])
        : "";


    /*
    |--------------------------------------------------------------------------
    | QUANTITY
    |--------------------------------------------------------------------------
    */

    $quantityRaw = isset(
        $quantities[$i]
    )
        ? trim($quantities[$i])
        : "";


    if ($quantityRaw === "") {

        $quantity = null;

    } else {

        if (!ctype_digit($quantityRaw)) {

            $insertStmt->close();

            $conn->rollback();

            $conn->close();

            die(
                "Quantity must contain numbers only."
            );
        }


        $quantity = (int)$quantityRaw;


        if ($quantity < 1) {

            $insertStmt->close();

            $conn->rollback();

            $conn->close();

            die(
                "Quantity must be at least 1."
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BREAKFAST
    |--------------------------------------------------------------------------
    */

    $breakfast = isset(
        $breakfasts[$i]
    )
        ? trim($breakfasts[$i])
        : "";


    /*
    |--------------------------------------------------------------------------
    | LUNCH
    |--------------------------------------------------------------------------
    */

    $lunch = isset(
        $lunches[$i]
    )
        ? trim($lunches[$i])
        : "";


    /*
    |--------------------------------------------------------------------------
    | DINNER
    |--------------------------------------------------------------------------
    */

    $dinner = isset(
        $dinners[$i]
    )
        ? trim($dinners[$i])
        : "";


    /*
    |--------------------------------------------------------------------------
    | BIND PARAMETERS
    |--------------------------------------------------------------------------
    |
    | TOTAL = 10 VARIABLES
    |
    | 1  headerId
    | 2  consultationId
    | 3  patientId
    | 4  medicineName
    | 5  strength
    | 6  quantity
    | 7  breakfast
    | 8  lunch
    | 9  dinner
    | 10 prescribedDate
    |
    | Types:
    |
    | i i i s s i s s s s
    |
    | = "iiississss"
    |
    |--------------------------------------------------------------------------
    */

    $insertStmt->bind_param(
        "iiississss",
        $headerId,
        $consultationId,
        $patientId,
        $medicineName,
        $strength,
        $quantity,
        $breakfast,
        $lunch,
        $dinner,
        $prescribedDate
    );


    /*
    |--------------------------------------------------------------------------
    | EXECUTE
    |--------------------------------------------------------------------------
    */

    if (!$insertStmt->execute()) {

        $error =
            $insertStmt->error;

        $insertStmt->close();

        $conn->rollback();

        $conn->close();

        die(
            "Unable to save prescription: " .
            $error
        );
    }


    $savedCount++;
}


/*
|--------------------------------------------------------------------------
| CHECK SAVED MEDICINE
|--------------------------------------------------------------------------
*/

if ($savedCount <= 0) {

    $insertStmt->close();

    $conn->rollback();

    $conn->close();

    die(
        "Please enter at least one medicine."
    );
}


/*
|--------------------------------------------------------------------------
| COMMIT
|--------------------------------------------------------------------------
*/

$conn->commit();

$insertStmt->close();

$conn->close();


/*
|--------------------------------------------------------------------------
| REDIRECT TO PRINT
|--------------------------------------------------------------------------
*/

header(
    "Location: print_prescription.php?prescription_header_id=" .
    $headerId
);

exit;

?>