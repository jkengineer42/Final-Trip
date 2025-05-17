<?php
require_once 'sessions.php';

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    // Si l'utilisateur n'est pas connecté, il ne peut pas personnaliser/résumer/ajouter au panier
    // Rediriger vers la connexion avec un message ou un paramètre de redirection
    $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI']; // Stocke la page actuelle
    header('Location: Connexion.php?error=login_required');
    exit;
}

// Récupère l'ID du voyage depuis le paramètre URL
$tripId = isset($_GET['id']) ? intval($_GET['id']) : null;

// Charge les données des voyages depuis le fichier JSON
$jsonData = @file_get_contents('../data/voyages.json');
$data = $jsonData ? json_decode($jsonData, true) : null;

// Trouve le voyage demandé
$trip = null;
$basePrice = 0.0; // Initialiser le prix de base
if ($data && isset($data['voyages']) && $tripId !== null) {
    foreach ($data['voyages'] as $voyage) {
        if (isset($voyage['id']) && $voyage['id'] === $tripId) {
            $trip = $voyage;
            // Calculer le prix de base ici pour l'avoir plus tard
            $priceStr = isset($trip['prix']) ? $trip['prix'] : '0';
            $priceClean = preg_replace('/[^\d,\.]/', '', $priceStr);
            $basePrice = floatval(str_replace(',', '.', $priceClean));
            break;
        }
    }
}

// --- Traitement du Formulaire (Clic sur "Voir le résumé") ---
if (isset($_POST['submit_personalization']) && $trip !== null) {

    // 1. Récupérer le prix dynamique soumis via le champ caché
    // Utiliser le prix de base comme fallback si le champ n'est pas envoyé ou invalide
    $submittedEstimatedPrice = isset($_POST['estimated_total_price']) ? floatval($_POST['estimated_total_price']) : $basePrice;

    // 2. Collecter les options sélectionnées pour le résumé
    $personalizedTrip = [
        'tripId' => $tripId,
        'tripTitle' => $_POST['trip_title'],
        'originalPrice' => $_POST['trip_price'], // Prix de base affiché
        'calculatedTotalPrice' => $submittedEstimatedPrice, // Prix dynamique calculé
        'duration' => [
            'debut' => $_POST['trip_start_date'],
            'fin' => $_POST['trip_end_date'],
            'jours' => $_POST['trip_days']
        ],
        'stages' => []
    ];

    // Boucle pour collecter les options de chaque étape (comme avant)
    if (isset($_POST['etape_ids'])) {
        foreach ($_POST['etape_ids'] as $i => $etapeId) {
            $stage = [
                'id' => $etapeId,
                'title' => $_POST['etape_titles'][$i] ?? 'Étape sans titre',
                'day' => $_POST['etape_days'][$i] ?? 'Jour inconnu',
                'options' => []
            ];

            // Collecter chaque catégorie d'option...
            foreach(['hebergement', 'restauration', 'activites', 'transport'] as $category) {
                $optionKey = "{$category}_{$etapeId}";
                if (isset($_POST[$optionKey])) {
                    $optionId = $_POST[$optionKey];
                    $optionNameKey = "{$category}_{$etapeId}_name_{$optionId}";
                    $optionPriceKey = "{$category}_{$etapeId}_price_{$optionId}";

                    $stage['options'][$category] = [
                        'id' => $optionId,
                        'nom' => $_POST[$optionNameKey] ?? 'Option inconnue',
                        'prix' => $_POST[$optionPriceKey] ?? 'inclus'
                    ];

                    // Ajouter les participants pour les activités
                    if ($category === 'activites') {
                         $participantsKey = "participants_{$etapeId}";
                         $stage['options'][$category]['participants'] = isset($_POST[$participantsKey]) ? intval($_POST[$participantsKey]) : 1;
                    }
                }
            }
            $personalizedTrip['stages'][] = $stage;
        }
    }

    // 3. Stocker le voyage personnalisé complet dans la session pour voyage_resume.php
    $_SESSION['personalized_trip'] = $personalizedTrip;

    // 4. Ajouter/Mettre à jour le panier
    if ($tripId > 0) {
        // Initialiser le panier s'il n'existe pas
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        // Initialiser le stockage des prix dynamiques s'il n'existe pas
        if (!isset($_SESSION['panier_prices'])) {
            $_SESSION['panier_prices'] = [];
        }

        // Ajouter ou mettre à jour la quantité (1 pour un voyage personnalisé)
        $_SESSION['panier'][$tripId] = 1;
        // Stocker le prix dynamique associé à cet ID de voyage dans le panier
        $_SESSION['panier_prices'][$tripId] = $submittedEstimatedPrice;
    }

    // 5. Rediriger vers la page récapitulative
    header('Location: voyage_resume.php');
    exit;
}


// --- Affichage de la page (si pas de soumission ou voyage non trouvé) ---

// Gère le cas où le voyage n'est pas trouvé après le chargement JSON
if (!$trip) {
     include('header.php'); // Afficher header/footer même si erreur
     echo "<link rel='stylesheet' href='../Css/voyage_detail.css'>"; // Charger CSS
     echo "<hr class='hr1'>";
     echo "<div class='detailed-trip-view error'><p>Voyage non trouvé ou ID invalide.</p> <a href='Destination.php' class='secondary-button'>Retour aux destinations</a></div>";
     include('footer.php');
     exit;
}

// Le prix de base a déjà été calculé lors de la recherche du voyage
$estimatedPrice = $basePrice;

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personnalisation - <?php echo htmlspecialchars($trip['titre']); ?></title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/voyage_detail.css">
    <!-- Le JS doit être chargé APRES les éléments HTML qu'il manipule -->
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">

    <div class="detailed-trip-view">

        <section class="trip-header">
            <h1><?php echo htmlspecialchars($trip['titre']); ?></h1>
            <div class="trip-info">
                <p><strong>Destination:</strong> <?php echo htmlspecialchars($trip['pays'] ?? 'N/A'); ?></p>
                <p><strong>Durée:</strong> <?php echo htmlspecialchars($trip['duree']['jours'] ?? 'N/A'); ?> jours</p>
                <p><strong>Prix de base:</strong> <?php echo htmlspecialchars($trip['prix'] ?? 'N/A'); ?></p>
            </div>
            <p class="trip-description"><?php echo htmlspecialchars($trip['description'] ?? 'Aucune description disponible.'); ?></p>
        </section>

        <!-- Affichage du prix total estimé -->
        <div class="price-estimation">
            <h3>Prix total estimé: <span id="estimated-price"><?php echo htmlspecialchars($trip['prix'] ?? 'N/A'); ?></span></h3>
            <p class="price-note">Le prix s'ajuste automatiquement en fonction de vos choix d'options.</p>
        </div>

        <section class="trip-stages">
            <h2>Personnalisez votre voyage</h2>

            <!-- Le formulaire soumet à la même page (action="") pour traiter le POST -->
            <form method="post" action="" id="trip-customization-form">
                <!-- Champ caché pour l'ID du voyage, nécessaire pour l'ajout au panier -->
                 <input type="hidden" name="tripId" value="<?php echo $tripId; ?>">
                <!-- Champs cachés pour stocker les informations de base du voyage -->
                <input type="hidden" name="trip_title" value="<?php echo htmlspecialchars($trip['titre'] ?? ''); ?>">
                <input type="hidden" name="trip_price" value="<?php echo htmlspecialchars($trip['prix'] ?? ''); ?>">
                <input type="hidden" name="trip_start_date" value="<?php echo htmlspecialchars($trip['duree']['debut'] ?? ''); ?>">
                <input type="hidden" name="trip_end_date" value="<?php echo htmlspecialchars($trip['duree']['fin'] ?? ''); ?>">
                <input type="hidden" name="trip_days" value="<?php echo htmlspecialchars($trip['duree']['jours'] ?? ''); ?>">
                <!-- Champ caché pour le prix de base (utile pour JS) -->
                <input type="hidden" id="base-price" value="<?php echo $basePrice; ?>">
                <!-- *** NOUVEAU CHAMP CACHÉ POUR LE PRIX DYNAMIQUE *** -->
                <input type="hidden" name="estimated_total_price" id="hidden-estimated-price" value="<?php echo $estimatedPrice; ?>">

                <?php if (isset($trip['etapes']) && is_array($trip['etapes'])): ?>
                    <?php foreach ($trip['etapes'] as $index => $etape):
                        $etapeId = $etape['id'] ?? $index; // Utiliser l'ID si dispo, sinon l'index
                    ?>
                        <div class="stage-card" data-stage-id="<?php echo htmlspecialchars($etapeId); ?>">
                             <h3 class="stage-day">Jour <?php echo htmlspecialchars($etape['jour'] ?? '?'); ?>: <?php echo htmlspecialchars($etape['titre'] ?? 'Étape'); ?></h3>
                             <p><?php echo htmlspecialchars($etape['description'] ?? ''); ?></p>

                            <!-- Stocke l'ID de l'étape et les informations -->
                            <input type="hidden" name="etape_ids[]" value="<?php echo htmlspecialchars($etapeId); ?>">
                            <input type="hidden" name="etape_titles[]" value="<?php echo htmlspecialchars($etape['titre'] ?? ''); ?>">
                            <input type="hidden" name="etape_days[]" value="<?php echo htmlspecialchars($etape['jour'] ?? ''); ?>">

                            <div class="options-container">
                                <?php foreach (['hebergement', 'restauration', 'activites', 'transport'] as $category): ?>
                                    <?php if (isset($etape['options'][$category]) && is_array($etape['options'][$category])): ?>
                                        <div class="option-category">
                                            <h4><?php echo ucfirst($category); ?></h4>
                                            <div class="option-list">
                                                <?php foreach ($etape['options'][$category] as $option):
                                                    $optionId = $option['id'] ?? uniqid($category . '_'); // Génère un ID si manquant
                                                    $optionPriceStr = $option['prix'] ?? 'inclus';
                                                    $isIncluded = ($optionPriceStr === "inclus");
                                                    $optionPriceNumeric = $isIncluded ? '0' : str_replace(['€', ' '], '', $optionPriceStr);
                                                    $optionPriceNumeric = is_numeric($optionPriceNumeric) ? $optionPriceNumeric : '0'; // Assure que c'est numérique
                                                    $isDefault = isset($option['par_defaut']) && $option['par_defaut'];
                                                ?>
                                                    <div class="option-item">
                                                        <input
                                                            type="radio"
                                                            id="<?php echo $category; ?>-<?php echo $etapeId; ?>-<?php echo $optionId; ?>"
                                                            name="<?php echo $category; ?>_<?php echo $etapeId; ?>"
                                                            value="<?php echo $optionId; ?>"
                                                            data-price="<?php echo $optionPriceNumeric; ?>"
                                                            data-category="<?php echo $category; ?>"
                                                            data-stage-id="<?php echo $etapeId; ?>"
                                                            class="price-option"
                                                            <?php echo $isDefault ? 'checked' : ''; ?>
                                                        >
                                                        <!-- Champs cachés pour stocker nom et prix AFFICHÉ -->
                                                        <input type="hidden" name="<?php echo $category; ?>_<?php echo $etapeId; ?>_name_<?php echo $optionId; ?>" value="<?php echo htmlspecialchars($option['nom'] ?? 'Option'); ?>">
                                                        <input type="hidden" name="<?php echo $category; ?>_<?php echo $etapeId; ?>_price_<?php echo $optionId; ?>" value="<?php echo htmlspecialchars($optionPriceStr); ?>">

                                                        <label for="<?php echo $category; ?>-<?php echo $etapeId; ?>-<?php echo $optionId; ?>">
                                                            <?php echo htmlspecialchars($option['nom'] ?? 'Option'); ?>
                                                            <?php if (!$isIncluded): ?>
                                                                (<?php echo htmlspecialchars($optionPriceStr); ?>)
                                                            <?php endif; ?>
                                                        </label>
                                                    </div>
                                                <?php endforeach; ?>

                                                 <!-- Sélecteur de participants SPÉCIFIQUEMENT pour les activités -->
                                                <?php if ($category === 'activites'): ?>
                                                    <?php
                                                    // Trouver l'option par défaut pour déterminer max_personnes
                                                    $defaultActivityOption = null;
                                                    foreach ($etape['options']['activites'] as $opt) {
                                                        if (isset($opt['par_defaut']) && $opt['par_defaut']) {
                                                            $defaultActivityOption = $opt;
                                                            break;
                                                        }
                                                    }
                                                    // Utiliser max_personnes de l'option par défaut ou 10 comme fallback
                                                    $maxParticipants = isset($defaultActivityOption['max_personnes']) ? intval($defaultActivityOption['max_personnes']) : 10;
                                                    $maxParticipants = max(1, $maxParticipants); // S'assurer qu'il y a au moins 1
                                                    ?>
                                                    <div class="participant-selector" style="margin-top: 10px;">
                                                         <label for="participants_<?php echo $etapeId; ?>">Nb participants:</label>
                                                         <select
                                                            name="participants_<?php echo $etapeId; ?>"
                                                            id="participants_<?php echo $etapeId; ?>"
                                                            data-stage-id="<?php echo $etapeId; ?>"
                                                            class="participants-select price-option"
                                                            data-category="participants" /* Important pour JS */
                                                            style="padding: 5px; border-radius: 4px;"
                                                        >
                                                            <?php for ($p = 1; $p <= $maxParticipants; $p++): ?>
                                                                <option value="<?php echo $p; ?>"><?php echo $p; ?></option>
                                                            <?php endfor; ?>
                                                        </select>
                                                    </div>
                                                <?php endif; // Fin condition $category === 'activites' ?>

                                            </div> <!-- Fin option-list -->
                                        </div> <!-- Fin option-category -->
                                    <?php endif; // Fin if options existent ?>
                                <?php endforeach; // Fin boucle categories ?>
                            </div> <!-- Fin options-container -->
                        </div> <!-- Fin stage-card -->
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Aucune étape définie pour ce voyage.</p>
                <?php endif; // Fin if etapes existent ?>

                <div class="actions">
                    <a href="Destination.php" class="secondary-button">Retour</a>
                    <!-- Le bouton soumet le formulaire -->
                    <button type="submit" name="submit_personalization" class="primary-button">Voir le résumé</button>
                </div>
            </form>
        </section>
    </div>

    <footer>
        <?php include('footer.php'); ?>
    </footer>

    <!-- Charger le JS après le HTML -->
    <script src="../Javascript/voyage_detail_prix.js" defer></script>
</body>
</html>
