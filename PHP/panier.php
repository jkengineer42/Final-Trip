<?php
// Démarre la session pour utiliser le panier et potentiellement les infos utilisateur
session_start();

// Gestion de l'action vider le panier
if (isset($_GET['action']) && $_GET['action'] === 'vider') {
    // Vide le panier
    unset($_SESSION['panier']);
    // Redirige vers la page Destination comme demandé
    header('Location: Destination.php');
    exit;
}

// Gestion de l'action ajouter un article au panier
if (
    isset($_GET['action'], $_GET['id']) &&
    $_GET['action'] === 'ajouter'
) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id > 0) {
        // 1) Initialise la session panier si elle n'existe pas
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }

        // 2) Incrémente la quantité de cet id (ou initialise à 1)
        $_SESSION['panier'][$id] = ($_SESSION['panier'][$id] ?? 0) + 1;

        // 3) Retourne à la page précédente (ou Destination par défaut)
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? 'Destination.php'));
        exit;
    } else {
        // Gérer ID invalide si nécessaire
        header('Location: Destination.php'); // Rediriger en cas d'ID invalide
        exit;
    }
}


// Vérifier si l'utilisateur est connecté (pour l'affichage du header)
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    $profileLink = 'Connexion.php'; // Lien vers la page de connexion
}

// --- Affichage du Panier ---

// Charger la base de données de voyages
$jsonData = @file_get_contents('../data/voyages.json');
$data = $jsonData ? json_decode($jsonData, true) : null;

// Vérifier si les données JSON sont valides
if ($data === null || !isset($data['voyages'])) {
     // Afficher une erreur ou gérer le cas où le JSON est invalide/manquant
     // Pour la simplicité, on peut juste afficher un message et sortir
     include('header.php'); // Inclure le header même en cas d'erreur
     echo "<link rel='stylesheet' href='../Css/panier.css'>";
     echo '<hr class="hr1">';
     echo "<div class='panier-container'><h1>Votre panier</h1>";
     echo "<p class='error-message'>Erreur: Impossible de charger les données des voyages.</p>";
     echo "</div>";
     include('footer.php'); // Inclure le footer
     exit;
}

$voyagesData = $data['voyages']; // Accéder au tableau des voyages

// Initialiser le prix total
$totalPrice = 0.0;
$panierEstVide = !isset($_SESSION['panier']) || empty($_SESSION['panier']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Panier - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/panier.css">
    <script src="../Javascript/theme.js"></script>
    <!-- Potentiellement ajouter un script JS pour le panier plus tard -->
    <!-- <script src="../Javascript/panier.js"></script> -->
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">

    <main>
        <div class="panier-container">
            <h1>Votre panier</h1>

            <?php if ($panierEstVide): ?>
                <div class="empty-panier">
                    <p>Votre panier est actuellement vide.</p>
                    <a href="Destination.php" class="primary-button">Découvrir nos voyages</a>
                </div>
            <?php else: ?>
                <div class="panier-items">
                    <?php
                    // Boucle sur les articles du panier (id => quantité)
                    foreach ($_SESSION['panier'] as $id => $qty):
                        // Retrouver le voyage correspondant dans le JSON chargé
                        $voyage = null;
                        foreach ($voyagesData as $v) {
                            if ($v['id'] == $id) {
                                $voyage = $v;
                                break;
                            }
                        }

                        // Si le voyage est trouvé, l'afficher et calculer le prix
                        if ($voyage):
                            // Nettoyer le prix pour le calcul
                            $itemPriceClean = preg_replace('/[^\d,\.]/', '', $voyage['prix']); // Garde chiffres, virgule, point
                            $itemPrice = floatval(str_replace(',', '.', $itemPriceClean)); // Remplace virgule par point pour floatval

                            // Ajouter au prix total
                            $totalPrice += $itemPrice * $qty;
                    ?>
                            <div class="panier-item">
                                <?php if (isset($voyage['image']) && !empty($voyage['image'])): ?>
                                    <div class="item-image">
                                        <img src="<?php echo htmlspecialchars($voyage['image']); ?>" alt="<?php echo htmlspecialchars($voyage['titre']); ?>">
                                    </div>
                                <?php endif; ?>
                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($voyage['titre']); ?></h3>
                                    <p class="item-price">Prix unitaire : <?php echo htmlspecialchars($voyage['prix']); ?></p>
                                    <p>Quantité : <?php echo $qty; ?></p>
                                    <p>Sous-total : <?php echo number_format($itemPrice * $qty, 2, ',', ' '); ?> €</p>
                                    <div class="item-actions">
                                        <!-- Bouton Voir Détails (lien vers la page de détail) -->
                                        <a href="voyage_detail.php?id=<?php echo $id; ?>" class="view-button">Voir les détails</a>
                                        <!-- Bouton Supprimer (pourrait être ajouté plus tard) -->
                                        <!-- <a href="panier.php?action=supprimer&id=<?php echo $id; ?>" class="remove-button">Supprimer</a> -->
                                    </div>
                                </div>
                            </div>
                    <?php
                        endif; // Fin de if ($voyage)
                    endforeach; // Fin de la boucle foreach panier
                    ?>
                </div>

                <!-- Section Récapitulatif -->
                <div class="panier-summary">
                    <div class="total-price">
                        <span>Total du panier :</span>
                        <span><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span>
                    </div>
                    <div class="panier-actions">
                         <!-- Bouton Valider et Payer -->
                        <a href="voyage_resume.php" class="primary-button">Valider et Préparer le Paiement</a>
                         <!-- Bouton Vider le Panier -->
                        <a href="panier.php?action=vider" class="secondary-button">Vider le panier</a>
                    </div>
                     <p class="info-message">Note: Pour finaliser la commande, vous devrez peut-être confirmer les options sur la page suivante avant de procéder au paiement.</p>
                </div>

            <?php endif; // Fin de if/else panier vide ?>

        </div>
    </main>

    <?php include('footer.php'); ?>
</body>
</html>
