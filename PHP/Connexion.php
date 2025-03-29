<?php
session_start();

// Vérifier si l'utilisateur est déjà connecté
if (isset($_SESSION['user_email'])) {
    header("Location: Accueil.php"); // Rediriger vers la page principale
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $email = htmlspecialchars($_POST['email']);
    $password = $_POST['password'];

    // Lire le fichier JSON existant
    $jsonFile = '../data/data_user.json';
    $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

    // Vérifier les informations d'identification
    foreach ($jsonData as $user) {
        if ($user['email'] === $email && password_verify($password, $user['password'])) {
            // Connexion réussie
            $_SESSION['user_email'] = $email;
            header("Location: Accueil.php"); // Rediriger vers la page principale
            exit();
        }
    }

    // Si les informations d'identification sont incorrectes
    $error = "<span style='color: var(--yellow);'>Email ou mot de passe incorrect.</span>";
}

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
    <title>Connexion - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/Connexion.css">
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
        <div class="login-container">
            <img src="../assets/icon/User2.png" alt="User Icon" class="user-icon">

            <form action="Connexion.php" method="POST">
                <?php if (isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email" class="right">Email</label>
                    <input type="email" id="email" name="email" required>
                </div><br>

                <div class="form-group">
                    <label for="password" class="right">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                </div>

                <button type="submit" class="button1">Connexion</button>
            </form>

            <p class="register-link">Toujours pas dans l’équipe ? <a href="Inscription.php">Aller clique ici <img src="../assets/icon/clic.png" class="clic"></a></p>
        </div>
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
