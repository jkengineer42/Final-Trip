<?php
// PHP/Logout.php

// Ensure session is started before trying to destroy it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

session_unset(); // Détruire toutes les variables de session
session_destroy(); // Détruire la session

// Regenerate session ID to prevent session fixation (good practice)
if (session_status() == PHP_SESSION_NONE) { // Check again as destroy might make it NONE
    session_start(); 
}
session_regenerate_id(true);


header("Location: Accueil.php"); // Rediriger vers la page d'accueil après la déconnexion
exit();
?>