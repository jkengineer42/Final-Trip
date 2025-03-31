<?php
// Démarre la session pour récupérer les données personnalisées du voyage
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    $profileLink = 'Connexion.php'; // Lien vers la page de connexion
}

// Vérifie si des données de voyage personnalisé existent dans la session
if (!isset($_SESSION['personalized_trip'])) {
    // Redirige vers la page d'accueil si aucune donnée n'est disponible
    header('Location: Acceuil.php');
    exit;
}

$personalizedTrip = $_SESSION['personalized_trip'];

// Gère la soumission du formulaire pour finaliser la réservation
if (isset($_POST['confirm_booking'])) {
    // Dans une vraie application, vous enregistreriez ces données dans une base de données
    // Pour l'instant, on affiche juste un message de succès
    $bookingConfirmed = true;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résumé de votre voyage personnalisé</title>
    <link rel="stylesheet" href="/Final-Trip-main/css/voyage_resume.css">
</head>
<body>
<header><?php include('header.php'); ?></header>
    <div class="trip-summary">
        <?php if (isset($bookingConfirmed)): ?>
            <div class="confirmation-message">
                <h2>Votre voyage personnalisé a été enregistré !</h2>
                <p>Un conseiller vous contactera prochainement pour finaliser votre réservation.</p>
                <a href="Accueil.php" class="primary-button">Retour à l'accueil</a>
            </div>
        <?php else: ?>
            <header class="summary-header">
                <h1>Résumé de votre voyage personnalisé</h1>
                <div class="trip-basic-info">
                    <h2><?php echo htmlspecialchars($personalizedTrip['tripTitle']); ?></h2>
                    <p><strong>Durée:</strong> <?php echo htmlspecialchars($personalizedTrip['duration']['jours']); ?> jours</p>
                    <p><strong>Date de départ:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['debut']))); ?></p>
                    <p><strong>Date de retour:</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['fin']))); ?></p>
                </div>
            </header>
            
            <section class="stages-summary">
                <h3>Votre itinéraire personnalisé</h3>
                
                <?php foreach ($personalizedTrip['stages'] as $stage): ?>
                    <div class="summary-stage-card">
                        <h4>Jour <?php echo htmlspecialchars($stage['day']); ?>: <?php echo htmlspecialchars($stage['title']); ?></h4>
                        
                        <div class="selected-options">
                            <div class="summary-option">
                                <span class="option-label">Hébergement:</span>
                                <span class="option-value"><?php echo htmlspecialchars($stage['options']['hebergement']['nom']); ?></span>
                                <span class="option-price"><?php echo htmlspecialchars($stage['options']['hebergement']['prix']); ?></span>
                            </div>
                            
                            <div class="summary-option">
                                <span class="option-label">Restauration:</span>
                                <span class="option-value"><?php echo htmlspecialchars($stage['options']['restauration']['nom']); ?></span>
                                <span class="option-price"><?php echo htmlspecialchars($stage['options']['restauration']['prix']); ?></span>
                            </div>
                            
                            <div class="summary-option">
                                <span class="option-label">Activité:</span>
                                <span class="option-value">
                                    <?php echo htmlspecialchars($stage['options']['activites']['nom']); ?> 
                                    <span class="participant-info">
                                        (<?php echo htmlspecialchars($stage['options']['activites']['participants']); ?> participant<?php echo $stage['options']['activites']['participants'] > 1 ? 's' : ''; ?>)
                                    </span>
                                </span>
                                <span class="option-price"><?php echo htmlspecialchars($stage['options']['activites']['prix']); ?></span>
                            </div>
                            
                            <div class="summary-option">
                                <span class="option-label">Transport:</span>
                                <span class="option-value"><?php echo htmlspecialchars($stage['options']['transport']['nom']); ?></span>
                                <span class="option-price"><?php echo htmlspecialchars($stage['options']['transport']['prix']); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </section>
            
            <div class="summary-note">
                <p>Note: Les modifications d'options n'entraînent pas de changement direct du prix, de la durée ou d'autres détails du voyage. Un conseiller vous contactera pour finaliser votre réservation personnalisée.</p>
            </div>
            
            <div class="summary-actions">
                <form method="post" action="" class="confirmation-form">
                    <a href="voyage_detail.php?id=<?php echo htmlspecialchars($personalizedTrip['tripId']); ?>" class="secondary-button">
                        Modifier les options
                    </a>
                    <button type="submit" name="confirm_booking" class="primary-button">
                        Valider cette configuration
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </div>
    <footer><?php include('footer.php'); ?></footer>
</body>
</html>
