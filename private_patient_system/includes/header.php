<?php

if (!isset($pageTitle)) {
    $pageTitle = "Private Patient System";
}

if (!isset($pageSubtitle)) {
    $pageSubtitle = "Patient Management";
}

require_once __DIR__ . "/../config/auth.php";

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        <?php echo htmlspecialchars($pageTitle); ?>
        - Private Patient System
    </title>


    <!-- =====================================================
         PREVENT BACK BUTTON FROM SHOWING CACHED PAGE
    ====================================================== -->

    <script>

    window.addEventListener("pageshow", function (event) {

        if (event.persisted) {

            window.location.reload();

        }

    });

    </script>


    <!-- =====================================================
         FAVICON
    ====================================================== -->

    <link
        rel="icon"
        type="image/png"
        href="/private_patient_system/asset/images/DCMDLOGO.png?v=1"
    >


    <!-- =====================================================
         MAIN CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        type="text/css"
        href="/private_patient_system/css/style.css?v=1"
    >

</head>


<body>


<header class="header">


    <div class="header-text">

        <h1>
            PRIVATE PATIENT SYSTEM
        </h1>

        <p>
            <?php
            echo htmlspecialchars($pageSubtitle);
            ?>
        </p>

    </div>


    <img
        src="/private_patient_system/asset/images/DCMD.png?v=1"
        alt="DCMD Logo"
        class="header-logo"
    >


</header>