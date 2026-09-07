<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/*
|--------------------------------------------------------------------------
| LOAD DATABASE AND ACTIVITY LOG
|--------------------------------------------------------------------------
*/

require_once "config/database.php";
require_once "config/activity_log.php";


/*
|--------------------------------------------------------------------------
| LOGOUT ACTIVITY
|--------------------------------------------------------------------------
|
| IMPORTANT:
| Log muna bago i-clear ang session para makuha pa ang
| username, full name, role, at user ID.
|
*/

if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {

    logActivity(
        $conn,
        "LOGOUT",
        "User logged out of the system."
    );
}


/*
|--------------------------------------------------------------------------
| CLOSE DATABASE CONNECTION
|--------------------------------------------------------------------------
*/

if (isset($conn)) {
    $conn->close();
}


/*
|--------------------------------------------------------------------------
| CLEAR ALL SESSION DATA
|--------------------------------------------------------------------------
*/

$_SESSION = array();


/*
|--------------------------------------------------------------------------
| DELETE SESSION COOKIE
|--------------------------------------------------------------------------
*/

if (ini_get("session.use_cookies")) {

    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}


/*
|--------------------------------------------------------------------------
| DESTROY SESSION
|--------------------------------------------------------------------------
*/

session_destroy();


/*
|--------------------------------------------------------------------------
| PREVENT CACHE
|--------------------------------------------------------------------------
*/

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: 0");


/*
|--------------------------------------------------------------------------
| REDIRECT TO LOGIN
|--------------------------------------------------------------------------
*/

header("Location: /private_patient_system/login.php");
exit;

?>