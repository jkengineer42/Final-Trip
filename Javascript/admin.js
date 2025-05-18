document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons
    var promoteButtons = document.querySelectorAll('button[name="promote"]');
    var deleteButtons = document.querySelectorAll('button[name="delete"]');
    
    // Fonction pour promouvoir un utilisateur
    function promouvoirUtilisateur(button) {
        // Trouver le formulaire parent
        var form = button.closest('form');
        if (!form) {
            alert("Erreur: formulaire non trouvé");
            return;
        }
        
        // Trouver l'email de l'utilisateur
        var emailInput = form.querySelector('input[name="email"]');
        if (!emailInput) {
            alert("Erreur: email non trouvé");
            return;
        }
        
        var email = emailInput.value;
        
        // Désactiver le bouton et ajouter un indicateur
        button.disabled = true;
        button.classList.add('processing');
        
        // Ajouter un texte de chargement
        var loadingText = document.createElement('span');
        loadingText.textContent = " Promotion en cours...";
        loadingText.style.color = "#FFFFFF";
        button.appendChild(loadingText);
        
        // Envoyer la requête au serveur
        fetch('../ajax/admin_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'promote',
                email: email
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            // Enlever le texte de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Vérifier si l'action a réussi
            if (data.success) {
                // Afficher un message de succès
                alert("L'utilisateur a été promu administrateur!");
                
                // Mettre à jour la ligne dans le tableau
                var row = button.closest('tr');
                if (row) {
                    // Mettre à jour la cellule "Admin"
                    var adminCell = row.querySelector('td:nth-child(5)');
                    if (adminCell) {
                        adminCell.textContent = 'Oui';
                    }
                    
                    // Supprimer les boutons d'action
                    var actionsCell = row.querySelector('td.icons');
                    if (actionsCell) {
                        actionsCell.innerHTML = '<span style="color:#FFCF30">Administrateur</span>';
                    }
                }
            } else {
                // Afficher l'erreur
                alert("Erreur: " + (data.message || "La promotion a échoué"));
                
                // Réactiver le bouton
                button.disabled = false;
                button.classList.remove('processing');
            }
        })
        .catch(function(error) {
            // Gérer les erreurs
            console.error("Erreur: ", error);
            alert("Erreur de communication avec le serveur");
            
            // Enlever le texte de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Réactiver le bouton
            button.disabled = false;
            button.classList.remove('processing');
        });
    }
    
    // Fonction pour supprimer un utilisateur
    function supprimerUtilisateur(button) {
        // Demander confirmation
        if (!confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur?")) {
            return;
        }
        
        // Trouver le formulaire parent
        var form = button.closest('form');
        if (!form) {
            alert("Erreur: formulaire non trouvé");
            return;
        }
        
        // Trouver l'email de l'utilisateur
        var emailInput = form.querySelector('input[name="email"]');
        if (!emailInput) {
            alert("Erreur: email non trouvé");
            return;
        }
        
        var email = emailInput.value;
        
        // Désactiver le bouton et ajouter un indicateur
        button.disabled = true;
        button.classList.add('processing');
        
        // Ajouter un texte de chargement
        var loadingText = document.createElement('span');
        loadingText.textContent = " Suppression en cours...";
        loadingText.style.color = "#FFFFFF";
        button.appendChild(loadingText);
        
        // Envoyer la requête au serveur
        fetch('../ajax/admin_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'delete',
                email: email
            })
        })
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            // Enlever le texte de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Vérifier si l'action a réussi
            if (data.success) {
                // Afficher un message de succès
                alert("L'utilisateur a été supprimé!");
                
                // Masquer la ligne dans le tableau
                var row = button.closest('tr');
                if (row) {
                    // Animation simple de disparition
                    row.style.opacity = "0";
                    
                    // Cacher la ligne après l'animation
                    setTimeout(function() {
                        row.style.display = "none";
                    }, 500);
                }
            } else {
                // Afficher l'erreur
                alert("Erreur: " + (data.message || "La suppression a échoué"));
                
                // Réactiver le bouton
                button.disabled = false;
                button.classList.remove('processing');
            }
        })
        .catch(function(error) {
            // Gérer les erreurs
            console.error("Erreur: ", error);
            alert("Erreur de communication avec le serveur");
            
            // Enlever le texte de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Réactiver le bouton
            button.disabled = false;
            button.classList.remove('processing');
        });
    }
    
    // Ajouter les écouteurs d'événements aux boutons
    promoteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            promouvoirUtilisateur(this);
        });
    });
    
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            supprimerUtilisateur(this);
        });
    });
});
