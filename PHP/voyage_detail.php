<?php
// Démarre la session pour stocker les données personnalisées du voyage
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    $profileLink = 'Connexion.php'; // Lien vers la page de connexion
}

// Récupère l'ID du voyage depuis le paramètre URL
$tripId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Vérifie si le formulaire a été soumis pour aller à la page récapitulative
if (isset($_POST['submit_personalization'])) {
    // Collecte toutes les options sélectionnées
    $personalizedTrip = [
        'tripId' => $tripId,
        'tripTitle' => $_POST['trip_title'],
        'originalPrice' => $_POST['trip_price'],
        'duration' => [
            'debut' => $_POST['trip_start_date'],
            'fin' => $_POST['trip_end_date'],
            'jours' => $_POST['trip_days']
        ],
        'stages' => []
    ];

    // Parcourt chaque étape pour obtenir les options sélectionnées
    foreach ($_POST['etape_ids'] as $i => $etapeId) {
        // Pour l'hébergement
        $hebergementId = $_POST["hebergement_$etapeId"];
        $hebergementName = $_POST["hebergement_{$etapeId}_name_{$hebergementId}"];
        $hebergementPrice = $_POST["hebergement_{$etapeId}_price_{$hebergementId}"];
        
        // Pour la restauration
        $restaurationId = $_POST["restauration_$etapeId"];
        $restaurationName = $_POST["restauration_{$etapeId}_name_{$restaurationId}"];
        $restaurationPrice = $_POST["restauration_{$etapeId}_price_{$restaurationId}"];
        
        // Pour les activités
        $activitesId = $_POST["activites_$etapeId"];
        $activitesName = $_POST["activites_{$etapeId}_name_{$activitesId}"];
        $activitesPrice = $_POST["activites_{$etapeId}_price_{$activitesId}"];
        $participants = isset($_POST["participants_$etapeId"]) ? $_POST["participants_$etapeId"] : 1;
        
        // Pour le transport
        $transportId = $_POST["transport_$etapeId"];
        $transportName = $_POST["transport_{$etapeId}_name_{$transportId}"];
        $transportPrice = $_POST["transport_{$etapeId}_price_{$transportId}"];
        
        $stage = [
            'id' => $etapeId,
            'title' => $_POST['etape_titles'][$i],
            'day' => $_POST['etape_days'][$i],
            'options' => [
                'hebergement' => [
                    'id' => $hebergementId,
                    'nom' => $hebergementName,
                    'prix' => $hebergementPrice
                ],
                'restauration' => [
                    'id' => $restaurationId,
                    'nom' => $restaurationName,
                    'prix' => $restaurationPrice
                ],
                'activites' => [
                    'id' => $activitesId,
                    'nom' => $activitesName,
                    'prix' => $activitesPrice,
                    'participants' => $participants
                ],
                'transport' => [
                    'id' => $transportId,
                    'nom' => $transportName,
                    'prix' => $transportPrice
                ]
            ]
        ];
        
        $personalizedTrip['stages'][] = $stage;
    }
    
    // Stocke dans la session
    $_SESSION['personalized_trip'] = $personalizedTrip;
    
    // Redirige vers la page récapitulative
    header('Location: voyage_resume.php');
    exit;
}

// Charge les données des voyages depuis le fichier JSON
$jsonData = file_get_contents('../data/voyages.json');
$data = json_decode($jsonData, true);

// Trouve le voyage demandé
$trip = null;
foreach ($data['voyages'] as $voyage) {
    if ($voyage['id'] === $tripId) {
        $trip = $voyage;
        break;
    }
}

// Gère le cas où le voyage n'est pas trouvé
if (!$trip) {
    echo "Voyage non trouvé";
    exit;
}

// Initialiser la variable pour le prix total estimé
$estimatedPrice = floatval(str_replace(['€', ' '], '', $trip['prix']));
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnalisation de voyage - <?php echo htmlspecialchars($trip['titre']); ?></title>
    <link rel="stylesheet" href="../Css/voyage_detail.css">
    <script src="../Javascript/theme.js"></script>
    <script src="../Javascript/voyage_detail_prix.js"></script>
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">
    
    <div class="detailed-trip-view">
        
        <section class="trip-header">
            <h1><?php echo htmlspecialchars($trip['titre']); ?></h1>
            <div class="trip-info">
                <p><strong>Destination:</strong> <?php echo htmlspecialchars($trip['pays']); ?></p>
                <p><strong>Durée:</strong> <?php echo htmlspecialchars($trip['duree']['jours']); ?> jours</p>
                <p><strong>Prix de base:</strong> <?php echo htmlspecialchars($trip['prix']); ?></p>
            </div>
            <p class="trip-description"><?php echo htmlspecialchars($trip['description']); ?></p>
        </section>

        <!-- Bouton Ajouter au panier -->
        <div class="trip-quick-actions">
            <a href="panier.php?action=ajouter&id=<?php echo $tripId; ?>" class="add-to-cart-button">
                <img src="../assets/icon/Shopping cart.png" alt="Panier" class="cart-icon">
                Ajouter au panier
            </a>
        </div>

        <!-- Affichage du prix total estimé -->
        <div class="price-estimation">
            <h3>Prix total estimé: <span id="estimated-price"><?php echo htmlspecialchars($trip['prix']); ?></span></h3>
            <p class="price-note">Le prix s'ajuste automatiquement en fonction de vos choix d'options.</p>
        </div>

        <section class="trip-stages">
            <h2>Personnalisez votre voyage</h2>
            
            <form method="post" action="" id="trip-customization-form">
                <!-- Champs cachés pour stocker les informations du voyage -->
                <input type="hidden" name="trip_title" value="<?php echo htmlspecialchars($trip['titre']); ?>">
                <input type="hidden" name="trip_price" value="<?php echo htmlspecialchars($trip['prix']); ?>">
                <input type="hidden" name="trip_start_date" value="<?php echo htmlspecialchars($trip['duree']['debut']); ?>">
                <input type="hidden" name="trip_end_date" value="<?php echo htmlspecialchars($trip['duree']['fin']); ?>">
                <input type="hidden" name="trip_days" value="<?php echo htmlspecialchars($trip['duree']['jours']); ?>">
                <input type="hidden" id="base-price" value="<?php echo $estimatedPrice; ?>">
                
                <?php foreach ($trip['etapes'] as $index => $etape): ?>
                    <div class="stage-card" data-stage-id="<?php echo htmlspecialchars($etape['id']); ?>">
                        <h3 class="stage-day">Jour <?php echo htmlspecialchars($etape['jour']); ?>: <?php echo htmlspecialchars($etape['titre']); ?></h3>
                        <p><?php echo htmlspecialchars($etape['description']); ?></p>
                        
                        <!-- Stocke l'ID de l'étape et les informations -->
                        <input type="hidden" name="etape_ids[]" value="<?php echo htmlspecialchars($etape['id']); ?>">
                        <input type="hidden" name="etape_titles[]" value="<?php echo htmlspecialchars($etape['titre']); ?>">
                        <input type="hidden" name="etape_days[]" value="<?php echo htmlspecialchars($etape['jour']); ?>">
                        
                        <div class="options-container">
                            <!-- Options d'hébergement -->
                            <div class="option-category">
                                <h4>Hébergement</h4>
                                <div class="option-list">
                                    <?php foreach ($etape['options']['hebergement'] as $option): ?>
                                        <div class="option-item">
                                            <input 
                                                type="radio" 
                                                id="hebergement-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>" 
                                                name="hebergement_<?php echo $etape['id']; ?>" 
                                                value="<?php echo $option['id']; ?>"
                                                data-price="<?php echo is_numeric(str_replace(['€', ' '], '', $option['prix'])) ? str_replace(['€', ' '], '', $option['prix']) : '0'; ?>"
                                                data-category="hebergement"
                                                data-stage-id="<?php echo $etape['id']; ?>"
                                                class="price-option"
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
                                            <!-- Champs cachés pour stocker nom et prix -->
                                            <input type="hidden" name="hebergement_<?php echo $etape['id']; ?>_name_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['nom']); ?>">
                                            <input type="hidden" name="hebergement_<?php echo $etape['id']; ?>_price_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['prix']); ?>">
                                            <label for="hebergement-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['nom']); ?> 
                                                <?php if ($option['prix'] !== "inclus"): ?>
                                                    (<?php echo htmlspecialchars($option['prix']); ?>)
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Options de restauration -->
                            <div class="option-category">
                                <h4>Restauration</h4>
                                <div class="option-list">
                                    <?php foreach ($etape['options']['restauration'] as $option): ?>
                                        <div class="option-item">
                                            <input 
                                                type="radio" 
                                                id="restauration-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>" 
                                                name="restauration_<?php echo $etape['id']; ?>" 
                                                value="<?php echo $option['id']; ?>"
                                                data-price="<?php echo is_numeric(str_replace(['€', ' '], '', $option['prix'])) ? str_replace(['€', ' '], '', $option['prix']) : '0'; ?>"
                                                data-category="restauration"
                                                data-stage-id="<?php echo $etape['id']; ?>"
                                                class="price-option"
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
                                            <!-- Champs cachés pour stocker nom et prix -->
                                            <input type="hidden" name="restauration_<?php echo $etape['id']; ?>_name_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['nom']); ?>">
                                            <input type="hidden" name="restauration_<?php echo $etape['id']; ?>_price_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['prix']); ?>">
                                            <label for="restauration-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['nom']); ?> 
                                                <?php if ($option['prix'] !== "inclus"): ?>
                                                    (<?php echo htmlspecialchars($option['prix']); ?>)
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <!-- Options d'activités -->
                            <div class="option-category">
                                <h4>Activités</h4>
                                <div class="option-list">
                                    <?php foreach ($etape['options']['activites'] as $option): ?>
                                        <div class="option-item">
                                            <input 
                                                type="radio" 
                                                id="activites-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>" 
                                                name="activites_<?php echo $etape['id']; ?>" 
                                                value="<?php echo $option['id']; ?>"
                                                data-price="<?php echo is_numeric(str_replace(['€', ' '], '', $option['prix'])) ? str_replace(['€', ' '], '', $option['prix']) : '0'; ?>"
                                                data-category="activites"
                                                data-stage-id="<?php echo $etape['id']; ?>"
                                                class="price-option"
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
                                            <!-- Champs cachés pour stocker nom et prix -->
                                            <input type="hidden" name="activites_<?php echo $etape['id']; ?>_name_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['nom']); ?>">
                                            <input type="hidden" name="activites_<?php echo $etape['id']; ?>_price_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['prix']); ?>">
                                            <label for="activites-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['nom']); ?> 
                                                <?php if ($option['prix'] !== "inclus"): ?>
                                                    (<?php echo htmlspecialchars($option['prix']); ?>)
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <!-- Ajout du sélecteur de participants pour les activités -->
                                    <?php 
                                    // Détermine le nombre maximum de participants pour l'activité par défaut
                                    $defaultOption = null;
                                    foreach ($etape['options']['activites'] as $option) {
                                        if ($option['par_defaut']) {
                                            $defaultOption = $option;
                                            break;
                                        }
                                    }
                                    $maxParticipants = $defaultOption ? ($defaultOption['max_personnes'] ?? 10) : 10;
                                    ?>
                                    <div class="participant-selector">
                                        <label for="participants_<?php echo $etape['id']; ?>">Nombre de participants :</label>
                                        <select 
                                            name="participants_<?php echo $etape['id']; ?>" 
                                            id="participants_<?php echo $etape['id']; ?>"
                                            data-stage-id="<?php echo $etape['id']; ?>"
                                            class="participants-select"
                                        >
                                            <?php for ($i = 1; $i <= $maxParticipants; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Options de transport -->
                            <div class="option-category">
                                <h4>Transport</h4>
                                <div class="option-list">
                                    <?php foreach ($etape['options']['transport'] as $option): ?>
                                        <div class="option-item">
                                            <input 
                                                type="radio" 
                                                id="transport-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>" 
                                                name="transport_<?php echo $etape['id']; ?>" 
                                                value="<?php echo $option['id']; ?>"
                                                data-price="<?php echo is_numeric(str_replace(['€', ' '], '', $option['prix'])) ? str_replace(['€', ' '], '', $option['prix']) : '0'; ?>"
                                                data-category="transport"
                                                data-stage-id="<?php echo $etape['id']; ?>"
                                                class="price-option"
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
                                            <!-- Champs cachés pour stocker nom et prix -->
                                            <input type="hidden" name="transport_<?php echo $etape['id']; ?>_name_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['nom']); ?>">
                                            <input type="hidden" name="transport_<?php echo $etape['id']; ?>_price_<?php echo $option['id']; ?>" value="<?php echo htmlspecialchars($option['prix']); ?>">
                                            <label for="transport-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['nom']); ?> 
                                                <?php if ($option['prix'] !== "inclus"): ?>
                                                    (<?php echo htmlspecialchars($option['prix']); ?>)
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="actions">
                    <a href="Destination.php" class="secondary-button">Retour</a>
                    <button type="submit" name="submit_personalization" class="primary-button">Voir le résumé</button>
                </div>
            </form>
        </section>
    </div>
    
    <footer>
        <?php include('footer.php'); ?> 
    </footer>
</body>
</html>
