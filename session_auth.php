<?php
// --- Security Gatekeeper ---

// This script must be included at the VERY TOP of any protected page.

// Start the session to access session variables.
// It's safe to call this even if it's already started elsewhere.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Checks if a user is logged in and has one of the allowed roles.
 * If not, it redirects to the login page and stops the script.
 *
 * @param array $allowed_roles An array of roles that are allowed to access the page (e.g., ['admin'], ['volunteer'], ['student', 'admin']).
 */
function check_auth($allowed_roles = []) {
    // 1. Check if the user is logged in at all.
    // If the 'loggedin' session variable doesn't exist or is not true, redirect to login.
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
        header("location: login.html");
        exit; // Stop script execution immediately.
    }

    // 2. Check if the user's role is in the list of allowed roles.
    // If the allowed roles array is not empty and the user's role is not in it, deny access.
    if (!empty($allowed_roles)) {
        if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
            // For security, we just redirect them to login. 
            // This prevents leaking information about what pages exist.
            header("location: login.html");
            exit; // Stop script execution immediately.
        }
    }
}
?>