

document.addEventListener('DOMContentLoaded', () => {
    const lightThemeBtn = document.getElementById('light-theme-btn-mobile'); // ID pour le bouton soleil
    const darkThemeBtn = document.getElementById('dark-theme-btn-mobile');   // ID pour le bouton lune
    const body = document.body;
    const docElement = document.documentElement; // Pour l'attribut data-theme

    // Fonction pour appliquer les styles spécifiques au thème (icônes, etc.)
    function applyThemeVisuals(theme) {
        if (lightThemeBtn && darkThemeBtn) {
            lightThemeBtn.style.display = (theme === 'light') ? 'none' : 'inline-flex';
            darkThemeBtn.style.display = (theme === 'dark') ? 'none' : 'inline-flex';
        }
    }

    function setLightMode() {
        body.classList.add('light-mode');
        body.classList.remove('dark-mode');
        docElement.setAttribute('data-theme', 'light'); // Pour CSS `:root[data-theme="light"]`
        localStorage.setItem('theme', 'light');
        applyThemeVisuals('light');
    }

    function setDarkMode() {
        body.classList.add('dark-mode');
        body.classList.remove('light-mode');
        docElement.setAttribute('data-theme', 'dark');
        localStorage.setItem('theme', 'dark');
        applyThemeVisuals('dark');
    }

    if (lightThemeBtn) {
        lightThemeBtn.addEventListener('click', setDarkMode); // Cliquer sur soleil active le mode sombre (et affiche la lune)
    }
    if (darkThemeBtn) {
        darkThemeBtn.addEventListener('click', setLightMode); // Cliquer sur lune active le mode clair (et affiche le soleil)
    }

    // Charger le thème sauvegardé ou appliquer le thème par défaut (sombre)
    const savedTheme = localStorage.getItem('theme');
    if (savedTheme === 'light') {
        setLightMode();
    } else {
        setDarkMode(); // Défaut ou si 'dark' est sauvegardé
    }
});