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
    <script src="../Javascript/theme.js"></script>
</head>
<body>
    <header>
        <a href="Accueil.php" class="logo">FINAL TRIP</a>
        <div class="right">
            <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
            <a href="Destination.php" class="head1">Destination</a>
            <button class="encadré">Contact</button>
            <a href="<?= $profileLink ?>" class="img1"><img src="../assets/icon/User.png" alt="Profil"></a>
            <a href="#" class="img2"><img src="../assets/icon/Shopping cart.png" alt="Panier"></a>
        </div>
    </header>

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
                <h2>Pays</h2>
                <select class="cadre">
                    <option>Sélectionnez votre pays ici</option>
                    <option>France</option>
                    <option>Pérou</option>
                    <option>Costa Rica</option>
                    <option>Maroc</option>
                    <option>Malaisie</option>
                    <option>Indonésie</option>
                </select>

                <h2>Climat</h2>
                <ul>
                    <li><input type="checkbox"> Chaud</li>
                    <li><input type="checkbox"> Froid</li>
                    <li><input type="checkbox"> Tempéré</li>
                    <li><input type="checkbox"> Humide</li>
                </ul>

                <h2>Durée</h2>
                <input type="number" placeholder="Indiquez la durée du séjour qui vous convient...">

                <h2>Terrain</h2>
                <ul>
                    <li><input type="checkbox"> Aquatique</li>
                    <li><input type="checkbox"> Terrestre</li>
                    <li><input type="checkbox"> Montagneux</li>
                    <li><input type="checkbox"> Aérien</li>
                </ul>

                <h2>Type de couchage</h2>
                <ul>
                    <li><input type="checkbox"> Tente</li>
                    <li><input type="checkbox"> Hôtel</li>
                    <li><input type="checkbox"> Auberge</li>
                    <li><input type="checkbox"> Chez l’habitant</li>
                </ul>

                <h2>Restrictions</h2>
                <ul>
                    <li><input type="checkbox"> Allergie</li>
                    <li><input type="checkbox"> Asthme</li>
                    <li><input type="checkbox"> Diabète</li>
                    <li><input type="checkbox"> Arthrose</li>
                </ul>

                <h2>Prix</h2>
                <input type="number" placeholder="Indiquez votre prix maximum...">
            </aside>
            
            <div class="trip-list">
           <?php
           // on limite au 5 voyages les plus Xtrem
            // Afficher chaque voyage trié par niveau décroissant
            if (isset($voyagesToShow) && is_array($voyagesToShow)) {
                foreach ($voyagesToShow as $voyage) {
                    echo '<a href="voyage_detail.php?id=' . $voyage['id'] . '" class="trip-card-link">';
                    echo '  <article class="trip-card">';
                    echo '    <img src="' . $voyage['image'] . '" alt="' . $voyage['titre'] . '">';
                    echo '    <div class="trip-info">';
                    echo '      <h3>' . $voyage['titre'] . '</h3>';
                    echo '      <p>' . $voyage['description'] . '</p>';
                    echo '      <div class="trip-meta">';
                    echo '        <span class="price">' . $voyage['prix'] . '</span>';
                    echo '        <span class="duration">' . $voyage['duree']['jours'] . ' jours</span>';
                    echo '      </div>';
                    echo '    </div>';
                    echo '  </article>';
                    echo '</a>';
                }
            } else {
                echo "Aucun voyage trouvé pour cette recherche.";
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
</body>
</html>
