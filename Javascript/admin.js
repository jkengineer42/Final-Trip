document.addEventListener('DOMContentLoaded', function() {
    // Sélectionner tous les boutons
    var promoteButtons = document.querySelectorAll('button[name="promote"]');
    var deleteButtons = document.querySelectorAll('button[name="delete"]');
    var unblockButtons = document.querySelectorAll('button[name="unblock"]'); 
    
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
                
                // Plutôt que de modifier le DOM, recharger la page
                location.reload();
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
    
    function bloquerUtilisateur(button) {
        // Demander confirmation
        if (!confirm("Êtes-vous sûr de vouloir bloquer cet utilisateur?")) {
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
        loadingText.textContent = " Blocage en cours...";
        loadingText.style.color = "#FFFFFF";
        button.appendChild(loadingText);
        
        // Envoyer la requête au serveur
        fetch('../ajax/admin_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'block',
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
                alert("L'utilisateur a été bloqué!");
                
                // Mettre à jour le statut avec un texte rouge "Bloqué"
                var row = button.closest('tr');
                if (row) {
                    var statusCell = row.querySelector('td:nth-child(6)');
                    if (statusCell) {
                        statusCell.innerHTML = '<span style="color:red;">Bloqué</span>';
                    }
                }
                
                // On cache le bouton de blocage et on cherche le formulaire parent du bouton
                var blockForm = button.closest('form');
                if (blockForm) {
                    blockForm.style.display = 'none';
                }
                
                // On cherche le bouton de déblocage qui pourrait être masqué et on l'affiche
                var actionsCell = row.querySelector('td.icons');
                if (actionsCell) {
                    var unblockForm = actionsCell.querySelector('form:has(button[name="unblock"])');
                    if (unblockForm) {
                        unblockForm.style.display = 'inline';
                    } else {
                        // S'il n'y a pas de bouton de déblocage, recharger la page pour l'obtenir
                        location.reload();
                    }
                }
            } else {
                // Afficher l'erreur
                alert("Erreur: " + (data.message || "Le blocage a échoué"));
                
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

    // Fonction pour débloquer un utilisateur
    function debloquerUtilisateur(button) {
        // Demander confirmation
        if (!confirm("Êtes-vous sûr de vouloir débloquer cet utilisateur?")) {
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
        loadingText.textContent = " Déblocage en cours...";
        loadingText.style.color = "#FFFFFF";
        button.appendChild(loadingText);
        
        // Envoyer la requête au serveur
        fetch('../ajax/admin_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'unblock',
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
                alert("L'utilisateur a été débloqué!");
                
                // Mettre à jour le statut 
                var row = button.closest('tr');
                if (row) {
                    var statusCell = row.querySelector('td:nth-child(6)');
                    if (statusCell) {
                        statusCell.textContent = 'Actif';
                    }
                }
                
                // On cache le bouton de déblocage
                var unblockForm = button.closest('form');
                if (unblockForm) {
                    unblockForm.style.display = 'none';
                }
                
                // On cherche le bouton de blocage qui pourrait être masqué et on l'affiche
                var actionsCell = row.querySelector('td.icons');
                if (actionsCell) {
                    var blockForm = actionsCell.querySelector('form:has(button[name="delete"])');
                    if (blockForm) {
                        blockForm.style.display = 'inline';
                    } else {
                        // S'il n'y a pas de bouton de blocage, recharger la page pour l'obtenir
                        location.reload();
                    }
                }
            } else {
                // Afficher l'erreur
                alert("Erreur: " + (data.message || "Le déblocage a échoué"));
                
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
            bloquerUtilisateur(this);
        });
    });
    
    unblockButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            debloquerUtilisateur(this);
        });
    });
});
