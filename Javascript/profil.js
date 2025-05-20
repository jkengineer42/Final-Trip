/**
 * Description : Gère l'édition des informations de profil utilisateur sur le site. Permet à l'utilisateur de modifier individuellement chaque champ de son profil  sans avoir à soumettre l'ensemble du formulaire.
 */

// Attendre que le document HTML soit entièrement chargé avant d'exécuter le JavaScript
// Cela garantit que tous les éléments DOM sont disponibles pour manipulation
document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons présents dans le formulaire
    // Ces sélecteurs récupèrent tous les éléments correspondants de la page
    var editButtons = document.querySelectorAll('.edit-btn');       // Boutons pour passer en mode édition
    var validateButtons = document.querySelectorAll('.validate-btn'); // Boutons pour valider les modifications
    var cancelButtons = document.querySelectorAll('.cancel-btn');    // Boutons pour annuler les modifications
    var form = document.getElementById('profile-form');              // Le formulaire principal du profil
    
    // Créer un objet vide pour stocker les valeurs originales des champs
    // Cet objet servira à restaurer les valeurs si l'utilisateur annule ses modifications
    var originalValues = {};
    
    // Parcourir tous les champs de saisie qui ont un ID et sauvegarder leurs valeurs initiales
    // C'est comme prendre une "photo" de l'état initial du formulaire
    document.querySelectorAll('input[id]').forEach(function(input) {
        originalValues[input.id] = input.value;
    });
    
    // Au chargement de la page, cacher les boutons de validation et d'annulation
    // L'utilisateur ne verra initialement que les boutons d'édition
    validateButtons.forEach(function(btn) {
        btn.classList.add('hidden');
    });
    
    cancelButtons.forEach(function(btn) {
        btn.classList.add('hidden');
    });
    
    /**
     * Active le mode édition pour un champ spécifique
     * Cette fonction est appelée quand l'utilisateur clique sur un bouton d'édition
     * @param {string} fieldName - L'ID du champ à éditer
     */
    function activerEdition(fieldName) {
        // Récupérer tous les éléments liés au champ à éditer
        var input = document.getElementById(fieldName);  // Le champ de saisie lui-même
        var editBtn = document.querySelector('.edit-btn[data-field="' + fieldName + '"]');  // Le bouton d'édition
        var validateBtn = document.querySelector('.validate-btn[data-field="' + fieldName + '"]');  // Le bouton de validation
        var cancelBtn = document.querySelector('.cancel-btn[data-field="' + fieldName + '"]');  // Le bouton d'annulation
        
        // Rendre le champ modifiable et y placer le curseur automatiquement
        // Par défaut, les champs sont en lecture seule pour éviter des modifications accidentelles
        input.readOnly = false;
        input.focus();
        
        // Modifier l'interface utilisateur pour afficher les boutons appropriés
        // On cache le bouton d'édition et on affiche les boutons de validation et d'annulation
        editBtn.classList.add('hidden');
        validateBtn.classList.remove('hidden');
        cancelBtn.classList.remove('hidden');
        
        // Désactiver les autres boutons d'édition pour éviter d'éditer plusieurs champs simultanément
        // Cela prévient les conflits potentiels lors de l'édition
        editButtons.forEach(function(btn) {
            if (btn !== editBtn) {
                btn.disabled = true;
            }
        });
    }
    
    /**
     * Valide et enregistre la modification d'un champ
     * Cette fonction est appelée quand l'utilisateur clique sur un bouton de validation
     * @param {string} fieldName - L'ID du champ modifié
     */
    function validerModification(fieldName) {
        // Récupérer tous les éléments liés au champ
        var input = document.getElementById(fieldName);
        var editBtn = document.querySelector('.edit-btn[data-field="' + fieldName + '"]');
        var validateBtn = document.querySelector('.validate-btn[data-field="' + fieldName + '"]');
        var cancelBtn = document.querySelector('.cancel-btn[data-field="' + fieldName + '"]');
        
        // Vérifier si la valeur a réellement été modifiée
        // Si la valeur n'a pas changé, pas besoin d'envoyer une requête au serveur
        if (input.value !== originalValues[fieldName]) {
            // Récupérer l'email de l'utilisateur pour l'identifier dans la base de données
            // Cet email est probablement stocké dans un champ caché du formulaire
            var userEmail = document.getElementById('user-email-to-edit').value;
            
            // Désactiver les boutons pendant l'envoi des données
            // Cela empêche l'utilisateur de cliquer plusieurs fois et d'envoyer des requêtes multiples
            validateBtn.disabled = true;
            cancelBtn.disabled = true;
            
            // Créer et afficher un message visuel de chargement
            // Cela informe l'utilisateur que sa demande est en cours de traitement
            var loadingMessage = document.createElement('span');
            loadingMessage.textContent = " Enregistrement...";
            loadingMessage.style.color = "#FFCF30";
            validateBtn.parentNode.appendChild(loadingMessage);
            
            // Préparer les données à envoyer au serveur
            // On envoie le nom du champ, sa nouvelle valeur et l'email de l'utilisateur
            var donnees = {
                field: fieldName,
                value: input.value,
                email: userEmail
            };
            
            // Envoyer la requête AJAX au serveur
            // AJAX permet d'envoyer et recevoir des données sans recharger toute la page
            fetch('../ajax/update_user.php', {
                method: 'POST',  // Méthode HTTP pour l'envoi des données
                headers: {
                    'Content-Type': 'application/json'  // Format des données envoyées
                },
                body: JSON.stringify(donnees)  // Conversion des données en format JSON
            })
            .then(function(response) {
                // Convertir la réponse du serveur de JSON en objet JavaScript
                return response.json();
            })
            .then(function(data) {
                // Supprimer le message de chargement une fois la réponse reçue
                if (loadingMessage.parentNode) {
                    loadingMessage.parentNode.removeChild(loadingMessage);
                }
                
                // Traiter la réponse du serveur
                if (data.success) {
                    // Si la mise à jour a réussi :
                    // 1. Mettre à jour la valeur originale dans notre objet de suivi
                    originalValues[fieldName] = input.value;
                    // 2. Informer l'utilisateur du succès
                    alert("Modification enregistrée avec succès!");
                } else {
                    // Si la mise à jour a échoué :
                    // 1. Afficher le message d'erreur
                    alert("Erreur: " + (data.message || "La modification a échoué"));
                    // 2. Restaurer la valeur originale dans le champ
                    input.value = originalValues[fieldName];
                }
                
                // Réactiver les boutons une fois le traitement terminé
                validateBtn.disabled = false;
                cancelBtn.disabled = false;
            })
            .catch(function(error) {
                // Gérer les erreurs de communication avec le serveur
                // Cette partie s'exécute si la requête AJAX échoue complètement
                console.error("Erreur: ", error);
                alert("Erreur de communication avec le serveur");
                
                // Supprimer le message de chargement
                if (loadingMessage.parentNode) {
                    loadingMessage.parentNode.removeChild(loadingMessage);
                }
                
                // Réactiver les boutons
                validateBtn.disabled = false;
                cancelBtn.disabled = false;
            });
        }
        
        // Remettre le champ en mode lecture seule
        // Qu'il y ait eu une modification ou non, on sort du mode édition
        input.readOnly = true;
        
        // Restaurer l'interface utilisateur à son état initial
        // On réaffiche le bouton d'édition et on cache les boutons de validation et d'annulation
        editBtn.classList.remove('hidden');
        validateBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        
        // Réactiver tous les boutons d'édition qui avaient été désactivés
        // L'utilisateur peut maintenant éditer d'autres champs
        editButtons.forEach(function(btn) {
            btn.disabled = false;
        });
    }
    
    /**
     * Annule la modification d'un champ et restaure sa valeur d'origine
     * Cette fonction est appelée quand l'utilisateur clique sur un bouton d'annulation
     * @param {string} fieldName - L'ID du champ à restaurer
     */
    function annulerModification(fieldName) {
        // Récupérer tous les éléments liés au champ
        var input = document.getElementById(fieldName);
        var editBtn = document.querySelector('.edit-btn[data-field="' + fieldName + '"]');
        var validateBtn = document.querySelector('.validate-btn[data-field="' + fieldName + '"]');
        var cancelBtn = document.querySelector('.cancel-btn[data-field="' + fieldName + '"]');
        
        // Restaurer la valeur originale du champ et le remettre en lecture seule
        // On utilise la valeur sauvegardée dans notre objet originalValues
        input.value = originalValues[fieldName];
        input.readOnly = true;
        
        // Restaurer l'interface utilisateur à son état initial
        // On réaffiche le bouton d'édition et on cache les boutons de validation et d'annulation
        editBtn.classList.remove('hidden');
        validateBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        
        // Réactiver tous les boutons d'édition
        // L'utilisateur peut maintenant éditer d'autres champs
        editButtons.forEach(function(btn) {
            btn.disabled = false;
        });
    }
    
    // === MISE EN PLACE DES ÉCOUTEURS D'ÉVÉNEMENTS ===
    
    // Ajouter des écouteurs d'événements sur tous les boutons d'édition
    // Quand on clique sur un bouton d'édition, on active l'édition du champ correspondant
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Récupérer l'ID du champ à partir de l'attribut data-field du bouton
            var fieldName = this.getAttribute('data-field');
            // Appeler la fonction d'activation de l'édition
            activerEdition(fieldName);
        });
    });
    
    // Ajouter des écouteurs d'événements sur tous les boutons de validation
    // Quand on clique sur un bouton de validation, on valide la modification du champ
    validateButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Récupérer l'ID du champ à partir de l'attribut data-field du bouton
            var fieldName = this.getAttribute('data-field');
            // Appeler la fonction de validation
            validerModification(fieldName);
        });
    });
    
    // Ajouter des écouteurs d'événements sur tous les boutons d'annulation
    // Quand on clique sur un bouton d'annulation, on annule la modification du champ
    cancelButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            // Récupérer l'ID du champ à partir de l'attribut data-field du bouton
            var fieldName = this.getAttribute('data-field');
            // Appeler la fonction d'annulation
            annulerModification(fieldName);
        });
    });
    
    // Empêcher la soumission normale du formulaire qui rechargerait la page
    // Dans ce système, les modifications sont envoyées individuellement via AJAX
    form.addEventListener('submit', function(event) {
        // Bloquer l'action par défaut (soumission du formulaire)
        event.preventDefault();
        // Informer l'utilisateur que les modifications sont déjà enregistrées
        alert("Toutes les modifications sont déjà enregistrées!");
    });
});
