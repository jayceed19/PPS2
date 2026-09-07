<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/*
|--------------------------------------------------------------------------
| Only Allow POST Request
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] != "POST") {

    header("Location: follow_up.php?type=due");
    exit;

}


/*
|--------------------------------------------------------------------------
| Get Submitted Values
|--------------------------------------------------------------------------
*/

$consultation_id = isset($_POST["consultation_id"])
    ? intval($_POST["consultation_id"])
    : 0;

$action = isset($_POST["action"])
    ? trim($_POST["action"])
    : "";

$type = isset($_POST["type"])
    ? trim($_POST["type"])
    : "due";


/*
|--------------------------------------------------------------------------
| Validate Consultation ID
|--------------------------------------------------------------------------
*/

if ($consultation_id <= 0) {

    header("Location: follow_up.php?type=due");
    exit;

}


/*
|--------------------------------------------------------------------------
| Determine New Follow-up Status
|--------------------------------------------------------------------------
*/

$new_status = "";


if ($action == "confirm") {

    $new_status = "CONFIRMED";

} elseif ($action == "cancel") {

    $new_status = "CANCELLED";

} elseif ($action == "complete") {

    $new_status = "COMPLETED";

} elseif ($action == "no_show") {

    $new_status = "NO SHOW";

} elseif ($action == "pending") {

    $new_status = "PENDING";

} else {

    header("Location: follow_up.php?type=due");
    exit;

}


/*
|--------------------------------------------------------------------------
| Update Follow-up Status
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE consultations
    SET follow_up_status = ?
    WHERE id = ?
    AND follow_up_date IS NOT NULL
";


$stmt = $conn->prepare($sql);


if ($stmt) {

    $stmt->bind_param(
        "si",
        $new_status,
        $consultation_id
    );

    $stmt->execute();

    $stmt->close();

}


/*
|--------------------------------------------------------------------------
| Determine Where To Return
|--------------------------------------------------------------------------
|
| After changing the status, return directly to the
| corresponding Follow-up tab.
|
*/


if ($new_status == "CONFIRMED") {

    $returnType = "confirmed";

} elseif ($new_status == "CANCELLED") {

    $returnType = "cancelled";

} elseif ($new_status == "COMPLETED") {

    $returnType = "completed";

} elseif ($new_status == "NO SHOW") {

    $returnType = "no_show";

} else {

    /*
    | PENDING records return to the tab they came from.
    */

    if (
        $type == "overdue" ||
        $type == "due" ||
        $type == "scheduled"
    ) {

        $returnType = $type;

    } else {

        $returnType = "due";

    }

}


/*
|--------------------------------------------------------------------------
| Redirect
|--------------------------------------------------------------------------
*/

header(
    "Location: follow_up.php?type=" . urlencode($returnType)
);

exit;

?>