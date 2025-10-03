<?php

session_start();


if (!isset($_SESSION['utilisateur'])) {
    // Redirige vers la page de connexion si l'utilisateur n'est pas connecté
    header('Location: connexion.php');
    exit();
}

// Récupérer les informations de l'utilisateur depuis la session
$utilisateur_session = $_SESSION['utilisateur'];


// paramètres et connexion à la base de données

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

$message = ''; // Variable pour stocker les messages de retour (erreurs/succès)


//  formulaire de modification

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Récupére et nettoie les nouvelles données
    $new_login = trim(htmlspecialchars($_POST['login'] ?? ''));
    $new_prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $new_nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    
    $current_pwd = $_POST['current_password'] ?? '';
    $new_pwd = $_POST['new_password'] ?? '';
    $new_pwd_confirm = $_POST['new_password_confirm'] ?? '';

    $changement_effectue = false;

    // Mise à jour des informations (login, prénom, nom)
    if (
        $new_login !== $utilisateur_session['login'] || 
        $new_prenom !== $utilisateur_session['prenom'] || 
        $new_nom !== $utilisateur_session['nom']
    ) {
        // Vérification de l'unicité du NOUVEAU login si celui-ci a changé
        if ($new_login !== $utilisateur_session['login']) {
             $sql_check = "SELECT id FROM utilisateurs WHERE login = :login AND id != :id";
             $stmt_check = $pdo->prepare($sql_check);
             $stmt_check->execute(['login' => $new_login, 'id' => $utilisateur_session['id']]);
             
             if ($stmt_check->rowCount() > 0) {
                 $message .= "<p style='color: red;'>Le nouveau login est déjà utilisé. Veuillez en choisir un autre.</p>";
             }
        }
        
        // Si aucune erreur de login et les champs sont valides
        if (!strpos($message, 'login est déjà utilisé')) {
            $sql_update_info = "UPDATE utilisateurs SET login = :login, prenom = :prenom, nom = :nom WHERE id = :id";
            $stmt_update_info = $pdo->prepare($sql_update_info);
            $stmt_update_info->execute([
                'login' => $new_login,
                'prenom' => $new_prenom,
                'nom' => $new_nom,
                'id' => $utilisateur_session['id']
            ]);
            $changement_effectue = true;
        }
    }

    // Mise à jour du mot de passe
    if (!empty($new_pwd)) {
        if ($new_pwd !== $new_pwd_confirm) {
            $message .= "<p style='color: red;'>Les nouveaux mots de passe ne correspondent pas.</p>";
        } else {
            // Récupére le mot de passe actuel hashé pour vérification
            $sql_pwd = "SELECT password FROM utilisateurs WHERE id = :id";
            $stmt_pwd = $pdo->prepare($sql_pwd);
            $stmt_pwd->execute(['id' => $utilisateur_session['id']]);
            $hash_actuel = $stmt_pwd->fetchColumn();

            // Vérifie si le mot de passe actuel est correct
            if (!password_verify($current_pwd, $hash_actuel)) {
                 $message .= "<p style='color: red;'>Le mot de passe actuel saisi est incorrect.</p>";
            } else {
                // Hache et met à jour le nouveau mot de passe
                $new_hashed_pwd = password_hash($new_pwd, PASSWORD_DEFAULT);
                $sql_update_pwd = "UPDATE utilisateurs SET password = :password WHERE id = :id";
                $stmt_update_pwd = $pdo->prepare($sql_update_pwd);
                $stmt_update_pwd->execute([
                    'password' => $new_hashed_pwd,
                    'id' => $utilisateur_session['id']
                ]);
                $changement_effectue = true;
            }
        }
    }

    // Recharge la session et affiche le message final
    if ($changement_effectue && !strpos($message, 'red')) {
        // Recharge les données pour mettre à jour la session
        $sql_reload = "SELECT id, login, prenom, nom FROM utilisateurs WHERE id = :id";
        $stmt_reload = $pdo->prepare($sql_reload);
        $stmt_reload->execute(['id' => $utilisateur_session['id']]);
        $_SESSION['utilisateur'] = $stmt_reload->fetch(PDO::FETCH_ASSOC);
        
        // Met à jour la variable locale pour l'affichage
        $utilisateur_session = $_SESSION['utilisateur'];

        $message = "<p style='color: yellow;'>✅ Tes informations ont été mises à jour avec succès !</p>";
    } elseif ($changement_effectue && strpos($message, 'red')) {
        $message .= "<p style='color: yellow;'>⚠️ Certaines informations ont été mises à jour, mais le mot de passe ou le login n'a pas pu l'être en raison d'une erreur.</p>";
    } elseif (!$changement_effectue && empty($message)) {
        $message = "<p style='color: blue;'>Aucune modification n'a été soumise ou nécessaire.</p>";
    }
}

// Après le traitement du POST, récupére les données actuelles (mises à jour ou initiales)
$current_login = $utilisateur_session['login'];
$current_prenom = $utilisateur_session['prenom'];
$current_nom = $utilisateur_session['nom'];

// Variables pour le header/footer (pour les liens de navigation)
$estConnecte = true;
$loginUtilisateur = $current_login; 
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil | Module de Connexion</title>
    <link rel="stylesheet" href="/assets/css/module-connexion.css">
    
</head>
<body>

    <header>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="profil.php" data-tooltip="Modifie ton nom ou ton mot de passe ici.">Profil</a>
            <?php if ($loginUtilisateur === 'admin'): ?>
                <a href="admin.php" data-tooltip="Le bureau du chef ! Ici, tu vois tous les membres.">Admin</a>
            <?php endif; ?>
            <a href="deconnexion.php" data-tooltip="Tu pars ! Clique ici pour te déconnecter en toute sécurité.">Déconnexion</a>
        </nav>
    </header>

    <div class="contenu-principal">
        <div class="form-container">
            <h1>Mon Profil : <?php echo $current_login; ?></h1>

            <?php echo $message; // Affiche les messages de retour ?>

            <form action="profil.php" method="post">
                
                <h2>Informations personnelles</h2>
                <label for="login" data-tooltip="Ton nom d'utilisateur. Tu peux le changer ici.">Login :</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo htmlspecialchars($current_login); ?>">

                <label for="prenom" data-tooltip="Ton prénom. Tu peux le changer ici.">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required
                       value="<?php echo htmlspecialchars($current_prenom); ?>">

                <label for="nom" data-tooltip="Ton nom de famille. Tu peux le changer ici.">Nom :</label>
                <input type="text" id="nom" name="nom" required
                       value="<?php echo htmlspecialchars($current_nom); ?>">
                
                <div class="section-separator">
                    <h2>Changer le mot de passe</h2>
                    <p style="font-size: 0.9em; color: #e3cf1eff;">* Remplis ces champs uniquement si tu souhaites changer de mot de passe.</p>

                    <label for="current_password" data-tooltip="Ton mot de passe secret.">Mot de passe actuel :</label>
                    <input type="password" id="current_password" name="current_password">
                    
                    <label for="new_password" data-tooltip="Ton nouveau mot de passe secret.">Nouveau mot de passe :</label>
                    <input type="password" id="new_password" name="new_password">

                    <label for="new_password_confirm" data-tooltip="Écris ton nouveau mot de passe encore une fois.">Confirme le nouveau mot de passe :</label>
                    <input type="password" id="new_password_confirm" name="new_password_confirm">
                </div>

                <input type="submit" value="Mettre à jour mon profil">
            </form>
        </div>
    </div> 

    <footer>
         <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="profil.php" data-tooltip="Modifie ton nom ou ton mot de passe ici.">Profil</a>
            <?php if ($loginUtilisateur === 'admin'): ?>
                <a href="admin.php" data-tooltip="Le bureau du chef ! Ici, tu vois tous les membres.">Admin</a>
            <?php endif; ?>
            <a href="deconnexion.php" data-tooltip="Tu pars ! Clique ici pour te déconnecter en toute sécurité.">Déconnexion</a>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion.</p>
    </footer>

</body>
</html>