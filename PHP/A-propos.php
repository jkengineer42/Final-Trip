    <?php
  require_once 'sessions.php';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINAL TRIP - À propos</title>
    <link rel="stylesheet" href="../Css/global.css">
    <link rel="stylesheet" href="../Css/A-propos.css">
    <script src="../Javascript/theme.js"></script>
</head>
<body>
     <?php include('header.php'); ?>
    <hr class="hr1">

    <main>
        <section class="hero">
            <h1>Découvrez les voyages les plus sensationnels du monde</h1>
            <div class="search-bar">
                <form action="Destination.php" method="get">
                    <button type="submit"><img src="../assets/icon/Loupe.svg" alt="Rechercher"></button>
                    <input type="text" name="search" placeholder="Recherchez le voyage de vos rêves..." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                </form>
            </div>
        </section>

               <div class="travel-section">
            <a href="voyage_detail.php?id=7" class="card-link">
                <div class="card card-antarctica">
                    <div class="card-content">
                        <div>
                            <div class="location">
                                <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Antarctique
                            </div>
                            <h2 class="title">Pas peur du froid ?</h2>
                            <p class="description">Découvrez l'aventure ultime dans le continent le plus austral de la Terre. Une expérience unique entre glaciers et manchots.</p>
                        </div>
                    </div>
                    <div class="image-container">
                        <img src="../assets/img/Voyage Antarctique.gif" alt="Expédition en Antarctique" />
                    </div>
                    <div class="arrow-button"></div>
                </div>
            </a>

            <a href="voyage_detail.php?id=5" class="card-link">
                <div class="card card-kenya"> 
                    <div class="card-content">
                        <div>
                            <div class="location">
                                <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Tanzanie 
                            </div>
                            <h2 class="title">Envie d'aventure ?</h2>
                            <p class="description">Explorez les savanes sauvages et rencontrez la faune africaine dans son habitat naturel.</p>
                        </div>
                    </div>
                    <div class="image-container">
                        <img src="../assets/img/Safari Zanzibar.jpg" alt="Safari en Tanzanie" />
                    </div>
                    <div class="arrow-button"></div>
                </div>
            </a>

            <a href="voyage_detail.php?id=6" class="card-link"> 
                <div class="card card-nepal">       
                    <div class="card-content">
                        <div>
                            <div class="location">
                                <svg class="location-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                    <circle cx="12" cy="10" r="3"></circle>
                                </svg>
                                Everest 
                            </div>
                            <h2 class="title">Une balade en montagne ?</h2>
                            <p class="description">Parcourez les sentiers de l'Himalaya et découvrez la culture unique des villages de montagne.</p>
                        </div>
                    </div>
                    <div class="image-container">
                        <img src="../assets/img/Chukung Nepal escalade Everest.jpg" alt="Trek Everest" />
                    </div>
                    <div class="arrow-button"></div>
                </div>
            </a>
        </div>

        <section class="about-section">
            <div class="text-content">
                <h2 class="section-title">Qui nous sommes?</h2>
                <p>Chez Final Trip, nous pensons que voyager vers une nouvelle destination est une expérience qui change la vie. Nous sommes prêts à vous faire voir le monde sous un nouvel angle. Notre équipe hautement qualifiée est heureuse de fournir à nos clients les conseils de voyage les plus précis et un service personnalisé, adapté à vos besoins et attentes.</p>
                <p>Laissez-nous nous concentrer sur la logistique de votre voyage, tandis que tout ce qui vous reste est le plaisir de l'expérience. Déconnectez-vous de vos préoccupations et connectez-vous avec votre famille, vos amis, la nature, la faune et une culture locale unique que vous ne pouvez trouver qu'ici avec nous.</p>
                <p>Préparez-vous à être immergé dans une expérience inspirante qui changera votre vie et dont vous vous souviendrez toute votre vie.</p>
            </div>
            <div class="image-content">
                <img src="../assets/img/Coucher de soleil montagnes Californie.jpg" alt="Photo de l'équipe" class="team-photo">
            </div>
        </section>

        <section class="avis-section">
            <h2 class="avis-title">Avis</h2>
            <div class="avis-container">
            <div class="avis-card">
                    <div class="avis-header">
                        <img src="../assets/Avis/Anna Ratailleau.jpg" alt="Photo de Profil" class="profile-pic">
                        <h3 class="name">Anna Ratailleau</h3>
                    </div>
                    <p class="avis-text">Très bon voyage, je recommande vivement. Le service client est également très réactif.</p>
                    <div class="rating">
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                    </div>
                </div>
                <div class="avis-card">
                    <div class="avis-header">
                        <img src="../assets/Avis/Emily Jackus.jpg" alt="Photo de Profil" class="profile-pic">
                        <h3 class="name">Emily Jackus</h3>
                    </div>
                    <p class="avis-text">J'ai adoré mon expérience avec cette agence. La qualité est au rendez-vous et le prix est raisonnable.</p>
                    <div class="rating">
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                    </div>
                </div>
                <div class="avis-card">
                    <div class="avis-header">
                        <img src="../assets/Avis/Jacqueline Robes.jpg" alt="Profile Picture" class="profile-pic">
                        <h3 class="name">Jacqueline Robes</h3>
                    </div>
                    <p class="avis-text">Nous avons passé des vacances inoubliables grâce à cette agence de voyage. Tout était parfaitement organisé, des vols aux hôtels en passant par les excursions. toutes nos questions.</p>
                    <div class="rating">
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                    </div>
                </div>
                <div class="avis-card">
                    <div class="avis-header">
                        <img src="../assets/Avis/Pierre Le Boucher.jpg" alt="Photo de Profil" class="profile-pic">
                        <h3 class="name">Pierre Le Boucher</h3>
                    </div>
                    <p class="avis-text">Cette agence est incroyable ! Je suis très satisfait de la qualité et de la rapidité de leur réponse. Dès le premier contact, j'ai été impressionné par leur professionnalisme, leur écoute et leur capacité à comprendre rapidement mes besoins.</p>
                    <div class="rating">
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                        <span class="star">&#9733;</span>
                    </div>
                </div>
            </div>
        </section>
    </main>

     <?php include('footer.php'); ?>
</body>
</html>
