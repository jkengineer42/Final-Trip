<?php
require_once 'sessions.php'; // Handles session_start()

// Vérifier si l'utilisateur est déjà connecté
if ($isLoggedIn) { // $isLoggedIn is from sessions.php
    header("Location: Accueil.php"); // Rediriger vers la page principale
    exit();
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $email_input = htmlspecialchars($_POST['email']); // Renamed to avoid conflict with $currentUserEmail
    $password_input = $_POST['password']; // Renamed

    // Lire le fichier JSON existant - Handled by ft_get_user_data_by_field in sessions.php
    // For login, we still need to iterate or get specific user.
    $jsonFile = '../data/data_user.json'; // Path relative to Connexion.php
    $jsonDataUsers = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    if ($jsonDataUsers === null) { $jsonDataUsers = []; }


    // Vérifier les informations d'identification
    $loginSuccessful = false;
    if (is_array($jsonDataUsers)) {
        foreach ($jsonDataUsers as $userAccount) { // Renamed to avoid conflict with $user from other contexts
            if (isset($userAccount['email']) && $userAccount['email'] === $email_input && isset($userAccount['password']) && password_verify($password_input, $userAccount['password'])) {
                // Connexion réussie
                $_SESSION['user_email'] = $email_input; // Set the session
                $loginSuccessful = true;
                header("Location: Accueil.php"); // Rediriger vers la page principale
                exit();
            }
        }
    }


    // Si les informations d'identification sont incorrectes
    if (!$loginSuccessful) {
        $error = "<span style='color: var(--yellow);'>Email ou mot de passe incorrect.</span>";
    }
}

// $profileLink is already set by sessions.php based on login status (which is false here if not redirected)
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
    <script src="../Javascript/formulaire.js"></script>
    <script src="../Javascript/theme.js"></script>
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
