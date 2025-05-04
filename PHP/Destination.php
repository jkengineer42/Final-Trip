<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    $profileLink = 'Connexion.php'; // Lien vers la page de connexion
}

// Charger le fichier JSON des voyages
$json = file_get_contents('../data/voyages.json');
if ($json === false) {
    echo "Erreur d'ouverture du fichier JSON. Détail de l'erreur : " . json_last_error_msg();
    exit;
}

// Parser
$voyages = json_decode($json, true);
if ($voyages === null) {
    echo "Erreur de décodage du fichier JSON. Détail de l'erreur : " . json_last_error_msg();
    exit;
}

// Récupérer le mot-clé de la recherche
$searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

//Filtrer les voyages selon le mot-clé
$filteredVoyages = array_filter($voyages['voyages'], function($voyage) use ($searchKeyword) {
    return strpos(strtolower($voyage['titre']), strtolower($searchKeyword)) !== false || 
           strpos(strtolower($voyage['description']), strtolower($searchKeyword)) !== false || 
           strpos(strtolower($voyage['pays']), strtolower($searchKeyword)) !== false;
});

// Trier les voyages par niveau décroissant
usort($filteredVoyages, function ($a, $b) {
    return $b['niveau'] - $a['niveau'];
});

// Pagination
$tripsPerPage = 6;  // Nombre de voyages à afficher par page
$totalTrips = count($filteredVoyages);  // Nombre total de voyages
$totalPages = ceil($totalTrips / $tripsPerPage);  // Nombre total de pages

// Obtenir la page actuelle
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$page = max(1, min($page, $totalPages));  // S'assurer que la page est valide

// Calculer l'index de départ pour la pagination
$startIndex = ($page - 1) * $tripsPerPage;

// Limiter les voyages à afficher sur la page actuelle
$voyagesToShow = array_slice($filteredVoyages, $startIndex, $tripsPerPage);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINAL TRIP - Destination</title>
    <link rel="stylesheet" href="../Css/Destination.css">
</head>
<body>
   <?php include('header.php'); ?>
    <hr class="hr1">

    <main>
        <section class="hero">
            <h1>Découvrez les voyages les plus sensationnels du moment</h1><br><br>
            <div class="search-bar">
            	<form action="Destination.php" method="get">
            		<input type="text" name="search" placeholder="Saisissez une destination..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
        		</form>
    		</div>
        </section>

        <section class="destination-container">
            <aside class="filters">
            
            <div class="sort-controls">
                    <h2>Trier par</h2>
                    <select id="sort-select" class="cadre" style="width: 100%; padding: 8px; margin-top: 5px;">
                        <option value="default">-- Ordre par défaut --</option>
                        <option value="price-asc">Prix (croissant)</option>
                        <option value="price-desc">Prix (décroissant)</option>
                        <option value="date-asc">Date de début (proche)</option>
                        <option value="date-desc">Date de début (lointaine)</option>
                        <option value="duration-asc">Durée (courte)</option>
                        <option value="duration-desc">Durée (longue)</option>
                        <option value="stages-asc">Étapes (moins)</option>
                        <option value="stages-desc">Étapes (plus)</option>
                    </select>
                </div>
                
                <hr style="margin: 20px 0;">
                
                 <h2>Durée (max jours)</h2>
    
                <input type="number" id="filter-duree-max" placeholder="Durée max..." class="filter-control" style="margin-bottom: 20px;">
                
                 <h2>Prix (max €)</h2>
                <input type="number" id="filter-prix-max" placeholder="Prix max..." class="filter-control">
                
                <h2>Pays</h2>
               <select id="filter-pays" class="cadre filter-control">
                    <option value="">-- Tous --</option> 
                    <?php
                    $countries = array_unique(array_column($voyages['voyages'], 'pays'));
                    sort($countries);
                    foreach ($countries as $country) {
                        echo '<option value="' . htmlspecialchars($country) . '">' . htmlspecialchars($country) . '</option>';
                    }
                    ?>
                </select>

                <h2>Climat</h2>
                <ul class="filter-group" data-filter-key="climat">
                    <!-- Add value and class to checkboxes -->
                    <li><input type="checkbox" value="chaud" class="filter-checkbox filter-control"> Chaud</li>
                    <li><input type="checkbox" value="froid" class="filter-checkbox filter-control"> Froid</li>
                    <li><input type="checkbox" value="tempéré" class="filter-checkbox filter-control"> Tempéré</li>
                    <li><input type="checkbox" value="humide" class="filter-checkbox filter-control"> Humide</li>
                      <li><input type="checkbox" value="spatial" class="filter-checkbox filter-control"> Spatial</li>
                       <li><input type="checkbox" value="désert" class="filter-checkbox filter-control"> Désertique</li>
                </ul>
                

                <h2>Terrain</h2>
                <ul class="filter-group" data-filter-key="terrain">
             
                    <li><input type="checkbox" value="aquatique" class="filter-checkbox filter-control"> Aquatique</li>
                    <li><input type="checkbox" value="terrestre" class="filter-checkbox filter-control"> Terrestre</li>
                    <li><input type="checkbox" value="montagneux" class="filter-checkbox filter-control"> Montagneux</li>
                    <li><input type="checkbox" value="aérien" class="filter-checkbox filter-control"> Aérien</li>
                     <li><input type="checkbox" value="glaciaire" class="filter-checkbox filter-control"> Glaciaire</li>
                      <li><input type="checkbox" value="spatial" class="filter-checkbox filter-control"> Spatial</li>
                       <li><input type="checkbox" value="désert" class="filter-checkbox filter-control"> Désertique</li>
                </ul>

                 <h2>Type de couchage</h2>
                <!-- Important: Check values match your JSON data (maybe lowercase?) -->
                <ul class="filter-group" data-filter-key="couchage">
                     <!-- Add value and class. Use comma-separated for card data -->
                    <li><input type="checkbox" value="tente" class="filter-checkbox filter-control"> Tente</li>
                    <li><input type="checkbox" value="hotel" class="filter-checkbox filter-control"> Hôtel</li>
                    <li><input type="checkbox" value="auberge" class="filter-checkbox filter-control"> Auberge</li>
                    <li><input type="checkbox" value="habitant" class="filter-checkbox filter-control"> Chez l’habitant</li>
                    <li><input type="checkbox" value="refuge" class="filter-checkbox filter-control"> Refuge</li>
                     <li><input type="checkbox" value="abri" class="filter-checkbox filter-control"> Abri</li>
                     <li><input type="checkbox" value="vaisseau spatial" class="filter-checkbox filter-control"> Vaisseau Spatial</li>
                </ul>

               <h2>Restrictions</h2>
                 <ul class="filter-group" data-filter-key="restrictions">
                     <!-- Example values - adjust to your JSON -->
                    <li><input type="checkbox" value="allergie" class="filter-checkbox filter-control"> Allergie</li>
                    <li><input type="checkbox" value="asthme" class="filter-checkbox filter-control"> Asthme</li>
                    <li><input type="checkbox" value="diabète" class="filter-checkbox filter-control"> Diabète</li>
                    <li><input type="checkbox" value="arthrose" class="filter-checkbox filter-control"> Arthrose</li>
                    <li><input type="checkbox" value="aucune" class="filter-checkbox filter-control"> Aucune</li>
                </ul>


            </aside>
            
           <div class="trip-list" id="trip-list-container">
                <?php
                if (isset($voyagesToShow) && is_array($voyagesToShow) && count($voyagesToShow) > 0) {
                    foreach ($voyagesToShow as $voyage) {
                        // --- Prepare data for sorting AND filtering attributes ---
                        $sortablePrice = floatval(str_replace([',', ' '], ['.', ''], preg_replace('/[^\d,\.]/', '', $voyage['prix']))); // Robust price cleaning
                        $sortableDate = $voyage['duree']['debut'];
                        $sortableDuration = intval($voyage['duree']['jours']);
                        $sortableStages = isset($voyage['etapes']) ? count($voyage['etapes']) : 0;

                        // --- Data for Filtering ---
                        $filterPays = $voyage['pays'] ?? '';
                        $filterClimat = $voyage['climat'] ?? '';
                        $filterTerrain = $voyage['terrain'] ?? '';
                        // Combine couchage options into a comma-separated string (lowercase)
                        $filterCouchage = isset($voyage['couchage']) && is_array($voyage['couchage']) ? strtolower(implode(',', $voyage['couchage'])) : '';
                         // Handle restrictions similarly if they are an array, otherwise use the string
                        $filterRestrictions = isset($voyage['restrictions']) ? (is_array($voyage['restrictions']) ? strtolower(implode(',', $voyage['restrictions'])) : strtolower($voyage['restrictions'])) : '';


                        echo '<a href="voyage_detail.php?id=' . $voyage['id'] . '" class="trip-card-link">'; // Add class here
                        // Add ALL data-* attributes needed for filtering and sorting
                        echo '  <article class="trip-card"
                                         data-price="' . $sortablePrice . '"
                                         data-date="' . htmlspecialchars($sortableDate) . '"
                                         data-duration="' . $sortableDuration . '"
                                         data-stages="' . $sortableStages . '"
                                         data-pays="' . htmlspecialchars($filterPays) . '"
                                         data-climat="' . htmlspecialchars($filterClimat) . '"
                                         data-terrain="' . htmlspecialchars($filterTerrain) . '"
                                         data-couchage="' . htmlspecialchars($filterCouchage) . '"
                                         data-restrictions="' . htmlspecialchars($filterRestrictions) . '"
                                         >';
                        // ... rest of the card content (img, info, meta) ...
                        echo '    <img src="' . htmlspecialchars($voyage['image']) . '" alt="' . htmlspecialchars($voyage['titre']) . '">';
                        echo '    <div class="trip-info">';
                        echo '      <h3>' . htmlspecialchars($voyage['titre']) . '</h3>';
                        echo '      <p>' . htmlspecialchars($voyage['description']) . '</p>';
                        echo '      <div class="trip-meta">';
                        echo '        <span class="price">' . htmlspecialchars($voyage['prix']) . '</span>';
                        echo '        <span class="duration">' . htmlspecialchars($voyage['duree']['jours']) . ' jours</span>';
                         echo '        <span class="stages-display" style="margin-left: 1em;">' . $sortableStages . ' étape(s)</span>';
                        echo '      </div>';
                        echo '    </div>';
                        echo '  </article>';
                        echo '</a>'; // Close the link
                    }
                } else {
                    echo "<p style='color: var(--white); width: 100%; text-align: center;'>Aucun voyage trouvé pour cette recherche.</p>";
                }
                ?>
            </div>
        </section>
        
        <div class="pagination">
            <?php if ($page > 1) : ?>
                <a href="?page=<?= $page - 1 ?>&search=<?= urlencode($searchKeyword) ?>" class="prev">Précédent</a>
            <?php endif; ?>

            <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                <a href="?page=<?= $i ?>&search=<?= urlencode($searchKeyword) ?>" class="page"><?= $i ?></a>
            <?php endfor; ?>

            <?php if ($page < $totalPages) : ?>
                <a href="?page=<?= $page + 1 ?>&search=<?= urlencode($searchKeyword) ?>" class="next">Suivant</a>
            <?php endif; ?>
        </div>
            </div>
    </main>

    <?php include('footer.php'); ?>
    
     <script src="../Javascript/tri_filtre.js"></script>
</body>
</html>
