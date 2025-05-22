<?php
// PHP/Profil.php

if (session_status() == PHP_SESSION_NONE) {
    session_start(); 
}

if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php");
    exit();
}

// Fonctions utilitaires
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) === $date) {
        $year = $d->format('Y');
        if ($year > 1900 && $d->getTimestamp() <= time()) { // Modifié pour permettre la date actuelle
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

// Logique de gestion du profil
$editEmail = isset($_GET['edit']) ? filter_var($_GET['edit'], FILTER_SANITIZE_EMAIL) : $_SESSION['user_email'];
$currentUserData = getUserData($_SESSION['user_email']);
$isAdmin = ($currentUserData && isset($currentUserData['is_admin']) && $currentUserData['is_admin']);
$user = getUserData($editEmail);

if ($user === null) {
    // Si l'utilisateur à éditer n'est pas trouvé, on peut afficher un message
    // ou rediriger. Pour l'instant, on affiche un message simple et on arrête.
    // Vous pourriez vouloir une page d'erreur plus élaborée.
    include('header.php'); // Pour avoir le style et le header
    echo "<div style='text-align:center; padding: 50px; color: var(--white);'>Utilisateur non trouvé.</div>";
    include('footer.php');
    exit();
}

$error = null;
$success = null;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'save_profile') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $prenom = htmlspecialchars(trim($_POST['prenom']));
    $birthdate_input = htmlspecialchars(trim($_POST['birthdate'])); 
    $password_input = trim($_POST['password'] ?? ''); 
    $password_to_save = null;
    
    // Logique de mise à jour du mot de passe
    // Permettre la modification du mot de passe si l'utilisateur modifie son propre profil
    // OU si un admin modifie le profil de quelqu'un d'autre ET un nouveau mot de passe est fourni.
    if (($_SESSION['user_email'] === $editEmail || $isAdmin) && !empty($password_input)) {
        $password_to_save = password_hash($password_input, PASSWORD_DEFAULT);
    } elseif (!empty($password_input) && $_SESSION['user_email'] !== $editEmail && !$isAdmin) {
        // Cas où un non-admin essaie de changer le mdp de qqn d'autre (ne devrait pas arriver avec l'UI)
        $error = "<span style='color: red;'>Action non autorisée.</span>";
    }
    // Si $password_input est vide, $password_to_save reste null, donc le mdp n'est pas changé dans updateUserData


    if (!validateDate($birthdate_input)) {
        $error = "<span style='color: var(--yellow);'>La date de naissance n'est pas valide (AAAA-MM-JJ), doit être après 1900 et ne peut pas être dans le futur.</span>";
    } elseif ($error === null) { // S'assurer qu'il n'y a pas déjà eu une erreur (ex: permission mdp)
        if (updateUserData($editEmail, $nom, $prenom, $birthdate_input, $password_to_save)) {
            $user = getUserData($editEmail); // Recharger les données utilisateur après mise à jour
            $success = "<span style='color: var(--yellow);'>Informations mises à jour avec succès.</span>";
        } else {
             $error = "<span style='color: red;'>Erreur lors de la sauvegarde des données.</span>";
        }
    }
}

// Récupération de l'historique des achats pour l'utilisateur du profil affiché
$emailForHistory = $user['email']; 
$userPurchases = [];
$historiqueFile = '../data/historique_achats.json';

if (file_exists($historiqueFile) && is_readable($historiqueFile)) {
    $jsonHistoriqueContent = file_get_contents($historiqueFile);
    if (!empty($jsonHistoriqueContent)) {
        $allPurchases = json_decode($jsonHistoriqueContent, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($allPurchases)) {
            foreach ($allPurchases as $purchase) {
                if (isset($purchase['user_email']) && $purchase['user_email'] === $emailForHistory) {
                    $userPurchases[] = $purchase;
                }
            }
            if (!empty($userPurchases)) {
                usort($userPurchases, function ($a, $b) {
                    return strtotime($b['purchase_timestamp']) - strtotime($a['purchase_timestamp']);
                });
            }
        } else {
            error_log("Erreur de décodage de historique_achats.json sur Profil.php ou ce n'est pas un tableau.");
        }
    }
} else {
    error_log("Fichier historique_achats.json non trouvé ou non lisible sur Profil.php.");
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
    <!-- Les styles pour l'historique des achats sont maintenant dans Profil.css -->
</head>
<body>
<?php include('header.php'); // Inclus sessions.php, gère $profileLink, $nombreArticlesPanier etc. ?>
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
                            <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($user['nom'] ?? '') ?>" readonly class="form-field" required>
                            <button type="button" class="edit-btn" data-field="nom">Modifier</button>
                            <button type="button" class="validate-btn hidden" data-field="nom">Valider</button>
                            <button type="button" class="cancel-btn hidden" data-field="nom">Annuler</button>
                        </div>
                    </div>

                    <div class="input-container field-container">
                        <label for="prenom">Prénom</label>
                        <div class="input-wrapper">
                            <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($user['prenom'] ?? '') ?>" readonly class="form-field" required>
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
							<input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" class="form-field" disabled> 
                            <!-- Champ email toujours désactivé pour la modification directe -->
						</div>
					</div>

					<?php if ($_SESSION['user_email'] === $editEmail || $isAdmin): ?>
					    <div class="input-container field-container">
    						<label for="password">Nouveau mot de passe</label>
   						    <div class="input-wrapper">
        					    <input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" maxlength="20" readonly class="form-field">
        						<button type="button" class="toggle-password-btn" onclick="togglePasswordVisibility()" style="border: none; background: none; cursor: pointer; padding: 0 5px; display: flex; align-items: center;">
            						<img id="togglePasswordIcon" src="../assets/icon/oeil.png" alt="Afficher/Masquer" style="width: 25px; height: auto;">
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
                                        // Assurer que la date est dans le format YYYY-MM-DD pour l'input type="date"
                                        $dateObj = new DateTime($user['birthdate']);
                                        $birthdateValue = $dateObj->format('Y-m-d'); 
                                    } catch (Exception $e) {
                                        // Gérer le cas où la date stockée n'est pas valide
                                        $birthdateValue = ''; 
                                        error_log("Date de naissance invalide pour l'utilisateur " . $user['email'] . ": " . $user['birthdate']);
                                    }
                                }
                            ?>
                            <input type="date" id="birthdate" name="birthdate" value="<?= $birthdateValue ?>" readonly class="form-field" required>
                            <button type="button" class="edit-btn" data-field="birthdate">Modifier</button>
                            <button type="button" class="validate-btn hidden" data-field="birthdate">Valider</button>
                            <button type="button" class="cancel-btn hidden" data-field="birthdate">Annuler</button>
                        </div>
                    </div>
                    <div class="input-container field-container placeholder" style="visibility: hidden;">
                        <!-- Juste pour l'alignement si le champ mot de passe n'est pas montré -->
                    </div>
                </div>

                <div class="main-buttons">
                    <button type="submit" id="save-changes-btn" class="button1 hidden">Soumettre les modifications</button>
                    <a href="Logout.php" class="button2">Déconnexion</a>
                </div>
            </form>

            <!-- Section pour l'historique des achats -->
            <section class="purchased-trips-section">
                <h2>Mes Voyages Achetés</h2>
                <?php if (!empty($userPurchases)): ?>
                    <?php foreach ($userPurchases as $index => $purchase): ?>
                        <div class="purchase-item">
                            <h3><?php echo htmlspecialchars($purchase['trip_title']); ?></h3>
                            <p><strong>Date d'achat :</strong> <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($purchase['purchase_timestamp']))); ?></p>
                            <p><strong>Montant payé :</strong> <?php echo number_format($purchase['total_paid'], 2, ',', ' '); ?> €</p>
                            <p><strong>ID Transaction :</strong> <?php echo htmlspecialchars($purchase['transaction_id']); ?></p>
                            
                            <button class="purchase-details-toggle" onclick="toggleDetails('details-<?php echo $index; ?>')">Voir les détails des options</button>
                            <div id="details-<?php echo $index; ?>" class="purchase-options-details">
                                <p><strong>Options personnalisées :</strong></p>
                                <?php if (isset($purchase['personalized_trip_details']['stages']) && is_array($purchase['personalized_trip_details']['stages'])): ?>
                                    <ul>
                                    <?php foreach ($purchase['personalized_trip_details']['stages'] as $stage): ?>
                                        <li>
                                            <strong>Jour <?php echo htmlspecialchars($stage['day'] ?? '?'); ?>: <?php echo htmlspecialchars($stage['title'] ?? 'Étape'); ?></strong>
                                            <ul>
                                                <?php foreach (['hebergement', 'restauration', 'activites', 'transport'] as $category): ?>
                                                    <?php if (isset($stage['options'][$category]['nom'])): ?>
                                                        <li>
                                                            <strong><?php echo ucfirst($category); ?>:</strong> <?php echo htmlspecialchars($stage['options'][$category]['nom']); ?>
                                                            <?php if (isset($stage['options'][$category]['prix']) && $stage['options'][$category]['prix'] !== "inclus"): ?>
                                                                (<?php echo htmlspecialchars($stage['options'][$category]['prix']); ?>)
                                                            <?php endif; ?>
                                                            <?php if ($category === 'activites' && isset($stage['options'][$category]['participants'])): ?>
                                                                pour <?php echo htmlspecialchars($stage['options'][$category]['participants']); ?> personne(s)
                                                            <?php endif; ?>
                                                        </li>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                            </ul>
                                        </li>
                                    <?php endforeach; ?>
                                    </ul>
                                <?php else: ?>
                                    <p>Détails des options non disponibles.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-purchases">Vous n'avez encore acheté aucun voyage.</p>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php include('footer.php'); ?>

<script>
    const isAdminEditingAnotherUser = <?= json_encode($isAdmin && isset($_SESSION['user_email']) && isset($editEmail) && $_SESSION['user_email'] !== $editEmail) ?>;
    const adminUpdateDelay = 3000; // Délai de 3 secondes pour la simulation admin

    // Fonction pour afficher/masquer les détails des options de voyage
    function toggleDetails(elementId) {
        const detailsDiv = document.getElementById(elementId);
        if (detailsDiv) {
            if (detailsDiv.style.display === 'none' || detailsDiv.style.display === '') {
                detailsDiv.style.display = 'block';
            } else {
                detailsDiv.style.display = 'none';
            }
        }
    }

    // Fonction pour afficher/masquer le mot de passe
    function togglePasswordVisibility() {
        const passwordField = document.getElementById('password');
        const eyeIconImg = document.getElementById('togglePasswordIcon'); 

        if (passwordField.type === 'password') {
            passwordField.type = 'text';
            if (eyeIconImg) eyeIconImg.style.opacity = '0.6'; 
        } else {
            passwordField.type = 'password';
            if (eyeIconImg) eyeIconImg.style.opacity = '1';
        }
    }
</script>
</body>
</html>