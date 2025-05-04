<?php
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['user_email'])) {
    header("Location: Connexion.php"); // Rediriger vers la page de connexion si l'utilisateur n'est pas connecté
    exit();
}

// Fonction pour valider la date de naissance
function validateDate($date, $format = 'Y-m-d') {
    $d = DateTime::createFromFormat($format, $date);
    if ($d && $d->format($format) === $date) {
        // Vérifier que l'année est supérieure à 1900 et que la date n'est pas dans le futur
        $year = $d->format('Y');
        if ($year > 1900 && $d->getTimestamp() <= time()) {
            return true;
        }
    }
    return false;
}

// Fonction pour obtenir les informations de l'utilisateur
function getUserData($email) {
    $jsonFile = '../data/data_user.json';
    $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
    if ($jsonData === null) { // Gérer l'erreur de décodage JSON
        // Log l'erreur ou retourne une erreur appropriée
        error_log("Erreur de décodage JSON dans le fichier: " . $jsonFile);
        return null; // ou lancez une exception
    }
    foreach ($jsonData as $user) {
        if (isset($user['email']) && $user['email'] === $email) {
            return $user;
        }
    }
    return null;
}

// Fonction pour mettre à jour les informations de l'utilisateur
function updateUserData($email, $nom, $prenom, $birthdate, $password) {
    $jsonFile = '../data/data_user.json';
    $jsonData = file_exists($jsonFile) ? json_decode(file_get_contents($jsonFile), true) : [];
     if ($jsonData === null) {
        error_log("Erreur de décodage JSON avant mise à jour dans le fichier: " . $jsonFile);
        return false; // Échec de la mise à jour
    }

    $userFound = false;
    for ($i = 0; $i < count($jsonData); $i++) {
        if (isset($jsonData[$i]['email']) && $jsonData[$i]['email'] === $email) {
            $jsonData[$i]['nom'] = $nom;
            $jsonData[$i]['prenom'] = $prenom;
            // Assurez-vous que la date est dans le bon format si nécessaire avant de sauvegarder
            $jsonData[$i]['birthdate'] = $birthdate; // Gardons Y-m-d pour la sauvegarde
            if ($password) { // Met à jour le mot de passe seulement s'il est fourni
                $jsonData[$i]['password'] = $password; // Le mot de passe doit être déjà haché
            }
            $userFound = true;
            break;
        }
    }

    if ($userFound) {
        // Sauvegarde dans le fichier JSON
       if (file_put_contents($jsonFile, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) === false) {
           error_log("Impossible d'écrire dans le fichier JSON: " . $jsonFile);
           return false; // Échec de l'écriture
       }
       return true; // Succès
    }
    return false; // Utilisateur non trouvé
}

// Récupérer l'email de l'utilisateur à modifier
$editEmail = isset($_GET['edit']) ? filter_var($_GET['edit'], FILTER_SANITIZE_EMAIL) : $_SESSION['user_email'];

// Récupérer les informations de l'utilisateur actuel (pour la vérification admin)
$currentUserData = getUserData($_SESSION['user_email']);
$isAdmin = ($currentUserData && isset($currentUserData['is_admin']) && $currentUserData['is_admin']);

// Récupérer les informations de l'utilisateur à afficher/modifier
$user = getUserData($editEmail);

// Si l'utilisateur n'existe pas, rediriger ou afficher une erreur
if ($user === null) {
    // Peut-être rediriger vers une page d'erreur ou la liste des utilisateurs pour l'admin
    echo "Utilisateur non trouvé.";
    exit();
}


// Vérifier si le formulaire a été soumis via POST (pour la sauvegarde finale)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Vérifier si l'action est bien 'save_profile' pour éviter les conflits
    if (isset($_POST['action']) && $_POST['action'] == 'save_profile') {

        $nom = htmlspecialchars(trim($_POST['nom']));
        $prenom = htmlspecialchars(trim($_POST['prenom']));
        $birthdate_input = htmlspecialchars(trim($_POST['birthdate'])); // Format YYYY-MM-DD attendu du champ date
        $password_input = trim($_POST['password'] ?? ''); // Utiliser l'opérateur null coalescent

        $password_to_save = null;
        $error = null;
        $success = null;

        // Vérifier si l'utilisateur modifie son propre profil ET si un nouveau mot de passe est fourni
        if ($_SESSION['user_email'] === $editEmail && !empty($password_input)) {
             // Ajouter des validations de mot de passe ici si nécessaire (longueur, complexité)
            $password_to_save = password_hash($password_input, PASSWORD_DEFAULT);
        } else {


            
        }


        // Vérification de la date de naissance
        if (!validateDate($birthdate_input)) {
            $error = "<span style='color: var(--yellow);'>La date de naissance n'est pas valide (AAAA-MM-JJ), doit être après 1900 et ne peut pas être dans le futur.</span>";
        } else {
            // Mettre à jour les informations de l'utilisateur
            // Passe $password_to_save (qui est soit le nouveau hash, soit null)
            if (updateUserData($editEmail, $nom, $prenom, $birthdate_input, $password_to_save)) {
                // Mettre à jour les données utilisateur après sauvegarde pour affichage immédiat
                $user = getUserData($editEmail);
                $success = "<span style='color: var(--yellow);'>Informations mises à jour avec succès.</span>";
            } else {
                 $error = "<span style='color: red;'>Erreur lors de la sauvegarde des données.</span>";
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
    <title>Profil - FINAL TRIP</title>
    <link rel="stylesheet" href="../Css/Profil.css">
    <link rel="stylesheet" href="../Css/root.css">
<script src="../Javascript/formulaire.js"></script>
<script src="../Javascript/theme.js"></script>
</head>
<body>
<header>
    <a href="Accueil.php" class="logo">FINAL TRIP</a>
    <div class="right">
        <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
        <a href="Destination.php" class="head1">Destination</a>
        <button class="encadré">Contact</button>
        <a href="Profil.php" class="img1"><img src="../assets/icon/User.png" alt="Profil"></a>
        <a href="#" class="img2"><img src="../assets/icon/Shopping cart.png" alt="Panier"></a>
    </div>
</header>
    <hr class="hr1">

    <main>
        <div class="profile-container">
            <img src="../assets/icon/User2.png" alt="Icône Utilisateur" class="user-icon">

            <form id="profile-form" action="Profil.php?edit=<?= urlencode($editEmail) ?>" method="POST">
                <input type="hidden" name="action" value="save_profile">

                <?php if (isset($success)): ?>
                    <div class="message success"><?= $success ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="message error"><?= $error ?></div>
                <?php endif; ?>

                <?php if ($isAdmin && $_SESSION['user_email'] != $editEmail): // Afficher si admin ET ne modifie pas son propre profil ?>
                 <?php endif; ?>

                 <?php if ($isAdmin && $_SESSION['user_email'] == $editEmail): // Lien admin seulement si l'admin est sur son propre profil ?>
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

					<?php if ($_SESSION['user_email'] === $editEmail): ?>
						<div class="input-container field-container">
							<label for="password">Nouveau mot de passe</label>
							<div class="input-wrapper">
								<input type="password" id="password" name="password" placeholder="Laisser vide pour ne pas changer" readonly>
								<button type="button" class="edit-btn" data-field="password">Modifier</button>
								<button type="button" class="validate-btn hidden" data-field="password">Valider</button>
								<button type="button" class="cancel-btn hidden" data-field="password">Annuler</button>
							</div>
							<small>Entrez un nouveau mot de passe uniquement si vous souhaitez le changer.</small>
						</div>
					<?php else: ?>
						<div class="input-container field-container">
							<label for="birthdate">Date de naissance</label>
							<div class="input-wrapper">
								<?php
									// Gestion de la date - S'assurer qu'elle est valide avant de formater
									$birthdateValue = '';
									if (!empty($user['birthdate'])) {
										try {
											$dateObj = new DateTime($user['birthdate']);
											$birthdateValue = $dateObj->format('Y-m-d'); // Format attendu par input type="date"
										} catch (Exception $e) {
											$birthdateValue = '';
										}
									}
								?>
								<input type="date" id="birthdate" name="birthdate" value="<?= $birthdateValue ?>" readonly>
								<button type="button" class="edit-btn" data-field="birthdate">Modifier</button>
								<button type="button" class="validate-btn hidden" data-field="birthdate">Valider</button>
								<button type="button" class="cancel-btn hidden" data-field="birthdate">Annuler</button>
							</div>
						</div>
					<?php endif; ?>
				</div>

				<?php if ($_SESSION['user_email'] === $editEmail): ?>
					<div class="form-group single-group">
						<div class="input-container field-container">
							<label for="birthdate">Date de naissance</label>
							<div class="input-wrapper">
								<?php
									// **Code dupliqué pour la date de naissance**
									// Gestion de la date - S'assurer qu'elle est valide avant de formater
									$birthdateValue = ''; // Re-initialiser ou utiliser la valeur calculée plus haut si préférer
									if (!empty($user['birthdate'])) {
										try {
											$dateObj = new DateTime($user['birthdate']);
											$birthdateValue = $dateObj->format('Y-m-d'); // Format attendu par input type="date"
										} catch (Exception $e) {
											$birthdateValue = '';
										}
									}
								?>
								<input type="date" id="birthdate" name="birthdate" value="<?= $birthdateValue ?>" readonly>
								<button type="button" class="edit-btn" data-field="birthdate">Modifier</button>
								<button type="button" class="validate-btn hidden" data-field="birthdate">Valider</button>
								<button type="button" class="cancel-btn hidden" data-field="birthdate">Annuler</button>
							</div>
						</div>
					</div>
				<?php endif; ?>

                <div class="main-buttons">
                    <button type="submit" id="save-changes-btn" class="button1 hidden">Sauvegarder les modifications</button>
                    <a href="logout.php" class="button2">Déconnexion</a>
                </div>

            </form>
        </div>
    </main>

    <footer><?php include('footer.php'); ?></footer>

<script>
    // Indiquer au JS si c'est un admin qui édite un autre utilisateur
    const isAdminEditingAnotherUser = <?= json_encode($isAdmin && isset($_SESSION['user_email']) && isset($editEmail) && $_SESSION['user_email'] !== $editEmail) ?>;
    const adminUpdateDelay = 5000; // Délai en millisecondes (5 secondes)
</script>
<script src="../Javascript/profil.js" defer></script>
</body>
</html>
