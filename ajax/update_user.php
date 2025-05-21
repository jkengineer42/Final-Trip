<?php
// Fichier ajax/update_user.php
require_once 'security.php';

// Démarrer ou récupérer la session
demarrerSession();

// Vérifier si l'utilisateur est connecté
$email_utilisateur = verifierConnexion();

// Vérifier si la requête est de type POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    envoyerErreur("Méthode non autorisée, utilisez POST");
}

// Récupérer les données soumises
$data = json_decode(file_get_contents('php://input'), true);

// Validation basique
if (!$data || !isset($data['field']) || !isset($data['value']) || !isset($data['email'])) {
    envoyerErreur("Données invalides ou manquantes");
}

// Charger les utilisateurs
$utilisateurs = chargerUtilisateurs();
$est_admin = false;

// Vérifier les droits et appliquer la modification
foreach ($utilisateurs as &$utilisateur) {
    if ($utilisateur['email'] === $email_utilisateur) {
        $est_admin = isset($utilisateur['is_admin']) && $utilisateur['is_admin'];
    }
    
    // Trouver l'utilisateur à modifier
    if ($utilisateur['email'] === $data['email']) {
        // Vérifier les droits
        if ($email_utilisateur !== $data['email'] && !$est_admin) {
            envoyerErreur("Droits insuffisants", 403);
        }

    // Vérifier si c'est un mot de passe à modifier
    if (isset($data['is_password']) && $data['is_password'] === true && $data['field'] === 'password') {
        // Hasher le mot de passe
        $data['value'] = password_hash($data['value'], PASSWORD_DEFAULT);
    }

// Appliquer la modification
$utilisateur[$data['field']] = $data['value'];

        
        // Appliquer la modification
        $utilisateur[$data['field']] = $data['value'];
        
        // Sauvegarder
        if (sauvegarderUtilisateurs($utilisateurs)) {
            // Ajouter un délai si admin modifie un autre utilisateur
            if ($est_admin && $email_utilisateur !== $data['email']) {
                sleep(3);
            }
            
            envoyerSucces(['message' => 'Modification réussie']);
        } else {
            envoyerErreur("Échec de la sauvegarde");
        }
        
        return; // Terminer ici
    }
}

envoyerErreur("Utilisateur non trouvé");
?>
