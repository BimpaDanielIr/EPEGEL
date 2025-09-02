<?php
// Fichier: logout.php
// Déconnecte l'utilisateur en détruisant la session.

session_start();
session_unset();
session_destroy();
setcookie(session_name(), '', time() - 3600, '/');

header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>