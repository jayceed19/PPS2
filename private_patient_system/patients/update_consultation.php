<?php

require_once "../config/database.php";
require_once "../config/auth.php";

/*
|--------------------------------------------------------------------------
| HELPER FUNCTION
|--------------------------------------------------------------------------
*/

function cleanText($value)
{
    $value = trim($value);
    $value = preg_replace('/\s+/', ' ', $value);
    $value = strtoupper($value);

    return $value;
}


/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/

$consultation_id = isset($_POST['consultation_id'])
    ? intval($_POST['consultation_id'])
    : 0;

$visit_date = isset($_POST['visit_date'])
    ? trim($_POST['visit_date'])
    : '';

$chief_complaint = isset($_POST['chief_complaint'])
    ? cleanText($_POST['chief_complaint'])
    : '';

$other_chief_complaint = isset($_POST['other_chief_complaint'])
    ? cleanText($_POST['other_chief_complaint'])
    : '';

$history_illness = isset($_POST['history_illness'])
    ? cleanText($_POST['history_illness'])
    : '';

$blood_pressure = isset($_POST['blood_pressure'])
    ? trim($_POST['blood_pressure'])
    : '';

$temperature = isset($_POST['temperature']) && $_POST['temperature'] !== ''
    ? $_POST['temperature']
    : null;

$pulse_rate = isset($_POST['pulse_rate']) && $_POST['pulse_rate'] !== ''
    ? $_POST['pulse_rate']
    : null;

$respiratory_rate = isset($_POST['respiratory_rate']) && $_POST['respiratory_rate'] !== ''
    ? $_POST['respiratory_rate']
    : null;

$oxygen_saturation = isset($_POST['oxygen_saturation']) && $_POST['oxygen_saturation'] !== ''
    ? $_POST['oxygen_saturation']
    : null;

$weight = isset($_POST['weight']) && $_POST['weight'] !== ''
    ? $_POST['weight']
    : null;

$height = isset($_POST['height']) && $_POST['height'] !== ''
    ? $_POST['height']
    : null;

$assessment = isset($_POST['assessment'])
    ? cleanText($_POST['assessment'])
    : '';

$management = isset($_POST['management'])
    ? cleanText($_POST['management'])
    : '';

$follow_up_date = isset($_POST['follow_up_date']) && $_POST['follow_up_date'] !== ''
    ? $_POST['follow_up_date']
    : null;

$remarks = isset($_POST['remarks'])
    ? cleanText($_POST['remarks'])
    : '';


/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/

if ($consultation_id <= 0) {
    die("Error: Invalid consultation ID.");
}

if ($visit_date == '') {
    die("Error: Visit date is required.");
}

if ($chief_complaint == '') {
    die("Error: Chief complaint is required.");
}

if ($history_illness == '') {
    die("Error: History of present illness is required.");
}

if ($assessment == '') {
    die("Error: Assessment / Diagnosis is required.");
}


/*
|--------------------------------------------------------------------------
| OTHERS CHIEF COMPLAINT
|--------------------------------------------------------------------------
|
| Kapag OTHERS ang pinili sa consultation form,
| dapat may actual custom chief complaint.
|
| Halimbawa:
|
| Chief Complaint: OTHERS
| Other Chief Complaint: BACK PAIN
|
| Ang ise-save sa database:
|
| BACK PAIN
|
| Hindi literal na "OTHERS".
|
|--------------------------------------------------------------------------
*/

if ($chief_complaint == 'OTHERS') {

    if ($other_chief_complaint == '') {
        die("Error: Other Chief Complaint is required when OTHERS is selected.");
    }

    $chief_complaint = $other_chief_complaint;
}


/*
|--------------------------------------------------------------------------
| VALIDATE BLOOD PRESSURE
|--------------------------------------------------------------------------
*/

if ($blood_pressure != '') {

    if (!preg_match('/^[0-9]{2,3}\/[0-9]{2,3}$/', $blood_pressure)) {

        die(
            "Error: Blood pressure must be in the format 120/80."
        );

    }

}


/*
|--------------------------------------------------------------------------
| GET CONSULTATION + PATIENT
|--------------------------------------------------------------------------
*/

$check_sql = "
    SELECT
        c.id,
        c.patient_id,
        c.visit_date,

        p.patient_id AS patient_number,
        p.first_name,
        p.middle_name,
        p.last_name

    FROM consultations c

    INNER JOIN patients p
        ON c.patient_id = p.id

    WHERE c.id = ?
";


$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    die("Database error: " . $conn->error);
}


$check_stmt->bind_param(
    "i",
    $consultation_id
);


$check_stmt->execute();

$result = $check_stmt->get_result();


if ($result->num_rows == 0) {

    $check_stmt->close();

    die("Error: Consultation not found.");

}


$consultation = $result->fetch_assoc();

$check_stmt->close();


/*
|--------------------------------------------------------------------------
| PATIENT DATABASE ID
|--------------------------------------------------------------------------
*/

$patient_database_id = (int) $consultation['patient_id'];

$patient_number = $consultation['patient_number'];


/*
|--------------------------------------------------------------------------
| GET CONSULTATION NUMBER
|--------------------------------------------------------------------------
|
| Kinukuha kung pang-ilang consultation ito
| para sa particular na patient.
|
| Gumagamit ng ORIGINAL visit date para hindi
| magbago ang consultation number kapag
| in-edit ang consultation.
|
|--------------------------------------------------------------------------
*/

$number_sql = "
    SELECT COUNT(*) AS consultation_number

    FROM consultations

    WHERE patient_id = ?

    AND (
        visit_date < ?

        OR (
            visit_date = ?
            AND id <= ?
        )
    )
";


$number_stmt = $conn->prepare($number_sql);

if (!$number_stmt) {
    die("Database error: " . $conn->error);
}


/*
|--------------------------------------------------------------------------
| ORIGINAL VISIT DATE
|--------------------------------------------------------------------------
*/

$original_visit_date = $consultation['visit_date'];


$number_stmt->bind_param(
    "issi",
    $patient_database_id,
    $original_visit_date,
    $original_visit_date,
    $consultation_id
);


$number_stmt->execute();

$number_result = $number_stmt->get_result();

$number_row = $number_result->fetch_assoc();


$consultation_number = (int) $number_row['consultation_number'];


$number_stmt->close();


/*
|--------------------------------------------------------------------------
| UPDATE CONSULTATION
|--------------------------------------------------------------------------
|
| NOTE:
| Tinanggal na ang medication.
|
| Prescription module na ang gagamitin
| para sa medicines.
|
|--------------------------------------------------------------------------
*/

$sql = "
    UPDATE consultations SET

        visit_date = ?,
        chief_complaint = ?,
        history_illness = ?,
        blood_pressure = ?,
        temperature = ?,
        pulse_rate = ?,
        respiratory_rate = ?,
        oxygen_saturation = ?,
        weight = ?,
        height = ?,
        assessment = ?,
        management = ?,
        follow_up_date = ?,
        remarks = ?

    WHERE id = ?
";


$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}


/*
|--------------------------------------------------------------------------
| BIND VALUES
|--------------------------------------------------------------------------
*/

$stmt->bind_param(
    "ssssssssssssssi",
    $visit_date,
    $chief_complaint,
    $history_illness,
    $blood_pressure,
    $temperature,
    $pulse_rate,
    $respiratory_rate,
    $oxygen_saturation,
    $weight,
    $height,
    $assessment,
    $management,
    $follow_up_date,
    $remarks,
    $consultation_id
);


/*
|--------------------------------------------------------------------------
| EXECUTE UPDATE
|--------------------------------------------------------------------------
*/

if (!$stmt->execute()) {

    die(
        "Error updating consultation: " .
        $stmt->error
    );

}


$stmt->close();


/*
|--------------------------------------------------------------------------
| FULL NAME
|--------------------------------------------------------------------------
*/

$fullName = $consultation['first_name'];


if (!empty($consultation['middle_name'])) {

    $fullName .=
        " " .
        $consultation['middle_name'];

}


$fullName .=
    " " .
    $consultation['last_name'];


/*
|--------------------------------------------------------------------------
| CLOSE DATABASE
|--------------------------------------------------------------------------
*/

$conn->close();

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <link
        rel="icon"
        type="image/png"
        href="../asset/images/DCMDLOGO.png?v=1"
    >

    <title>
        Consultation Updated
    </title>


    <style>

        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }


        body {

            margin: 0;

            background: #f4f6f9;

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

        }


        .box {

            width: 450px;

            max-width: 90%;

            background: white;

            padding: 40px;

            border-radius: 12px;

            text-align: center;

            box-shadow:
                0 5px 20px
                rgba(0,0,0,0.10);

        }


        h2 {

            color: #1f4e78;

            margin-bottom: 10px;

        }


        .success {

            color: #198754;

            font-weight: bold;

        }


        .consultation-label {

            font-size: 13px;

            color: #777;

            margin-top: 25px;

            margin-bottom: 5px;

        }


        .consultation-id {

            font-size: 36px;

            font-weight: bold;

            color: #1f4e78;

            margin: 5px 0 20px;

        }


        .patient-name {

            font-size: 18px;

            font-weight: bold;

            margin-bottom: 10px;

        }


        .patient-number {

            color: #777;

            margin-bottom: 25px;

        }


        .buttons {

            display: flex;

            justify-content: center;

            flex-wrap: wrap;

            gap: 10px;

        }


        a {

            display: inline-block;

            padding: 12px 18px;

            border-radius: 6px;

            background: #1f4e78;

            color: white;

            text-decoration: none;

            font-size: 13px;

            font-weight: bold;

        }


        a:hover {

            background: #173a5c;

        }


        .secondary {

            background: #6c757d;

        }


        .secondary:hover {

            background: #565e64;

        }

    </style>

</head>


<body>


<div class="box">


    <h2>

        Consultation Updated!

    </h2>


    <p class="success">

        Consultation record has been
        updated successfully.

    </p>


    <div class="consultation-label">

        Consultation No.

    </div>


    <div class="consultation-id">

        #

        <?php

        echo htmlspecialchars(
            $consultation_number
        );

        ?>

    </div>


    <div class="patient-name">

        <?php

        echo htmlspecialchars(
            $fullName
        );

        ?>

    </div>


    <div class="patient-number">

        Patient ID:

        <strong>

            <?php

            echo htmlspecialchars(
                $patient_number
            );

            ?>

        </strong>

    </div>


    <div class="buttons">


        <a
            href="consultation_view.php?id=<?php echo urlencode($consultation_id); ?>"
        >

            View Consultation

        </a>


        <a
            href="view.php?id=<?php echo urlencode($patient_database_id); ?>"
            class="secondary"
        >

            View Patient Profile

        </a>


    </div>


</div>


</body>

</html>