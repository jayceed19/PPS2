<?php

function logActivity($conn, $action, $description = "")
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $userId = isset($_SESSION['user_id'])
        ? intval($_SESSION['user_id'])
        : null;

    $username = isset($_SESSION['username'])
        ? $_SESSION['username']
        : '';

    $fullName = isset($_SESSION['full_name'])
        ? $_SESSION['full_name']
        : '';

    $role = isset($_SESSION['role'])
        ? $_SESSION['role']
        : '';

    $stmt = $conn->prepare("
        INSERT INTO activity_logs
        (
            user_id,
            username,
            full_name,
            role,
            action,
            description
        )
        VALUES (?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        return false;
    }

    $stmt->bind_param(
        "isssss",
        $userId,
        $username,
        $fullName,
        $role,
        $action,
        $description
    );

    $result = $stmt->execute();

    $stmt->close();

    return $result;
}
?>