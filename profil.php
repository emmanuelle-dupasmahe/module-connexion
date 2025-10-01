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
    <style>
        
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .contenu-principal { flex-grow: 1; padding: 20px; }
        
        header { background-color: #e0e0e0; padding: 10px 20px; border-bottom: 2px solid #ccc; display: flex; gap: 10px; }
        .navigation a { background-color: #007bff; color: yellow; text-decoration: none; padding: 10px; border-radius: 5px; display: inline-block; font-weight: bold; text-align: center; min-width: 80px; transition: background-color 0.3s; }
        .navigation a:hover { background-color: #0056b3; }
        
        footer { background-color: #037430; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }
        
        /* formulaire de profil */
        .form-container { 
            max-width: 500px; 
            margin: 30px auto;
            color: blue; 
            padding: 25px; 
            background: red; 
            border-radius: 8px; 
            box-shadow: 0 0 15px rgba(0,0,0,0.1); 
        }
        .form-container h1 { text-align: center; color: #dceb0ce7; margin-bottom: 20px; }
        .form-container label { display: block; margin: 10px 0 5px; font-weight: bold; }
        .form-container input:not([type="submit"]) {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
            background-color: yellow;
        }
        .navigation a { 
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
            position: relative; /* CLÉ pour l'infobulle */
        }
        .navigation a:hover { background-color: #0056b3; }
        
        
        .navigation a::after {
            content: attr(data-tooltip); 
            position: absolute;
            left: 50%;
            transform: translateX(-50%); 
            background-color: #5cb85c; 
            color: white;
            padding: 8px 12px;
            border-radius: 8px; 
            font-size: 0.8em;
            white-space: nowrap; 
            z-index: 10; 
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }

        
        .navigation a::before {
            content: '';
            position: absolute;
            left: 50%;
            border-width: 5px;
            border-style: solid;
            z-index: 10;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }
        
        
        header .navigation a::after {
            bottom: auto; 
            top: 130%;
        }
        
        header .navigation a::before {
            bottom: auto; 
            top: 130%;
            border-color: transparent transparent #5cb85c transparent; 
            transform: translateX(-50%) translateY(-13px); 
        }
        
        
        footer .navigation a::after {
            bottom: 150%; 
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

        footer { background-color: #037430; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }
        
        /* formulaire de profil */
        .form-container { 
            max-width: 500px; 
            margin: 30px auto;
            color: blue; 
            padding: 25px; 
            background: red; 
            border-radius: 8px; 
            box-shadow: 0 0 15px rgba(0,0,0,0.1); 
        }
        .form-container h1 { text-align: center; color: #dceb0ce7; margin-bottom: 20px; }
        
       
        .form-container label { 
            display: block; 
            margin: 10px 0 5px; 
            font-weight: bold; 
            position: relative; 
            cursor: help;
        }
        
        .form-container label::after 
        {
            content: attr(data-tooltip); 
            position: absolute;
            top: -45px; 
            left: 50%;
            transform: translateX(-50%); 
            
            background-color: #20d03aff; 
            color: white;
            padding: 6px 10px;
            border-radius: 6px; 
            font-size: 0.75em;
            white-space: nowrap; 
            z-index: 10; 
            
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s, visibility 0.3s;
        }

        .form-container label:hover::after {
            visibility: visible;
            opacity: 1;
        }

        .form-container input[type="submit"] {
            background-color: #2f37d8ff; 
            color: yellow;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            margin-top: 20px;
        }
        .form-container input[type="submit"]:hover {
            background-color: #08165fff;
        }
        .section-separator {
            border-top: 1px solid #f1e50bff;
            padding-top: 20px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <header>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="profil.php" data-tooltip="Modifie ton nom ou ton mot de passe ici.">Profil</a>
            <?php if ($loginUtilisateur === 'admin'): ?>
                <a href="admin.php" data-tooltip="Attention ! Cette page est réservée au super-utilisateur.">Admin</a>
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
                <a href="admin.php" data-tooltip="Cette page est réservée au super-utilisateur.">Admin</a>
            <?php endif; ?>
            <a href="deconnexion.php" data-tooltip="Tu pars ! Clique ici pour te déconnecter en toute sécurité.">Déconnexion</a>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion.</p>
    </footer>

</body>
</html>