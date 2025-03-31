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
    <?php include('header.php'); ?>

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
                	<form action="Destination.php" method="get">
                    	<input type="text" class="barre" name="search" placeholder="   Saisissez une destination" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
           			    <button type="submit" class="recherche">Rechercher</button>
                    </form>
                </div>
            </div>
        </section>
    </main>

<?php include('footer.php'); ?>
</body>
</html>
