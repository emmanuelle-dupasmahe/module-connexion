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
    <link rel="stylesheet" href="/assets/css/module-connexion.css">
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