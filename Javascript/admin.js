/*
1. PROMOTION D'UTILISATEURS :
2. BLOCAGE D'UTILISATEURS
3. DÉBLOCAGE D'UTILISATEURS :
*/


// Attendre que la page soit complètement chargée avant d'exécuter le code
document.addEventListener('DOMContentLoaded', function() {
    
    // Sélectionner tous les boutons d'action de la page admin
    var promoteButtons = document.querySelectorAll('button[name="promote"]'); // Boutons pour promouvoir un utilisateur en admin
    var deleteButtons = document.querySelectorAll('button[name="delete"]');   // Boutons pour bloquer un utilisateur
    var unblockButtons = document.querySelectorAll('button[name="unblock"]'); // Boutons pour débloquer un utilisateur
    
    // ==FONCTION PROMOTION USER==
    function promouvoirUtilisateur(button) {
        // Trouver le formulaire HTML qui contient le bouton cliqué
        var form = button.closest('form');
        if (!form) {
            // Si aucun formulaire n'est trouvé, afficher une erreur et arrêter l'exécution
            alert("Erreur: formulaire non trouvé");
            return; // Sortir de la fonction immédiatement
        }
        
        // Chercher le champ caché qui contient l'email de l'utilisateur à promouvoirv(trouve le premier élément correspondant au sélecteur CSS)
        var emailInput = form.querySelector('input[name="email"]');
        if (!emailInput) {
            // Si le champ d'email n'est pas trouvé, afficher une erreur et arrêter
            alert("Erreur: email non trouvé");
            return;
        }
        
        // Récupérer la valeur de l'email depuis le champ caché
        var email = emailInput.value;
        
        // Désactiver le bouton pour éviter que l'utilisateur clique plusieurs fois
        button.disabled = true;
        button.classList.add('processing');
        
        // Créer un élément HTML <span> pour afficher un message de chargement
        var loadingText = document.createElement('span');
        loadingText.textContent = " Promotion en cours..."; 
        loadingText.style.color = "#FFFFFF"; 
        // Ajouter ce texte au bouton
        button.appendChild(loadingText);
        
        //Empêcher la soumission normale du formulaire qui rechargerait la page
        form.onsubmit = function(e) {
            e.preventDefault(); // Annule l'événement de soumission
            return false; // Double sécurité pour empêcher la soumission
        };
        
        // Envoyer une requête asynchrone au serveur pour promouvoir l'utilisateur
        fetch('../ajax/admin_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json' 
            },
            body: JSON.stringify({ // Convertir l'objet JavaScript en chaîne JSON
                action: 'promote', 
                email: email       
            })
        })
        // Analyser la réponse JSON du serveur
        .then(function(response) {
            return response.json(); // Convertir la réponse en objet JavaScript
        })
        // Traiter les données de la réponse
        .then(function(data) {
            // Supprimer le texte de chargement du bouton
            if (loadingText.parentNode) {
                loadingText.parentNode.removeChild(loadingText);
            }
            
            // Vérifier si l'opération a réussi
            if (data.success) {
                // Afficher un message de succès à l'utilisateur
                alert("L'utilisateur a été promu administrateur!");
                
                // Mettre à jour l'interface utilisateur sans recharger la page
                var row = button.closest('tr'); // Trouver la ligne du tableau contenant ce bouton
                if (row) {
                    // Chercher la cellule qui affiche le rôle (5e colonne du tableau)
                    var roleCell = row.querySelector('td:nth-child(5)');
                    if (roleCell) {
                        // Mettre à jour le texte pour indiquer que l'utilisateur est admin
                        roleCell.textContent = 'Oui';
                    }
                }
                
                // Cacher le bouton de promotion car l'utilisateur est maintenant admin
                button.closest('form').style.display = 'none';
            } else {
                // Si l'opération a échoué, afficher le message d'erreur du serveur
                alert("Erreur: " + (data.message || "La promotion a échoué"));
                
                // Réactiver le bouton pour permettre une nouvelle tentative
                button.disabled = false;
                button.classList.remove('processing');
            }
        })
        // Capturer et gérer les erreurs de réseau ou autres erreurs techniques
        .catch(function(error) {
            // Afficher l'erreur dans la console du navigateur pour le débogage
            console.error("Erreur: ", error);
            // Afficher un message d'erreur générique pour l'utilisateur
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
    
    // ==FONCTION BLOCKAGE USER==
    function bloquerUtilisateur(button) {
        // Demander confirmation avant de procéder au blocage
        if (!confirm("Êtes-vous sûr de vouloir bloquer cet utilisateur?")) {
            return; // Si l'utilisateur clique sur "Annuler", arrêter la fonction
        }
        
        // Trouver le formulaire parent du bouton cliqué
        var form = button.closest('form');
        if (!form) {
            alert("Erreur: formulaire non trouvé");
            return;
        }
        
        // Récupérer l'email de l'utilisateur à bloquer depuis le champ caché
        var emailInput = form.querySelector('input[name="email"]');
        if (!emailInput) {
            alert("Erreur: email non trouvé");
            return;
        }
        
        var email = emailInput.value;
        
        // Désactiver le bouton
        button.disabled = true;
        button.classList.add('processing');
        
        // Ajouter un texte pour indiquer que le blocage est en cours
        var loadingText = document.createElement('span');
        loadingText.textContent = " Blocage en cours...";
        loadingText.style.color = "#FFFFFF";
        button.appendChild(loadingText);
        
        // Empêcher la soumission normale du formulaire
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
                action: 'block', // Action de blocage
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
                var row = button.closest('tr'); 
                if (row) {
                    // Mettre à jour la cellule de statut (6e colonne)
                    var statusCell = row.querySelector('td:nth-child(6)');
                    if (statusCell) {
                        // Afficher "Bloqué" en rouge pour indiquer le nouveau statut
                        statusCell.innerHTML = '<span style="color:red;">Bloqué</span>';
                    }
                }
                
                // Cacher le formulaire contenant le bouton de blocage
                var blockForm = button.closest('form');
                if (blockForm) {
                    blockForm.style.display = 'none';
                }
                
                // Chercher la cellule qui contient tous les boutons d'actions
                var actionsCell = row.querySelector('td.icons');
                if (actionsCell) {
                    // Chercher si un formulaire de déblocage existe déjà dans cette cellule
                    var forms = actionsCell.querySelectorAll('form');
                    var unblockForm = null;
                    // Parcourir tous les formulaires pour trouver celui qui contient un bouton de déblocage
                    forms.forEach(function(form) {
                        if (form.querySelector('button[name="unblock"]')) {
                            unblockForm = form;
                        }
                    });
                    
                    if (unblockForm) {
                        // Si le formulaire de déblocage existe déjà, l'afficher
                        unblockForm.style.display = 'inline';
                        
                        // Réactiver le bouton de déblocage (ancien bug)
                        var unblockButton = unblockForm.querySelector('button[name="unblock"]');
                        if (unblockButton) {
                            unblockButton.disabled = false; // Réactiver le bouton
                            unblockButton.classList.remove('processing'); // Retirer la classe de traitement
                        }
                    } else {
                        // Si le formulaire n'existe pas, le créer dynamiquement. On recrée le bouton manuellement si jamais il a disparu, a été mal affiché, ou est resté bloqué (disabled)
                        var newForm = document.createElement('form');
                        newForm.style.display = 'inline'; // Affichage en ligne pour rester sur la même ligne
                        
                        // Créer un champ caché pour stocker l'email
                        var emailInput = document.createElement('input');
                        emailInput.type = 'hidden'; // Champ invisible
                        emailInput.name = 'email';
                        emailInput.value = email;
                        newForm.appendChild(emailInput); // Ajouter le champ au formulaire
                        
                        // Créer le bouton de déblocage
                        var unblockButton = document.createElement('button');
                        unblockButton.name = 'unblock'; 
                        unblockButton.type = 'submit';
                        unblockButton.className = 'unblock-button'; 
                        unblockButton.title = "Débloquer l'utilisateur"; 
                        unblockButton.disabled = false; // S'assurer que le bouton est activé
                        
                        // Créer l'image (icône) pour le bouton
                        var img = document.createElement('img');
                        img.src = '../assets/icon/unlock.png'; 
                        img.alt = 'Débloquer';
                        img.className = 'icon unblock-icon'; 
                        unblockButton.appendChild(img); // Ajouter l'image au bouton
                        
                        // Ajouter un événement de clic au nouveau bouton
                        unblockButton.addEventListener('click', function(event) {
                            event.preventDefault(); // Empêcher la soumission normale du formulaire
                            debloquerUtilisateur(this); // Appeler la fonction de déblocage
                        });
                        
                        // Ajouter le bouton au formulaire
                        newForm.appendChild(unblockButton);
                        
                        // Ajouter le nouveau formulaire à la cellule d'actions
                        actionsCell.appendChild(newForm);
                    }
                }
            } else {
                // Si l'opération a échoué, afficher l'erreur
                alert("Erreur: " + (data.message || "Le blocage a échoué"));
                
                // Réactiver le bouton pour permettre une nouvelle tentative
                button.disabled = false;
                button.classList.remove('processing');
            }
        })
        .catch(function(error) {
            // Gérer les erreurs de communication avec le serveur
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
    
    // == FONCTION DEBLOCKAGE USER==
    function debloquerUtilisateur(button) {
        // Demander confirmation avant de procéder au déblocage
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
        
        // Empêcher la soumission normale du formulaire
        form.onsubmit = function(e) {
            e.preventDefault();
            return false;
        };
        
        // Envoyer la requête AJAX au serveur pour débloquer l'utilisateur
        fetch('../ajax/admin_action.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'unblock', // Action de déblocage
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
                        // Remettre le statut à "Actif" (statut normal)
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
                        // Chercher le formulaire qui contient un bouton de blocage
                        if (form.querySelector('button[name="delete"]')) {
                            blockForm = form;
                        }
                    });
                    
                    if (blockForm) {
                        // Si le formulaire de blocage existe, l'afficher
                        blockForm.style.display = 'inline';
                        
                        // S'assurer que le bouton de blocage est activé
                        var blockButton = blockForm.querySelector('button[name="delete"]');
                        if (blockButton) {
                            blockButton.disabled = false; // Réactiver le bouton
                            blockButton.classList.remove('processing'); // Retirer les classes de traitement
                        }
                    } else {
                        // Si le formulaire n'existe pas, le créer dynamiquement.On recrée le bouton manuellement si jamais il a disparu, a été mal affiché, ou est resté bloqué (disabled)
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
                        blockButton.name = 'delete'; // Nom historique conservé pour compatibilité
                        blockButton.type = 'submit';
                        blockButton.className = 'delete-button';
                        blockButton.title = "Bloquer l'utilisateur";
                        blockButton.disabled = false; // S'assurer que le bouton est activé
                        blockButton.classList.remove('processing'); // S'assurer qu'aucune classe de désactivation n'est présente
                        
                        // Créer l'image pour le bouton
                        var img = document.createElement('img');
                        img.src = '../assets/icon/delete.png'; // Icône de blocage
                        img.alt = 'Bloquer';
                        img.className = 'icon delete-icon';
                        blockButton.appendChild(img);
                        
                        // Ajouter un événement de clic au bouton
                        blockButton.addEventListener('click', function(event) {
                            event.preventDefault();
                            bloquerUtilisateur(this); // Appeler la fonction de blocage
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
    
    //  ==ÉCOUTEURS D'ÉVÉNEMENTS==
    
    // Ajouter des écouteurs d'événements à tous les boutons "Promouvoir" présents sur la page
    promoteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Empêcher le rechargement de page par défaut du formulaire 
            event.preventDefault();
            // Appeler la fonction pour promouvoir l'utilisateur, en passant le bouton cliqué
            promouvoirUtilisateur(this);
        });
    });
    
    // Ajouter des écouteurs d'événements à tous les boutons "Bloquer" présents sur la page
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Empêcher le comportement par défaut du bouton/formulaire
            event.preventDefault();
            // Appeler la fonction pour bloquer l'utilisateur
            bloquerUtilisateur(this);
        });
    });
    
    // Ajouter des écouteurs d'événements à tous les boutons "Débloquer" présents sur la page
    unblockButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            // Empêcher le comportement par défaut du bouton/formulaire
            event.preventDefault();
            // Appeler la fonction pour débloquer l'utilisateur
            debloquerUtilisateur(this);
        });
    });
});

