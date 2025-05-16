<div align="center">
<h1 align="center">
🏖️ Final Trip 🏖️
</h1>
<p align="center">
  Your ultimate gateway to discovering, customizing, and booking sensational and extreme travel experiences around the globe.
</p>
<p align="center">
  <a href="https://github.com/jkengineer42/Final-Trip">
    <img src="https://img.shields.io/github/contributors/jkengineer42/Final-Trip.svg?style=for-the-badge" alt="Contributors" /> </a>
  <a href="https://github.com/jkengineer42/Final-Trip/issues">
    <img alt="Issues" src="https://img.shields.io/github/issues/jkengineer42/Final-Trip?style=for-the-badge">
    </a>
  <a href="https://github.com/jkengineer42/Final-Trip/network/members">
    <img alt="Forks" src="https://img.shields.io/github/forks/jkengineer42/Final-Trip.svg?style=for-the-badge"></a>
  <a href="https://github.com/jkengineer42/Final-Trip/stargazers">
    <img alt="Stars" src="https://img.shields.io/github/stars/jkengineer42/Final-Trip.svg?style=for-the-badge"></a>
  <a href="https://raw.githubusercontent.com/jkengineer42/Final-Trip/main/LICENSE">
    <img src="https://img.shields.io/badge/License-MIT-blue?style=for-the-badge" alt="License" /> </a>
</p>

<br />
<br />
  
  <img src="./readme-images/project-logo.png" />

  <h2 align="center">Tourly - Travel website</h2>

  Tourly is fully responsive travel website, <br />Responsive for all devices, built using HTML, CSS, and JavaScript.

  <a href="https://codewithsadee.github.io/tourly/"><strong>➥ Live Demo</strong></a>

</div>

<br />

### Demo Screeshots

![Tourly Desktop Demo](./readme-images/desktop.png "Desktop Demo")
---

## 📜 Table of Contents

- [About The Project](#-about-the-project)
- [✨ Features](#-features)
- [🛠️ Tech Stack](#️-tech-stack)
- [🚀 Getting Started](#-getting-started)
  - [Prerequisites](#prerequisites)
  - [Installation](#installation)
- [⚙️ Configuration](#️-configuration)
- [📂 Project Structure](#-project-structure)
- [🤝 Contributing](#-contributing)
- [📄 License](#-license)
- [👥 Authors](#-authors)

---

## 📖 About The Project

Final Trip is a dynamic web application designed for adventure enthusiasts seeking "out-of-the-norm" travel experiences. The platform allows users to explore a curated list of extreme voyages, customize them with various options (accommodation, activities, transport), and simulate a booking process. It features user authentication, an admin panel for user management, a dynamic pricing system, and a theme switcher for a personalized browsing experience.

The project emphasizes a full-stack approach, utilizing PHP for backend logic and data management (via JSON files), and HTML, CSS, and JavaScript for a responsive and interactive frontend.

---

## ✨ Features

*   **👤 User Management:**
    *   Secure Registration & Login
    *   User Profile Management (view & edit personal information)
    *   Password Hashing
    *   Session Management
*   **🌍 Voyage Discovery:**
    *   Browse diverse extreme travel destinations
    *   Search functionality for voyages
    *   Dynamic filtering and sorting of voyages (by price, date, duration, country, climate, etc.)
*   **🎨 Voyage Customization:**
    *   Interactive selection of options for each travel stage (accommodation, dining, activities, transport)
    *   Real-time dynamic price estimation based on selected options
    *   Summary page for customized trips
*   **🛒 Shopping Cart & Booking:**
    *   Add customized voyages to a shopping cart
    *   Manage cart items (view, remove, modify)
    *   Simulated payment process via CYBank integration (using `getapikey.php`)
    *   Payment confirmation and logging
*   **🛠️ Admin Panel:**
    *   View and manage user accounts
    *   Promote users to admin status
    *   Delete non-admin users
    *   Paginated user list with search filters
*   **🎨 UI/UX:**
    *   Responsive design for various screen sizes
    *   Theme switcher (Light/Dark mode) with cookie persistence
    *   AJAX-powered updates for profile editing, admin actions, and dynamic content loading (e.g., voyage options, price calculation) for a smoother experience.
*   **📄 Static & Legal Pages:**
    *   "About Us" page
    *   Contact page with a map and mailto form
    *   Links to CGU, Legal Mentions, and Privacy Policy

---

## 🛠️ Tech Stack

*   **Frontend:** HTML5, CSS3, JavaScript (ES6+)
*   **Backend:** PHP (7.x / 8.x recommended)
*   **Data Storage:** JSON files
*   **Version Control:** Git & GitHub
*   **Design (Initial Mockups):** Figma (as per project report)
*   **Payment Gateway (Simulated):** CYBank (via `getapikey.php`)

---

## 🚀 Getting Started

To get a local copy up and running, follow these simple steps.

### Prerequisites

*   A web server (e.g., Apache, Nginx) with PHP support.
*   PHP (version 7.4 or higher recommended).
*   A web browser.

### Installation

1.  **Clone the repository:**
    ```sh
    git clone https://github.com/jkengineer42/Final-Trip.git
    ```
2.  **Navigate to the project directory:**
    ```sh
    cd Final-Trip-main
    ```
3.  **Place the project in your web server's document root:**
    *   For XAMPP: `htdocs/`
    *   For WAMP: `www/`
    *   For MAMP: `htdocs/`
    *   Or configure a virtual host to point to the `Final-Trip-main` directory.
4.  **Permissions:**
    Ensure that your web server has write permissions for the `data/` directory, as user registration, profile updates, and payment logging will write to JSON files within this directory (e.g., `data_user.json`, `paiements.json`).
    ```sh
    # On Linux/macOS, from within Final-Trip-main/
    sudo chmod -R 775 data/
    sudo chown -R www-data:www-data data/ # Replace www-data with your web server's user/group
    ```
5.  **Access the application:**
    Open your web browser and navigate to the project's URL (e.g., `http://localhost/Final-Trip-main/PHP/Accueil.php` or your configured virtual host).

---

## ⚙️ Configuration

*   **Payment Gateway:**
    *   The `PHP/getapikey.php` file is used for the CYBank payment simulation. The vendor code is hardcoded as `MEF-1_H`.
    *   The return URL for payment confirmation in `PHP/paiement.php` is hardcoded to `http://localhost:8080/Final-Trip-main/PHP/retour_paiement.php`. You may need to adjust the hostname and port (`localhost:8080`) if your local server setup differs.

---

## 📂 Project Structure
Final-Trip-main/
├── Css/ # Stylesheets (global, page-specific, themes)
├── Javascript/ # Client-side JavaScript files
├── PHP/ # Backend PHP files (pages, includes, logic)
├── data/ # JSON data files (users, voyages, payments)
├── assets/ # Images, icons, and other static assets
│ ├── icon/
│ ├── img/
│ └── Avis/
├── documentation/ # PDF documents (CGU, Legal, Project Report, etc.)
├── ajax/ # PHP scripts for handling AJAX requests (e.g., security.php)
├── README.md # This file
└── LICENSE # Project's MIT License

---

## 🤝 Contributing

This project was developed as part of a student assignment. While direct contributions are not actively sought at this moment, feedback and suggestions are welcome via GitHub Issues.

If you wish to fork the project and enhance it:

1.  Fork the Project
2.  Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3.  Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4.  Push to the Branch (`git push origin feature/AmazingFeature`)
5.  Open a Pull Request (against your own fork or if you intend to share significant improvements)

---

## 📄 License

Distributed under the MIT License. See `LICENSE` file for more information.

---

## 👥 Authors

This project was brought to life by:

*   **KONDA-MOUGNONGUI Jérémie**
*   **BOUCHAM Jibril**
*   **KAE-NUNE Damien**

Project supervised by: LE BRETON Caryl.
Copyright (c) 2025 42 (as per LICENSE file).

---
