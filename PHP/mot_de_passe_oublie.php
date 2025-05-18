<?php
require_once 'sessions.php'; // Gère session_start()

// Fichier de configuration (à créer si vous n'en avez pas)
require_once 'config.php'; // Pour $app_base_url

$user_email_input = '';
$error_message = '';
$info_message = '';

// Chemin vers le fichier des utilisateurs
$jsonFile = '../data/data_user.json';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['submit_email'])) {
        $user_email_input = htmlspecialchars(trim($_POST['email']));

        if (!filter_var($user_email_input, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Veuillez entrer une adresse e-mail valide.";
        } else {
            $jsonDataUsers = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
            if ($jsonDataUsers === null) { $jsonDataUsers = []; }

            $userFound = false;
            $userKey = null;

            foreach ($jsonDataUsers as $key => $userAccount) {
                if (isset($userAccount['email']) && strtolower($userAccount['email']) === strtolower($user_email_input)) {
                    $userFound = true;
                    $userKey = $key;
                    break;
                }
            }

            if ($userFound) {
                $token = bin2hex(random_bytes(32));
                $token_expiry = time() + 3600; // Token valide pour 1 heure

                $jsonDataUsers[$userKey]['reset_token'] = $token;
                $jsonDataUsers[$userKey]['reset_token_expiry'] = $token_expiry;

                if (file_put_contents($jsonFile, json_encode($jsonDataUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false) {
                    // Utilisation de $app_base_url depuis config.php
                    $reset_link = rtrim($app_base_url, '/') . "/PHP/reinitialiser_mot_de_passe.php?email=" . urlencode($user_email_input) . "&token=" . urlencode($token);

                    $to = $user_email_input;
                    $subject = "Réinitialisation de votre mot de passe - FINAL TRIP";
                    $message_body = "Bonjour,<br><br>";
                    $message_body .= "Vous avez demandé une réinitialisation de mot de passe pour votre compte sur FINAL TRIP.<br>";
                    $message_body .= "Veuillez cliquer sur le lien suivant pour choisir un nouveau mot de passe :<br>";
                    $message_body .= "<a href='" . $reset_link . "'>" . $reset_link . "</a><br><br>";
                    $message_body .= "Ce lien expirera dans une heure.<br>";
                    $message_body .= "Si vous n'avez pas demandé cette réinitialisation, veuillez ignorer cet e-mail.<br><br>";
                    $message_body .= "Cordialement,<br>L'équipe FINAL TRIP";

                    $headers = "From: noreply@final-trip.local\r\n";
                    $headers .= "Reply-To: noreply@final-trip.local\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
                    $headers .= "X-Mailer: PHP/" . phpversion();

                    if (mail($to, $subject, $message_body, $headers)) {
                        $info_message = "Si votre adresse e-mail est enregistrée dans notre système, vous recevrez d'ici quelques minutes un lien pour réinitialiser votre mot de passe.";
                    } else {
                        $php_errormsg = error_get_last()['message'] ?? 'Erreur inconnue lors de l\'envoi de l\'email.';
                        error_log("ERREUR MOT_DE_PASSE_OUBLIE: Échec de l'envoi de l'email de réinitialisation à: " . $to . ". Erreur PHP: " . $php_errormsg);
                        $error_message = "Le service de réinitialisation de mot de passe est temporairement indisponible. Veuillez réessayer plus tard.";
                    }
                } else {
                    error_log("ERREUR MOT_DE_PASSE_OUBLIE: Échec de l'écriture du token pour l'email: " . $user_email_input . " dans le fichier " . $jsonFile);
                    $error_message = "Une erreur technique est survenue lors de la préparation de la réinitialisation. Veuillez réessayer.";
                }
            } else {
                // Message générique pour ne pas révéler si un e-mail existe
                $info_message = "Si votre adresse e-mail est enregistrée dans notre système, vous recevrez d'ici quelques minutes un lien pour réinitialiser votre mot de passe.";
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
    <title>Mot de Passe Oublié - FINAL TRIP</title>
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
            <h2>Mot de Passe Oublié</h2>

            <?php if (!empty($error_message)): ?>
                <div class="error-message" style="color: var(--yellow); margin-bottom:15px; text-align:center; padding: 10px; border: 1px solid var(--yellow); border-radius: 5px;"><?= htmlspecialchars($error_message) ?></div>
            <?php endif; ?>
            <?php if (!empty($info_message)): ?>
                <div class="success-message" style="color: green; margin-bottom:15px; text-align:center; padding: 10px; border: 1px solid green; border-radius: 5px;"><?= htmlspecialchars($info_message) ?></div>
            <?php endif; ?>

            <?php if (empty($info_message) || !empty($error_message) ) : // Afficher le formulaire si pas de message d'info (ou si erreur après soumission)?>
            <form action="mot_de_passe_oublie.php" method="POST">
                <p style="color: var(--white); margin-bottom: 20px;">Veuillez entrer votre adresse e-mail. Si elle est associée à un compte, nous vous enverrons un lien pour réinitialiser votre mot de passe.</p>
                <div class="form-group">
                    <label for="email" class="right">Email</label>
                    <input type="email" id="email" name="email" value="<?= htmlspecialchars($user_email_input) ?>" required>
                </div>
                <button type="submit" name="submit_email" class="button1">Envoyer le lien</button>
            </form>
            <?php endif; ?>
            <p class="register-link" style="margin-top: 30px;"><a href="Connexion.php">Retour à la connexion</a></p>
        </div>
    </main>
    <?php include('footer.php'); ?>
</body>
</html>