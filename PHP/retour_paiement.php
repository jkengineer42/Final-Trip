<?php
session_start();
require_once 'getapikey.php'; // Assurez-vous que ce fichier existe et fonctionne

// --- Récupération des Données de la Redirection CYBank ---
$received_transaction = $_GET['transaction'] ?? null;
$received_montant = $_GET['montant'] ?? null;
$received_vendeur = $_GET['vendeur'] ?? null;
$received_status = $_GET['status'] ?? null;
$received_control = $_GET['control'] ?? null;

// --- Récupération des Données Stockées en Session ---
$stored_transaction = $_SESSION['payment_transaction_id'] ?? null;
$stored_montant = $_SESSION['payment_montant'] ?? null; // Montant envoyé à CYBank
$stored_vendeur = $_SESSION['payment_vendeur'] ?? null;
$userEmail = $_SESSION['user_email'] ?? null; // Nécessaire si le paiement est accepté
$tripData = $_SESSION['personalized_trip'] ?? null; // Nécessaire si le paiement est accepté
$totalPriceToSave = $_SESSION['payment_total_price'] ?? null; // Prix total original

// --- Vérification ---
$error_message = null;
$is_valid = false;

if (!$received_transaction || !$received_montant || !$received_vendeur || !$received_status || !$received_control) {
    $error_message = "Erreur: Données de retour incomplètes.";
} elseif (!$stored_transaction || !$stored_montant || !$stored_vendeur) {
    $error_message = "Erreur: Session de paiement invalide ou expirée.";
} elseif ($received_transaction !== $stored_transaction) {
    $error_message = "Erreur: Incohérence de l'identifiant de transaction.";
} elseif ($received_montant !== $stored_montant) {
    // Utiliser une tolérance pour la comparaison des flottants si nécessaire, mais une correspondance exacte est attendue ici
     $error_message = "Erreur: Incohérence du montant de la transaction.";
} elseif ($received_vendeur !== $stored_vendeur) {
    $error_message = "Erreur: Incohérence du code vendeur.";
} else {
    // Toutes les vérifications préliminaires ont réussi, vérification du hash de contrôle maintenant
    $api_key = getAPIKey($received_vendeur);
    if ($api_key === 'zzzz') { // Clé invalide retournée par getAPIKey
        $error_message = "Erreur: Code vendeur invalide lors de la vérification.";
    } else {
        $control_string_verify = $api_key . "#" . $received_transaction . "#" . $received_montant . "#" . $received_vendeur . "#" . $received_status . "#";
        $calculated_control_verify = md5($control_string_verify);

        if ($received_control !== $calculated_control_verify) {
            $error_message = "Erreur: La signature de contrôle est invalide. La réponse a peut-être été altérée.";
            // Journaliser les informations détaillées pour le débogage si nécessaire :
             error_log("Incohérence du Hash de Contrôle. Attendu: $calculated_control_verify, Reçu: $received_control. Chaîne: $control_string_verify");
        } else {
            // Vérification réussie !
            $is_valid = true;
        }
    }
}

// --- Traitement du Résultat ---
$payment_successful = false;
$confirmation_title = "Résultat du Paiement";
$confirmation_message = "";
$transaction_id_display = $received_transaction; // Utiliser celui reçu pour l'affichage

if ($is_valid) {
    if ($received_status === 'accepted') {
        $payment_successful = true;
        $confirmation_title = "Paiement Réussi !";
        $confirmation_message = "Votre transaction a été acceptée et enregistrée avec succès.";

        // --- Enregistrement du Paiement (Logique de l'ancien fichier enregistrer_paiement.php) ---
        if ($userEmail && $tripData && $totalPriceToSave !== null) {
            $newPayment = [
                'transaction_id' => $received_transaction, // Utiliser l'ID vérifié
                'timestamp' => date('Y-m-d H:i:s'),
                'user_email' => $userEmail,
                'total_paid' => $totalPriceToSave, // Enregistrer le total calculé en interne
                'amount_processed' => $received_montant, // Montant traité par CYBank
                'status' => 'accepted',
                'vendeur' => $received_vendeur,
                'trip_configuration' => $tripData
            ];

            $logFile = '../data/paiements.json';
            $payments = [];
            if (file_exists($logFile) && is_readable($logFile)) {
                $jsonContent = file_get_contents($logFile);
                // Gérer le cas où le fichier est vide
                if (!empty($jsonContent)) {
                    $payments = json_decode($jsonContent, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                         error_log("Erreur de décodage de payments.json dans retour_paiement: " . json_last_error_msg());
                         $payments = []; // Réinitialiser en cas d'erreur de décodage
                    }
                }
                // Assurer que $payments est toujours un tableau
                if (!is_array($payments)) $payments = [];
            } elseif (!file_exists($logFile)) {
                 // Si le fichier n'existe pas, $payments est déjà []
                 // On peut éventuellement le créer ici si nécessaire, mais file_put_contents le fera
            } else {
                 // Le fichier existe mais n'est pas lisible
                 error_log("Erreur: payments.json existe mais n'est pas lisible dans retour_paiement.");
                 $confirmation_message .= " Erreur interne lors de la sauvegarde (lecture fichier)."; // Ajouter l'erreur
                 $payment_successful = false; // Marquer comme échoué si erreur de sauvegarde
            }

            if ($payment_successful) { // Tenter la sauvegarde uniquement si aucune erreur de lecture ne s'est produite
                $payments[] = $newPayment;
                if (file_put_contents($logFile, json_encode($payments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                    error_log("Erreur: Échec de l'écriture dans payments.json dans retour_paiement.");
                    $confirmation_message .= " Erreur interne lors de la sauvegarde (écriture fichier)."; // Ajouter l'erreur
                     $payment_successful = false; // Marquer comme échoué si erreur de sauvegarde
                }
            }
        } else {
            $missing_data_error = "Erreur interne: Données utilisateur";
            if (!$tripData) $missing_data_error .= ", voyage";
            if ($totalPriceToSave === null) $missing_data_error .= ", prix total";
            $missing_data_error .= " manquantes pour la sauvegarde.";
            $confirmation_message .= " " . $missing_data_error;
             error_log($missing_data_error . " Email: " . ($userEmail ? 'OK' : 'MANQUANT') . ", TripData: " . ($tripData ? 'OK' : 'MANQUANT') . ", TotalPrice: " . ($totalPriceToSave !== null ? 'OK' : 'MANQUANT'));
             $payment_successful = false; // Marquer comme échoué si erreur de sauvegarde
        }
        // --- Fin de l'Enregistrement du Paiement ---

    } elseif ($received_status === 'declined') {
        $confirmation_title = "Paiement Refusé";
        $confirmation_message = "Votre transaction a été refusée par le système de paiement.";
    } else {
        $confirmation_title = "Statut Inconnu";
        $confirmation_message = "Le statut retourné par le système de paiement est inconnu ('" . htmlspecialchars($received_status) . "').";
    }
} else {
    // Utiliser le message d'erreur généré lors de la vérification
    $confirmation_title = "Erreur de Vérification";
    $confirmation_message = $error_message; // $error_message contient déjà le message d'erreur spécifique
}

// --- Nettoyage de la Session (toujours nettoyer les infos de tentative de paiement) ---
unset($_SESSION['payment_transaction_id']);
unset($_SESSION['payment_montant']);
unset($_SESSION['payment_vendeur']);
// Ne désactiver les données du voyage que si le paiement a RÉUSSI et a été SAUVEGARDÉ
if ($payment_successful) {
    unset($_SESSION['personalized_trip']);
    unset($_SESSION['payment_total_price']);
    // Conserver user_email en session car il n'est pas spécifique au paiement
}

$profileLink = 'Profil.php'; 

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($confirmation_title); ?> - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/Paiement.css"> 
    <script src="../Javascript/theme.js"></script>
    <style>
        .confirmation-container {
            background-color: var(--darker-grey);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%; 
            margin: 40px auto; 
            text-align: center;
            border: 2px solid <?php echo $payment_successful ? 'var(--yellow)' : '#e74c3c'; ?>;
        }
        .confirmation-container h1 {
            color: <?php echo $payment_successful ? 'var(--yellow)' : '#e74c3c'; ?>;
            margin-bottom: 20px;
        }
        .confirmation-container p {
            font-size: 1.1em;
            line-height: 1.6;
            margin-bottom: 15px;
            color: var(--white);
        }
         .confirmation-container .transaction-details p {
             font-size: 0.95em;
             color: var(--white);
             opacity: 0.9;
             margin-bottom: 5px;
         }
         .confirmation-container .transaction-details strong {
             color: var(--white2);
         }
        .confirmation-container .action-buttons {
            margin-top: 30px;
            display: flex;
            justify-content: center; 
            gap: 20px; 
            flex-wrap: wrap; 
        }
        .primary-button, .secondary-button {
            padding: 12px 25px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
            cursor: pointer;
            border: none; 
            display: inline-block; 
        }
        .primary-button {
            background-color: var(--yellow);
            color: var(--black);
        }
        .primary-button:hover {
            background-color: #ffd966; /
        }
        .secondary-button {
            background-color: var(--grey);
            color: var(--white);
        }
        .secondary-button:hover {
            background-color: var(--darker-grey);
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">

    <main>
        <div class="confirmation-container">
            <h1><?php echo htmlspecialchars($confirmation_title); ?></h1>
            <p><?php echo htmlspecialchars($confirmation_message); ?></p>

            <?php if ($is_valid): // Afficher les détails uniquement si la vérification de base a réussi (même si refusé) ?>
            <div class="transaction-details">
                <p><strong>ID Transaction :</strong> <?php echo htmlspecialchars($transaction_id_display); ?></p>
                <?php if ($received_montant !== null): ?>
                    <p><strong>Montant traité :</strong> <?php echo number_format(floatval($received_montant), 2, ',', ' '); ?> €</p>
                <?php endif; ?>
                <p><strong>Statut :</strong> <?php echo htmlspecialchars($received_status); ?></p>
            </div>
            <?php elseif (!empty($transaction_id_display)): // Afficher au moins l'ID de transaction si disponible, même en cas d'erreur de vérification ?>
             <div class="transaction-details">
                <p><strong>ID Transaction (Reçu) :</strong> <?php echo htmlspecialchars($transaction_id_display); ?></p>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="Accueil.php" class="primary-button">Retour à l'accueil</a>
                <?php if (!$payment_successful && $is_valid && $received_status !== 'accepted'): // Proposer de réessayer uniquement si refusé/inconnu mais vérifié (et que le voyage est potentiellement encore en session) ?>
                    <a href="Paiement.php" class="secondary-button">Réessayer le paiement</a>
                <?php elseif ($payment_successful): ?>
                     <!-- Lien vers l'historique des commandes si implémenté -->
                     <!-- <a href="Profil.php?section=commandes" class="secondary-button">Voir mes commandes</a> -->
                <?php endif; ?>
                <!-- Toujours proposer d'aller au profil -->
                <a href="Profil.php" class="secondary-button">Mon Profil</a>
            </div>
        </div>
    </main>

    <?php include('footer.php'); ?>
</body>
</html>
