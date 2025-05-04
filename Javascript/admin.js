
function init() {
    console.log("DOM prêt pour la page Admin");
    
    // Sélection des boutons de promotion et suppression
    const promoteButtons = document.querySelectorAll('button[name="promote"]');
    const deleteButtons = document.querySelectorAll('button[name="delete"]');
    
    // Ajout des écouteurs d'événements pour les boutons de promotion
    promoteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault(); // Empêche la soumission immédiate du formulaire
            const form = this.closest('form'); // Récupère le formulaire parent du bouton
            
            // Désactivation du bouton et ajout de la classe de traitement
            this.disabled = true;
            this.classList.add('processing');
            
            // Simulation d'attente de 2 secondes
            setTimeout(() => {
                this.disabled = false;
                this.classList.remove('processing'); // Retire la classe de traitement
                form.submit(); // Soumet le formulaire pour effectuer l'action réelle
            }, 2000);
        });
    });
    
    // Ajout des écouteurs d'événements pour les boutons de suppression
    deleteButtons.forEach(function(button) {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            const form = this.closest('form');
            
            // Désactivation du bouton et ajout de la classe de traitement
            this.disabled = true;
            this.classList.add('processing');
            
            // Simulation d'attente de 2 secondes
            setTimeout(() => {
                this.disabled = false;
                this.classList.remove('processing');
                form.submit();
            }, 2000);
        });
    });
}

// Exécution du script lorsque le DOM est complètement chargé
document.addEventListener('DOMContentLoaded', init);
