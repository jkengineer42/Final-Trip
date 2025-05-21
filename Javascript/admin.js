// Attendre que la page soit complètement chargée avant d'exécuter le code
document.addEventListener('DOMContentLoaded', function() {
    
    // Sélectionner tous les boutons d'action de la page
    var promoteButtons = document.querySelectorAll('button[name="promote"]'); // Boutons pour promouvoir un utilisateur
    var deleteButtons = document.querySelectorAll('button[name="delete"]');   // Boutons pour bloquer un utilisateur
    var unblockButtons = document.querySelectorAll('button[name="unblock"]'); // Boutons pour débloquer un utilisateur
    
    // Fonction qui gère la promotion d'un utilisateur au statut d'administrateur
    function promouvoirUtilisateur(button) {
        // Trouver le formulaire qui contient le bouton cliqué
        var form = button.closest('form');
        if (!form) {
            // Si aucun formulaire n'est trouvé, afficher une erreur et arrêter
            alert("Erreur: formulaire non trouvé");
            return;
        }
        
        // Chercher le champ caché qui contient l'email de l'utilisateur
        var emailInput = form.querySelector('input[name="email"]');
        if (!emailInput) {
            // Si le champ d'email n'est pas trouvé, afficher une erreur et arrêter
            alert("Erreur: email non trouvé");
            return;
        }
        
        // Récupérer la valeur de l'email
        var email = emailInput.value;
        
        // Désactiver le bouton pour éviter les clics multiples
        button.disabled = true;
        // Ajouter une classe CSS pour indiquer visuellement que le traitement est en cours
        button.classList.add('processing');
        
        // Créer un élément texte pour montrer que l'action est en cours
        var loadingText = document.createElement('span');
        loadingText.textContent = " Promotion en cours...";
        loadingText.style.color = "#FFFFFF"; // Texte en blanc
        // Ajouter ce texte au bouton
        button.appendChild(loadingText);
        
        // IMPORTANT: Désactiver la soumission du formulaire pour empêcher le rechargement de la page
        form.onsubmit = function(e) {
            e.preventDefault();
            return false;
        };
        
        // Envoyer une requête AJAX asynchrone au serveur pour promouvoir l'utilisateur
        fetch('../ajax/admin_action.php', {
            method: 'POST', // Méthode HTTP POST
            headers: {
                'Content-Type': 'application/json' // Format des données envoyées: JSON
            },
            body: JSON.stringify({ // Convertir l'objet JavaScript en chaîne JSON
                action: 'promote', // Action à effectuer
                email: email       // Email de l'utilisateur à promouvoir
            })
        })
        // Analyser la réponse JSON du serveur
        .then(function(response) {
            return response.json();
        })
        // Traiter les données de la réponse
        .then(function(data) {
            // Supprimer le texte de chargement du bouton
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Vérifier si l'opération a réussi
            if (data.success) {
                // Afficher un message de succès
                alert("L'utilisateur a été promu administrateur!");
                
                // Mettre à jour l'interface sans recharger la page (conforme Phase 4)
                var row = button.closest('tr'); // Trouver la ligne du tableau
                if (row) {
                    // Chercher la cellule qui affiche le rôle (5e colonne)
                    var roleCell = row.querySelector('td:nth-child(5)');
                    if (roleCell) {
                        // Mettre à jour le texte du rôle
                        roleCell.textContent = 'Oui';
                    }
                }
                
                // Cacher le bouton de promotion car l'utilisateur est maintenant admin
                button.closest('form').style.display = 'none';
            } else {
                // Si l'opération a échoué, afficher un message d'erreur
                alert("Erreur: " + (data.message || "La promotion a échoué"));
                
                // Réactiver le bouton pour permettre une nouvelle tentative
                button.disabled = false;
                button.classList.remove('processing');
            }
        })
        // Capturer et gérer les erreurs de réseau ou autres
        .catch(function(error) {
            // Afficher l'erreur dans la console 
            console.error("Erreur: ", error);
            // Afficher un message pour l'utilisateur
            alert("Erreur de communication avec le serveur");
            
            // Supprimer le texte de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Réactiver le bouton
            button.disabled = false;
            button.classList.remove('processing');
        });
    }
    
    // Fonction qui gère le blocage d'un utilisateur
    function bloquerUtilisateur(button) {
        // Demander confirmation avant de procéder
        if (!confirm("Êtes-vous sûr de vouloir bloquer cet utilisateur?")) {
            return; // Si l'utilisateur annule, arrêter la fonction
        }
        
        // Trouver le formulaire parent du bouton
        var form = button.closest('form');
        if (!form) {
            alert("Erreur: formulaire non trouvé");
            return;
        }
        
        // Récupérer l'email de l'utilisateur à bloquer
        var emailInput = form.querySelector('input[name="email"]');
        if (!emailInput) {
            alert("Erreur: email non trouvé");
            return;
        }
        
        var email = emailInput.value;
        
        // Désactiver le bouton et ajouter une indication visuelle
        button.disabled = true;
        button.classList.add('processing');
        
        // Ajouter un texte pour indiquer que le blocage est en cours
        var loadingText = document.createElement('span');
        loadingText.textContent = " Blocage en cours...";
        loadingText.style.color = "#FFFFFF";
        button.appendChild(loadingText);
        
        // IMPORTANT: Désactiver la soumission du formulaire pour empêcher le rechargement de la page
        form.onsubmit = function(e) {
            e.preventDefault();
            return false;
        };
        
        // Envoyer la requête au serveur pour bloquer l'utilisateur
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
            // Supprimer l'indicateur de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Traiter la réponse du serveur
            if (data.success) {
                // Informer l'utilisateur du succès de l'opération
                alert("L'utilisateur a été bloqué!");
                
                // Mettre à jour l'interface utilisateur
                var row = button.closest('tr'); // Trouver la ligne du tableau
                if (row) {
                    // Mettre à jour la cellule de statut (6e colonne)
                    var statusCell = row.querySelector('td:nth-child(6)');
                    if (statusCell) {
                        // Afficher "Bloqué" en rouge
                        statusCell.innerHTML = '<span style="color:red;">Bloqué</span>';
                    }
                }
                
                // Cacher le formulaire avec le bouton de blocage
                var blockForm = button.closest('form');
                if (blockForm) {
                    blockForm.style.display = 'none';
                }
                
                // Chercher la cellule qui contient les actions
                var actionsCell = row.querySelector('td.icons');
                if (actionsCell) {
                    // Chercher si un formulaire de déblocage existe déjà
                    // Trouver tous les formulaires et rechercher celui qui contient le bouton approprié
                    var forms = actionsCell.querySelectorAll('form');
                    var unblockForm = null;
                    forms.forEach(function(form) {
                        if (form.querySelector('button[name="unblock"]')) {
                            unblockForm = form;
                        }
                    });
                    
                    if (unblockForm) {
                        // Si le formulaire existe, l'afficher
                        unblockForm.style.display = 'inline';
                    } else {
                        // Si le formulaire n'existe pas, le créer dynamiquement
                        var newForm = document.createElement('form');
                        newForm.style.display = 'inline';
                        
                        // Créer un champ caché pour l'email
                        var emailInput = document.createElement('input');
                        emailInput.type = 'hidden';
                        emailInput.name = 'email';
                        emailInput.value = email;
                        newForm.appendChild(emailInput);
                        
                        // Créer le bouton de déblocage
                        var unblockButton = document.createElement('button');
                        unblockButton.name = 'unblock';
                        unblockButton.type = 'submit';
                        unblockButton.className = 'unblock-button';
                        unblockButton.title = "Débloquer l'utilisateur";
                        // IMPORTANT: S'assurer que le nouveau bouton n'est pas désactivé
                        unblockButton.disabled = false;
                        
                        // Créer l'image pour le bouton
                        var img = document.createElement('img');
                        img.src = '../assets/icon/unlock.png';
                        img.alt = 'Débloquer';
                        img.className = 'icon unblock-icon';
                        unblockButton.appendChild(img);
                        
                        // Ajouter un événement de clic au bouton
                        unblockButton.addEventListener('click', function(event) {
                            event.preventDefault();
                            debloquerUtilisateur(this);
                        });
                        
                        // Ajouter le bouton au formulaire
                        newForm.appendChild(unblockButton);
                        
                        // Ajouter le formulaire à la cellule d'actions
                        actionsCell.appendChild(newForm);
                    }
                }
            } else {
                // Si l'opération a échoué, afficher l'erreur
                alert("Erreur: " + (data.message || "Le blocage a échoué"));
                
                // Réactiver le bouton
                button.disabled = false;
                button.classList.remove('processing');
            }
        })
        .catch(function(error) {
            // Gérer les erreurs de communication
            console.error("Erreur: ", error);
            alert("Erreur de communication avec le serveur");
            
            // Supprimer l'indicateur de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Réactiver le bouton
            button.disabled = false;
            button.classList.remove('processing');
        });
    }
    
    // Fonction qui gère le déblocage d'un utilisateur
    function debloquerUtilisateur(button) {
        // Demander confirmation avant de procéder
        if (!confirm("Êtes-vous sûr de vouloir débloquer cet utilisateur?")) {
            return; // Si l'utilisateur annule, arrêter la fonction
        }
        
        // Trouver le formulaire parent
        var form = button.closest('form');
        if (!form) {
            alert("Erreur: formulaire non trouvé");
            return;
        }
        
        // Récupérer l'email de l'utilisateur à débloquer
        var emailInput = form.querySelector('input[name="email"]');
        if (!emailInput) {
            alert("Erreur: email non trouvé");
            return;
        }
        
        var email = emailInput.value;
        
        // Désactiver le bouton et ajouter une indication visuelle
        button.disabled = true;
        button.classList.add('processing');
        
        // Ajouter un texte pour indiquer que le déblocage est en cours
        var loadingText = document.createElement('span');
        loadingText.textContent = " Déblocage en cours...";
        loadingText.style.color = "#FFFFFF";
        button.appendChild(loadingText);
        
        // IMPORTANT: Désactiver la soumission du formulaire pour empêcher le rechargement de la page
        form.onsubmit = function(e) {
            e.preventDefault();
            return false;
        };
        
        // Envoyer la requête au serveur pour débloquer l'utilisateur
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
            // Supprimer l'indicateur de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Traiter la réponse du serveur
            if (data.success) {
                // Informer l'utilisateur du succès de l'opération
                alert("L'utilisateur a été débloqué!");
                
                // Mettre à jour l'interface utilisateur
                var row = button.closest('tr'); // Trouver la ligne du tableau
                if (row) {
                    // Mettre à jour la cellule de statut (6e colonne)
                    var statusCell = row.querySelector('td:nth-child(6)');
                    if (statusCell) {
                        // Remettre le statut à "Actif"
                        statusCell.textContent = 'Actif';
                    }
                }
                
                // Cacher le formulaire de déblocage
                var unblockForm = button.closest('form');
                if (unblockForm) {
                    unblockForm.style.display = 'none';
                }
                
                // Chercher la cellule qui contient les actions
                var actionsCell = row.querySelector('td.icons');
                if (actionsCell) {
                    // Chercher si un formulaire de blocage existe déjà
                    var forms = actionsCell.querySelectorAll('form');
                    var blockForm = null;
                    forms.forEach(function(form) {
                        if (form.querySelector('button[name="delete"]')) {
                            blockForm = form;
                        }
                    });
                    
                    if (blockForm) {
                        // Si le formulaire existe, l'afficher
                        blockForm.style.display = 'inline';
                        
                        // IMPORTANT: S'assurer que le bouton de blocage est activé
                        var blockButton = blockForm.querySelector('button[name="delete"]');
                        if (blockButton) {
                            blockButton.disabled = false;
                            blockButton.classList.remove('processing');
                        }
                    } else {
                        // Si le formulaire n'existe pas, le créer dynamiquement
                        var newForm = document.createElement('form');
                        newForm.style.display = 'inline';
                        
                        // Créer un champ caché pour l'email
                        var emailInput = document.createElement('input');
                        emailInput.type = 'hidden';
                        emailInput.name = 'email';
                        emailInput.value = email;
                        newForm.appendChild(emailInput);
                        
                        // Créer le bouton de blocage
                        var blockButton = document.createElement('button');
                        blockButton.name = 'delete';
                        blockButton.type = 'submit';
                        blockButton.className = 'delete-button';
                        blockButton.title = "Bloquer l'utilisateur";
                        // IMPORTANT: S'assurer que le nouveau bouton n'est pas désactivé
                        blockButton.disabled = false;
                        // IMPORTANT: S'assurer qu'aucune classe CSS de désactivation n'est présente
                        blockButton.classList.remove('processing');
                        
                        // Créer l'image pour le bouton
                        var img = document.createElement('img');
                        img.src = '../assets/icon/delete.png';
                        img.alt = 'Bloquer';
                        img.className = 'icon delete-icon';
                        blockButton.appendChild(img);
                        
                        // Ajouter un événement de clic au bouton
                        blockButton.addEventListener('click', function(event) {
                            event.preventDefault();
                            bloquerUtilisateur(this);
                        });
                        
                        // Ajouter le bouton au formulaire
                        newForm.appendChild(blockButton);
                        
                        // Ajouter le formulaire à la cellule d'actions
                        actionsCell.appendChild(newForm);
                    }
                }
            } else {
                // Si l'opération a échoué, afficher l'erreur
                alert("Erreur: " + (data.message || "Le déblocage a échoué"));
                
                // Réactiver le bouton
                button.disabled = false;
                button.classList.remove('processing');
            }
        })
        .catch(function(error) {
            // Gérer les erreurs de communication
            console.error("Erreur: ", error);
            alert("Erreur de communication avec le serveur");
            
            // Supprimer l'indicateur de chargement
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Réactiver le bouton
            button.disabled = false;
            button.classList.remove('processing');
        });
    }
    
    // Ajouter des écouteurs d'événements à tous les boutons "Promouvoir"
    promoteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Empêcher le comportement par défaut du formulaire (rechargement de page)
            event.preventDefault();
            // Appeler la fonction pour promouvoir l'utilisateur
            promouvoirUtilisateur(this);
        });
    });
    
    // Ajouter des écouteurs d'événements à tous les boutons "Bloquer"
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Empêcher le comportement par défaut du bouton/formulaire
            event.preventDefault();
            // Appeler la fonction pour bloquer l'utilisateur
            bloquerUtilisateur(this);
        });
    });
    
    // Ajouter des écouteurs d'événements à tous les boutons "Débloquer"
    unblockButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Empêcher le comportement par défaut du bouton/formulaire
            event.preventDefault();
            // Appeler la fonction pour débloquer l'utilisateur
            debloquerUtilisateur(this);
        });
    });
});
