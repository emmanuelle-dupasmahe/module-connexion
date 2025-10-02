<?php

session_start();




// Redirection si l'utilisateur n'est pas connecté ou n'est pas l'admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['login'] !== 'admin') {
    header('Location: index.php'); // Redirection vers l'accueil ou la connexion
    exit();
}



$host = 'localhost'; 
$dbname = 'moduleconnexion';
$username = 'root'; 
$password = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}

$message = ''; // Variable pour stocker les messages de retour 

// la liste des utilisateurs

$sql_users = "SELECT id, login, prenom, nom FROM utilisateurs ORDER BY id";
$stmt_users = $pdo->query($sql_users);
$utilisateurs = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Variables pour le header/footer
$estConnecte = true;
$loginUtilisateur = $_SESSION['utilisateur']['login']; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration | Liste des utilisateurs</title>
    <link rel="stylesheet" href="/assets/css/module-connexion.css">
    
</head>
<body>

    <header>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="profil.php" data-tooltip="Modifie ton nom ou ton mot de passe.">Profil</a>
            <a href="admin.php" data-tooltip="Le bureau du chef ! Ici, tu vois tous les membres.">Admin</a>
            <a href="deconnexion.php" data-tooltip="Tu pars ! Clique ici pour te déconnecter en toute sécurité.">Déconnexion</a>
        </nav>
    </header>

    <div class="contenu-principal">
        <h1>Espace d'Administration</h1>

        <?php echo $message; ?>
        
        <h2>Liste des Utilisateurs</h2>
        <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($utilisateurs)): ?>
                    <?php foreach ($utilisateurs as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['id']); ?></td>
                            <td><?php echo htmlspecialchars($user['login']); ?></td>
                            <td><?php echo htmlspecialchars($user['prenom']); ?></td>
                            <td><?php echo htmlspecialchars($user['nom']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="4" style="text-align: center;">Aucun utilisateur trouvé.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

    </div> 

    <footer>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="profil.php" data-tooltip="Modifie ton nom ou ton mot de passe.">Profil</a>
            <a href="admin.php" data-tooltip="Le bureau du chef ! Ici, tu vois tous les membres.">Admin</a>
            <a href="deconnexion.php" data-tooltip="Tu pars ! Clique ici pour te déconnecter en toute sécurité.">Déconnexion</a>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion.</p>
    </footer>

</body>
</html>