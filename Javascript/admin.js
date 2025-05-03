function init (){

console.log("Dom prêt");

const promoteButton = document.querySelectorAll('button[name="promote"]');
const deleteButtons = document.querySelectorAll('button[name="delete"]');


promoteButtons.forEach(function(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault(); // Empêche la soumission immédiate du formulaire

        const form = this.closest('form');// Récupère le formulaire parent du bouton

        this.disabled = true;
        this.classList.add('processing');// Désactive le bouton pendant le traitement


        setTimeout(() => {
            this.disabled = false;
            this.classList.remove('processing'); // Retire la classe de traitemen
            form.submit(); // Soumet le formulaire pour effectuer l'action réelle
        }, 2000);
    });
});



deleteButtons.forEach(function(button) {
    button.addEventListener('click', function(event) {
        event.preventDefault();

        const form = this.closest('form');

        this.disabled = true;
        this.classList.add('processing');
        
        setTimeout(() => {
            this.disabled = false;
            this.classList.remove('processing');
            form.submit();
        }, 2000);
    });
});

}

document.addEventListener('DOMContentLoaded', init);
