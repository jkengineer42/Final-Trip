<?php

function demarrerSession() {
    // Vérifie si une session est déjà active avant d'en démarrer une nouvelle
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
}

function verifierConnexion() {
    // Démarrer la session
    demarrerSession();
    
    // Vérifier si l'utilisateur est connecté (si son email est en session)
    if (!isset($_SESSION['user_email'])) {
        // Si non connecté, renvoyer une erreur
        envoyerErreur("Vous devez être connecté pour effectuer cette action");
    }
    
    // Renvoyer l'email de l'utilisateur connecté
    return $_SESSION['user_email'];
}


function verifierAdmin() {
    // D'abord vérifier que l'utilisateur est connecté
    $email = verifierConnexion();
    
    // Charger le fichier des utilisateurs
    $utilisateurs = chargerUtilisateurs();
    
    // Vérifier si l'utilisateur est admin
    foreach ($utilisateurs as $utilisateur) {
        if ($utilisateur['email'] === $email) {
            // Vérifier si l'attribut is_admin existe et est à true
            if (isset($utilisateur['is_admin']) && $utilisateur['is_admin'] === true) {
                return $email; // C'est un admin, on retourne son email
            }
            break; // On a trouvé l'utilisateur, mais ce n'est pas un admin
        }
    }
    
    // Si on arrive ici, l'utilisateur n'est pas admin
    envoyerErreur("Vous n'avez pas les droits d'administration nécessaires", 403);
}

function chargerUtilisateurs() {
    // Chemin vers le fichier JSON des utilisateurs
    $fichierUtilisateurs = '../data/data_user.json';
    
    // Vérifier si le fichier existe
    if (!file_exists($fichierUtilisateurs)) {
        envoyerErreur("Erreur: Fichier utilisateurs introuvable", 500);
    }
    
    // Lire le contenu du fichier
    $contenu = file_get_contents($fichierUtilisateurs);
    if ($contenu === false) {
        envoyerErreur("Erreur: Impossible de lire le fichier utilisateurs", 500);
    }
    
    // Décoder le JSON
    $utilisateurs = json_decode($contenu, true);
    if ($utilisateurs === null) {
        envoyerErreur("Erreur: Format de fichier utilisateurs invalide", 500);
    }
    
    return $utilisateurs;
}

function sauvegarderUtilisateurs($utilisateurs) {
    // Chemin vers le fichier JSON des utilisateurs
    $fichierUtilisateurs = '../data/data_user.json';
    
    // Encoder le tableau en JSON avec formatage pour lisibilité
    $contenuJSON = json_encode($utilisateurs, JSON_PRETTY_PRINT);
    if ($contenuJSON === false) {
        envoyerErreur("Erreur: Impossible d'encoder les données utilisateurs", 500);
    }
    
    // Écrire dans le fichier
    $resultat = file_put_contents($fichierUtilisateurs, $contenuJSON);
    if ($resultat === false) {
        envoyerErreur("Erreur: Impossible d'écrire dans le fichier utilisateurs", 500);
    }
    
    return true;
}

function validerDate($date) {
    // Vérifier le format de la date (YYYY-MM-DD)
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) { // à vérifier
        return false;
    }
    
    // Créer un objet DateTime
    $dateObj = DateTime::createFromFormat('Y-m-d', $date);
    
    // Vérifier si la date est valide et pas dans le futur
    if (!$dateObj || $dateObj->format('Y-m-d') !== $date || $dateObj > new DateTime()) {
        return false;
    }
    
    // Vérifier si l'année est supérieure à 1900
    if ($dateObj->format('Y') < 1900) {
        return false;
    }
    
    return true;
}


function envoyerErreur($message, $code = 400) { // le echo ca marche pas 
    // Définir le code HTTP de réponse
    http_response_code($code);
    
    // Définir l'en-tête Content-Type comme JSON
    header('Content-Type: application/json');
    
    // Créer et envoyer la réponse JSON
    echo json_encode([
        'success' => false,
        'message' => $message
    ]);
    
    // Arrêter l'exécution du script
    exit;
}



function envoyerSucces($donnees = []) {
    // Définir l'en-tête Content-Type comme JSON
    header('Content-Type: application/json');
    
    // Créer la réponse de base avec success=true
    $reponse = [
        'success' => true
    ];
    
    // Ajouter les données supplémentaires
    if (!empty($donnees)) {
        $reponse = array_merge($reponse, $donnees);
    }
    
    // Envoyer la réponse JSON
    echo json_encode($reponse);
    
    // Arrêter l'exécution du script
    exit;
}
?>
