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
    <div class="right">
        <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
        <a href="Destination.php" class="head1">Destination</a>
        <a href="Contact.php" class="encadré">Contact</a>
        <a href="<?= $profileLink ?>" class="img1"><img src="../assets/icon/User.png" alt="Profil"></a>
        <a href="panier.php" class="img2 cart-link">
            <img src="../assets/icon/Shopping cart.png" alt="Panier">
            <?php if ($nombreArticlesPanier > 0): ?>
                <span class="cart-counter"><?php echo $nombreArticlesPanier; ?></span>
            <?php endif; ?>
        </a>
    </div>

    <script src="../Javascript/theme.js"></script>
</header>
