<?php

require_once "../config/database.php";
require_once "../config/auth.php";
require_once "../config/activity_log.php";


// =========================================================
// KUNIN ANG DATA MULA SA FORM
// =========================================================

$last_name = isset($_POST['last_name']) ? trim($_POST['last_name']) : '';
$first_name = isset($_POST['first_name']) ? trim($_POST['first_name']) : '';
$middle_name = isset($_POST['middle_name']) ? trim($_POST['middle_name']) : '';
$birthdate = isset($_POST['birthdate']) ? $_POST['birthdate'] : '';
$sex = isset($_POST['sex']) ? $_POST['sex'] : '';
$address = isset($_POST['address']) ? trim($_POST['address']) : '';
$contact_no = isset($_POST['contact_no']) ? trim($_POST['contact_no']) : '';
$emergency_contact = isset($_POST['emergency_contact']) ? trim($_POST['emergency_contact']) : '';
$emergency_contact_no = isset($_POST['emergency_contact_no']) ? trim($_POST['emergency_contact_no']) : '';
$date_registered = isset($_POST['date_registered']) ? $_POST['date_registered'] : date('Y-m-d');


// =========================================================
// CHECK REQUIRED FIELDS
// =========================================================

if ($last_name == '' || $first_name == '' || $birthdate == '' || $sex == '') {
    die("Please complete all required fields.");
}


// =========================================================
// AUTOMATIC PATIENT ID
// =========================================================

$result = $conn->query("SELECT MAX(id) AS last_id FROM patients");

if (!$result) {
    die("Error checking patient ID: " . $conn->error);
}

$row = $result->fetch_assoc();

$last_id = $row['last_id'];

if ($last_id == null) {
    $next_id = 1;
} else {
    $next_id = $last_id + 1;
}

$patient_id = str_pad($next_id, 4, '0', STR_PAD_LEFT);


// =========================================================
// SAVE PATIENT
// =========================================================

$sql = "INSERT INTO patients (
    patient_id,
    last_name,
    first_name,
    middle_name,
    birthdate,
    sex,
    address,
    contact_no,
    emergency_contact,
    emergency_contact_no,
    date_registered
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    die("Database error: " . $conn->error);
}

$stmt->bind_param(
    "sssssssssss",
    $patient_id,
    $last_name,
    $first_name,
    $middle_name,
    $birthdate,
    $sex,
    $address,
    $contact_no,
    $emergency_contact,
    $emergency_contact_no,
    $date_registered
);


// =========================================================
// CHECK IF SAVED
// =========================================================

if ($stmt->execute()) {

    // =====================================================
    // ACTIVITY LOG
    // =====================================================

    $patientName = trim(
        $first_name . ' ' .
        $middle_name . ' ' .
        $last_name
    );

    $activityDescription =
        "Added patient: " .
        $patient_id .
        " - " .
        $patientName;

    logActivity(
        $conn,
        "ADD_PATIENT",
        $activityDescription
    );

?>

<!DOCTYPE html>
<html>
<head>

    <meta charset="UTF-8">

    <link
        rel="icon"
        type="image/png"
        href="../asset/images/DCMDLOGO.png?v=1"
    >

    <title>Patient Saved</title>

    <style>

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f6f9;

            display: flex;
            justify-content: center;
            align-items: center;

            min-height: 100vh;
        }

        .box {
            background: white;
            width: 450px;
            padding: 40px;

            border-radius: 12px;

            text-align: center;

            box-shadow: 0 5px 20px rgba(0,0,0,0.10);
        }

        h2 {
            color: #1f4e78;
            margin-bottom: 10px;
        }

        .success {
            color: #28a745;
            font-weight: bold;
        }

        .label {
            margin-top: 25px;
            color: #777;
        }

        .patient-id {
            font-size: 38px;
            font-weight: bold;
            color: #1f4e78;

            margin: 10px 0 20px;
        }

        .patient-name {
            font-size: 18px;
            font-weight: bold;

            margin-bottom: 25px;
        }

        a {
            display: inline-block;

            padding: 12px 18px;

            background: #1f4e78;
            color: white;

            text-decoration: none;

            border-radius: 6px;

            margin: 5px;
        }

        a:hover {
            background: #173a5c;
        }

    </style>

</head>

<body>

    <div class="box">

        <h2>Patient Successfully Registered!</h2>

        <p class="success">
            Patient record has been saved.
        </p>

        <p class="label">
            Patient ID
        </p>

        <div class="patient-id">
            <?php echo htmlspecialchars($patient_id); ?>
        </div>

        <div class="patient-name">
            <?php
            echo htmlspecialchars(
                $first_name . ' ' .
                $middle_name . ' ' .
                $last_name
            );
            ?>
        </div>

        <a href="add.php">
            Add Another Patient
        </a>

        <a href="index.php">
            View Patients
        </a>

    </div>

</body>
</html>

<?php

} else {

    echo "Error saving patient: " . $stmt->error;

}

$stmt->close();
$conn->close();

?>