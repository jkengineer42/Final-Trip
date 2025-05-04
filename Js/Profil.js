document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('profile-form');
    const saveChangesBtn = document.getElementById('save-changes-btn');
    const fieldContainers = form.querySelectorAll('.field-container');
    let originalValues = {}; // Pour stocker les valeurs initiales
    let hasValidatedChanges = false; // Indicateur pour savoir si au moins un champ a été validé
    let saveTimer = null; // Pour gérer le timer de sauvegarde admin

    // --- Initialisation ---
    fieldContainers.forEach(container => {
        const input = container.querySelector('input:not([disabled])');
        if (!input) return;

        const fieldName = input.id;
        // S'assurer que l'id existe avant de l'utiliser comme clé
        if (!fieldName) {
            console.warn("Champ sans ID trouvé dans un field-container:", container);
            return;
        }
        originalValues[fieldName] = input.value; // Stocker la valeur initiale

        const editBtn = container.querySelector(`.edit-btn[data-field="${fieldName}"]`);
        const validateBtn = container.querySelector(`.validate-btn[data-field="${fieldName}"]`);
        const cancelBtn = container.querySelector(`.cancel-btn[data-field="${fieldName}"]`);

        if(validateBtn) validateBtn.classList.add('hidden');
        if(cancelBtn) cancelBtn.classList.add('hidden');

        if (editBtn) {
            editBtn.addEventListener('click', () => handleEdit(fieldName));
        }
        if (validateBtn) {
            validateBtn.addEventListener('click', () => handleValidate(fieldName));
        }
        if (cancelBtn) {
            cancelBtn.addEventListener('click', () => handleCancel(fieldName));
        }
    });

    // --- Gestionnaires d'événements ---

    function handleEdit(fieldName) {
        // ... (Code existant de handleEdit - pas de changement nécessaire ici)
        const container = document.getElementById(fieldName)?.closest('.field-container');
         if (!container) return;
         const input = container.querySelector(`#${fieldName}`);
         const editBtn = container.querySelector(`.edit-btn[data-field="${fieldName}"]`);
         const validateBtn = container.querySelector(`.validate-btn[data-field="${fieldName}"]`);
         const cancelBtn = container.querySelector(`.cancel-btn[data-field="${fieldName}"]`);

         // Désactiver les autres boutons "Modifier"
         disableOtherEditButtons(true, fieldName);

         // Rendre le champ modifiable
         input.readOnly = false;
         input.focus();
         // Gérer le cas où select() n'est pas dispo (ex: type date)
         if (typeof input.select === 'function') {
            input.select();
         }


         // Cacher "Modifier", Afficher "Valider" et "Annuler"
         editBtn.classList.add('hidden');
         validateBtn.classList.remove('hidden');
         cancelBtn.classList.remove('hidden');
    }

    function handleValidate(fieldName) {
        // ... (Début du code existant de handleValidate) ...
        const container = document.getElementById(fieldName)?.closest('.field-container');
        if (!container) return;
        const input = container.querySelector(`#${fieldName}`);
        const editBtn = container.querySelector(`.edit-btn[data-field="${fieldName}"]`);
        const validateBtn = container.querySelector(`.validate-btn[data-field="${fieldName}"]`);
        const cancelBtn = container.querySelector(`.cancel-btn[data-field="${fieldName}"]`);

        // --- Validation Côté Client (Optionnelle) ---
        if ((fieldName === 'nom' || fieldName === 'prenom') && input.value.trim() === '') {
            alert(`Le champ ${input.labels[0]?.textContent || fieldName} ne peut pas être vide.`);
            return;
        }
        if (fieldName === 'birthdate' && input.value && !isValidDate(input.value)) {
             alert("Le format de la date de naissance n'est pas valide (AAAA-MM-JJ), ou la date est invalide.");
             return;
        }
        // -------------------------------------------

        input.readOnly = true; // Rendre le champ non modifiable

        // Vérifier si la valeur a réellement changé
        // !! Important: Utiliser la clé correcte pour originalValues
        if (input.value !== originalValues[fieldName]) {

             console.log(`Changement détecté pour ${fieldName}: "${originalValues[fieldName]}" -> "${input.value}"`);
             hasValidatedChanges = true; // Marquer qu'il y a des changements
             updateSaveChangesButtonVisibility(); // Mettre à jour la visibilité du bouton principal
         } else {
             console.log(`Pas de changement réel pour ${fieldName}.`);
         }


        // Cacher "Valider" et "Annuler", Afficher "Modifier"
        validateBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        editBtn.classList.remove('hidden');

        // Réactiver les autres boutons "Modifier"
        disableOtherEditButtons(false);
    }

    function handleCancel(fieldName) {
        // ... (Code existant de handleCancel - pas de changement nécessaire ici) ...
        const container = document.getElementById(fieldName)?.closest('.field-container');
        if (!container) return;
        const input = container.querySelector(`#${fieldName}`);
        const editBtn = container.querySelector(`.edit-btn[data-field="${fieldName}"]`);
        const validateBtn = container.querySelector(`.validate-btn[data-field="${fieldName}"]`);
        const cancelBtn = container.querySelector(`.cancel-btn[data-field="${fieldName}"]`);

        // Restaurer la valeur originale stockée au chargement
        input.value = originalValues[fieldName];
        input.readOnly = true;

        validateBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        editBtn.classList.remove('hidden');

        disableOtherEditButtons(false);

    }


    function updateSaveChangesButtonVisibility() {
        // Affiche le bouton s'il y a eu au moins une validation de changement
        if (hasValidatedChanges) {
            saveChangesBtn.classList.remove('hidden');
        } else {
            // Optionnel: Cacher si plus aucun changement n'est en attente après des annulations multiples.
             // saveChangesBtn.classList.add('hidden');
        }
    }

    function disableOtherEditButtons(disable, currentField = null) {
        // ... (Code existant - pas de changement) ...
         fieldContainers.forEach(container => {
            const editBtn = container.querySelector('.edit-btn');
            if (!editBtn) return;
            const fieldName = editBtn.getAttribute('data-field');

             // S'assurer que fieldName est valide avant la comparaison
             if (fieldName && fieldName !== currentField) {
                 editBtn.disabled = disable;
                 editBtn.style.opacity = disable ? '0.5' : '1';
                 editBtn.style.cursor = disable ? 'not-allowed' : 'pointer';
             }
        });
    }

    function isValidDate(dateString) {
        // ... (Code existant - pas de changement) ...
         const regEx = /^\d{4}-\d{2}-\d{2}$/;
         if (!dateString.match(regEx)) return false; // Mauvais format
         const d = new Date(dateString);
         const dNum = d.getTime();
         if (!dNum && dNum !== 0) return false; // Date invalide (ex: 2023-02-31)
         // Vérifie aussi que la date créée correspond bien à la string
         return d.toISOString().slice(0, 10) === dateString;
    }

    // --- Logique de Soumission du Formulaire ---

    // Intercepter la soumission du formulaire qui serait déclenchée par le bouton 'submit'
    form.addEventListener('submit', (event) => {
        // Empêcher la soumission par défaut IMMÉDIATEMENT
        event.preventDefault();

        // Vérifier si c'est un admin qui modifie un autre user
        // 'isAdminEditingAnotherUser' et 'adminUpdateDelay' sont définis par le PHP via le <script> ajouté
        if (typeof isAdminEditingAnotherUser !== 'undefined' && isAdminEditingAnotherUser === true) {
            console.log(`Admin modifie un autre user. Lancement du timer de ${adminUpdateDelay / 1000}s.`);

            // Afficher un message et désactiver le bouton pendant le délai
            saveChangesBtn.disabled = true;
            saveChangesBtn.textContent = `Sauvegarde dans ${adminUpdateDelay / 1000}...`; // Feedback visuel

            // Effacer un timer précédent s'il existe (au cas où le bouton est cliqué plusieurs fois rapidement)
            clearTimeout(saveTimer);

            // Lancer le timer
            saveTimer = setTimeout(() => {
                console.log("Timer terminé. Soumission du formulaire.");
                // Rétablir le texte/état du bouton n'est pas nécessaire car la page va recharger
                form.submit(); // Soumettre le formulaire après le délai
            }, adminUpdateDelay); // Utiliser la variable de délai

        } else {
            // Si ce n'est pas un admin modifiant un autre user, soumettre immédiatement
            console.log("Utilisateur normal ou admin sur son profil. Soumission immédiate.");
            form.submit();
        }
    });


    // --- Initialisation finale ---
    updateSaveChangesButtonVisibility(); // Cacher le bouton au début si pas de changement

    // Optionnel : Empêcher Entrée de soumettre (le addEventListener 'submit' ci-dessus gère déjà le délai)
    form.addEventListener('keydown', function(event) {
        if (event.key === 'Enter' && event.target.tagName === 'INPUT' && !event.target.readOnly) {
             event.preventDefault(); // Empêche la soumission par Entrée
             // Simuler un clic sur le bouton Valider correspondant s'il est visible
             const fieldName = event.target.id;
             const validateBtn = document.querySelector(`.validate-btn[data-field="${fieldName}"]:not(.hidden)`);
             if (validateBtn) {
                 validateBtn.click();
             }
        }
    });

}); // Fin de DOMContentLoaded