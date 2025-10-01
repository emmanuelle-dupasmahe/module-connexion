<?php

session_start();


// VÉRIFICATION DES DROITS D'ADMINISTRATION (PROTECTION)

// Redirection si l'utilisateur n'est pas connecté ou n'est pas l'admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['login'] !== 'admin') {
    header('Location: index.php'); // Redirection vers l'accueil ou la connexion
    exit();
}


//  PARAMÈTRES ET CONNEXION À LA BASE DE DONNÉES

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

// LA LISTE DES UTILISATEURS

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
    <style>
        /* Styles réutilisés de index.php */
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .contenu-principal { flex-grow: 1; padding: 20px; }
        
        header { background-color: #e0e0e0; padding: 10px 20px; border-bottom: 2px solid #ccc; display: flex; gap: 10px; }
        .navigation a { background-color: #007bff; color: yellow; text-decoration: none; padding: 10px; border-radius: 5px; display: inline-block; font-weight: bold; text-align: center; min-width: 80px; transition: background-color 0.3s; }
        .navigation a:hover { background-color: #0056b3; }
        
        footer { background-color: #333; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }

        h2 { color: blue; }
        h1 { color: blue; text-align: center; }
        table { width: 100%;border-collapse: separate; margin-top: 20px; border-spacing: 0 5px; box-shadow: 0 0 20px rgba(11, 7, 214, 0.1); border-radius: 10px; overflow: hidden;}
        th, td { border: 2px solid #1b1be2ff; background-color: yellow; padding: 12px; text-align: left; border-radius: 8px;}
        th { background-color: red; color: yellow; }
    </style>
</head>
<body>

    <header>
        <nav class="navigation">
            <a href="index.php">Accueil</a>
            <a href="profil.php">Profil</a>
            <a href="admin.php">Admin</a>
            <a href="deconnexion.php">Déconnexion</a>
        </nav>
    </header>

    <div class="contenu-principal">
        <h1>Espace d'Administration</h1>

        <?php echo $message; ?>
        
        <h2>Liste des Utilisateurs</h2>
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
            <a href="index.php">Accueil</a>
            <a href="profil.php">Profil</a>
            <a href="admin.php">Admin</a>
            <a href="deconnexion.php">Déconnexion</a>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion.</p>
    </footer>

</body>
</html>