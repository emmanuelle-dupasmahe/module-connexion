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
    <link rel="stylesheet" href="/assets/css/module-connexion.css">
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
        <a href="connexion.php" data-tooltip="Tu as déjà ton mot de passe ! Tu rentres dans le site.">Connexion</a>
        
        <a href="inscription.php" data-tooltip="C'est ta première fois ! Tu crées ton compte ici.">Inscription</a>
    <?php endif; ?>
</nav>
    </header>
    
    <div class="contenu-principal">
        <div class="titre-accueil">
        <img src="/assets/images/module-connexion.png" alt="Logo Connexion Enfant" class="logo-titre">
        <h1>Bienvenue sur ton site de connexion</h1>
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
                <a href="connexion.php" data-tooltip="Tu as déjà ton mot de passe ! Tu peux rentrer dans le site.">Connexion</a>
                
                <a href="inscription.php" data-tooltip="C'est ta première fois ! Tu crées ton compte ici.">Inscription</a>
            <?php endif; ?>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion. Tous droits réservés.</p>
    </footer>


</body>
</html>