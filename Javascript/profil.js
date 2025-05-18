document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons
    var editButtons = document.querySelectorAll('.edit-btn');
    var validateButtons = document.querySelectorAll('.validate-btn');
    var cancelButtons = document.querySelectorAll('.cancel-btn');
    var form = document.getElementById('profile-form');
    
    // Stocker les valeurs originales
    var originalValues = {};
    
    // Sauvegarder les valeurs initiales
    document.querySelectorAll('input[id]').forEach(function(input) {
        originalValues[input.id] = input.value;
    });
    
    // Cacher les boutons valider/annuler au départ
    validateButtons.forEach(function(btn) {
        btn.classList.add('hidden');
    });
    
    cancelButtons.forEach(function(btn) {
        btn.classList.add('hidden');
    });
    
    // Fonction pour activer l'édition d'un champ
    function activerEdition(fieldName) {
        // Trouver les éléments concernés
        var input = document.getElementById(fieldName);
        var editBtn = document.querySelector('.edit-btn[data-field="' + fieldName + '"]');
        var validateBtn = document.querySelector('.validate-btn[data-field="' + fieldName + '"]');
        var cancelBtn = document.querySelector('.cancel-btn[data-field="' + fieldName + '"]');
        
        // Rendre le champ modifiable
        input.readOnly = false;
        input.focus();
        
        // Cacher/montrer les bons boutons
        editBtn.classList.add('hidden');
        validateBtn.classList.remove('hidden');
        cancelBtn.classList.remove('hidden');
        
        // Désactiver les autres boutons d'édition
        editButtons.forEach(function(btn) {
            if (btn !== editBtn) {
                btn.disabled = true;
            }
        });
    }
    
    // Fonction pour valider une modification
    function validerModification(fieldName) {
        // Trouver les éléments concernés
        var input = document.getElementById(fieldName);
        var editBtn = document.querySelector('.edit-btn[data-field="' + fieldName + '"]');
        var validateBtn = document.querySelector('.validate-btn[data-field="' + fieldName + '"]');
        var cancelBtn = document.querySelector('.cancel-btn[data-field="' + fieldName + '"]');
        
        // Vérifier si la valeur a changé
        if (input.value !== originalValues[fieldName]) {
            // Récupérer l'email de l'utilisateur
            var userEmail = document.getElementById('user-email-to-edit').value;
            
            // Désactiver les boutons pendant l'envoi
            validateBtn.disabled = true;
            cancelBtn.disabled = true;
            
            // Ajouter un message de chargement
            var loadingMessage = document.createElement('span');
            loadingMessage.textContent = " Enregistrement...";
            loadingMessage.style.color = "#FFCF30";
            validateBtn.parentNode.appendChild(loadingMessage);
            
            // Préparer les données à envoyer
            var donnees = {
                field: fieldName,
                value: input.value,
                email: userEmail
            };
            
            // Envoyer la requête AJAX
            fetch('../ajax/update_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(donnees)
            })
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                // Enlever le message de chargement
                if (loadingMessage.parentNode) {
                    loadingMessage.parentNode.removeChild(loadingMessage);
                }
                
                // Si la mise à jour a réussi
                if (data.success) {
                    // Mettre à jour la valeur originale
                    originalValues[fieldName] = input.value;
                    // Afficher un message de succès
                    alert("Modification enregistrée avec succès!");
                } else {
                    // Afficher l'erreur et restaurer la valeur originale
                    alert("Erreur: " + (data.message || "La modification a échoué"));
                    input.value = originalValues[fieldName];
                }
                
                // Réactiver les boutons
                validateBtn.disabled = false;
                cancelBtn.disabled = false;
            })
            .catch(function(error) {
                // Gérer les erreurs
                console.error("Erreur: ", error);
                alert("Erreur de communication avec le serveur");
                
                // Restaurer la valeur originale
                input.value = originalValues[fieldName];
                
                // Enlever le message de chargement
                if (loadingMessage.parentNode) {
                    loadingMessage.parentNode.removeChild(loadingMessage);
                }
                
                // Réactiver les boutons
                validateBtn.disabled = false;
                cancelBtn.disabled = false;
            });
        }
        
        // Rendre le champ non modifiable
        input.readOnly = true;
        
        // Cacher/montrer les bons boutons
        editBtn.classList.remove('hidden');
        validateBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        
        // Réactiver tous les boutons d'édition
        editButtons.forEach(function(btn) {
            btn.disabled = false;
        });
    }
    
    // Fonction pour annuler une modification
    function annulerModification(fieldName) {
        // Trouver les éléments concernés
        var input = document.getElementById(fieldName);
        var editBtn = document.querySelector('.edit-btn[data-field="' + fieldName + '"]');
        var validateBtn = document.querySelector('.validate-btn[data-field="' + fieldName + '"]');
        var cancelBtn = document.querySelector('.cancel-btn[data-field="' + fieldName + '"]');
        
        // Restaurer la valeur originale
        input.value = originalValues[fieldName];
        input.readOnly = true;
        
        // Cacher/montrer les bons boutons
        editBtn.classList.remove('hidden');
        validateBtn.classList.add('hidden');
        cancelBtn.classList.add('hidden');
        
        // Réactiver tous les boutons d'édition
        editButtons.forEach(function(btn) {
            btn.disabled = false;
        });
    }
    
    // Ajouter les écouteurs d'événements aux boutons
    editButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var fieldName = this.getAttribute('data-field');
            activerEdition(fieldName);
        });
    });
    
    validateButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var fieldName = this.getAttribute('data-field');
            validerModification(fieldName);
        });
    });
    
    cancelButtons.forEach(function(button) {
        button.addEventListener('click', function() {
            var fieldName = this.getAttribute('data-field');
            annulerModification(fieldName);
        });
    });
    
    // Empêcher la soumission normale du formulaire
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        alert("Toutes les modifications sont déjà enregistrées!");
    });
});
