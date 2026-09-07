<?php

/*
|--------------------------------------------------------------------------
| SESSION START
|--------------------------------------------------------------------------
*/

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| PREVENT BROWSER CACHING
|--------------------------------------------------------------------------
|
| This is important so protected pages are not stored in browser cache
| after logout.
|
*/

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


/*
|--------------------------------------------------------------------------
| CHECK LOGIN
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION['user_id']) ||
    empty($_SESSION['user_id'])
) {

    /*
     * Save the page the user attempted to access.
     * This can be used later if needed.
     */
    $_SESSION['login_required'] = true;

    header("Location: /private_patient_system/login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| OPTIONAL USER INFORMATION
|--------------------------------------------------------------------------
*/

$currentUserId = $_SESSION['user_id'];

$currentUsername = isset($_SESSION['username'])
    ? $_SESSION['username']
    : '';

$currentFullName = isset($_SESSION['full_name'])
    ? $_SESSION['full_name']
    : '';

$currentRole = isset($_SESSION['role'])
    ? $_SESSION['role']
    : 'Staff';

?>