<?php
// Démarre la session pour utiliser le panier et potentiellement les infos utilisateur
session_start();

// --- Gestion des Actions ---

// Gestion de l'action vider le panier
if (isset($_GET['action']) && $_GET['action'] === 'vider') {
    // Vide le panier et les prix associés
    unset($_SESSION['panier']);
    unset($_SESSION['panier_prices']); // Important : vider aussi les prix dynamiques
    // Redirige vers la page Destination comme demandé (URL propre)
    header('Location: Destination.php');
    exit; // Important: Arrêter l'exécution après redirection
}

// Gestion de l'action ajouter un article au panier (Typiquement depuis Destination.php)
// Cette action ajoute le voyage avec son PRIX DE BASE. La personnalisation mettra à jour le prix.
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
        // 2) Initialise le stockage des prix dynamiques s'il n'existe pas
        if (!isset($_SESSION['panier_prices'])) {
            $_SESSION['panier_prices'] = [];
        }

        // 3) Incrémente la quantité de cet id (ou initialise à 1)
        // Si le voyage est déjà personnalisé (prix dynamique existe), on ne change pas la quantité (on suppose 1)
        // Sinon, on incrémente comme un produit standard.
        if (!isset($_SESSION['panier_prices'][$id])) {
             $_SESSION['panier'][$id] = ($_SESSION['panier'][$id] ?? 0) + 1;
        } else {
             // Si déjà personnalisé, on force la quantité à 1 pour éviter la multiplication du prix personnalisé
             $_SESSION['panier'][$id] = 1;
        }


        // 4) Redirige vers panier.php SANS les paramètres d'action pour éviter la boucle
        header('Location: panier.php');
        exit; // Important: Arrêter l'exécution après redirection
    } else {
        // Gérer ID invalide si nécessaire - rediriger vers une page sûre
        header('Location: Destination.php');
        exit;
    }
}

// --- Fin de la Gestion des Actions ---


// Vérifier si l'utilisateur est connecté (pour l'affichage du header)
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    // Si pas connecté, on peut afficher le panier mais pas valider
    $profileLink = 'Connexion.php';
    // Pas de redirection ici, on permet de voir le panier
}

// --- Affichage du Panier ---

// Charger la base de données de voyages
$jsonData = @file_get_contents('../data/voyages.json');
$data = $jsonData ? json_decode($jsonData, true) : null;

// Vérifier si les données JSON sont valides
$voyagesData = null;
if ($data !== null && isset($data['voyages'])) {
    $voyagesData = $data['voyages']; // Accéder au tableau des voyages
}

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
    <!-- Potentiellement ajouter un script JS pour le panier plus tard (ex: supprimer item sans recharger) -->
    <!-- <script src="../Javascript/panier.js"></script> -->
</head>
<body>
    <?php include('header.php'); // Assurez-vous que header.php démarre aussi la session si ce n'est pas déjà fait ?>
    <hr class="hr1">

    <main>
        <div class="panier-container">
            <h1>Votre panier</h1>

            <?php if ($voyagesData === null): // Erreur chargement JSON ?>
                <p class="error-message">Erreur: Impossible de charger les données des voyages pour afficher le panier.</p>
            <?php elseif ($panierEstVide): ?>
                <div class="empty-panier">
                    <p>Votre panier est actuellement vide.</p>
                    <a href="Destination.php" class="primary-button">Découvrir nos voyages</a>
                </div>
            <?php else: // Le panier n'est pas vide et les données sont chargées ?>
                <div class="panier-items">
                    <?php
                    $itemsValidesTrouves = false;
                    // Boucle sur les articles du panier (id => quantité)
                    foreach ($_SESSION['panier'] as $id => $qty):
                        // Retrouver le voyage correspondant dans le JSON chargé
                        $voyage = null;
                        foreach ($voyagesData as $v) {
                            if (isset($v['id']) && $v['id'] == $id) { // Vérifier que 'id' existe
                                $voyage = $v;
                                break;
                            }
                        }

                        // Si le voyage est trouvé, l'afficher et calculer le prix
                        if ($voyage):
                            $itemsValidesTrouves = true;
                            $itemPrice = 0.0; // Prix unitaire à utiliser pour cet item
                            $isPersonalized = false; // Par défaut, non personnalisé

                            // *** VÉRIFIER S'IL EXISTE UN PRIX DYNAMIQUE POUR CET ID ***
                            if (isset($_SESSION['panier_prices'][$id])) {
                                $itemPrice = floatval($_SESSION['panier_prices'][$id]);
                                // Si un prix dynamique existe, la quantité est toujours 1
                                $qty = 1;
                                $isPersonalized = true; // Marquer comme personnalisé
                            }
                            // *** SINON, UTILISER LE PRIX DE BASE DU JSON ***
                            elseif (isset($voyage['prix'])) {
                                 $itemPriceStr = $voyage['prix'];
                                 $itemPriceClean = preg_replace('/[^\d,\.]/', '', $itemPriceStr);
                                 $itemPrice = floatval(str_replace(',', '.', $itemPriceClean));
                                 $isPersonalized = false; // Marquer comme non personnalisé (prix de base)
                            } else {
                                 // Si aucun prix n'est trouvé, on met 0 mais on pourrait logger une erreur
                                 $itemPrice = 0.0;
                                 $isPersonalized = false;
                                 // error_log("Avertissement: Prix non trouvé pour l'article ID $id dans voyages.json");
                            }


                            // Ajouter au prix total (prix unitaire * quantité)
                            $totalPrice += $itemPrice * $qty;
                    ?>
                            <div class="panier-item">
                                <?php // Bloc Image (vérifier si l'image existe réellement)
                                $imagePath = isset($voyage['image']) ? htmlspecialchars($voyage['image']) : '';
                                $imageAlt = isset($voyage['titre']) ? htmlspecialchars($voyage['titre']) : 'Image du voyage';
                                // Simple check if path is not empty, for more robustness check file_exists() server side if needed before rendering
                                if (!empty($imagePath)) : ?>
                                    <div class="item-image">
                                        <img src="<?php echo $imagePath; ?>" alt="<?php echo $imageAlt; ?>">
                                    </div>
                                <?php else: ?>
                                    <div class="item-image placeholder"></div>
                                <?php endif; ?>

                                <div class="item-details">
                                    <h3><?php echo htmlspecialchars($voyage['titre'] ?? 'Titre inconnu'); ?></h3>

                                    <?php if ($isPersonalized): ?>
                                         <p class="item-price">Prix (personnalisé) : <?php echo number_format($itemPrice, 2, ',', ' '); ?> €</p>
                                    <?php else: ?>
                                         <p class="item-price">Prix unitaire : <?php echo htmlspecialchars($voyage['prix'] ?? 'N/A'); ?></p>
                                    <?php endif; ?>

                                    <p>Quantité : <?php echo $qty; ?></p>
                                    <!-- Le sous-total utilise le prix unitaire (dynamique ou base) * quantité -->
                                    <p>Sous-total : <?php echo number_format($itemPrice * $qty, 2, ',', ' '); ?> €</p>

                                    <div class="item-actions">
                                        <!-- Lien pour voir/modifier la personnalisation OU voir détails du voyage de base -->
                                        <a href="voyage_detail.php?id=<?php echo $id; ?>" class="view-button">
                                            <?php echo $isPersonalized ? 'Modifier / Voir détails' : 'Personnaliser / Voir détails'; ?>
                                        </a>
                                        <!-- Bouton Supprimer (Fonctionnalité à ajouter si nécessaire) -->
                                         <a href="panier.php?action=supprimer&id=<?php echo $id; ?>" class="remove-button" onclick="return confirm('Voulez-vous vraiment supprimer cet article ?');">Supprimer</a>
                                    </div>
                                </div>
                            </div>
                    <?php
                        else: // Si l'ID du panier ne correspond à aucun voyage dans le JSON
                             // Optionnel : Retirer l'ID invalide du panier ici pour auto-nettoyer
                             unset($_SESSION['panier'][$id]);
                             unset($_SESSION['panier_prices'][$id]); // Retirer aussi le prix associé
                             // error_log("Avertissement: ID de voyage $id trouvé dans le panier mais pas dans voyages.json. Article retiré.");
                        endif; // Fin de if ($voyage)
                    endforeach; // Fin de la boucle foreach panier

                    // Affichage si aucun item valide trouvé après la boucle
                    if (!$itemsValidesTrouves) {
                        echo "<p class='info-message'>Les articles de votre panier ne sont plus disponibles ou ont été retirés.</p>";
                         // Si aucun item valide, on peut considérer le panier comme vide pour le récapitulatif
                         $panierEstVide = true;
                    }
                    ?>
                </div> <!-- Fin panier-items -->

                 <?php if ($itemsValidesTrouves): // N'afficher le récapitulatif que s'il y a des items valides ?>
                    <!-- Section Récapitulatif -->
                    <div class="panier-summary">
                        <div class="total-price">
                            <span>Total du panier :</span>
                            <span><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span>
                        </div>
                        <div class="panier-actions">
                            <?php
                            // Déterminer la cible du bouton "Valider"
                            // Si l'utilisateur est connecté, on va vers le résumé, sinon vers la connexion
                            $validationLink = isset($_SESSION['user_email']) ? 'voyage_resume.php' : 'Connexion.php?redirect=panier';
                            $validationText = isset($_SESSION['user_email']) ? 'Valider et Préparer le Paiement' : 'Se connecter pour Valider';
                            ?>
                            <!-- Bouton Valider et Payer / Se connecter -->
                            <a href="<?php echo $validationLink; ?>" class="primary-button"><?php echo $validationText; ?></a>

                            <!-- Bouton Vider le Panier -->
                            <a href="panier.php?action=vider" class="secondary-button">Vider le panier</a>
                        </div>
                        <?php if(isset($_SESSION['user_email'])): ?>
                            <p class="info-message">Note: La validation vous mènera au récapitulatif du dernier voyage personnalisé (ou du premier du panier) pour confirmer les options avant paiement.</p>
                        <?php else: ?>
                             <p class="info-message">Vous devez être connecté pour pouvoir valider votre panier et procéder au paiement.</p>
                        <?php endif; ?>
                    </div>
                 <?php endif; ?>

            <?php endif; // Fin de if/else panier vide ?>

        </div>
    </main>

    <?php include('footer.php'); ?>
</body>
</html>
