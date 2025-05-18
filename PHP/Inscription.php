<?php
require_once 'sessions.php';

// If user is already logged in, redirect them from the inscription page
if ($isLoggedIn) {
    header("Location: Accueil.php");
    exit();
}

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
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $email = htmlspecialchars(trim($_POST['email']));
    $confirmEmail = htmlspecialchars(trim($_POST['confirm-email']));
    $birthdate = htmlspecialchars(trim($_POST['birthdate']));
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hacher le mot de passe

    // Les lignes suivantes pour security_question et security_answer ont été supprimées
    // $security_question_key = $_POST['security_question'];
    // $security_answer = htmlspecialchars(trim($_POST['security_answer']));

    $autoLogin = isset($_POST['auto-login']); // Vérifier si l'utilisateur veut se connecter automatiquement

    // Vérification des informations (sans security_question et security_answer)
    if (empty($nom) || empty($prenom) || empty($email) || empty($confirmEmail) || empty($birthdate) || empty($_POST['password'])) {
        $error = "<span style='color: var(--yellow);'>Tous les champs sont obligatoires.</span>";
    } elseif ($email != $confirmEmail) {
        $error = "<span style='color: var(--yellow);'>Les emails ne correspondent pas.</span>";
    } elseif (!validateDate($birthdate)) {
        $error = "<span style='color: var(--yellow);'>La date de naissance n'est pas valide ou est dans le futur.</span>";
    } else {
        // Lire le fichier JSON existant
        $jsonFile = '../data/data_user.json';
        $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        if ($jsonData === null) { // Gérer l'erreur de décodage JSON
            $jsonData = [];
            error_log("Erreur de décodage JSON dans le fichier: " . $jsonFile . ". Le fichier sera réinitialisé si l'inscription aboutit.");
        }

        // Vérifier si l'email existe déjà
        foreach ($jsonData as $user) {
            if (isset($user['email']) && $user['email'] === $email) {
                $error = "<span style='color: var(--yellow);'>Cette adresse email est déjà utilisée.</span>";
                break;
            }
        }

        // Si aucune erreur, ajouter les nouvelles données
        if (!isset($error)) {
            $newUser = [
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'birthdate' => $birthdate,
                'password' => $password,
                'is_admin' => false
                // Les lignes pour security_question_key et security_answer ont été supprimées ici aussi
            ];
            $jsonData[] = $newUser;

            // Réécrire le fichier JSON
            if (file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))) {
                if ($autoLogin) {
                    $_SESSION['user_email'] = $email;
                    header("Location: Accueil.php");
                    exit();
                } else {
                    $success = "<span style='color: var(--yellow);'>Inscription réussie. Vous pouvez maintenant vous connecter.</span>";
                }
            } else {
                $error = "<span style='color: var(--yellow);'>Erreur lors de l'enregistrement des données. Veuillez réessayer.</span>";
                error_log("Impossible d'écrire dans le fichier JSON: " . $jsonFile);
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/Inscription.css">
    <link rel="stylesheet" href="../Css/formulaire.css">
    <script src="../Javascript/theme.js" defer></script>
    <script src="../Javascript/formulaire.js" defer></script>
</head>
<body>
     <?php include('header.php'); ?>

    <hr class="hr1">

    <main>
        <div class="profile-container">
            <img src="../assets/icon/User2.png" alt="User Icon" class="user-icon">

            <form action="Inscription.php" method="POST">
                <?php if (isset($error)): ?>
                    <div class="error" style="color: var(--yellow); margin-bottom: 15px; text-align: center;"><?= $error ?></div>
                <?php endif; ?>
                <?php if (isset($success)): ?>
                    <div class="success" style="color: green; margin-bottom: 15px; text-align: center;"><?= $success ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <div class="input-container">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required value="<?= isset($nom) ? htmlspecialchars($nom) : '' ?>">
                    </div><br>

                    <div class="input-container">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required value="<?= isset($prenom) ? htmlspecialchars($prenom) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                    </div><br>

                    <div class="input-container">
                        <label for="confirm-email">Confirmation email</label>
                        <input type="email" id="confirm-email" name="confirm-email" required value="<?= isset($confirmEmail) ? htmlspecialchars($confirmEmail) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="birthdate">Date de naissance</label>
                        <input type="date" id="birthdate" name="birthdate" required value="<?= isset($birthdate) ? htmlspecialchars($birthdate) : '' ?>">
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" maxlength="20" required>
                    </div>
                </div>

                <div class="checkbox-label" style="text-align: left; max-width: 400px; margin: 10px auto 20px auto;">
                    <input type="checkbox" id="auto-login" name="auto-login" style="width: auto; margin-right: 10px;" <?= isset($autoLogin) && $autoLogin ? 'checked' : '' ?>>
                    <label for="auto-login" style="display: inline; color: var(--white);">
                        Se connecter automatiquement après l'inscription
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