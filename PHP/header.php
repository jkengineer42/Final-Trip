<?php
// Calculer le nombre d'articles dans le panier si la session existe
$nombreArticlesPanier = 0;
if (isset($_SESSION['panier'])) {
    $nombreArticlesPanier = isset($_SESSION['panier'])
    ? array_sum($_SESSION['panier']) // total réel
    : 0;


}
?>

<header>
    <a href="Accueil.php" class="logo">FINAL TRIP</a>

    <button class="hamburger" id="hamburger-button" aria-label="Toggle navigation" aria-expanded="false">
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
        <span class="hamburger-bar"></span>
    </button>

    <div class="nav-links" id="nav-links-container">
        <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
        <a href="Destination.php" class="head1">Destination</a>
        <a href="Contact.php" class="encadré">Contact</a>
         <!-- AJOUT DES BOUTONS DE THÈME ICI -->
         <div class="theme-selector-mobile">
            <button id="light-theme-btn-mobile" class="theme-btn"><img src="../assets/icon/sun.png" alt="Light Mode"></button>
            <button id="dark-theme-btn-mobile" class="theme-btn"><img src="../assets/icon/moon.png" alt="Dark Mode"></button>
        </div>
        <a href="<?= $profileLink ?>" class="img1 nav-icon-link"><img src="../assets/icon/User.png" alt="Profil"></a>
        <a href="panier.php" class="img2 cart-link nav-icon-link">
            <img src="../assets/icon/Shopping cart.png" alt="Panier">
            <?php if ($nombreArticlesPanier > 0): ?>
                <span class="cart-counter"><?php echo $nombreArticlesPanier; ?></span>
            <?php endif; ?>
        </a>
       
    </div>
</header>