<?php

session_start();

// ----------------------------------------------------
// 1. VÉRIFICATION DES DROITS D'ADMINISTRATION (PROTECTION)
// ----------------------------------------------------
// Rediriger si l'utilisateur n'est pas connecté OU n'est pas l'admin
if (!isset($_SESSION['utilisateur']) || $_SESSION['utilisateur']['login'] !== 'admin') {
    header('Location: index.php'); // Rediriger vers l'accueil ou la connexion
    exit();
}

// ----------------------------------------------------
// 2. PARAMÈTRES ET CONNEXION À LA BASE DE DONNÉES
// ----------------------------------------------------
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

// ----------------------------------------------------
// 3. TRAITEMENT DES ACTIONS (SUPPRESSION/MODIFICATION)
// ----------------------------------------------------

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $id_a_supprimer = (int)$_GET['id'];
    
    // Empêcher la suppression du compte admin lui-même
    if ($id_a_supprimer === $_SESSION['utilisateur']['id']) {
        $message = "<p style='color: red;'>⚠️ Tu ne peux pas supprimer ton propre compte administrateur.</p>";
    } else {
        $sql_delete = "DELETE FROM utilisateurs WHERE id = :id";
        $stmt_delete = $pdo->prepare($sql_delete);
        $stmt_delete->execute(['id' => $id_a_supprimer]);
        
        if ($stmt_delete->rowCount() > 0) {
            $message = "<p style='color: green;'>✅ Utilisateur ID " . $id_a_supprimer . " supprimé avec succès.</p>";
        } else {
            $message = "<p style='color: orange;'>Utilisateur non trouvé ou déjà supprimé.</p>";
        }
    }
}

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['modifier_utilisateur'])) {
    
    $id_a_modifier = (int)($_POST['user_id'] ?? 0);
    $new_login = trim(htmlspecialchars($_POST['login'] ?? ''));
    $new_prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $new_nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    
    if (empty($new_login) || empty($new_prenom) || empty($new_nom)) {
         $message = "<p style='color: red;'>Tous les champs de modification doivent être remplis.</p>";
    } else {
        // Vérification de l'unicité du login
        $sql_check = "SELECT id FROM utilisateurs WHERE login = :login AND id != :id";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['login' => $new_login, 'id' => $id_a_modifier]);
        
        if ($stmt_check->rowCount() > 0) {
            $message = "<p style='color: red;'>Le login '{$new_login}' est déjà utilisé par un autre utilisateur.</p>";
        } else {
            // Mise à jour de l'utilisateur
            $sql_update = "UPDATE utilisateurs SET login = :login, prenom = :prenom, nom = :nom WHERE id = :id";
            $stmt_update = $pdo->prepare($sql_update);
            $stmt_update->execute([
                'login' => $new_login,
                'prenom' => $new_prenom,
                'nom' => $new_nom,
                'id' => $id_a_modifier
            ]);
            $message = "<p style='color: green;'>✅ Utilisateur ID " . $id_a_modifier . " mis à jour avec succès.</p>";
        }
    }
}


// ----------------------------------------------------
// 4. RÉCUPÉRATION DE LA LISTE DES UTILISATEURS
// ----------------------------------------------------
$sql_users = "SELECT id, login, prenom, nom FROM utilisateurs ORDER BY id";
$stmt_users = $pdo->query($sql_users);
$utilisateurs = $stmt_users->fetchAll(PDO::FETCH_ASSOC);

// Déterminer si un formulaire de modification doit être affiché
$user_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] === 'editer' && isset($_GET['id'])) {
    $id_a_editer = (int)$_GET['id'];
    $sql_edit = "SELECT id, login, prenom, nom FROM utilisateurs WHERE id = :id";
    $stmt_edit = $pdo->prepare($sql_edit);
    $stmt_edit->execute(['id' => $id_a_editer]);
    $user_to_edit = $stmt_edit->fetch(PDO::FETCH_ASSOC);
}

// Variables pour le header/footer
$estConnecte = true;
$loginUtilisateur = $_SESSION['utilisateur']['login']; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration | Gestion des Utilisateurs</title>
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
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background-color: yellow; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        th, td { border: 1px solid #1b1be2ff; padding: 12px; text-align: left; }
        th { background-color: red; color: yellow; }
        
        .action-link {
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            margin-right: 5px;
            font-size: 0.9em;
            font-weight: bold;
        }
        .editer { background-color: blue; color: yellow; }
        .editer:hover { background-color: #1e097dff; }
        .supprimer { background-color: red; color: yellow; }
        .supprimer:hover { background-color: #928b0dff; }

        /* Style pour le formulaire de modification (s'il est affiché) */
        .edit-form-container {
            max-width: 500px; 
            margin: 20px auto; 
            padding: 20px; 
            background: red; 
            border: 1px solid #e2e211ff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        .edit-form-container input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #2121d3ff;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: yellow;
        }
        .edit-form-container input[type="submit"] {
            background-color: #007bff;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }
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

        <?php echo $message; // Afficher les messages de retour ?>
        
        <?php if ($user_to_edit): ?>
            <div class="edit-form-container">
                <h2>Modification de l'utilisateur : ID <?php echo $user_to_edit['id']; ?></h2>
                <form action="admin.php" method="post">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_to_edit['id']); ?>">
                    <input type="hidden" name="modifier_utilisateur" value="1">
                    
                    <label for="login">Login :</label>
                    <input type="text" id="login" name="login" required 
                           value="<?php echo htmlspecialchars($user_to_edit['login']); ?>">

                    <label for="prenom">Prénom :</label>
                    <input type="text" id="prenom" name="prenom" required
                           value="<?php echo htmlspecialchars($user_to_edit['prenom']); ?>">

                    <label for="nom">Nom :</label>
                    <input type="text" id="nom" name="nom" required
                           value="<?php echo htmlspecialchars($user_to_edit['nom']); ?>">

                    <input type="submit" value="Enregistrer les modifications">
                    <p style="text-align: center; margin-top: 10px;"><a href="admin.php">Annuler la modification</a></p>
                </form>
            </div>
        <?php endif; ?>

        <h2>Liste des Utilisateurs</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Login</th>
                    <th>Prénom</th>
                    <th>Nom</th>
                    <th>Actions</th>
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
                            <td>
                                <a href="admin.php?action=editer&id=<?php echo $user['id']; ?>" class="action-link editer">Éditer</a>
                                <a href="admin.php?action=supprimer&id=<?php echo $user['id']; ?>" 
                                   class="action-link supprimer" 
                                   onclick="return confirm('Êtes-vous sûr de vouloir supprimer l\'utilisateur <?php echo htmlspecialchars($user['login']); ?> ?')">
                                    Supprimer
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="5" style="text-align: center;">Aucun utilisateur trouvé.</td></tr>
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