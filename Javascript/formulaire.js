// Fonction qui s'exécute quand le document est complètement chargé
document.addEventListener('DOMContentLoaded', function() {
    // On récupère le formulaire
    const formulaire = document.querySelector('form');
    
    // Si aucun formulaire n'est trouvé, on s'arrête
    if (!formulaire) return;
    
    // Récupération des champs de mot de passe
    const champsMdp = document.querySelectorAll('input[type="password"]');
    
    // Récupération des champs avec une limite de caractères
    const champsLimites = document.querySelectorAll('input[maxlength]');
    
    // Pour chaque champ de mot de passe, on ajoute l'icône pour afficher/masquer
    champsMdp.forEach(function(champ) {
        // Création de l'icône "œil" pour le mot de passe
        const iconeOeil = document.createElement('img');
  
        iconeOeil.src = '../assets/icon/oeil.png'; // Icone à changer
        iconeOeil.alt = 'Afficher/Masquer le mot de passe';
        iconeOeil.className = 'icone-oeil';
        iconeOeil.style.cursor = 'pointer';
        iconeOeil.style.width = '20px';
        iconeOeil.style.position = 'absolute';
        iconeOeil.style.right = '10px';
        iconeOeil.style.top = '50%';
        iconeOeil.style.transform = 'translateY(-50%)';
        

        const conteneur = champ.parentElement;
        conteneur.style.position = 'relative';
        

        conteneur.appendChild(iconeOeil);
        
        // Gestion du clic sur l'icône
        iconeOeil.addEventListener('click', function() {
            // On change le type du champ entre "password" et "text"
            if (champ.type === 'password') {
                champ.type = 'text';

                iconeOeil.style.opacity = '0.5'; 
            } else {
                champ.type = 'password';
                iconeOeil.style.opacity = '1'; // Retour à l'opacité normale
            }
        });
    });
    
    // Pour chaque champ avec limite de caractères, on ajoute un compteur
    champsLimites.forEach(function(champ) {
        // Création du compteur
        const compteur = document.createElement('span');
        compteur.className = 'compteur-caracteres';
        compteur.style.fontSize = '0.8rem';
        compteur.style.color = '#666';
        compteur.style.display = 'block';
        compteur.style.textAlign = 'right';
        compteur.style.marginTop = '5px';
        
        // On calcule le nombre de caractères initial
        const longueurMax = champ.getAttribute('maxlength');
        compteur.textContent = `0/${longueurMax} caractères`;
        
        // On place le compteur après le champ
        champ.parentElement.appendChild(compteur);
        
        // Mise à jour du compteur à chaque saisie
        champ.addEventListener('input', function() {
            const longueurActuelle = champ.value.length;
            compteur.textContent = `${longueurActuelle}/${longueurMax} caractères`;
            
            // Changer la couleur si on approche de la limite
            if (longueurActuelle > longueurMax * 0.8) {
                compteur.style.color = 'orange';
            } else if (longueurActuelle === parseInt(longueurMax)) { // Conversion explicite en nombre
                compteur.style.color = 'red';
            } else {
                compteur.style.color = '#666';
            }
        });
    });
    
    // Validation à la soumission du formulaire
    formulaire.addEventListener('submit', function(evenement) {
        // On empêche la soumission par défaut
        evenement.preventDefault();
        
        // Variable pour suivre la validité globale
        let formulaireValide = true;
        
        // Récupération de tous les champs obligatoires
        const champsObligatoires = formulaire.querySelectorAll('[required]');
        
        // Validation de chaque champ obligatoire
        champsObligatoires.forEach(function(champ) {
            // Réinitialiser le message d'erreur précédent
            supprimerMessageErreur(champ);
            
            // Vérification si le champ est vide
            if (!champ.value.trim()) {
                afficherMessageErreur(champ, 'Ce champ est obligatoire');
                formulaireValide = false;
                return;
            }
            
            // Validation spécifique selon le type de champ
            if (champ.type === 'email') {
                // Vérification de format d'email basique
                const motifEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!motifEmail.test(champ.value)) {
                    afficherMessageErreur(champ, 'Veuillez entrer une adresse email valide');
                    formulaireValide = false;
                }
            } else if (champ.id === 'password') {
                // Vérification de longueur minimale pour le mot de passe
                if (champ.value.length < 8) {
                    afficherMessageErreur(champ, 'Le mot de passe doit contenir au moins 8 caractères');
                    formulaireValide = false;
                }
            } else if (champ.id === 'confirm-email') {
                // Vérification que les emails correspondent
                const champEmail = document.getElementById('email');
                if (champEmail && champ.value !== champEmail.value) {
                    afficherMessageErreur(champ, 'Les adresses email ne correspondent pas');
                    formulaireValide = false;
                }
            } else if (champ.id === 'birthdate') {
                // Vérification que la date est valide et pas dans le futur
                const dateSelectionnee = new Date(champ.value);
                const aujourdhui = new Date();
                
                if (dateSelectionnee > aujourdhui) {
                    afficherMessageErreur(champ, 'La date ne peut pas être dans le futur');
                    formulaireValide = false;
                }
                
                // Vérifier que l'utilisateur a au moins 18 ans
                const dixHuitAnsAvant = new Date();
                dixHuitAnsAvant.setFullYear(dixHuitAnsAvant.getFullYear() - 18);
                
                if (dateSelectionnee > dixHuitAnsAvant) {
                    afficherMessageErreur(champ, 'Vous devez avoir au moins 18 ans');
                    formulaireValide = false;
                }
            }
        });
        
        // Si le formulaire est valide, on le soumet
        if (formulaireValide) {
            formulaire.submit();
        }
    });
    
    // Fonction pour afficher un message d'erreur sous un champ
    function afficherMessageErreur(champ, message) {
        // Création de l'élément pour le message d'erreur
        const elementErreur = document.createElement('div');
        elementErreur.className = 'message-erreur';
        elementErreur.textContent = message;
        elementErreur.style.color = 'var(--yellow)';
        elementErreur.style.fontSize = '0.8rem';
        elementErreur.style.marginTop = '5px';
        
        // Ajout du message d'erreur après le champ
        champ.parentElement.appendChild(elementErreur);
        
        // Mise en évidence du champ en erreur
        champ.style.borderColor = 'red';
    }
    
    // Fonction pour supprimer un message d'erreur
    function supprimerMessageErreur(champ) {
        // Suppression du message d'erreur précédent s'il existe
        const elementErreur = champ.parentElement.querySelector('.message-erreur');
        if (elementErreur) {
            elementErreur.remove();
        }
        
        // Réinitialisation du style du champ
        champ.style.borderColor = '';
    }
    
    // Ajouter une validation en temps réel pour une meilleure expérience utilisateur
    const champsObligatoires = formulaire.querySelectorAll('[required]');
    champsObligatoires.forEach(function(champ) {
        champ.addEventListener('input', function() {
            // Supprimer le message d'erreur dès que l'utilisateur commence à corriger
            supprimerMessageErreur(champ);
        });
    });
});
