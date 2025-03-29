<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (isset($_SESSION['user_email'])) {
    $profileLink = 'Profil.php'; // Lien vers la page de profil
} else {
    $profileLink = 'Connexion.php'; // Lien vers la page de connexion
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Trip</title>
    <link rel="stylesheet" href="../Css/Accueil.css">
</head>
<body>
    <header>
        <a href="Accueil.php" class="logo">FINAL TRIP</a>
        <div class="right">
            <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
            <a href="Destination.php" class="head1">Destination</a>
            <button class="encadré">Contact</button>
            <a href="<?= $profileLink ?>" class="img1"><img src="../assets/icon/User.png"></a>
            <a href="#" class="img2"><img src="../assets/icon/Shopping cart.png"></a>
        </div>
    </header>

    <hr class="hr1">

    <main>
        <section class="hero">
            <div class="picture">
                <img src="../assets/img/Parachute_bis.jpg" alt="">
            </div>
            <div class="hero-content">
                <h1><span class="sansita">Des voyages sensationnels
                    pour vivre chaque instant
                    comme le <span class="dernier">dernier</span></span>
                </h1>
                <div class="search-bar">
                    <input type="text" placeholder="Saisissez une destination, un voyage qui vous donne envie...">
                    <button>Rechercher</button>
                </div>
            </div>
        </section>
    </main>

    <footer>
        <h2>Le dernier voyage que vous rêvez d’avoir</h2>

        <div class="contact">
            <p><strong>Adresse :</strong> <a href="#">34, Boulevard Haussmann, Paris 75009</a></p>
            <p><strong>Numéro :</strong> <a href="tel:0749685456">07 49 68 54 56</a></p>
            <p><strong>Email :</strong> <a href="mailto:contact@final-trip.com">contact@final-trip.com</a></p>
        </div>

        <p class="copyright">© 2025 Final Trip, ALL RIGHTS RESERVED.</p>

        <hr class="hr2">

        <div class="links">
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
            <a href="#">Conditions d’utilisations</a>
        </div>
    </footer>
</body>
</html>
