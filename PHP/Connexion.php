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
    <script src="../Javascript/formulaire.js"></script>
</head>
<body>
     <?php include('header.php'); ?>

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

     <?php include('footer.php'); ?>
</body>
</html>
