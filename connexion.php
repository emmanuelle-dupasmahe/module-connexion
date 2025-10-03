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
    <link rel="stylesheet" href="/assets/css/module-connexion.css">
    
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