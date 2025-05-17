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
if (
    isset($_GET['action'], $_GET['id']) &&
    $_GET['action'] === 'ajouter'
) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($id > 0) {
        if (!isset($_SESSION['panier'])) {
            $_SESSION['panier'] = [];
        }
        if (!isset($_SESSION['panier_prices'])) {
            $_SESSION['panier_prices'] = [];
        }

        if (!isset($_SESSION['panier_prices'][$id])) { // N'incrémenter que si non personnalisé
             $_SESSION['panier'][$id] = ($_SESSION['panier'][$id] ?? 0) + 1;
        } else {
             $_SESSION['panier'][$id] = 1; // Forcer à 1 si déjà personnalisé
        }

        // Redirige vers panier.php SANS les paramètres d'action
        header('Location: panier.php');
        exit;
    } else {
        header('Location: Destination.php'); // ID invalide
        exit;
    }
}

// *** NOUVEAU : Gestion de l'action supprimer un article ***
if (
    isset($_GET['action'], $_GET['id']) &&
    $_GET['action'] === 'supprimer'
) {
    $idToRemove = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

    if ($idToRemove > 0) {
        // Supprimer l'article du panier principal
        if (isset($_SESSION['panier'])) {
            unset($_SESSION['panier'][$idToRemove]);
        }
        // Supprimer aussi le prix dynamique associé s'il existe
        if (isset($_SESSION['panier_prices'])) {
            unset($_SESSION['panier_prices'][$idToRemove]);
        }

        // Rediriger vers panier.php SANS les paramètres pour rafraîchir l'affichage
        header('Location: panier.php');
        exit;
    } else {
         // ID invalide, rediriger simplement vers le panier
         header('Location: panier.php');
         exit;
    }
}


// --- Fin de la Gestion des Actions ---


// Vérifier si l'utilisateur est connecté (pour l'affichage du header)
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    $profileLink = 'Connexion.php';
}

// --- Affichage du Panier ---

// Charger la base de données de voyages
$jsonData = @file_get_contents('../data/voyages.json');
$data = $jsonData ? json_decode($jsonData, true) : null;

// Vérifier si les données JSON sont valides
$voyagesData = null;
if ($data !== null && isset($data['voyages'])) {
    $voyagesData = $data['voyages'];
}

// Initialiser le prix total
$totalPrice = 0.0;
// Vérifier si le panier existe et s'il contient des éléments après une éventuelle suppression
$panierEstVide = !isset($_SESSION['panier']) || empty($_SESSION['panier']);

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Votre Panier - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/root.css">
    <link rel="stylesheet" href="../Css/panier.css">
    <script src="../Javascript/theme.js"></script>
    <!-- <script src="../Javascript/panier.js"></script> -->
</head>
<body>
    <?php include('header.php'); ?>
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
                    // S'assurer que $_SESSION['panier'] est itérable
                    if (is_array($_SESSION['panier'])) {
                        foreach ($_SESSION['panier'] as $id => $qty):
                            // Retrouver le voyage correspondant dans le JSON chargé
                            $voyage = null;
                            foreach ($voyagesData as $v) {
                                if (isset($v['id']) && $v['id'] == $id) {
                                    $voyage = $v;
                                    break;
                                }
                            }

                            // Si le voyage est trouvé, l'afficher et calculer le prix
                            if ($voyage):
                                $itemsValidesTrouves = true;
                                $itemPrice = 0.0;
                                $isPersonalized = false;

                                // Vérifier prix dynamique
                                if (isset($_SESSION['panier_prices'][$id])) {
                                    $itemPrice = floatval($_SESSION['panier_prices'][$id]);
                                    $qty = 1; // Forcer quantité à 1 pour personnalisé
                                    $isPersonalized = true;
                                }
                                // Sinon, prix de base
                                elseif (isset($voyage['prix'])) {
                                     $itemPriceStr = $voyage['prix'];
                                     $itemPriceClean = preg_replace('/[^\d,\.]/', '', $itemPriceStr);
                                     $itemPrice = floatval(str_replace(',', '.', $itemPriceClean));
                                }

                                $totalPrice += $itemPrice * $qty;
                        ?>
                                <div class="panier-item">
                                    <?php
                                    $imagePath = isset($voyage['image']) ? htmlspecialchars($voyage['image']) : '';
                                    $imageAlt = isset($voyage['titre']) ? htmlspecialchars($voyage['titre']) : 'Image du voyage';
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
                                        <p>Sous-total : <?php echo number_format($itemPrice * $qty, 2, ',', ' '); ?> €</p>
                                        <div class="item-actions">
                                            <a href="voyage_detail.php?id=<?php echo $id; ?>" class="view-button">
                                                <?php echo $isPersonalized ? 'Modifier / Voir détails' : 'Personnaliser / Voir détails'; ?>
                                            </a>
                                            <!-- Bouton Supprimer fonctionnel -->
                                            <a href="panier.php?action=supprimer&id=<?php echo $id; ?>" class="remove-button" onclick="return confirm('Voulez-vous vraiment supprimer cet article ?');">Supprimer</a>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            else: // Si l'ID n'est pas trouvé dans le JSON (article obsolète)
                                if (isset($_SESSION['panier'][$id])) unset($_SESSION['panier'][$id]);
                                if (isset($_SESSION['panier_prices'][$id])) unset($_SESSION['panier_prices'][$id]);
                            endif; // Fin de if ($voyage)
                        endforeach; // Fin de la boucle foreach panier
                    } // Fin if is_array($_SESSION['panier'])

                    // Vérifier à nouveau si le panier est vide après nettoyage potentiel
                    $panierEstVide = !isset($_SESSION['panier']) || empty($_SESSION['panier']);

                    // Affichage si aucun item valide trouvé après la boucle
                    if (!$itemsValidesTrouves && !$panierEstVide) { // S'il y avait des items mais aucun n'est valide
                        echo "<p class='info-message'>Les articles précédemment ajoutés à votre panier ne semblent plus disponibles et ont été retirés.</p>";
                        $panierEstVide = true; // Considérer comme vide pour le récapitulatif
                    }
                    ?>
                </div> <!-- Fin panier-items -->

                 <?php if (!$panierEstVide): // N'afficher le récapitulatif que si le panier n'est pas vide après traitement ?>
                    <!-- Section Récapitulatif -->
                    <div class="panier-summary">
                        <div class="total-price">
                            <span>Total du panier :</span>
                            <span><?php echo number_format($totalPrice, 2, ',', ' '); ?> €</span>
                        </div>
                        <div class="panier-actions">
                            <?php
                            $validationLink = isset($_SESSION['user_email']) ? 'voyage_resume.php' : 'Connexion.php?redirect=panier';
                            $validationText = isset($_SESSION['user_email']) ? 'Valider et Préparer le Paiement' : 'Se connecter pour Valider';
                            ?>
                            <a href="<?php echo $validationLink; ?>" class="primary-button"><?php echo $validationText; ?></a>
                            <a href="panier.php?action=vider" class="secondary-button">Vider le panier</a>
                        </div>
                        <?php if(isset($_SESSION['user_email'])): ?>
                            <p class="info-message">Note: La validation vous mènera au récapitulatif du dernier voyage personnalisé (ou du premier du panier) pour confirmer les options avant paiement.</p>
                        <?php else: ?>
                             <p class="info-message">Vous devez être connecté pour pouvoir valider votre panier et procéder au paiement.</p>
                        <?php endif; ?>
                    </div>
                 <?php elseif ($itemsValidesTrouves == false && $panierEstVide == false):
                       // Ce cas arrive si tous les items ont été invalidés pendant la boucle
                       // On affiche le message panier vide à nouveau
                 ?>
                      <div class="empty-panier">
                          <p>Votre panier est actuellement vide ou les articles ne sont plus disponibles.</p>
                          <a href="Destination.php" class="primary-button">Découvrir nos voyages</a>
                      </div>
                 <?php endif; // Fin if (!$panierEstVide pour le récapitulatif) ?>

            <?php endif; // Fin de if/else ($panierEstVide au début) ?>

        </div>
    </main>

    <?php include('footer.php'); ?>
    <script src="../Javascript/menu.js"></script>
</body>
</html>
