document.addEventListener('DOMContentLoaded', function() {
    console.log("Admin.js initialisé - vérification des boutons de promotion et de suppression");
    
    // Sélectionner tous les boutons de promotion et de suppression plus précisément en utilisant des sélecteurs d'attributs
    const promoteButtons = document.querySelectorAll('button[name="promote"]');
    const deleteButtons = document.querySelectorAll('button[name="delete"]');
    
    console.log("Boutons de promotion trouvés :", promoteButtons.length);
    console.log("Boutons de suppression trouvés :", deleteButtons.length);
    
    // Ajouter des écouteurs d'événements aux boutons de promotion
    promoteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            console.log("Bouton de promotion cliqué");
            
            // Trouver l'élément de formulaire le plus proche
            const form = this.closest('form');
            if (!form) {
                console.error("Aucun formulaire parent trouvé pour le bouton de promotion");
                return;
            }
            
            // Désactiver le bouton et ajouter un retour visuel
            this.disabled = true;
            this.classList.add('processing');
            
            // Journaliser le formulaire qui sera soumis
            console.log("Formulaire à soumettre :", form);
            
            // Soumettre le formulaire après un délai
            setTimeout(() => {
                try {
                    console.log("Soumission du formulaire pour la promotion");
                    form.submit();
                } catch (error) {
                    console.error("Erreur lors de la soumission du formulaire :", error);
                    // Réactiver le bouton si la soumission échoue
                    this.disabled = false;
                    this.classList.remove('processing');
                }
            }, 2000);
        });
    });
    
    // Ajouter des écouteurs d'événements aux boutons de suppression
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            console.log("Bouton de suppression cliqué");
            
            // Confirmer la suppression avec l'utilisateur
            if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
                // Trouver l'élément de formulaire le plus proche
                const form = this.closest('form');
                if (!form) {
                    console.error("Aucun formulaire parent trouvé pour le bouton de suppression");
                    return;
                }
                
                // Désactiver le bouton et ajouter un retour visuel
                this.disabled = true;
                this.classList.add('processing');
                
                // Journaliser le formulaire qui sera soumis
                console.log("Formulaire à soumettre :", form);
                
                // Soumettre le formulaire après un délai
                setTimeout(() => {
                    try {
                        console.log("Soumission du formulaire pour la suppression");
                        form.submit();
                    } catch (error) {
                        console.error("Erreur lors de la soumission du formulaire :", error);
                        // Réactiver le bouton si la soumission échoue
                        this.disabled = false;
                        this.classList.remove('processing');
                    }
                }, 2000);
            } else {
                console.log("Suppression annulée par l'utilisateur");
            }
        });
    });
});
