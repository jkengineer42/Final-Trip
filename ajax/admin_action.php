<?php
// Fichier ajax/admin_action.php
require_once 'security.php';

// Vérifier si l'utilisateur est admin
$admin_email = verifierAdmin();

// Récupérer les données
$data = json_decode(file_get_contents('php://input'), true);

// Validation basique
if (!$data || !isset($data['action']) || !isset($data['email'])) {
    envoyerErreur("Données invalides");
}

// Charger les utilisateurs
$utilisateurs = chargerUtilisateurs();
$index_utilisateur = -1;

// Trouver l'utilisateur
foreach ($utilisateurs as $index => $utilisateur) {
    if ($utilisateur['email'] === $data['email']) {
        $index_utilisateur = $index;
        break;
    }
}

if ($index_utilisateur === -1) {
    envoyerErreur("Utilisateur introuvable");
}

// Ajouter un délai pour la simulation
sleep(3);

// Appliquer l'action
if ($data['action'] === 'promote') {
    $utilisateurs[$index_utilisateur]['is_admin'] = true;
    $message = "Promotion réussie";
} elseif ($action === 'block') {
    // Vérifier si l'utilisateur à bloquer est admin
    if (isset($users[$user_index]['is_admin']) && $users[$user_index]['is_admin'] === true) {
        echo json_encode(['success' => false, 'message' => 'Impossible de bloquer un administrateur']);
        exit;
    }
    $users[$user_index]['is_blocked'] = true;
    $message = "Suppression réussie";
} else {
    envoyerErreur("Action non reconnue");
}

// Sauvegarder
if (sauvegarderUtilisateurs($utilisateurs)) {
    envoyerSucces(['message' => $message]);
} else {
    envoyerErreur("Échec de la sauvegarde");
}
?>
