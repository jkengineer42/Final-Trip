<?php
require_once 'sessions.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header('Location: Connexion.php?error=login_required_for_print');
    exit;
}

// Vérifie si des données de voyage personnalisé existent dans la session
if (!isset($_SESSION['personalized_trip'])) {
    header('Location: Destination.php?error=no_trip_to_print');
    exit;
}

$personalizedTrip = $_SESSION['personalized_trip'];
$totalPriceForDisplay = isset($personalizedTrip['calculatedTotalPrice']) ? floatval($personalizedTrip['calculatedTotalPrice']) : 0.0;
$originalPriceNumeric = 0;
if (isset($personalizedTrip['originalPrice'])) {
    $priceStr = $personalizedTrip['originalPrice'];
    $priceClean = preg_replace('/[^\d,\.]/', '', $priceStr);
    $originalPriceNumeric = floatval(str_replace(',', '.', $priceClean));
}
$optionsPrice = $totalPriceForDisplay - $originalPriceNumeric;

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Récapitulatif Imprimable - <?php echo htmlspecialchars($personalizedTrip['tripTitle']); ?></title>
    <link rel="stylesheet" href="../Css/root.css"> 
    <link rel="stylesheet" href="../Css/print-recap.css">
    <style>
        /* Styles de base pour la page avant impression (boutons, etc.) */
        body {
            font-family: 'DM Sans', sans-serif;
            background-color: var(--grey);
            color: var(--white);
            margin: 0;
            padding: 20px;
        }
        .print-container {
            background-color: var(--darker-grey);
            padding: 30px;
            border-radius: 10px;
            max-width: 800px;
            margin: 20px auto;
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }
        .print-header h1 {
            color: var(--yellow);
            text-align: center;
            margin-bottom: 10px;
            font-size: 2em;
        }
        .print-header p {
            text-align: center;
            font-size: 1.1em;
            margin-bottom: 20px;
        }
        .trip-main-info {
            background-color: var(--grey);
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .trip-main-info p { margin: 5px 0; }

        .stage-details {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--grey);
        }
        .stage-details:last-child {
            border-bottom: none;
        }
        .stage-title {
            color: var(--yellow);
            font-size: 1.5em;
            margin-bottom: 10px;
        }
        .option-category { margin-bottom: 8px; }
        .option-label { font-weight: bold; color: var(--white2); }
        .option-value { color: var(--white); }
        .option-price { font-style: italic; color: var(--light-grey); }
        
        .print-total {
            text-align: right;
            font-size: 1.3em;
            font-weight: bold;
            color: var(--yellow);
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid var(--yellow);
        }

        .print-actions {
            text-align: center;
            margin-top: 30px;
        }
        .print-actions button {
            padding: 10px 20px;
            background-color: var(--yellow);
            color: var(--black_f);
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            margin: 0 10px;
        }
         .print-actions button:hover {
            background-color: var(--yellow2);
        }
    </style>
</head>
<body>
    <div class="print-container">
        <div class="print-header">
            <h1>Récapitulatif de Voyage</h1>
            <p><?php echo htmlspecialchars($personalizedTrip['tripTitle']); ?></p>
        </div>

        <div class="trip-main-info">
            <p><strong>Durée totale :</strong> <?php echo htmlspecialchars($personalizedTrip['duration']['jours']); ?> jours</p>
            <p><strong>Date de départ :</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['debut']))); ?></p>
            <p><strong>Date de retour :</strong> <?php echo htmlspecialchars(date('d/m/Y', strtotime($personalizedTrip['duration']['fin']))); ?></p>
            <p><strong>Prix de base :</strong> <?php echo htmlspecialchars($personalizedTrip['originalPrice']); ?></p>
        </div>

        <h2>Détail jour par jour :</h2>

        <?php foreach ($personalizedTrip['stages'] as $stage): ?>
            <div class="stage-details">
                <h3 class="stage-title">Jour <?php echo htmlspecialchars($stage['day']); ?> : <?php echo htmlspecialchars($stage['title']); ?></h3>
                
                <?php if (isset($stage['options']['hebergement'])): ?>
                <div class="option-category">
                    <span class="option-label">Hébergement :</span>
                    <span class="option-value"><?php echo htmlspecialchars($stage['options']['hebergement']['nom']); ?></span>
                    <?php if (isset($stage['options']['hebergement']['prix']) && $stage['options']['hebergement']['prix'] !== "inclus"): ?>
                        <span class="option-price">(<?php echo htmlspecialchars($stage['options']['hebergement']['prix']); ?>)</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($stage['options']['restauration'])): ?>
                <div class="option-category">
                    <span class="option-label">Restauration :</span>
                    <span class="option-value"><?php echo htmlspecialchars($stage['options']['restauration']['nom']); ?></span>
                    <?php if (isset($stage['options']['restauration']['prix']) && $stage['options']['restauration']['prix'] !== "inclus"): ?>
                        <span class="option-price">(<?php echo htmlspecialchars($stage['options']['restauration']['prix']); ?>)</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($stage['options']['activites'])): ?>
                <div class="option-category">
                    <span class="option-label">Activité :</span>
                    <span class="option-value">
                        <?php echo htmlspecialchars($stage['options']['activites']['nom']); ?>
                        (<?php echo htmlspecialchars($stage['options']['activites']['participants']); ?> participant(s))
                    </span>
                    <?php if (isset($stage['options']['activites']['prix']) && $stage['options']['activites']['prix'] !== "inclus"): ?>
                        <span class="option-price">(<?php echo htmlspecialchars($stage['options']['activites']['prix']); ?>)</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (isset($stage['options']['transport'])): ?>
                <div class="option-category">
                    <span class="option-label">Transport :</span>
                    <span class="option-value"><?php echo htmlspecialchars($stage['options']['transport']['nom']); ?></span>
                    <?php if (isset($stage['options']['transport']['prix']) && $stage['options']['transport']['prix'] !== "inclus"): ?>
                        <span class="option-price">(<?php echo htmlspecialchars($stage['options']['transport']['prix']); ?>)</span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div class="print-total">
            Total estimé du voyage : <?php echo number_format($totalPriceForDisplay, 2, ',', ' '); ?> €
        </div>

        <div class="print-actions">
            <button onclick="window.print()">Imprimer</button>
            <button onclick="window.close()">Fermer</button>
        </div>
    </div>
</body>
</html>