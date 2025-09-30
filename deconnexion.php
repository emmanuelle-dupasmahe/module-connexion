<?php
// 1. Démarrer la session. 
session_start();

// 2. Supprimer toutes les variables de session.
$_SESSION = array();

// 3. Si l'on souhaite détruire complètement la session, 
// il faut aussi détruire le cookie de session.
// Note : Cela rendra obsolète le 'session_id' qui est dans le navigateur.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalement, détruire la session.
session_destroy();

// 5. Rediriger l'utilisateur vers la page d'accueil (index.php)
header('Location: index.php');
exit();
?>