<?php

session_start();


if (isset($_SESSION['utilisateur'])) {
    header('Location: index.php');
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

$message = ''; // Variable pour stocker les messages d'erreur ou de succès


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $login = trim(htmlspecialchars($_POST['login'] ?? ''));
    $pwd = $_POST['password'] ?? '';

    if (empty($login) || empty($pwd)) {
        $message = "<p style='color: red;'>Veuillez entrer votre login et votre mot de passe.</p>";
    } else {
        // Prépare la requête pour récupérer l'utilisateur par son login
        $sql_select = "SELECT id, login, prenom, nom, password FROM utilisateurs WHERE login = :login";
        $stmt = $pdo->prepare($sql_select);
        $stmt->execute(['login' => $login]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifie si l'utilisateur existe eT si le mot de passe est correct
        if ($utilisateur && password_verify($pwd, $utilisateur['password'])) {
            
            // connexion de la session
            
            // Stocke les informations pertinentes dans la session
            
            $_SESSION['utilisateur'] = [
                'id' => $utilisateur['id'],
                'login' => $utilisateur['login'],
                'prenom' => $utilisateur['prenom'],
                'nom' => $utilisateur['nom']
            ];

            // Redirige vers la page d'accueil (index.php)
            header('Location: index.php');
            exit();

        } else {
            // Échec de la connexion
            $message = "<p style='color: red;'>Login ou mot de passe incorrect.</p>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion | Module de Connexion</title>
    <style>
        
        body { font-family: Arial, Helvetica, sans-serif, sans-serif; margin: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .contenu-principal { flex-grow: 1; padding: 20px; }
        
       
        header { background-color: #e0e0e0; padding: 10px 20px; border-bottom: 2px solid #ccc; display: flex; gap: 10px; }
        .navigation a { background-color: #007bff; color: yellow; text-decoration: none; padding: 10px; border-radius: 5px; display: inline-block; font-weight: bold; text-align: center; min-width: 80px; transition: background-color 0.3s; }
        .navigation a:hover { background-color: #0056b3; }
        
        
        
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

        
        .form-container { 
            max-width: 350px; 
            margin: 50px auto; 
            color: #0056b3;
            padding: 20px; 
            background: red; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        .form-container h1 { text-align: center; color: #eaf10de3; }
        
        /* Style du label (avec infobulle) */
        .form-container label { 
            display: block; 
            margin: 10px 0 5px; 
            font-weight: bold; 
            position: relative; /* CLÉ pour l'infobulle */
            cursor: help; /* Indique qu'on peut survoler */
        }
        
        .form-container label::after 
        {
            content: attr(data-tooltip); /* Affiche le texte */
            position: absolute;
            top: -45px; /* Positionnement au-dessus du label */
            left: 50%;
            transform: translateX(-50%); /* Centrage */
            
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

        footer { background-color: #037430; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }

        /* formulaire */
        .form-container { 
            max-width: 350px; 
            margin: 50px auto; 
            color: #0056b3;
            padding: 20px; 
            background: red; 
            border-radius: 8px; 
            box-shadow: 0 0 10px rgba(0,0,0,0.1); 
        }
        .form-container h1 { text-align: center; color: #eaf10de3; }
        .form-container label { display: block; margin: 10px 0 5px; font-weight: bold; }
        .form-container input[type="text"], 
        .form-container input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            background: yellow;
            border-radius: 4px;
            box-sizing: border-box;
        }
        .form-container input[type="submit"] {
            background-color: #007bff; 
            color: yellow;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
        }
        .form-container input[type="submit"]:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

    <header>
        <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="connexion.php" data-tooltip="Tu es ici, c'est l'entrée secrète.">Connexion</a>
            <a href="inscription.php" data-tooltip="Tu crées ton nouveau compte ici.">Inscription</a>
        </nav>
    </header>

    <div class="contenu-principal">
        <div class="form-container">
            <h1>Connexion</h1>

            <?php echo $message; // Afficher les messages de retour ?>

            <form action="connexion.php" method="post">
                
                <label for="login" data-tooltip="Ton nom d'utilisateur, celui que tu as choisi en t'inscrivant.">Login :</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">

                <label for="password" data-tooltip="Ton mot de passe secret. Chut !">Mot de passe :</label>
                <input type="password" id="password" name="password" required>

                <input type="submit" value="Se connecter">
                
                <p style="text-align: center; margin-top: 15px;"><a href="inscription.php">Pas encore de compte ? Inscris-toi.</a></p>
            </form>
        </div>
    </div> 

    <footer>
         <nav class="navigation">
            <a href="index.php" data-tooltip="C'est la maison ! Clique ici pour revenir au début.">Accueil</a>
            <a href="connexion.php" data-tooltip="Tu es ici, c'est l'entrée secrète.">Connexion</a>
            <a href="inscription.php" data-tooltip="Tu n'as pas de compte ? Clique ici.">Inscription</a>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion.</p>
    </footer>

</body>
</html>