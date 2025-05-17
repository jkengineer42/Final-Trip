<?php
require_once 'sessions.php'; // Handles session_start(), sets $isLoggedIn, $currentUserEmail, etc.
require_once 'getapikey.php'; 


if (!$isLoggedIn) { // Check if user is logged in
    $_SESSION['redirect_after_login'] = 'panier.php'; // Or a more appropriate redirect
    header("Location: Connexion.php?error=login_required_for_payment");
    exit();
}


if (!isset($_SESSION['personalized_trip'])) {
    // If no trip is being personalized, redirect to destinations or cart
    header("Location: Destination.php?error=no_trip_for_payment"); 
    exit();
}

// $currentUserEmail is available from sessions.php
$personalizedTrip = $_SESSION['personalized_trip'];
// $profileLink is available from sessions.php for the header
$totalPrice = isset($personalizedTrip['calculatedTotalPrice']) ? floatval($personalizedTrip['calculatedTotalPrice']) : 0.0;


// --- Préparation des données pour CYBank ---
$transaction_id = substr(md5(uniqid('FT_') . rand(100, 999)), 0, 23);
$montant_cybank = $totalPrice;
//______________PROF____________
$vendeur_code = 'MEF-1_H';
$retour_url = 'http://localhost:8080/Final-Trip-main/PHP/retour_paiement.php';
$api_key = getAPIKey($vendeur_code);
$control_hash_send = md5( 
    $api_key
. "#" . $transaction_id
. "#" . $montant_cybank
. "#" . $vendeur_code
. "#" . $retour_url . "#" 
);
$_SESSION['payment_transaction_id'] = $transaction_id;
$_SESSION['payment_montant'] = (string)$montant_cybank; // Stocker comme chaîne pour une comparaison exacte
$_SESSION['payment_vendeur'] = $vendeur_code;
$_SESSION['payment_total_price'] = $totalPrice; // Pour l'enregistrement si succès

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirection vers Paiement - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/Paiement.css">
    <script src="../Javascript/theme.js"></script>
    
   
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
                <p class="total-price-display"><strong>Montant total à régler :</strong> <?php echo number_format($totalPrice, 2, ',', ' '); ?> €</p>
                 <p style="font-size: 0.9em; color: var(--grey); margin-top:10px;"><strong>ID Transaction :</strong> <?php echo htmlspecialchars($transaction_id); ?></p>
            </div>

            <div class="payment-form-container">
                <h2>Redirection vers CYBank</h2>
                 <p class="info-text">Vous allez être redirigé vers notre partenaire de paiement sécurisé CYBank pour finaliser votre transaction de <?php echo number_format($totalPrice, 2, ',', ' '); ?> €.</p>
               
                <form action="https://www.plateforme-smc.fr/cybank/index.php" method="POST" id="cybank-form">
                    
                    <input type="hidden" name="transaction" value="<?php echo htmlspecialchars($transaction_id); ?>">
                    <input type="hidden" name="montant" value="<?php echo htmlspecialchars($montant_cybank); ?>">
                    <input type="hidden" name="vendeur" value="<?php echo htmlspecialchars($vendeur_code); ?>">
                    <input type="hidden" name="retour" value="<?php echo htmlspecialchars($retour_url); ?>">
                    <input type="hidden" name="control" value="<?php echo htmlspecialchars($control_hash_send); ?>">



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
