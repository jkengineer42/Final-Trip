<?php
require_once 'sessions.php'; 

$error_message = '';
$success_message = '';
$token_valid = false;
$user_email_from_url = ''; 
$received_token_from_url = '';

$jsonFile = '../data/data_user.json';

function validatePasswordStrength($password) {
    $errors = [];
    if (strlen($password) < 8) {
        $errors[] = "doit contenir au moins 8 caractères.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = "doit contenir au moins une lettre majuscule.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = "doit contenir au moins une lettre minuscule.";
    }
    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = "doit contenir au moins un chiffre.";
    }
    // Optionnel: vérifier la présence de caractères spéciaux
    // if (!preg_match('/[\W_]/', $password)) { // \W correspond à tout ce qui n'est pas une lettre, un chiffre ou un tiret bas
    //     $errors[] = "doit contenir au moins un caractère spécial.";
    // }
    return $errors;
}

if (isset($_GET['email']) && isset($_GET['token'])) {
    $user_email_from_url = filter_var(trim($_GET['email']), FILTER_SANITIZE_EMAIL);
    $received_token_from_url = trim($_GET['token']);

    if (!filter_var($user_email_from_url, FILTER_VALIDATE_EMAIL)) {
        $error_message = "L'adresse e-mail fournie dans le lien n'est pas valide.";
    } elseif (empty($received_token_from_url)) {
        $error_message = "Le jeton de réinitialisation est manquant dans le lien.";
    } else {
        $jsonDataUsers = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
        if ($jsonDataUsers === null) { $jsonDataUsers = []; }

        $userFound = false;
        $userKey = null;

        foreach ($jsonDataUsers as $key => $userAccount) {
            if (isset($userAccount['email']) && strtolower($userAccount['email']) === strtolower($user_email_from_url)) {
                $userFound = true;
                if (isset($userAccount['reset_token']) && hash_equals($userAccount['reset_token'], $received_token_from_url) &&
                    isset($userAccount['reset_token_expiry']) && $userAccount['reset_token_expiry'] > time()) {
                    $token_valid = true;
                    $userKey = $key; 
                } else {
                    $error_message = "Le lien de réinitialisation est invalide ou a expiré. Veuillez refaire une demande.";
                }
                break;
            }
        }

        if (!$userFound && empty($error_message)) {
            $error_message = "Aucun utilisateur trouvé pour cette demande de réinitialisation.";
        }
    }
} else {
    $error_message = "Informations de réinitialisation manquantes dans le lien. Veuillez utiliser le lien envoyé par e-mail.";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $token_valid && isset($_POST['submit_new_password'])) {
    // S'assurer de récupérer l'email et le token à partir des champs cachés du formulaire pour la soumission POST
    $email_from_form = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $token_from_form = trim($_POST['token']);

    // Double vérification que l'email et le token du formulaire correspondent à ceux validés via GET
    // C'est une sécurité supplémentaire, bien que $token_valid devrait déjà le couvrir.
    if (strtolower($email_from_form) !== strtolower($user_email_from_url) || !hash_equals($token_from_form, $received_token_from_url)) {
        $error_message = "Erreur de soumission. Veuillez réessayer depuis le lien d'origine.";
        $token_valid = false; // Invalider si discordance
    } else {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        $password_errors = validatePasswordStrength($new_password);

        if (empty($new_password) || empty($confirm_password)) {
            $error_message = "Veuillez remplir les deux champs de mot de passe.";
        } elseif (!empty($password_errors)) {
             $error_message = "Le nouveau mot de passe ne respecte pas les critères de sécurité :<br>" . implode("<br>", $password_errors);
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Les mots de passe ne correspondent pas.";
        } else {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // Recharger les données utilisateur pour s'assurer qu'elles sont à jour avant modification
            $currentData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
            if ($currentData === null) {
                 $error_message = "Erreur critique lors de la lecture des données utilisateurs. Veuillez contacter le support.";
                 error_log("REINITIALISER_MDP: Erreur de décodage JSON au moment de la mise à jour pour: " . $email_from_form);
            } else {
                $jsonDataUsers = $currentData; // Utiliser les données fraîches
                $updateKey = null;
                foreach ($jsonDataUsers as $key => $user) {
                    if (isset($user['email']) && strtolower($user['email']) === strtolower($email_from_form)) {
                        $updateKey = $key;
                        break;
                    }
                }

                if ($updateKey !== null) {
                    $jsonDataUsers[$updateKey]['password'] = $hashed_password;
                    unset($jsonDataUsers[$updateKey]['reset_token']);
                    unset($jsonDataUsers[$updateKey]['reset_token_expiry']);

                    if (file_put_contents($jsonFile, json_encode($jsonDataUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
                        $success_message = "Votre mot de passe a été réinitialisé avec succès. Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.";
                        $token_valid = false; 
                    } else {
                        $error_message = "Une erreur est survenue lors de la mise à jour de votre mot de passe. Veuillez réessayer.";
                        error_log("ERREUR REINITIALISER_MDP: Échec d'écriture dans data_user.json pour : " . $email_from_form);
                    }
                } else {
                    $error_message = "Utilisateur non trouvé lors de la tentative de mise à jour. Veuillez contacter le support.";
                     error_log("ERREUR REINITIALISER_MDP: Utilisateur " . $email_from_form . " non trouvé au moment de la mise à jour finale.");
                }
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
    <title>Réinitialiser le Mot de Passe - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/Connexion.css"> 
    <link rel="stylesheet" href="../Css/formulaire.css">
    <script src="../Javascript/theme.js" defer></script>
    <script src="../Javascript/formulaire.js" defer></script> 
</head>
<body>
    <?php include('header.php'); ?>
    <hr class="hr1">
    <main>
        <div class="login-container">
            <h2>Réinitialiser Votre Mot de Passe</h2>

            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: var(--yellow); margin-bottom:15px; text-align:center; padding: 10px; border: 1px solid var(--yellow); border-radius: 5px;"><?= $error_message /* No htmlspecialchars for <br> */ ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="success-message" style="color: green; margin-bottom:15px; text-align:center; padding: 10px; border: 1px solid green; border-radius: 5px;"><?= htmlspecialchars($success_message) ?></div>
                <p style="text-align:center; margin-top:20px;"><a href="Connexion.php" class="button1">Se Connecter</a></p>
            <?php endif; ?>

            <?php if ($token_valid && empty($success_message)): ?>
                <form action="reinitialiser_mot_de_passe.php?email=<?= htmlspecialchars(urlencode($user_email_from_url)) ?>&token=<?= htmlspecialchars(urlencode($received_token_from_url)) ?>" method="POST">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($user_email_from_url) ?>">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($received_token_from_url) ?>">

                    <p style="color: var(--white); margin-bottom: 20px;">Veuillez choisir un nouveau mot de passe pour le compte associé à <?= htmlspecialchars($user_email_from_url) ?>.</p>
                    
                    <div class="form-group">
                        <label for="new_password" class="right">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required minlength="8" maxlength="50">
                         <small style="color: var(--white); font-size:0.8em; display:block; margin-top:5px;">Minimum 8 caractères, avec majuscule, minuscule et chiffre.</small>
                    </div>
                    <br>
                    <div class="form-group">
                        <label for="confirm_password" class="right">Confirmer le nouveau mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="8" maxlength="50">
                    </div>
                    <br>
                    <button type="submit" name="submit_new_password" class="button1">Réinitialiser</button>
                </form>
            <?php elseif (empty($success_message) && !$token_valid): // Si le token n'est pas valide et qu'il n'y a pas de message de succès ?>
                <p style="color: var(--white); text-align:center;">Si vous rencontrez des difficultés, veuillez refaire une demande de réinitialisation.</p>
                <p style="text-align:center; margin-top:20px;"><a href="mot_de_passe_oublie.php" class="button1">Nouvelle demande</a></p>
            <?php endif; ?>
        </div>
    </main>
    <?php include('footer.php'); ?>
</body>
</html>