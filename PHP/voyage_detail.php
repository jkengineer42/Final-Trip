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
        $stage = [
            'id' => $etapeId,
            'title' => $_POST['etape_titles'][$i],
            'day' => $_POST['etape_days'][$i],
            'options' => [
                'hebergement' => [
                    'id' => $_POST["hebergement_$etapeId"],
                    'nom' => $_POST["hebergement_{$etapeId}_name"],
                    'prix' => $_POST["hebergement_{$etapeId}_price"]
                ],
                'restauration' => [
                    'id' => $_POST["restauration_$etapeId"],
                    'nom' => $_POST["restauration_{$etapeId}_name"],
                    'prix' => $_POST["restauration_{$etapeId}_price"]
                ],
                'activites' => [
                    'id' => $_POST["activites_$etapeId"],
                    'nom' => $_POST["activites_{$etapeId}_name"],
                    'prix' => $_POST["activites_{$etapeId}_price"],
                    'participants' => $_POST["participants_$etapeId"]
                ],
                'transport' => [
                    'id' => $_POST["transport_$etapeId"],
                    'nom' => $_POST["transport_{$etapeId}_name"],
                    'prix' => $_POST["transport_{$etapeId}_price"]
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
$jsonData = file_get_contents(../data/voyages.json');
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
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnalisation de voyage - <?php echo htmlspecialchars($trip['titre']); ?></title>
    <link rel="stylesheet" href="/Final-Trip-main/css/voyage_detail.css">
</head>
<body>
 <?php include('header.php'); ?>
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

        <section class="trip-stages">
            <h2>Personnalisez votre voyage</h2>
            
            <form method="post" action="">
                <!-- Champs cachés pour stocker les informations du voyage -->
                <input type="hidden" name="trip_title" value="<?php echo htmlspecialchars($trip['titre']); ?>">
                <input type="hidden" name="trip_price" value="<?php echo htmlspecialchars($trip['prix']); ?>">
                <input type="hidden" name="trip_start_date" value="<?php echo htmlspecialchars($trip['duree']['debut']); ?>">
                <input type="hidden" name="trip_end_date" value="<?php echo htmlspecialchars($trip['duree']['fin']); ?>">
                <input type="hidden" name="trip_days" value="<?php echo htmlspecialchars($trip['duree']['jours']); ?>">
                
                <?php foreach ($trip['etapes'] as $index => $etape): ?>
                    <div class="stage-card">
                        <h3>Jour <?php echo htmlspecialchars($etape['jour']); ?>: <?php echo htmlspecialchars($etape['titre']); ?></h3>
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
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
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
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
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
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
                                            <label for="activites-<?php echo $etape['id']; ?>-<?php echo $option['id']; ?>">
                                                <?php echo htmlspecialchars($option['nom']); ?> 
                                                <?php if ($option['prix'] !== "inclus"): ?>
                                                    (<?php echo htmlspecialchars($option['prix']); ?>)
                                                <?php endif; ?>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
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
                                                <?php echo $option['par_defaut'] ? 'checked' : ''; ?>
                                            >
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
