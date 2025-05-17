document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const mobileMenu = document.querySelector('.mobile-menu');
    const body = document.body;

    const desktopNavContainer = document.querySelector('header .right.desktop-nav');
    const mobileThemePlaceholder = document.querySelector('.mobile-menu .mobile-theme-selector-placeholder');

    // Function to move theme selector
    function moveThemeSelector() {
        const themeSelector = document.querySelector('.theme-selector'); // This is created by theme.js
        if (!themeSelector) {
            // console.warn('Theme selector not found. Ensure theme.js has run.');
            return;
        }

        if (window.innerWidth <= 992 && mobileMenu.classList.contains('active')) {
            // If on mobile and menu is open, move to mobile menu
            if (mobileThemePlaceholder && !mobileThemePlaceholder.contains(themeSelector)) {
                mobileThemePlaceholder.appendChild(themeSelector);
            }
        } else if (window.innerWidth <= 992 && !mobileMenu.classList.contains('active')) {
            // If on mobile and menu is closed, move back to header (or hide)
            // For simplicity, we can just ensure it's not in mobile. theme.js appends it to .right
            // If it was in mobile placeholder, move it back to desktop container
             if (desktopNavContainer && mobileThemePlaceholder.contains(themeSelector)) {
                 desktopNavContainer.appendChild(themeSelector); // Or a specific hidden place if needed
             }
        }
        else {
            // On desktop, ensure it's in the desktop nav
            if (desktopNavContainer && !desktopNavContainer.contains(themeSelector)) {
                desktopNavContainer.appendChild(themeSelector);
            }
        }
    }


    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', () => {
            hamburger.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            body.classList.toggle('no-scroll'); // Optional: prevent body scroll when menu is open

            // Move theme selector after menu state changes
            moveThemeSelector();
        });
    }

    // Handle window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 992) {
            // If resized to desktop, close mobile menu if it's open
            if (mobileMenu.classList.contains('active')) {
                hamburger.classList.remove('active');
                mobileMenu.classList.remove('active');
                body.classList.remove('no-scroll');
            }
        }
        // Always try to reposition theme selector on resize
        moveThemeSelector();
    });

    // Initial check in case the page loads on mobile
    moveThemeSelector();

    // Close mobile menu if a link inside it is clicked
    const mobileMenuLinks = mobileMenu.querySelectorAll('a');
    mobileMenuLinks.forEach(link => {
        link.addEventListener('click', () => {
            if (mobileMenu.classList.contains('active')) {
                hamburger.classList.remove('active');
                mobileMenu.classList.remove('active');
                body.classList.remove('no-scroll');
                moveThemeSelector(); // Move theme selector back
            }
        });
    });
});

// CSS for no-scroll
// Add this to your global.css if you use body.classList.toggle('no-scroll');
/*
body.no-scroll {
    overflow: hidden;
}
*/