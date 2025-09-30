<?php
// Démarrer la session
session_start();

// Redirection si l'utilisateur est déjà connecté
if (isset($_SESSION['utilisateur'])) {
    header('Location: index.php');
    exit();
}

// ----------------------------------------------------
// 1. PARAMÈTRES ET CONNEXION À LA BASE DE DONNÉES
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

$message = ''; // Variable pour stocker les messages d'erreur ou de succès

// ----------------------------------------------------
// 2. TRAITEMENT DU FORMULAIRE DE CONNEXION
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $login = trim(htmlspecialchars($_POST['login'] ?? ''));
    $pwd = $_POST['password'] ?? '';

    if (empty($login) || empty($pwd)) {
        $message = "<p style='color: red;'>Veuillez entrer votre login et votre mot de passe.</p>";
    } else {
        // A. Préparer la requête pour récupérer l'utilisateur par son login
        $sql_select = "SELECT id, login, prenom, nom, password FROM utilisateurs WHERE login = :login";
        $stmt = $pdo->prepare($sql_select);
        $stmt->execute(['login' => $login]);
        $utilisateur = $stmt->fetch(PDO::FETCH_ASSOC);

        // B. Vérifier si l'utilisateur existe ET si le mot de passe est correct
        if ($utilisateur && password_verify($pwd, $utilisateur['password'])) {
            
            // --- CONNEXION RÉUSSIE : INITIALISATION DE LA SESSION ---
            
            // 1. Stocker les informations pertinentes dans la session
            // NOTE: On ne stocke JAMAIS le mot de passe dans la session !
            $_SESSION['utilisateur'] = [
                'id' => $utilisateur['id'],
                'login' => $utilisateur['login'],
                'prenom' => $utilisateur['prenom'],
                'nom' => $utilisateur['nom']
            ];

            // 2. Rediriger vers la page d'accueil (index.php)
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
        /* Styles de base et mise en page (réutilisés) */
        body { font-family: Arial, Helvetica, sans-serif, sans-serif; margin: 0; background-color: #f4f4f4; display: flex; flex-direction: column; min-height: 100vh; }
        .contenu-principal { flex-grow: 1; padding: 20px; }
        
        /* Styles de la navigation et du footer (réutilisés) */
        header { background-color: #e0e0e0; padding: 10px 20px; border-bottom: 2px solid #ccc; display: flex; gap: 10px; }
        .navigation a { background-color: #007bff; color: yellow; text-decoration: none; padding: 10px; border-radius: 5px; display: inline-block; font-weight: bold; text-align: center; min-width: 80px; transition: background-color 0.3s; }
        .navigation a:hover { background-color: #0056b3; }
        
        footer { background-color: #333; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }
        
        /* Style spécifique au formulaire */
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
            background-color: #007bff; /* Bleu pour l'action de connexion */
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
            <a href="index.php">Accueil</a>
            <a href="connexion.php">Connexion</a>
            <a href="inscription.php">Inscription</a>
        </nav>
    </header>

    <div class="contenu-principal">
        <div class="form-container">
            <h1>Connexion</h1>

            <?php echo $message; // Afficher les messages de retour ?>

            <form action="connexion.php" method="post">
                
                <label for="login">Login :</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo htmlspecialchars($_POST['login'] ?? ''); ?>">

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>

                <input type="submit" value="Se connecter">
                
                <p style="text-align: center; margin-top: 15px;"><a href="inscription.php">Pas encore de compte ? Inscris-toi.</a></p>
            </form>
        </div>
    </div> 

    <footer>
        <nav class="navigation">
            <a href="index.php">Accueil</a>
            <a href="connexion.php">Connexion</a>
            <a href="inscription.php">Inscription</a>
        </nav>
        <p style="margin-top: 10px; font-size: 0.8em;">&copy; <?php echo date("Y"); ?> Module de Connexion.</p>
    </footer>

</body>
</html>