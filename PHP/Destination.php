<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FINAL TRIP - Destination</title>
    <link rel="stylesheet" href="../Css/Destination.css">
</head>
<body>
    <header>
        <a href="Accueil.php" class="logo">FINAL TRIP</a>
        <div class="right">
            <a href="A-propos.php" class="head1">Qui sommes nous ?</a>
            <a href="Destination.php" class="head1">Destination</a>
            <button class="encadré">Contact</button>
            <a href="Connexion.php" class="img1"><img src="../assets/icon/User.png" alt="Profil"></a>
            <a href="#" class="img2"><img src="../assets/icon/Shopping cart.png" alt="Panier"></a>
        </div>
    </header>

    <hr class="hr1">

    <main>
        <section class="hero">
            <h1>Découvrez les voyages les plus sensationnels des États-Unis</h1>
        </section>

        <section class="destination-container">
            <aside class="filters">
                <h2>Pays</h2>
                <select class="cadre"><option>Sélectionnez votre pays ici</option>
                    <option>France</option>
                    <option>Pérou</option>
                    <option>Costa Rica</option>
                    <option>Maroc</option>
                    <option>Malaisie</option>
                    <option>Indonésie</option>
                </select>

                <h2>Climat</h2>
                <ul>
                    <li><input type="checkbox"> Chaud</li>
                    <li><input type="checkbox"> Froid</li>
                    <li><input type="checkbox"> Tempéré</li>
                    <li><input type="checkbox"> Humide</li>
                </ul>

                <h2>Durée</h2>
                <input type="number" placeholder="Indiquez la durée du séjour qui vous convient...">

                <h2>Terrain</h2>
                <ul>
                    <li><input type="checkbox"> Aquatique</li>
                    <li><input type="checkbox"> Terrestre</li>
                    <li><input type="checkbox"> Montagneux</li>
                    <li><input type="checkbox"> Aérien</li>
                </ul>

                <h2>Type de couchage</h2>
                <ul>
                    <li><input type="checkbox"> Tente</li>
                    <li><input type="checkbox"> Hôtel</li>
                    <li><input type="checkbox"> Auberge</li>
                    <li><input type="checkbox"> Chez l’habitant</li>
                </ul>

                <h2>Restrictions</h2>
                <ul>
                    <li><input type="checkbox"> Allergie</li>
                    <li><input type="checkbox"> Asthme</li>
                    <li><input type="checkbox"> Diabète</li>
                    <li><input type="checkbox"> Arthrose</li>
                </ul>

                <h2>Prix</h2>
                <input type="number" placeholder="Indiquez votre prix maximum...">
            </aside>

            <div class="trip-list">
                <a href="details-expedition-polaire.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Voyage Groenland Expédition Polaire.jpg" alt="Expédition polaire">
                        <div class="trip-info">
                            <h3>Expédition polaire</h3>
                            <p>Une expédition exceptionnelle entre banquises, hautes montagnes et glaciers en un lieu sans doute le plus esthétique et isolé du Groenland.</p>
                            <div class="trip-meta">
                                <span class="price">3200€</span>
                                <span class="duration">18 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-plongee-cage.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Plongée sous marine Guadalupe.jpg" alt="Plongée en cage">
                        <div class="trip-info">
                            <h3>Plongée en cage</h3>
                            <p>Pour les amateurs de sensations fortes, et ceux qui aiment se retrouver face à face avec l'un des prédateurs les plus redoutables du monde.</p>
                            <div class="trip-meta">
                                <span class="price">6290€</span>
                                <span class="duration">14 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-randonnee-velo.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Vélo de montagne Costa Rica.jpg" alt="Randonnée à vélo">
                        <div class="trip-info">
                            <h3>Randonnée à vélo</h3>
                            <p>Le Costa Rica est un terrain de jeu idéal pour les amateurs de VTT. Les montagnes, les volcans et les jungles tropicales offrent des défis uniques.</p>
                            <div class="trip-meta">
                                <span class="price">4600€</span>
                                <span class="duration">21 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-kilimandjaro.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Lava Tower Kilimandjaro.webp" alt="Ascension du Mont Kilimandjaro">
                        <div class="trip-info">
                            <h3>Ascension du Mont Kilimandjaro</h3>
                            <p>L’ascension du Kilimandjaro est une aventure inoubliable, passant de la jungle tropicale à la neige éternelle.</p>
                            <div class="trip-meta">
                                <span class="price">7490€</span>
                                <span class="duration">10 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-balade-maghreb.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Desert du Maghreb.jpg" alt="Balade dans le désert du Maghreb">
                        <div class="trip-info">
                            <h3>Balade dans le désert du Maghreb</h3>
                            <p>Découvrez l'envoûtante beauté du désert du Maghreb lors d'une balade inoubliable. Explorez des dunes dorées à perte de vue et laissez-vous captiver par le silence majestueux et les ciels étoilés.</p>
                            <div class="trip-meta">
                                <span class="price">2990€</span>
                                <span class="duration">6 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-everest.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Camp de Base Everest Nepal.jpg" alt="Escalade de l'Everest">
                        <div class="trip-info">
                            <h3>Escalade sur l'Everest</h3>
                            <p>L’ascension de l'Everest est l'un des défis les plus extrêmes au monde. Les trekkings vers le camp de base de l'Everest sont également très populaires, offrant un aperçu de l'environnement himalayen.</p>
                            <div class="trip-meta">
                                <span class="price">3290€</span>
                                <span class="duration">18 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-sentier-inca.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Sentier Inca Machu Picchu.jpg" alt="Randonnée sur le sentier Inca">
                        <div class="trip-info">
                            <h3>Randonnée sur le sentier Inca</h3>
                            <p>Le sentier Inca est une aventure à la fois historique et physique, qui mène les aventuriers à travers des paysages magnifiques et des ruines anciennes.</p>
                            <div class="trip-meta">
                                <span class="price">9990€</span>
                                <span class="duration">60 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-saut-elastique.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Saut à l'élastique Pont Bloukrans (1).jpg" alt="Saut à l'élastique au Pont Bloukrans">
                        <div class="trip-info">
                            <h3>Saut à l'élastique au Pont Bloukrans</h3>
                            <p>Le saut à l'élastique au-dessus du Bloukrans River vous permet de vivre une montée d'adrénaline pure.</p>
                            <div class="trip-meta">
                                <span class="price">1990€</span>
                                <span class="duration">6 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-antarctique.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Voyage Antarctique.gif" alt="Expédition en Antarctique">
                        <div class="trip-info">
                            <h3>Expédition en Antarctique</h3>
                            <p>L'Antarctique est un lieu d'aventure ultime. Vous pourrez observer des paysages glacés spectaculaires et des animaux qui donnent la givre.</p>
                            <div class="trip-meta">
                                <span class="price">12490€</span>
                                <span class="duration">60 jours</span>
                            </div>
                        </div>
                    </article>
                </a>

                <a href="details-safari.php" class="trip-card-link">
                    <article class="trip-card">
                        <img src="../assets/img/Safari Zanzibar.jpg" alt="Safari en Afrique">
                        <div class="trip-info">
                            <h3>Safari en Afrique</h3>
                            <p>Partez en safari pour une expérience au cœur de la nature sauvage, avec la possibilité de voir des animaux dans leur habitat naturel.</p>
                            <div class="trip-meta">
                                <span class="price">6280€</span>
                                <span class="duration">14 jours</span>
                            </div>
                        </div>
                    </article>
                </a>
            </div>
        </section>
    </main>

    <footer>
        <h2>Le dernier voyage que vous rêvez d’avoir</h2>
        <div class="contact">
            <p><strong>Adresse :</strong> <a href="#">34, Boulevard Haussmann, Paris 75009</a></p>
            <p><strong>Numéro :</strong> <a href="tel:0749685456">07 49 68 54 56</a></p>
            <p><strong>Email :</strong> <a href="mailto:contact@final-trip.com">contact@final-trip.com</a></p>
        </div>
        <p class="copyright">© 2025 Final Trip, ALL RIGHTS RESERVED.</p>
        <hr class="hr2">
        <div class="links">
            <a href="#">Mentions légales</a>
            <a href="#">Politique de confidentialité</a>
            <a href="#">Conditions d’utilisations</a>
        </div>
    </footer>
</body>
</html>
