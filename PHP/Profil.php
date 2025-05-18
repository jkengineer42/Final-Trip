<?php
// session_start(); // Déjà inclus et géré par sessions.php ou header.php
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Assurez-vous que la session est démarrée
}

// Redirection si non connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php");
    exit();
}

// Fonctions utilitaires (validateDate, getUserData, updateUserData - reprises de votre version précédente)
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) === $date) {
        $year = $d->format('Y');
        if ($year > 1900 && $d->getTimestamp() <= time()) {
            return true;
        }
    }
    return false;
}

function getUserData($email) {
    $jsonFile = '../data/data_user.json';
    $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    if ($jsonData === null) { 
        error_log("Erreur de décodage JSON dans le fichier: " . $jsonFile);
        return null;
    }
    foreach ($jsonData as $user) {
        if (isset($user['email']) && $user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

function updateUserData($email, $nom, $prenom, $birthdate, $password) {
    $jsonFile = '../data/data_user.json';
    $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
     if ($jsonData === null) {
        error_log("Erreur de décodage JSON avant mise à jour dans le fichier: " . $jsonFile);
        return false; 
    }
    $userFound = false;
    for ($i = 0; $i < count($jsonData); $i++) {
        if (isset($jsonData[$i]['email']) && $jsonData[$i]['email'] === $email) {
            $jsonData[$i]['nom'] = $nom;
            $jsonData[$i]['prenom'] = $prenom;
            $jsonData[$i]['birthdate'] = $birthdate; 
            if ($password) { 
                $jsonData[$i]['password'] = $password; 
            }
            $userFound = true;
            break;
        }
    }
    if ($userFound) {
       if (file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
           error_log("Impossible d'écrire dans le fichier JSON: " . $jsonFile);
           return false; 
       }
       return true; 
    }
    return false; 
}

// Logique de gestion du profil (reprise de votre version précédente)
$editEmail = isset($_GET['edit']) ? filter_var($_GET['edit'], FILTER_SANITIZE_EMAIL) : $_SESSION['user_email'];
$currentUserData = getUserData($_SESSION['user_email']);
$isAdmin = ($currentUserData && isset($currentUserData['is_admin']) && $currentUserData['is_admin']);
$user = getUserData($editEmail);

if ($user === null) {
    echo "Utilisateur non trouvé.";
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_profile') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $birthdate_input = htmlspecialchars(trim($_POST['birthdate'])); 
    $password_input = trim($_POST['password'] ?? ''); 
    $password_to_save = null;
    $error = null;
    $success = null;

    if (($_SESSION['user_email'] === $editEmail || $isAdmin) && !empty($password_input)) {
        $password_to_save = password_hash($password_input, PASSWORD_DEFAULT);
    }

    if (!validateDate($birthdate_input)) {
        $error = "<span style='color: var(--yellow);'>La date de naissance n'est pas valide (AAAA-MM-JJ), doit être après 1900 et ne peut pas être dans le futur.</span>";
    } else {
        if (updateUserData($editEmail, $nom, $prenom, $birthdate_input, $password_to_save)) {
            $user = getUserData($editEmail);
            $success = "<span style='color: var(--yellow);'>Informations mises à jour avec succès.</span>";
        } else {
             $error = "<span style='color: red;'>Erreur lors de la sauvegarde des données.</span>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/Profil.css"> 
    <script src="../Javascript/theme.js" defer></script>
    <script src="../Javascript/profil.js" defer></script>
</head>
<body>
<?php include('header.php'); ?>
    <hr class="hr1">

    <main>
        <div class="profile-container">
            <img src="../assets/icon/User2.png" alt="Icône Utilisateur" class="user-icon">

            <form id="profile-form" action="Profil.php<?= ($isAdmin && $_SESSION['user_email'] !== $editEmail) ? '?edit='.urlencode($editEmail) : '' ?>" method="POST">
                <input type="hidden" name="action" value="save_profile">
		<input type="hidden" id="user-email-to-edit" value="<?php echo htmlspecialchars($editEmail); ?>">

                <?php if (isset($success)): ?>
                    <div class="message success"><?= $success ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="message error"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($isAdmin && $_SESSION['user_email'] != $editEmail): ?>
                    <div class="admin-info-banner">Vous modifiez le profil de <?= htmlspecialchars($editEmail) ?>.</div>
                 <?php endif; ?>

                 <?php if ($isAdmin && $_SESSION['user_email'] == $editEmail): ?>
                 <div class="admin-link">
                     <a href="Admin.php" class="button-small admin-button">Accéder à la page Admin</a>
                 </div>
                 <?php endif; ?>

                <div class="form-group">
                    <div class="input-container field-container">
                        <label for="nom">Nom</label>
                        <div class="input-wrapper">
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" readonly required>
                            <button type="button" class="edit-btn" data-field="nom">Modifier</button>
                            <button type="button" class="validate-btn hidden" data-field="nom">Valider</button>
                            <button type="button" class="cancel-btn hidden" data-field="nom">Annuler</button>
                        </div>
                    </div>

                    <div class="input-container field-container">
                        <label for="prenom">Prénom</label>
                        <div class="input-wrapper">
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" readonly required>
                             <button type="button" class="edit-btn" data-field="prenom">Modifier</button>
                            <button type="button" class="validate-btn hidden" data-field="prenom">Valider</button>
                            <button type="button" class="cancel-btn hidden" data-field="prenom">Annuler</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
					<div class="input-container field-container">
						<label for="email">Email</label>
						<div class="input-wrapper">
							<input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" disabled>
							</div>
					</div>

					<?php if ($_SESSION['user_email'] === $editEmail || $isAdmin): ?>


					<div class="input-container field-container">
    						<label for="password">Nouveau mot de passe</label>
   						 <div class="input-wrapper">
        					<input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" maxlength="20" readonly>
        
        						<!-- Bouton œil simplifié, inséré en tant que bouton -->
        						<button type="button" style="border: none; background: none; cursor: pointer; margin: 0 5px;" onclick="togglePasswordVisibility()">
            						<img src="../assets/icon/oeil.png" alt="Afficher/Masquer" style="width: 25px; height: 25px;">
        						</button>
        
        						<button type="button" class="edit-btn" data-field="password">Modifier</button>
        						<button type="button" class="validate-btn hidden" data-field="password">Valider</button>
        						<button type="button" class="cancel-btn hidden" data-field="password">Annuler</button>
   					 	</div>
    						<small>Entrez un nouveau mot de passe uniquement si vous souhaitez le changer.</small>
					</div>
						
					<?php else: ?>
                        <div class="input-container field-container placeholder"></div>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <div class="input-container field-container">
                        <label for="birthdate">Date de naissance</label>
                        <div class="input-wrapper">
                            <?php
                                $birthdateValue = '';
                                if (!empty($user['birthdate'])) {
                                    try {
                                        $dateObj = new DateTime($user['birthdate']);
                                        $birthdateValue = $dateObj->format('Y-m-d'); 
                                    } catch (Exception $e) {
                                        $birthdateValue = '';
                                    }
                                }
                            ?>
                            <input type="date" id="birthdate" name="birthdate" value="<?= $birthdateValue ?>" readonly required>
                            <button type="button" class="edit-btn" data-field="birthdate">Modifier</button>
                            <button type="button" class="validate-btn hidden" data-field="birthdate">Valider</button>
                            <button type="button" class="cancel-btn hidden" data-field="birthdate">Annuler</button>
                        </div>
                    </div>
                    <div class="input-container field-container placeholder" style="visibility: hidden;">
                        </div>
                </div>


                <div class="main-buttons">
                    <button type="submit" id="save-changes-btn" class="button1 hidden">Soumettre les modifications</button>
                    <a href="Logout.php" class="button2">Déconnexion</a>
                </div>

            </form>
        </div>
    </main>

    <?php include('footer.php'); ?>

<script>
    const isAdminEditingAnotherUser = <?= json_encode($isAdmin && isset($_SESSION['user_email']) && isset($editEmail) && $_SESSION['user_email'] !== $editEmail) ?>;
    const adminUpdateDelay = 5000; 
</script>

	<script>
        function togglePasswordVisibility(icon) {
            const passwordField = document.getElementById('password');
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.style.opacity = '0.5';
            } else {
                passwordField.type = 'password';
                icon.style.opacity = '1';
            }
        }
        </script>
</body>
</html>
