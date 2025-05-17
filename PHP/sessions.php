<?php

// Ensure session is started only once
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Initialize session-dependent variables ---
$isLoggedIn = false;
$isAdmin = false;
$currentUserEmail = null;
$currentUserData = null; // Holds data for the currently logged-in user
$profileLink = 'Connexion.php'; // Default link if not logged in
$nombreArticlesPanier = 0;

// --- Helper function to get user data by a specific field (e.g., email) ---
if (!function_exists('ft_get_user_data_by_field')) {
    function ft_get_user_data_by_field($identifier_value, $field_name = 'email') {
        // __DIR__ gives the directory of the current file (PHP/)
        // So, ../data/data_user.json points to the data directory at the project root
        $jsonFilePath = __DIR__ . '/../data/data_user.json';

        if (!file_exists($jsonFilePath)) {
            error_log("User data file not found: " . $jsonFilePath);
            return null;
        }

        $jsonData = @file_get_contents($jsonFilePath);
        if ($jsonData === false) {
            error_log("Could not read user data file: " . $jsonFilePath);
            return null;
        }

        $usersArray = json_decode($jsonData, true);
        // Check for JSON decoding errors
        if ($usersArray === null && json_last_error() !== JSON_ERROR_NONE) {
            error_log("Error decoding user data JSON: " . json_last_error_msg() . " in file: " . $jsonFilePath);
            return null;
        }
        
        // Ensure $usersArray is an array, even if the JSON file was empty or contained non-array data
        if (!is_array($usersArray)) {
             error_log("User data is not a valid array or is empty in file: " . $jsonFilePath);
            return null; // Or return [] if you prefer to handle it as an empty list downstream
        }

        foreach ($usersArray as $user) {
            if (isset($user[$field_name]) && $user[$field_name] === $identifier_value) {
                return $user; // Return the full user data array
            }
        }
        return null; // User not found
    }
}

// --- Determine Login Status, User Data, and Admin Role ---
if (isset($_SESSION['user_email'])) {
    $isLoggedIn = true;
    $currentUserEmail = $_SESSION['user_email'];
    $profileLink = 'Profil.php'; // Link to user's own profile

    // Fetch data for the currently logged-in user
    $currentUserData = ft_get_user_data_by_field($currentUserEmail, 'email');

    if ($currentUserData && isset($currentUserData['is_admin']) && $currentUserData['is_admin'] === true) {
        $isAdmin = true;
    }
} else {
    // User is not logged in
    $isLoggedIn = false;
    $isAdmin = false;
    $profileLink = 'Connexion.php';
    // $currentUserEmail and $currentUserData remain null
}

// --- Calculate Cart Item Count ---
if (isset($_SESSION['panier']) && is_array($_SESSION['panier'])) {
    // Ensure all values are numeric before summing to avoid warnings/errors with non-numeric array values
    $numericQuantities = array_filter($_SESSION['panier'], 'is_numeric');
    $nombreArticlesPanier = array_sum($numericQuantities);
} else {
    $nombreArticlesPanier = 0;
}

?>