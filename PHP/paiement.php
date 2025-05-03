<?php
session_start();
require_once 'getapikey.php'; 

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php");
    exit();
}

// Vérifier si un voyage personnalisé existe en session
if (!isset($_SESSION['personalized_trip'])) {
    // Rediriger vers une page appropriée si aucun voyage n'est configuré
    header("Location: Destination.php"); 
    exit();
}

// Récupérer les données de la session
$userEmail = $_SESSION['user_email'];
$personalizedTrip = $_SESSION['personalized_trip'];
$profileLink = 'Profil.php'; 

// --- Préparation des données pour CYBank ---

// 3. Calculer le prix total
$basePrice = floatval(str_replace(['€', ' '], '', $personalizedTrip['originalPrice']));
$optionsPrice = 0;
// Calcul robuste nécessaire ici basé sur la façon dont 'inclus' et les prix sont stockés
foreach ($personalizedTrip['stages'] as $stage) {
    foreach ($stage['options'] as $category => $option) {
        // Vérification exemple : ajouter le prix s'il est numérique et > 0
        // Assurez-vous que 'prix' existe avant de l'utiliser
        $priceStr = str_replace(['€', ' '], '', $option['prix'] ?? '0');
        if (is_numeric($priceStr) && floatval($priceStr) > 0) {
            $price = floatval($priceStr);
            // Gérer les participants pour les activités, sinon 1 par défaut
            $participants = ($category === 'activites' && isset($option['participants'])) ? intval($option['participants']) : 1;
            $optionsPrice += $price * $participants;
        }
    }
}
$totalPrice = $basePrice + $optionsPrice;
// Format spécifique requis par CYBank (XX.XX)
$montant_cybank = number_format($totalPrice, 2, '.', ''); 
// 4. Générer un ID de transaction unique
$transaction_id = uniqid('FT_') . rand(100, 999); // Préfixe FT_ pour Final Trip

// 5. Définir le code vendeur
$vendeur_code = 'MEF-1_H'; // Code vendeur fourni

// 6. Définir l'URL de retour
$server_name = $_SERVER['SERVER_NAME'];
$script_path = dirname($_SERVER['PHP_SELF']);
// Construire l'URL de base dynamiquement
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$server_name";
// Ajouter le port s'il n'est pas le port par défaut 80 ou 443
if(isset($_SERVER['SERVER_PORT']) && !in_array($_SERVER['SERVER_PORT'], [80, 443])) {
    $base_url .= ":" . $_SERVER['SERVER_PORT'];
}
// S'assurer que le chemin se termine par un / avant d'ajouter le nom du fichier
$retour_url = $base_url . rtrim($script_path, '/') . '/retour_paiement.php';


// 7. Obtenir la clé API
$api_key = getAPIKey($vendeur_code);
if ($api_key === 'zzzz') { // Valeur indiquant une clé invalide selon getapikey.php
    error_log("Erreur critique : Code vendeur '$vendeur_code' invalide ou clé API non trouvée.");
    die("Une erreur technique est survenue lors de la préparation du paiement. Veuillez réessayer plus tard ou contacter le support.");
}

// Supprimer les espaces potentiels des variables avant le hachage (sécurité)
$api_key_trimmed = trim($api_key);
$transaction_id_trimmed = trim($transaction_id);
$montant_cybank_trimmed = trim($montant_cybank); // Le montant a déjà un format spécifique, trim est une sécurité supplémentaire
$vendeur_code_trimmed = trim($vendeur_code);
$retour_url_trimmed = trim($retour_url);

// 8. Calculer le Hash de Contrôle pour l'envoi en utilisant les valeurs nettoyées (TRIMMED)
// La chaîne de contrôle DOIT correspondre exactement à ce qu'attend CYBank
$control_string_send = $api_key_trimmed . "#" . $transaction_id_trimmed . "#" . $montant_cybank_trimmed . "#" . $vendeur_code_trimmed . "#" . $retour_url_trimmed . "#";
$control_hash_send = md5($control_string_send);

// 9. Stocker les informations nécessaires en session pour vérification au retour
$_SESSION['payment_transaction_id'] = $transaction_id;
$_SESSION['payment_montant'] = $montant_cybank; 
$_SESSION['payment_total_price'] = $totalPrice; 
$_SESSION['payment_vendeur'] = $vendeur_code;
// Conserver les informations de l'utilisateur et du voyage déjà en session
// $_SESSION['user_email'] = $userEmail; // Déjà défini
// $_SESSION['personalized_trip'] = $personalizedTrip; // Déjà défini

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirection vers Paiement - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/Paiement.css">
    <style>
 
        .trip-summary {
            margin-bottom: 30px;
            padding: 20px;
            background-color: rgba(255, 255, 255, 0.05);
            border-radius: 8px;
            border: 1px solid var(--grey);
        }
        .trip-summary h2 {
            color: var(--yellow);
            margin-bottom: 15px;
        }
        .trip-summary p {
            margin-bottom: 8px;
            line-height: 1.5;
        }
        .total-price-display {
            font-size: 1.3em;
            margin-top: 15px;
            font-weight: bold;
            color: var(--white);
        }
        .info-text {
            margin-bottom: 25px;
            font-style: italic;
            color: var(--white2);
        }
        .form-actions {
            display: flex;
            justify-content: space-between; 
            align-items: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">

    <main>
        <div class="payment-container">
            <h1>Finalisation de la commande</h1>

            <div class="trip-summary">
                <h2>Récapitulatif du voyage</h2>
                <p><strong>Voyage :</strong> <?php echo htmlspecialchars($personalizedTrip['tripTitle']); ?></p>
                <p><strong>Dates :</strong> Du <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['debut']))); ?> au <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['fin']))); ?> (<?php echo htmlspecialchars($personalizedTrip['duration']['jours']); ?> jours)</p>
                <!-- Afficher le prix total calculé -->
                <p class="total-price-display"><strong>Montant total à régler :</strong> <?php echo number_format($totalPrice, 2, ',', ' '); ?> €</p>
                 <!-- Afficher l'ID de transaction (peut être utile pour le support) -->
                 <p style="font-size: 0.9em; color: var(--grey); margin-top:10px;"><strong>ID Transaction :</strong> <?php echo htmlspecialchars($transaction_id); ?></p>
            </div>

            <div class="payment-form-container">
                <h2>Redirection vers CYBank</h2>
                 <p class="info-text">Vous allez être redirigé vers notre partenaire de paiement sécurisé CYBank pour finaliser votre transaction de <?php echo number_format($totalPrice, 2, ',', ' '); ?> €.</p>
                <!-- Formulaire envoyant les données à CYBank -->
                <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST" id="cybank-form">
                    <!-- Champs cachés requis par CYBank -->
                    <input type="hidden" name="transaction" value="<?php echo htmlspecialchars($transaction_id); ?>">
                    <input type="hidden" name="montant" value="<?php echo htmlspecialchars($montant_cybank); ?>">
                    <input type="hidden" name="vendeur" value="<?php echo htmlspecialchars($vendeur_code); ?>">
                    <input type="hidden" name="retour" value="<?php echo htmlspecialchars($retour_url); ?>">
                    <input type="hidden" name="control" value="<?php echo htmlspecialchars($control_hash_send); ?>">

                    <!-- Options supplémentaires (facultatives, vérifier la doc CYBank) -->
                    <!-- <input type="hidden" name="email" value="<?php //echo htmlspecialchars($userEmail); ?>"> -->
                    <!-- <input type="hidden" name="ref_commande" value="<?php //echo 'commande_' . $transaction_id; ?>"> -->

                    <div class="form-actions">
                         <!-- Lien pour annuler et retourner au résumé du voyage -->
                         <a href="voyage_resume.php" class="secondary-button">Annuler</a>
                         <!-- Bouton pour soumettre le formulaire et rediriger vers CYBank -->
                        <button type="submit" class="primary-button">Procéder au Paiement</button>
                    </div>
                </form>
                
            </div>

        </div>
    </main>

    <?php include('footer.php'); ?>
</body>
</html>