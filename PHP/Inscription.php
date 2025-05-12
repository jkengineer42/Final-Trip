<?php
session_start();

// Fonction pour valider la date de naissance
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) === $date) {
        // Vérifier que l'année est supérieure à 1900
        $year = $d->format('Y');
        if ($year > 1900 && $d->getTimestamp() <= time()) {
            return true;
        }
    }
    return false;
}

// Vérifier si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = htmlspecialchars($_POST['email']);
    $confirmEmail = htmlspecialchars($_POST['confirm-email']);
    $birthdate = htmlspecialchars($_POST['birthdate']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hacher le mot de passe
    $autoLogin = isset($_POST['auto-login']); // Vérifier si l'utilisateur veut se connecter automatiquement

    // Vérification des informations
    if ($email != $confirmEmail) {
        $error = "<span style='color: var(--yellow);'>Les emails ne correspondent pas.</span>";
    } elseif (!validateDate($birthdate)) {
        $error = "<span style='color: var(--yellow);'>La date de naissance n'est pas valide ou est dans le futur.</span>";
    } else {
        // Lire le fichier JSON existant
        $jsonFile = '../data/data_user.json';
        $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];

        // Vérifier si l'email existe déjà
        foreach ($jsonData as $user) {
            if ($user['email'] === $email) {
                $error = "<span style='color: var(--yellow);'>Cette adresse email est déjà utilisée.</span>";
                break;
            }
        }

        // Si aucune erreur, ajouter les nouvelles données
        if (!isset($error)) {
            $jsonData[] = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'birthdate' => $birthdate, // Stocker la date au format YYYY-MM-DD
                'password' => $password,
                'is_admin' => false // Par défaut, l'utilisateur n'est pas administrateur
            ];

            // Réécrire le fichier JSON
            file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT));

            // Rediriger ou afficher un message de succès
            if ($autoLogin) {
                // Logique pour connecter automatiquement l'utilisateur
                $_SESSION['user_email'] = $email;
                header("Location: Accueil.php"); // Rediriger vers la page principale
                exit();
            } else {
                $success = "<span style='color: var(--yellow);'>Inscription réussie. Vous pouvez maintenant vous connecter.</span>";
            }
        }
    }
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
    <title>Inscription - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/Inscription.css">
    <link rel="stylesheet" href="../Css/formulaire.css">
    <script src="../Javascript/theme.js"></script>
    <script src="../Javascript/formulaire.js"></script>
</head>
<body>
     <?php include('header.php'); ?>

    <hr class="hr1">

    <main>
        <div class="profile-container">
            <img src="../assets/icon/User2.png" alt="User Icon" class="user-icon">

            <form action="inscription.php" method="POST">
                <?php if (isset($error)): ?>
                    <div class="error"><?= $error ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="success"><?= $success ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <div class="input-container">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required>
                    </div><br>

                    <div class="input-container">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                    </div><br>

                    <div class="input-container">
                        <label for="confirm-email">Confirmation email</label>
                        <input type="email" id="confirm-email" name="confirm-email" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" id="birthdate" name="birthdate" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" maxlength="20" required>
                    </div>
                </div>

                <div class="checkbox-label">
                    <label for="auto-login">
                        Se connecter automatiquement
                        <input type="checkbox" id="auto-login" name="auto-login">
                    </label>
                </div>

                <button type="submit" class="button1">Inscription</button>
                <a href="#" class="google-signup">Ou continuer avec <img src="../assets/icon/google.png" alt="Google"></a>
            </form>
        </div>
    </main>

     <?php include('footer.php'); ?>
</body>
</html>
