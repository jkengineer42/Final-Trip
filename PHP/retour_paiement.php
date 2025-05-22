<?php
require_once 'sessions.php';
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
     $error_message = "Erreur: Incohérence du montant de la transaction.";
} elseif ($received_vendeur !== $stored_vendeur) {
    $error_message = "Erreur: Incohérence du code vendeur.";
} else {
    $api_key = getAPIKey($received_vendeur);
    if ($api_key === 'zzzz') {
        $error_message = "Erreur: Code vendeur invalide lors de la vérification.";
    } else {
        $control_string_verify = $api_key . "#" . $received_transaction . "#" . $received_montant . "#" . $received_vendeur . "#" . $received_status . "#";
        $calculated_control_verify = md5($control_string_verify);

        if ($received_control !== $calculated_control_verify) {
            $error_message = "Erreur: La signature de contrôle est invalide. La réponse a peut-être été altérée.";
            error_log("Incohérence du Hash de Contrôle. Attendu: $calculated_control_verify, Reçu: $received_control. Chaîne: $control_string_verify");
        } else {
            $is_valid = true;
        }
    }
}

// --- Traitement du Résultat ---
$payment_successful = false;
$confirmation_title = "Résultat du Paiement";
$confirmation_message = "";
$transaction_id_display = $received_transaction;

if ($is_valid) {
    if ($received_status === 'accepted') {
        $payment_successful = true; // Marquer comme succès pour le moment
        $confirmation_title = "Paiement Réussi !";
        $confirmation_message = "Votre transaction a été acceptée."; // Message initial

        // --- Enregistrement du Paiement ---
        if ($userEmail && $tripData && $totalPriceToSave !== null) {
            $newPayment = [
                'transaction_id' => $received_transaction,
                'timestamp' => date('Y-m-d H:i:s'),
                'user_email' => $userEmail,
                'total_paid' => $totalPriceToSave,
                'amount_processed' => $received_montant,
                'status' => 'accepted',
                'vendeur' => $received_vendeur,
                'trip_configuration' => $tripData
            ];

            // 1. Sauvegarde dans paiements.json
            $logFilePayments = '../data/paiements.json';
            $payments = [];
            if (file_exists($logFilePayments) && is_readable($logFilePayments)) {
                $jsonContent = file_get_contents($logFilePayments);
                if (!empty($jsonContent)) {
                    $payments = json_decode($jsonContent, true);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                         error_log("Erreur de décodage de payments.json: " . json_last_error_msg());
                         $payments = [];
                    }
                }
            }
            if (!is_array($payments)) $payments = []; // S'assurer que c'est un tableau

            $payments[] = $newPayment;
            if (file_put_contents($logFilePayments, json_encode($payments, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                error_log("Erreur: Échec de l'écriture dans payments.json.");
                $confirmation_message .= " Une erreur est survenue lors de la sauvegarde des détails du paiement.";
                $payment_successful = false; // Le paiement a échoué au niveau de notre système
            } else {
                 $confirmation_message .= " Votre paiement a été enregistré avec succès."; // Confirmer la sauvegarde
            }


            // 2. Sauvegarde dans historique_achats.json (SEULEMENT SI la sauvegarde dans paiements.json a réussi)
            if ($payment_successful) {
                $historiqueFile = '../data/historique_achats.json';
                $historiqueAchats = [];

                if (file_exists($historiqueFile) && is_readable($historiqueFile)) {
                    $jsonHistoriqueContent = file_get_contents($historiqueFile);
                    if (!empty($jsonHistoriqueContent)) {
                        $historiqueAchats = json_decode($jsonHistoriqueContent, true);
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            error_log("Erreur de décodage de historique_achats.json: " . json_last_error_msg());
                            $historiqueAchats = [];
                        }
                    }
                }
                if (!is_array($historiqueAchats)) $historiqueAchats = [];

                $newPurchaseRecord = [
                    'user_email' => $userEmail,
                    'transaction_id' => $received_transaction,
                    'purchase_timestamp' => date('Y-m-d H:i:s'),
                    'trip_title' => $tripData['tripTitle'] ?? 'Titre du voyage non disponible',
                    'trip_id_original' => $tripData['tripId'] ?? null,
                    'total_paid' => $totalPriceToSave,
                    'personalized_trip_details' => $tripData
                ];

                $historiqueAchats[] = $newPurchaseRecord;

                if (file_put_contents($historiqueFile, json_encode($historiqueAchats, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
                    error_log("Erreur: Échec de l'écriture dans historique_achats.json.");
                    $confirmation_message .= " Une erreur est survenue lors de la sauvegarde de l'historique de votre achat.";
                    // Note: $payment_successful reste true car le paiement bancaire a réussi et le log principal est fait.
                    // C'est un problème de log secondaire.
                } else {
                    // Optionnel : ajouter un message de succès pour la sauvegarde de l'historique si souhaité
                    // $confirmation_message .= " L'historique de votre achat a aussi été sauvegardé.";
                }
            }

        } else {
            $missing_data_error = "Erreur interne: Données utilisateur";
            if (!$tripData) $missing_data_error .= ", voyage";
            if ($totalPriceToSave === null) $missing_data_error .= ", prix total";
            $missing_data_error .= " manquantes pour la sauvegarde.";
            $confirmation_message .= " " . $missing_data_error;
            error_log($missing_data_error . " Email: " . ($userEmail ? 'OK' : 'MANQUANT') . ", TripData: " . ($tripData ? 'OK' : 'MANQUANT') . ", TotalPrice: " . ($totalPriceToSave !== null ? 'OK' : 'MANQUANT'));
            $payment_successful = false; // Le paiement a échoué au niveau de notre système
        }

    } elseif ($received_status === 'declined') {
        $confirmation_title = "Paiement Refusé";
        $confirmation_message = "Votre transaction a été refusée par le système de paiement.";
        $payment_successful = false; // Clairement pas un succès
    } else {
        $confirmation_title = "Statut Inconnu";
        $confirmation_message = "Le statut retourné par le système de paiement est inconnu ('" . htmlspecialchars($received_status) . "').";
        $payment_successful = false; // Statut incertain, considérer comme échec
    }
} else {
    // Erreur de vérification, $error_message est déjà défini
    $confirmation_title = "Erreur de Vérification";
    $confirmation_message = $error_message;
    $payment_successful = false; // Échec de vérification
}

// --- Nettoyage de la Session ---
unset($_SESSION['payment_transaction_id']);
unset($_SESSION['payment_montant']);
unset($_SESSION['payment_vendeur']);

// Nettoyer les données du voyage et du panier seulement si le paiement a été enregistré avec succès dans notre système
if ($payment_successful) {
    $tripIdToClean = $tripData['tripId'] ?? null;
    unset($_SESSION['personalized_trip']);
    if ($tripIdToClean && isset($_SESSION['panier'][$tripIdToClean])) {
        unset($_SESSION['panier'][$tripIdToClean]);
    }
    if ($tripIdToClean && isset($_SESSION['panier_prices'][$tripIdToClean])) {
        unset($_SESSION['panier_prices'][$tripIdToClean]);
    }
    unset($_SESSION['payment_total_price']);
}

$profileLink = 'Profil.php'; // Est déjà défini par sessions.php, mais pour être sûr

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
        .primary-button, .secondary-button { /* Styles repris de paiement.css pour cohérence */
            padding: 12px 25px;
            border-radius: 200px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            border: none;
            display: inline-block; 
        }
        .primary-button {
            background-color: var(--yellow);
            color: var(--black_f);
        }
        .primary-button:hover {
            background-color: var(--yellow2);
            box-shadow: 0 4px 8px rgba(255, 207, 48, 0.4);
        }
        .secondary-button { /* Bouton secondaire pour le profil par exemple */
            background-color: var(--grey); /* Ou var(--white) et var(--black_f) comme dans paiement.css */
            color: var(--white);
            border: 1px solid var(--white); /* Optionnel: ajouter une bordure */
        }
        .secondary-button:hover {
            background-color: var(--darker-grey); /* Ou var(--light-grey) etc. */
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

            <?php if ($is_valid || !empty($transaction_id_display)): // Afficher détails si au moins l'ID est là ?>
            <div class="transaction-details">
                <p><strong>ID Transaction :</strong> <?php echo htmlspecialchars($transaction_id_display); ?></p>
                <?php if ($received_montant !== null): ?>
                    <p><strong>Montant traité :</strong> <?php echo number_format(floatval($received_montant), 2, ',', ' '); ?> €</p>
                <?php endif; ?>
                <?php if ($received_status !== null): ?>
                    <p><strong>Statut :</strong> <?php echo htmlspecialchars($received_status); ?></p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <div class="action-buttons">
                <a href="Accueil.php" class="primary-button">Retour à l'accueil</a>
                <?php if (!$payment_successful && $is_valid && $received_status !== 'accepted' && isset($_SESSION['personalized_trip']) /* Vérifier que le voyage est encore en session pour réessayer */): ?>
                    <a href="Paiement.php" class="secondary-button">Réessayer le paiement</a>
                <?php elseif ($payment_successful): ?>
                     <a href="Profil.php" class="secondary-button">Voir mon profil et mes achats</a>
                <?php else: // Cas d'erreur de vérification ou autre ?>
                    <a href="Profil.php" class="secondary-button">Mon Profil</a>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include('footer.php'); ?>
</body>
</html>