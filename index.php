<?php
// Démarrer la session PHP
session_start();

// Paramètres de connexion à la base de données
$host = 'localhost'; 
$dbname = 'moduleconnexion';
$username = 'root'; 
$password = ''; 

// Tentative de connexion à la base de données
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

// Déterminer l'état de la connexion utilisateur
$estConnecte = isset($_SESSION['utilisateur']);
$loginUtilisateur = $estConnecte ? htmlspecialchars($_SESSION['utilisateur']['login']) : 'Invité';
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil | Mon Module de Connexion</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            background-color: #f4f4f4;
            min-height: 100vh; /* le footer rester en bas (nécessite body et html à 100% de hauteur) */
            display: flex;
            flex-direction: column;
        }
        
        .contenu-principal {
            flex-grow: 1; /* Permet au contenu de prendre tout l'espace restant */
            margin: 20px;
        }
        
        /* Header (Navigation) */
        header { 
            background-color: #e0e0e0;;  
            padding: 10px 20px; 
            border-bottom: 2px solid #ccc; 
            display: flex; 
            gap: 10px; 
        }
        
        /* Style des liens de navigation (commun au header et au footer) */
        .navigation a {
            /* Bouton : Carré bleu */
            background-color: #007bff;
            color: yellow; 
            text-decoration: none; 
            padding: 10px; 
            border-radius: 5px; 
            display: inline-block;
            font-weight: bold;
            text-align: center;
            min-width: 80px; 
            transition: background-color 0.3s; 
            position: relative; 
        }

        .navigation a:hover {
        background-color: #0056b3;
        }
        /* Style de l'infobulle */
        .navigation a::after {
        /* Récupère le texte de l'attribut data-tooltip pour le contenu */
        content: attr(data-tooltip); 
    
        /* Position et apparence */
        position: absolute;
        bottom: auto; /* Place l'infobulle en-dessous du lien */
        top: 130%;
        left: 50%;
        transform: translateX(-50%); /* Centre l'infobulle horizontalement */
    
       
        background-color: #5cb85c; 
        color: white;
        padding: 8px 12px;
        border-radius: 8px; 
        font-size: 0.8em;
        white-space: nowrap; /* Empêche le texte de se couper */
        z-index: 10; /* S'assure qu'elle est au-dessus des autres éléments */
    
        /* Rendre l'infobulle invisible par défaut */
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s, visibility 0.3s;
        }

        /* Affichage de l'infobulle au survol (hover) */
        .navigation a:hover::after {
        visibility: visible;
        opacity: 1;
        }

        /* Flèche sous l'infobulle */
        .navigation a::before {
        content: '';
        position: absolute;
        bottom: auto; 
        top: 130%;
        left: 50%;
        transform: translateX(-50%) translateY(8px); 
    
        border-width: 5px;
        border-style: solid;
        border-color: #5cb85c transparent transparent transparent; 
        transform: translateX(-50%) translateY(-13px); /* Ajuste la position de la flèche */ 
    
        visibility: hidden;
        opacity: 0;
        transition: opacity 0.3s, visibility 0.3s;
        z-index: 10;
        }

        /* Affichage de la flèche au survol */
        .navigation a:hover::before {
        visibility: visible;
        opacity: 1;
        }

        .navigation a:first-child::after,
        .navigation a:first-child::before {
        /* Décale l'infobulle de 30 pixels vers la droite */
        transform: translateX(-20%); 
        }

        footer .navigation a::after {
            bottom: 150%; /* Positionne l'infobulle au-dessus du lien */
            top: auto; 
        }

        footer .navigation a::before {
            bottom: 150%; 
            top: auto;
            border-color: #5cb85c transparent transparent transparent; 
            transform: translateX(-50%) translateY(8px); 
        }
        
        .navigation a:hover::after, footer .navigation a:hover::after {
            visibility: visible;
            opacity: 1;
        }

        .navigation a:hover::before, footer .navigation a:hover::before {
            visibility: visible;
            opacity: 1;
        }

    
        .navigation a:first-child::after,
        .navigation a:first-child::before {
            transform: translateX(-20%); 
        }
    
        footer .navigation a:first-child::after,
        footer .navigation a:first-child::before {
             transform: translateX(-30%); 
        }

        /* Style du titre principal dans le rectangle rouge */
        .titre-accueil {
            background-color: red; 
            padding: 60px 25px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 40px;
        }

        .titre-accueil h1 {
            color: yellow; 
            margin: 0; 
            font-size: 2.5em; 
        }
        
        /* Style pour le reste du contenu */
        .bienvenue { 
            margin-top: 20px;
            color:yellow; 
            padding: 25px; 
            border: 1px solid #ddd;
            border-radius: 10px; 
            background-color: red; 
        }
        
        
        footer {
            background-color: #037430; 
            color: #fff;
            padding: 15px 20px;
            text-align: center;
            margin-top: auto; /* Pousse le footer en bas */
        }
        
        footer nav {
            display: flex;
            justify-content: center;
            gap: 15px;
        }
        
    </style>
</head>
<body>

    <header>
        <nav class="navigation">
    <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
    
    <?php if ($estConnecte): ?>
        <a href="profil.php" data-tooltip="Ta page secrète. Vois tes infos ici !">Profil</a>
        
        <?php if ($loginUtilisateur === 'admin'): ?>
            <a href="admin.php" data-tooltip="Le bureau du chef ! Ici, tu vois tous les membres.">Admin</a>
        <?php endif; ?>
        
        <a href="deconnexion.php" data-tooltip="C'est l'heure de partir. N'oublie pas de te déconnecter !">Déconnexion</a>
    <?php else: ?>
        <a href="connexion.php" data-tooltip="J'ai déjà mon mot de passe ! Je rentre dans le site.">Connexion</a>
        
        <a href="inscription.php" data-tooltip="C'est ma première fois ! Je crée mon compte ici.">Inscription</a>
    <?php endif; ?>
</nav>
    </header>
    
    <div class="contenu-principal">
        <div class="titre-accueil">
            <h1>Bienvenue sur le site de connexion</h1>
        </div>

        <div class="bienvenue">
            <h2>Information du site</h2>
            <?php if ($estConnecte): ?>
                <p style="font-size: 1.3em;">
                    Bonjour **<?php echo $loginUtilisateur; ?>** ! Tu es maintenant connecté.
                </p>
            <?php else: ?>
                <p>
                    Clique sur Connexion ou Inscription.
                </p>
            <?php endif; ?>
            
        </div>
    </div> <footer>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            
            <?php if ($estConnecte): ?>
                <a href="profil.php" data-tooltip="Regarde tes informations secrètes.">Profil</a>
                
                <?php if ($loginUtilisateur === 'admin'): ?>
                    <a href="admin.php" data-tooltip="L'endroit où seul le chef peut aller !">Admin</a>
                <?php endif; ?>
                
                <a href="deconnexion.php" data-tooltip="Dis au revoir et ferme ta session.">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php" data-tooltip="J'ai déjà mon mot de passe ! Je peux rentrer dans le site.">Connexion</a>
                
                <a href="inscription.php" data-tooltip="C'est ma première fois ! Je crée mon compte ici.">Inscription</a>
            <?php endif; ?>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion. Tous droits réservés.</p>
    </footer>


</body>
</html>