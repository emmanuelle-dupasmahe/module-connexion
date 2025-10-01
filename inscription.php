<?php

session_start();

// Redirection si l'utilisateur est déjà connecté
if (isset($_SESSION['utilisateur'])) {
    header('Location: index.php');
    exit();
}


//paramètres et connexion à la base de données

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

$message = ''; // Variable pour stocker les messages d'erreur ou de succès


// traitement du formulaire d'inscription

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupére et nettoie les données du formulaire
    $login = trim(htmlspecialchars($_POST['login'] ?? ''));
    $prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $pwd = $_POST['password'] ?? '';
    $pwd_confirm = $_POST['password_confirm'] ?? '';

    // Vérification de base des champs
    if (empty($login) || empty($prenom) || empty($nom) || empty($pwd) || empty($pwd_confirm)) {
        $message = "<p style='color: red;'>Il faut remplir tous les champs.</p>";
    } elseif ($pwd !== $pwd_confirm) {
        $message = "<p style='color: red;'>Les mots de passe ne correspondent pas.</p>";
    } else {
        // Vérifie si le login existe déjà
        $sql_check = "SELECT id FROM utilisateurs WHERE login = :login";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['login' => $login]);
        
        if ($stmt_check->rowCount() > 0) {
            $message = "<p style='color: red;'>Ce login est déjà utilisé. Tu dois en choisir un autre.</p>";
        } else {
            // Hachage du mot de passe
            $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);

            // Insertion du nouvel utilisateur
            $sql_insert = "INSERT INTO utilisateurs (login, prenom, nom, password) VALUES (:login, :prenom, :nom, :password)";
            $stmt_insert = $pdo->prepare($sql_insert);
            
            try {
                $stmt_insert->execute([
                    'login' => $login,
                    'prenom' => $prenom,
                    'nom' => $nom,
                    'password' => $hashed_password
                ]);

                $message = "<p style='color: yellow;'>🎉 Ton compte a été créé avec succès ! Tu peux maintenant te <a href='connexion.php'>connecter</a>.</p>";
                
                
            } catch (PDOException $e) {
                $message = "<p style='color: red;'>Erreur lors de l'inscription : " . $e->getMessage() . "</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription | Module de Connexion</title>
    <link rel="stylesheet" href="style.css"> <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .contenu-principal { flex-grow: 1; padding: 20px; }
        
        
        header { background-color: #e0e0e0; padding: 10px 20px; border-bottom: 2px solid #ccc; display: flex; gap: 10px; }
        

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
        
        
        footer { background-color: #037430; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }
        
        /* Style du formulaire */
        .form-container { 
            max-width: 400px; 
            margin: 50px auto; 
            color: #0056b3;
            padding: 20px; 
            background: red; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(101, 9, 9, 0.1); 
        }
        .form-container h1 { text-align: center; color: #efeb0fff; }
        .form-container label { display: block; margin: 10px 0 5px; font-weight: bold; }
        .form-container input[type="text"], 
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            background: yellow;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container input[type="submit"] {
            background-color: #1e17e9ff; 
            color:yellow;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
        }
        .form-container input[type="submit"]:hover {
            background-color: #201e7eff;
        }

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
        
        
        footer { background-color: #037430; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }
        
        /* Style du formulaire */
        .form-container { 
            max-width: 400px; 
            margin: 50px auto; 
            color: #0056b3;
            padding: 20px; 
            background: red; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(101, 9, 9, 0.1); 
        }
        .form-container h1 { text-align: center; color: #efeb0fff; }
        .form-container label 
        { 
            display: block; 
            margin: 10px 0 5px; 
            font-weight: bold; 
            position: relative; /* permet l'affichage de l'infobulle */
            cursor: help;
        }

        .form-container label::after 
        {
        content: attr(data-tooltip);
        position: absolute;
        top: -35px; /* Positionnement au-dessus du label */
        left: 10px;
    
        background-color: #20d03aff; /* Nouvelle couleur pour les distinguer, par exemple Orange */
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
        .form-container input[type="text"], 
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            background: yellow;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container input[type="submit"] {
            background-color: #1e17e9ff; 
            color:yellow;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
        }
        .form-container input[type="submit"]:hover {
            background-color: #201e7eff;
        }
    </style>
</head>
<body>

    <header>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="connexion.php" data-tooltip="Tu as déjà ton mot de passe ! Tu rentres dans le site.">Connexion</a>
            <a href="inscription.php" data-tooltip="C'est ta première fois ! Tu crées ton compte ici.">Inscription</a>
        </nav>
    </header>

    <div class="contenu-principal">
        <div class="form-container">
            <h1>Créer un compte</h1>

            <?php echo $message; // Afficher les messages de retour ?>

            <form action="inscription.php" method="post">
                
                <label for="login" data-tooltip="Ton nom d'utilisateur, celui que tu utiliseras pour te connecter !">Login :</label>
                <input type="text" id="login" name="login" required 
                        value="<?php echo htmlspecialchars($login ?? ''); ?>"> 

                <label for="prenom" data-tooltip="Écris ton prénom ici.">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required
                        value="<?php echo htmlspecialchars($prenom ?? ''); ?>">

                <label for="nom" data-tooltip="Écris ton nom de famille ici.">Nom :</label>
                <input type="text" id="nom" name="nom" required
                        value="<?php echo htmlspecialchars($nom ?? ''); ?>"> 

                <label for="password" data-tooltip="Choisis un mot de passe secret (garde-le bien !)"> Mot de passe :</label>
                <input type="password" id="password" name="password" required >

                <label for="password_confirm" data-tooltip="Écris ton mot de passe secret une deuxième fois.">Confirme le mot de passe :</label>
                <input type="password" id="password_confirm" name="password_confirm" required>

                <input type="submit" value="S'inscrire">
            </form>
        </div>
    </div> 

    <footer>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="connexion.php" data-tooltip="Tu as déjà ton mot de passe.">Connexion</a>
            <a href="inscription.php" data-tooltip="Tu es ici, tu crées ton compte !">Inscription</a>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion.</p>
    </footer>

</body>
</html>