// Ce fichier gère l'interactivité de la page profil

// Stocker les valeurs originales des champs pour pouvoir les restaurer si nécessaire
const originalValues = {};

// Variable pour suivre si des modifications ont été validées
let hasValidatedChanges = false;

// Fonction qui s'exécute quand la page est entièrement chargée
document.addEventListener('DOMContentLoaded', function() {
    console.log('Script profil.js chargé'); // Pour vérifier que le script est bien chargé
    
    // 1. SAUVEGARDER LES VALEURS ORIGINALES
    // On parcourt tous les champs du formulaire et on stocke leur valeur initiale
    document.querySelectorAll('.form-field').forEach(function(field) {
        originalValues[field.id] = field.value;
        console.log('Valeur originale stockée pour', field.id, ':', field.value);
    });
    
    // 2. GESTION DES BOUTONS "MODIFIER"
    // On ajoute un écouteur d'événements sur chaque bouton "Modifier"
    document.querySelectorAll('.edit-btn').forEach(function(button) {
        // Quand on clique sur un bouton "Modifier"...
        button.addEventListener('click', function() {
            // On récupère l'ID du champ associé au bouton
            const fieldId = this.getAttribute('data-field');
            console.log('Bouton Modifier cliqué pour:', fieldId);
            
            // On active l'édition du champ
            enableEditing(fieldId);
            
            // Si c'est le champ mot de passe, on affiche le champ de confirmation
            if (fieldId === 'password') {
                document.querySelector('.password-confirm-group').style.display = 'flex';
            }
        });
    });
    
    // 3. GESTION DES BOUTONS "VALIDER"
    // On ajoute un écouteur d'événements sur chaque bouton "Valider"
    document.querySelectorAll('.save-btn').forEach(function(button) {
        // Quand on clique sur un bouton "Valider"...
        button.addEventListener('click', function() {
            // On récupère l'ID du champ associé au bouton
            const fieldId = this.getAttribute('data-field');
            console.log('Bouton Valider cliqué pour:', fieldId);
            
            // On sauvegarde les modifications
            saveChanges(fieldId);
        });
    });
    
    // 4. GESTION DES BOUTONS "ANNULER"
    // On ajoute un écouteur d'événements sur chaque bouton "Annuler"
    document.querySelectorAll('.cancel-btn').forEach(function(button) {
        // Quand on clique sur un bouton "Annuler"...
        button.addEventListener('click', function() {
            // On récupère l'ID du champ associé au bouton
            const fieldId = this.getAttribute('data-field');
            console.log('Bouton Annuler cliqué pour:', fieldId);
            
            // On annule les modifications
            cancelChanges(fieldId);
            
            // Si c'est le champ mot de passe, on masque le champ de confirmation
            if (fieldId === 'password') {
                document.querySelector('.password-confirm-group').style.display = 'none';
                document.getElementById('password_confirm').value = '';
            }
        });
    });
});

// Fonction pour activer l'édition d'un champ
function enableEditing(fieldId) {
    // On récupère le champ
    const field = document.getElementById(fieldId);
    
    // On récupère le conteneur des boutons d'action
    const actionButtons = field.parentElement.querySelector('.action-buttons');
    
    // On active l'édition du champ
    field.readOnly = false; // Rend le champ éditable
    field.classList.add('editing'); // Ajoute une classe CSS pour le style
    
    // On affiche les boutons de validation/annulation
    actionButtons.style.display = 'flex';
    
    // On masque le bouton d'édition
    field.parentElement.querySelector('.edit-btn').style.display = 'none';
    
    // On donne le focus au champ
    field.focus();
}

// Fonction pour sauvegarder les modifications d'un champ
function saveChanges(fieldId) {
    // On récupère le champ
    const field = document.getElementById(fieldId);
    
    // On récupère le conteneur des boutons d'action
    const actionButtons = field.parentElement.querySelector('.action-buttons');
    
    // On vérifie si la valeur a changé
    if (field.value !== originalValues[fieldId]) {
        // Si c'est le cas, on marque qu'une modification a été validée
        hasValidatedChanges = true;
        
        // On affiche le bouton de soumission du formulaire
        document.getElementById('submitBtn').style.display = 'block';
    }
    
    // On désactive l'édition du champ
    field.readOnly = true;
    field.classList.remove('editing');
    
    // On masque les boutons de validation/annulation
    actionButtons.style.display = 'none';
    
    // On affiche le bouton d'édition
    field.parentElement.querySelector('.edit-btn').style.display = 'block';
}

// Fonction pour annuler les modifications d'un champ
function cancelChanges(fieldId) {
    // On récupère le champ
    const field = document.getElementById(fieldId);
    
    // On récupère le conteneur des boutons d'action
    const actionButtons = field.parentElement.querySelector('.action-buttons');
    
    // On restaure la valeur originale
    field.value = originalValues[fieldId];
    
    // On désactive l'édition du champ
    field.readOnly = true;
    field.classList.remove('editing');
    
    // On masque les boutons de validation/annulation
    actionButtons.style.display = 'none';
    
    // On affiche le bouton d'édition
    field.parentElement.querySelector('.edit-btn').style.display = 'block';
}