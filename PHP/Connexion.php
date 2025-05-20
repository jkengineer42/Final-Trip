<?php
require_once 'sessions.php'; // Handles session_start()

// Récupérer le message d'erreur de blocage (s'il existe)
$error = '';
if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']); // Effacer le message après l'avoir affiché
}

// Vérifier si l'utilisateur est déjà connecté
if ($isLoggedIn) { // $isLoggedIn is from sessions.php
    header("Location: Accueil.php"); // Rediriger vers la page principale
    exit();
}

$email_value_to_display = ''; // Variable pour stocker l'email à réafficher

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $email_input = htmlspecialchars($_POST['email']);
    $password_input = $_POST['password'];

    // Conserver l'email saisi pour le réafficher en cas d'erreur
    $email_value_to_display = $email_input;

    // Lire le fichier JSON existant
    $jsonFile = '../data/data_user.json';
    $jsonDataUsers = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    if ($jsonDataUsers === null) { $jsonDataUsers = []; }

    // Vérifier les informations d'identification
    $loginSuccessful = false;
    if (is_array($jsonDataUsers)) {
        foreach ($jsonDataUsers as $userAccount) {
            if (isset($userAccount['email']) && $userAccount['email'] === $email_input && isset($userAccount['password']) && password_verify($password_input, $userAccount['password'])) {
                
                // Vérifier si l'utilisateur est bloqué
                if (isset($userAccount['is_blocked']) && $userAccount['is_blocked'] === true) {
                    $error = "<span style='color: var(--yellow);'>Votre compte a été bloqué. Veuillez contacter l'administrateur.</span>";
                    break; // Important : sortir de la boucle ici
                }

                // Connexion réussie
                $_SESSION['user_email'] = $email_input; // Set the session
                $loginSuccessful = true;

                // NOUVEAU : Vérifier s'il y a une redirection en attente (pour la fonctionnalité "mot de passe oublié" ou autre)
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect_url = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']); // Nettoyer la variable de session
                    header("Location: " . $redirect_url);
                } else {
                    header("Location: Accueil.php"); // Redirection par défaut
                }
                exit();
            }
        }
    }

    // Si les informations d'identification sont incorrectes
    if (!$loginSuccessful) {
        $error = "<span style='color: var(--yellow);'>Email ou mot de passe incorrect.</span>";
        // L'email est déjà dans $email_value_to_display
    }
}

// $profileLink est déjà défini par sessions.php
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/Connexion.css">
    <link rel="stylesheet" href="../Css/formulaire.css">
    <script src="../Javascript/formulaire.js" defer></script>
    <script src="../Javascript/theme.js" defer></script>
</head>
<body>
     <?php include('header.php'); ?>

    <hr class="hr1">

    <main>
        <div class="login-container">
            <img src="../assets/icon/User2.png" alt="User Icon" class="user-icon">

            <form action="Connexion.php" method="POST">
                <?php if (isset($error) && !empty($error)): ?>
                    <div class="error" style="color: var(--yellow); margin-bottom: 15px; text-align: center;"><?= $error ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="email" class="right">Email</label>
                    <input type="email" id="email" name="email" required value="<?= htmlspecialchars($email_value_to_display) ?>">
                </div><br>

                <div class="form-group">
                    <label for="password" class="right">Mot de passe</label>
                    <input type="password" id="password" name="password" required>
                    </div><br>
                
                <p class="style1">
                    <a href="mot_de_passe_oublie.php" style="color: var(--white); font-size: 0.9rem; text-decoration: underline;">Mot de passe oublié ?</a>
                </p>

                <button type="submit" class="button1">Connexion</button>
            </form>

            <p class="register-link">Toujours pas dans l'équipe ? <a href="Inscription.php">Aller clique ici <img src="../assets/icon/clic.png" class="clic"></a></p>
        </div>
    </main>

     <?php include('footer.php'); ?>
</body>
</html>
