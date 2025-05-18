<?php
// Final/PHP/config.php

// Détecter si le serveur tourne en HTTPS
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";

// Obtenir le nom d'hôte (ex: localhost, votre_domaine.com)
// Si $_SERVER['HTTP_HOST'] n'est pas défini (ex: script CLI), fallback sur 'localhost'.
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Le nom du dossier de votre projet accessible depuis la racine du serveur web.
// Dans votre cas, c'est "/Final".
$project_folder_name = '/Final'; 

// Construction de l'URL de base de l'application
$app_base_url = $protocol . $host . $project_folder_name;

?>