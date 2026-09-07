<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


// =========================================================
// HELPER FUNCTION
// =========================================================

function cleanText($value)
{
    $value = trim($value);
    $value = preg_replace('/\s+/', ' ', $value);
    return strtoupper($value);
}


// =========================================================
// GET FORM DATA
// =========================================================

$patient_id = isset($_POST['patient_id'])
    ? intval($_POST['patient_id'])
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
    ? floatval($_POST['temperature'])
    : null;

$pulse_rate = isset($_POST['pulse_rate']) && $_POST['pulse_rate'] !== ''
    ? intval($_POST['pulse_rate'])
    : null;

$respiratory_rate = isset($_POST['respiratory_rate']) && $_POST['respiratory_rate'] !== ''
    ? intval($_POST['respiratory_rate'])
    : null;

$oxygen_saturation = isset($_POST['oxygen_saturation']) && $_POST['oxygen_saturation'] !== ''
    ? intval($_POST['oxygen_saturation'])
    : null;

$weight = isset($_POST['weight']) && $_POST['weight'] !== ''
    ? floatval($_POST['weight'])
    : null;

$height = isset($_POST['height']) && $_POST['height'] !== ''
    ? floatval($_POST['height'])
    : null;

$assessment = isset($_POST['assessment'])
    ? cleanText($_POST['assessment'])
    : '';

$management = isset($_POST['management'])
    ? cleanText($_POST['management'])
    : '';

$follow_up_date = isset($_POST['follow_up_date']) && $_POST['follow_up_date'] !== ''
    ? trim($_POST['follow_up_date'])
    : null;

$remarks = isset($_POST['remarks'])
    ? cleanText($_POST['remarks'])
    : '';


// =========================================================
// REQUIRED FIELD VALIDATION
// =========================================================

if ($patient_id <= 0) {
    die("Error: Invalid patient.");
}

if ($visit_date == '') {
    die("Error: Visit date is required.");
}

if ($chief_complaint == '') {
    die("Error: Chief complaint is required.");
}


// =========================================================
// OTHERS CHIEF COMPLAINT
// =========================================================

if ($chief_complaint === 'OTHERS') {

    if ($other_chief_complaint === '') {
        die(
            "Error: Please enter the actual Chief Complaint under OTHERS."
        );
    }

    $chief_complaint = $other_chief_complaint;
}


// =========================================================
// HISTORY VALIDATION
// =========================================================

if ($history_illness == '') {
    die(
        "Error: History of present illness is required."
    );
}


// =========================================================
// ASSESSMENT VALIDATION
// =========================================================

if ($assessment == '') {
    die(
        "Error: Assessment / Diagnosis is required."
    );
}


// =========================================================
// DATE VALIDATION
// =========================================================

$visit_date_object = DateTime::createFromFormat(
    'Y-m-d',
    $visit_date
);

if (
    !$visit_date_object ||
    $visit_date_object->format('Y-m-d') !== $visit_date
) {
    die("Error: Invalid visit date.");
}

if ($follow_up_date !== null) {

    $follow_up_date_object = DateTime::createFromFormat(
        'Y-m-d',
        $follow_up_date
    );

    if (
        !$follow_up_date_object ||
        $follow_up_date_object->format('Y-m-d') !== $follow_up_date
    ) {
        die("Error: Invalid follow-up date.");
    }

    if ($follow_up_date < $visit_date) {
        die(
            "Error: Follow-up date cannot be earlier than visit date."
        );
    }
}


// =========================================================
// BLOOD PRESSURE VALIDATION
// =========================================================

if ($blood_pressure !== '') {

    if (
        !preg_match(
            '/^[0-9]{2,3}\/[0-9]{2,3}$/',
            $blood_pressure
        )
    ) {
        die(
            "Error: Blood pressure must be in format 120/80."
        );
    }

    list(
        $systolic,
        $diastolic
    ) = explode(
        '/',
        $blood_pressure
    );

    $systolic = intval($systolic);
    $diastolic = intval($diastolic);

    if ($systolic < 50 || $systolic > 300) {
        die(
            "Error: Invalid systolic blood pressure."
        );
    }

    if ($diastolic < 30 || $diastolic > 200) {
        die(
            "Error: Invalid diastolic blood pressure."
        );
    }
}


// =========================================================
// VITAL SIGN VALIDATION
// =========================================================

if ($temperature !== null) {

    if ($temperature < 25 || $temperature > 45) {
        die("Error: Invalid temperature.");
    }
}

if ($pulse_rate !== null) {

    if ($pulse_rate < 20 || $pulse_rate > 250) {
        die("Error: Invalid pulse rate.");
    }
}

if ($respiratory_rate !== null) {

    if ($respiratory_rate < 5 || $respiratory_rate > 80) {
        die("Error: Invalid respiratory rate.");
    }
}

if ($oxygen_saturation !== null) {

    if (
        $oxygen_saturation < 50 ||
        $oxygen_saturation > 100
    ) {
        die("Error: Invalid oxygen saturation.");
    }
}

if ($weight !== null) {

    if ($weight <= 0 || $weight > 500) {
        die("Error: Invalid weight.");
    }
}

if ($height !== null) {

    if ($height <= 0 || $height > 250) {
        die("Error: Invalid height.");
    }
}


// =========================================================
// CHECK PATIENT
// =========================================================

$check_sql = "
    SELECT
        id,
        patient_id,
        first_name,
        middle_name,
        last_name,
        status
    FROM patients
    WHERE id = ?
";

$check_stmt = $conn->prepare($check_sql);

if (!$check_stmt) {
    die(
        "Database error: " .
        $conn->error
    );
}

$check_stmt->bind_param(
    "i",
    $patient_id
);

$check_stmt->execute();
$check_stmt->store_result();

if ($check_stmt->num_rows == 0) {

    $check_stmt->close();

    die("Error: Patient not found.");
}


// =========================================================
// GET PATIENT INFORMATION
// =========================================================

$db_id = 0;
$db_patient_id = '';
$first_name = '';
$middle_name = '';
$last_name = '';
$patient_status = '';

$check_stmt->bind_result(
    $db_id,
    $db_patient_id,
    $first_name,
    $middle_name,
    $last_name,
    $patient_status
);

$check_stmt->fetch();

$check_stmt->close();


// =========================================================
// DECEASED PROTECTION
// =========================================================

if ($patient_status === 'Deceased') {

    $conn->close();

    die(
        "This patient is marked as Deceased. " .
        "A new consultation cannot be saved."
    );
}


// =========================================================
// PREVENT EXACT DUPLICATE SUBMISSION
// =========================================================

$duplicate_sql = "
    SELECT id
    FROM consultations
    WHERE patient_id = ?
    AND visit_date = ?
    AND chief_complaint = ?
    AND history_illness = ?
    AND assessment = ?
    LIMIT 1
";

$duplicate_stmt = $conn->prepare($duplicate_sql);

if (!$duplicate_stmt) {
    die(
        "Database error: " .
        $conn->error
    );
}

$duplicate_stmt->bind_param(
    "issss",
    $db_id,
    $visit_date,
    $chief_complaint,
    $history_illness,
    $assessment
);

$duplicate_stmt->execute();
$duplicate_stmt->store_result();

if ($duplicate_stmt->num_rows > 0) {

    $duplicate_stmt->close();
    $conn->close();

    die(
        "This consultation appears to have already been saved."
    );
}

$duplicate_stmt->close();


// =========================================================
// INITIAL FOLLOW-UP STATUS
// =========================================================

$follow_up_status = "PENDING";


// =========================================================
// SAVE CONSULTATION
// =========================================================

$sql = "
    INSERT INTO consultations (
        patient_id,
        visit_date,
        chief_complaint,
        history_illness,
        blood_pressure,
        temperature,
        pulse_rate,
        respiratory_rate,
        oxygen_saturation,
        weight,
        height,
        assessment,
        management,
        follow_up_date,
        remarks,
        follow_up_status
    )
    VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
    )
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die(
        "Database error: " .
        $conn->error
    );
}


// =========================================================
// BIND VALUES
// =========================================================

$stmt->bind_param(
    "issssdiiiddsssss",
    $db_id,
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
    $follow_up_status
);


// =========================================================
// EXECUTE
// =========================================================

if (!$stmt->execute()) {

    die(
        "Error saving consultation: " .
        $stmt->error
    );
}


// =========================================================
// GET DATABASE CONSULTATION ID
// =========================================================

$consultation_id = $stmt->insert_id;


// =========================================================
// GET PATIENT-SPECIFIC CONSULTATION NUMBER
// =========================================================

$count_sql = "
    SELECT COUNT(*) AS consultation_count
    FROM consultations
    WHERE patient_id = ?
    AND id <= ?
";

$count_stmt = $conn->prepare($count_sql);

if (!$count_stmt) {

    $stmt->close();
    $conn->close();

    die(
        "Database error: " .
        $conn->error
    );
}

$count_stmt->bind_param(
    "ii",
    $db_id,
    $consultation_id
);

$count_stmt->execute();

$count_result =
    $count_stmt->get_result();

$count_row =
    $count_result->fetch_assoc();

$patient_consultation_no =
    (int) $count_row['consultation_count'];

$count_stmt->close();


// =========================================================
// FULL NAME
// =========================================================

$full_name = $first_name;

if (!empty($middle_name)) {

    $full_name .=
        " " .
        $middle_name;
}

$full_name .=
    " " .
    $last_name;


// =========================================================
// ACTIVITY LOG
// =========================================================

$activityDescription =
    "Added consultation: " .
    $db_patient_id .
    " - " .
    $full_name .
    " - Consultation #" .
    $patient_consultation_no;

logActivity(
    $conn,
    "CONSULTATION",
    $activityDescription
);


// =========================================================
// CLOSE DATABASE
// =========================================================

$stmt->close();
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
        Consultation Saved
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
            margin: 5px;
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
        Consultation Saved!
    </h2>

    <p class="success">
        Consultation record has been
        saved successfully.
    </p>

    <div class="consultation-label">
        Consultation No.
    </div>

    <div class="consultation-id">

        #

        <?php

        echo htmlspecialchars(
            $patient_consultation_no
        );

        ?>

    </div>

    <div class="patient-name">

        <?php

        echo htmlspecialchars(
            $full_name
        );

        ?>

    </div>

    <div class="patient-number">

        Patient ID:

        <strong>

            <?php

            echo htmlspecialchars(
                $db_patient_id
            );

            ?>

        </strong>

    </div>

    <div class="buttons">

        <a
            href="view.php?id=<?php echo urlencode($db_id); ?>"
        >
            View Patient Profile
        </a>

        <a
            href="consultation.php?patient_id=<?php echo urlencode($db_id); ?>"
            class="secondary"
        >
            New Consultation
        </a>

    </div>

</div>

</body>

</html>