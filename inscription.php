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
// 2. TRAITEMENT DU FORMULAIRE D'INSCRIPTION
// ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer et nettoyer les données du formulaire
    $login = trim(htmlspecialchars($_POST['login'] ?? ''));
    $prenom = trim(htmlspecialchars($_POST['prenom'] ?? ''));
    $nom = trim(htmlspecialchars($_POST['nom'] ?? ''));
    $pwd = $_POST['password'] ?? '';
    $pwd_confirm = $_POST['password_confirm'] ?? '';

    // Vérification de base des champs
    if (empty($login) || empty($prenom) || empty($nom) || empty($pwd) || empty($pwd_confirm)) {
        $message = "<p style='color: red;'>Veuillez remplir tous les champs.</p>";
    } elseif ($pwd !== $pwd_confirm) {
        $message = "<p style='color: red;'>Les mots de passe ne correspondent pas.</p>";
    } else {
        // A. Vérifier si le login existe déjà
        $sql_check = "SELECT id FROM utilisateurs WHERE login = :login";
        $stmt_check = $pdo->prepare($sql_check);
        $stmt_check->execute(['login' => $login]);
        
        if ($stmt_check->rowCount() > 0) {
            $message = "<p style='color: red;'>Ce login est déjà utilisé. Veuillez en choisir un autre.</p>";
        } else {
            // B. Hachage du mot de passe
            $hashed_password = password_hash($pwd, PASSWORD_DEFAULT);

            // C. Insertion du nouvel utilisateur
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
                // Optionnel: vider les champs après succès pour éviter la ré-insertion
                // $login = $prenom = $nom = ''; 
                
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
        
        /* Styles de la navigation (réutilisés de index.php) */
        header { background-color: #e0e0e0; padding: 10px 20px; border-bottom: 2px solid #ccc; display: flex; gap: 10px; }
        .navigation a { background-color: #007bff; color: yellow; text-decoration: none; padding: 10px; border-radius: 5px; display: inline-block; font-weight: bold; text-align: center; min-width: 80px; transition: background-color 0.3s; }
        .navigation a:hover { background-color: #0056b3; }
        
        /* Style du footer (réutilisé de index.php) */
        footer { background-color: #333; color: #fff; padding: 15px 20px; text-align: center; margin-top: auto; }
        footer nav { display: flex; justify-content: center; gap: 15px; }
        
        /* Style spécifique au formulaire */
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
            background-color: #1e17e9ff; /* bleu pour l'action d'inscription */
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
            <a href="index.php">Accueil</a>
            <a href="connexion.php">Connexion</a>
            <a href="inscription.php">Inscription</a>
        </nav>
    </header>

    <div class="contenu-principal">
        <div class="form-container">
            <h1>Créer un compte</h1>

            <?php echo $message; // Afficher les messages de retour ?>

            <form action="inscription.php" method="post">
                
                <label for="login">Login :</label>
                <input type="text" id="login" name="login" required 
                       value="<?php echo htmlspecialchars($login ?? ''); ?>">

                <label for="prenom">Prénom :</label>
                <input type="text" id="prenom" name="prenom" required
                       value="<?php echo htmlspecialchars($prenom ?? ''); ?>">

                <label for="nom">Nom :</label>
                <input type="text" id="nom" name="nom" required
                       value="<?php echo htmlspecialchars($nom ?? ''); ?>">

                <label for="password">Mot de passe :</label>
                <input type="password" id="password" name="password" required>

                <label for="password_confirm">Confirme le mot de passe :</label>
                <input type="password" id="password_confirm" name="password_confirm" required>

                <input type="submit" value="S'inscrire">
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