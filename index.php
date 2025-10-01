<?php
// Démarrer la session PHP
session_start();

// Paramètres de connexion à la base de données
$host = 'localhost'; 
$dbname = 'moduleconnexion';
$username = 'root'; // À adapter si nécessaire
$password = ''; // À adapter si nécessaire

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
            transition: background-color 0.3s; /* Transition pour un effet plus doux */
        }
        
        .navigation a:hover {
            background-color: #0056b3; /* Bleu plus foncé au survol */
        }

        /* Style du titre principal dans le rectangle rouge */
        .titre-accueil {
            background-color: red; 
            padding: 20px;
            text-align: center;
            border-radius: 10px;
            margin-bottom: 20px;
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
            padding: 15px; 
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
            <a href="index.php">Accueil</a>
            <?php if ($estConnecte): ?>
                <a href="profil.php">Profil</a>
                <?php if ($loginUtilisateur === 'admin'): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
                <a href="inscription.php">Inscription</a>
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
                <p>
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
            <a href="index.php">Accueil</a>
            <?php if ($estConnecte): ?>
                <a href="profil.php">Profil</a>
                <?php if ($loginUtilisateur === 'admin'): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="deconnexion.php">Déconnexion</a>
            <?php else: ?>
                <a href="connexion.php">Connexion</a>
                <a href="inscription.php">Inscription</a>
            <?php endif; ?>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion. Tous droits réservés.</p>
    </footer>

</body>
</html>