<?php

session_start();


$_SESSION = array();

// Pour détruire complètement la session, 
// il faut aussi détruire le cookie de session.

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// destruction de la session.
session_destroy();

// vers la page d'accueil (index.php)
header('Location: index.php');
exit();
?>