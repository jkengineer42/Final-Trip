<?php
session_start();
session_unset(); // Détruire toutes les variables de session
session_destroy(); // Détruire la session
header("Location: Accueil.php"); // Rediriger vers la page d'accueil après la déconnexion
exit();
?>
