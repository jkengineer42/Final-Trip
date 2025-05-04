<?php
// Démarre la session pour récupérer les données personnalisées du voyage
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    // If not logged in, they shouldn't be able to confirm a trip, redirect to login
    header('Location: Connexion.php');
    exit;
}

// Vérifie si des données de voyage personnalisé existent dans la session
if (!isset($_SESSION['personalized_trip'])) {
    // Redirige vers la page d'accueil ou destination si aucune donnée n'est disponible
    header('Location: Destination.php'); // Or Accueil.php
    exit;
}


$personalizedTrip = $_SESSION['personalized_trip'];


$basePrice = floatval(str_replace(['€', ' '], '', $personalizedTrip['originalPrice']));
$optionsPrice = 0;
foreach ($personalizedTrip['stages'] as $stage) {
    foreach ($stage['options'] as $category => $option) {
        $priceStr = str_replace(['€', ' '], '', $option['prix'] ?? '0');
        if (is_numeric($priceStr) && floatval($priceStr) > 0) {
            $price = floatval($priceStr);
            $participants = ($category === 'activites' && isset($option['participants'])) ? intval($option['participants']) : 1;
            $optionsPrice += $price * $participants;
        }
    }
}
$totalPriceForDisplay = $basePrice + $optionsPrice;
// -----------------------------------------------------------------

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résumé de votre voyage personnalisé</title>
   
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/voyage_resume.css">
    <script src="../Javascript/theme.js"></script>
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">

    <div class="trip-summary">
    

        <header class="summary-header">
            <h1>Résumé de votre voyage personnalisé</h1>
            <div class="trip-basic-info">
                <h2><?php echo htmlspecialchars($personalizedTrip['tripTitle']); ?></h2>
                <p><strong>Durée:</strong> <?php echo htmlspecialchars($personalizedTrip['duration']['jours']); ?> jours</p>
                <p><strong>Date de départ:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['debut']))); ?></p>
                <p><strong>Date de retour:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['fin']))); ?></p>
                 <!-- Display the calculated total price -->
                 <p class="total-price-summary"><strong>Prix estimé :</strong> <?php echo number_format($totalPriceForDisplay, 2, ',', ' '); ?> €</p>
            </div>
        </header>

        <section class="stages-summary">
            <h3>Votre itinéraire personnalisé</h3>

            <?php foreach ($personalizedTrip['stages'] as $stage): ?>
                <div class="summary-stage-card">
                    <h4>Jour <?php echo htmlspecialchars($stage['day']); ?>: <?php echo htmlspecialchars($stage['title']); ?></h4>

                    <div class="selected-options">
                        <!-- Hebergement -->
                        <div class="summary-option">
                            <span class="option-label">Hébergement:</span>
                            <span class="option-value"><?php echo htmlspecialchars($stage['options']['hebergement']['nom']); ?></span>
                            <?php if (isset($stage['options']['hebergement']['prix']) && $stage['options']['hebergement']['prix'] !== "inclus"): ?>
                                <span class="option-price">(<?php echo htmlspecialchars($stage['options']['hebergement']['prix']); ?>)</span>
                            <?php endif; ?>
                        </div>

                        <!-- Restauration -->
                         <div class="summary-option">
                            <span class="option-label">Restauration:</span>
                            <span class="option-value"><?php echo htmlspecialchars($stage['options']['restauration']['nom']); ?></span>
                             <?php if (isset($stage['options']['restauration']['prix']) && $stage['options']['restauration']['prix'] !== "inclus"): ?>
                                <span class="option-price">(<?php echo htmlspecialchars($stage['options']['restauration']['prix']); ?>)</span>
                             <?php endif; ?>
                        </div>

                        <!-- Activites -->
                        <div class="summary-option">
                            <span class="option-label">Activité:</span>
                            <span class="option-value">
                                <?php echo htmlspecialchars($stage['options']['activites']['nom']); ?>
                                <span class="participant-info">
                                    (<?php echo htmlspecialchars($stage['options']['activites']['participants']); ?> participant<?php echo $stage['options']['activites']['participants'] > 1 ? 's' : ''; ?>)
                                </span>
                            </span>
                             <?php if (isset($stage['options']['activites']['prix']) && $stage['options']['activites']['prix'] !== "inclus"): ?>
                                <span class="option-price">(<?php echo htmlspecialchars($stage['options']['activites']['prix']); ?>)</span>
                             <?php endif; ?>
                        </div>

                        <!-- Transport -->
                         <div class="summary-option">
                            <span class="option-label">Transport:</span>
                            <span class="option-value"><?php echo htmlspecialchars($stage['options']['transport']['nom']); ?></span>
                             <?php if (isset($stage['options']['transport']['prix']) && $stage['options']['transport']['prix'] !== "inclus"): ?>
                                <span class="option-price">(<?php echo htmlspecialchars($stage['options']['transport']['prix']); ?>)</span>
                             <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

       
        <div class="total-summary">
             <h3>Total Estimé</h3>
             <div class="price-breakdown">
                
                 <div class="price-item">
                     <span>Prix de base</span>
                     <span><?php echo htmlspecialchars($personalizedTrip['originalPrice']); ?></span>
                 </div>
                  <div class="price-item">
                     <span>Options ajoutées (estimé)</span>
                     <span><?php echo number_format($optionsPrice, 2, ',', ' '); ?> €</span>
                 </div>
             </div>
             <div class="total-price">
                 <span>Total</span>
                 <span><?php echo number_format($totalPriceForDisplay, 2, ',', ' '); ?> €</span>
             </div>
        </div>


       
        <div class="actions">
            <a href="voyage_detail.php?id=<?php echo htmlspecialchars($personalizedTrip['tripId']); ?>" class="secondary-button">
                Modifier les options
            </a>
          
            <a href="paiement.php" class="primary-button">
                Procéder au Paiement
            </a>
        </div>


    </div>

    <?php include('footer.php'); ?>
</body>
</html>
