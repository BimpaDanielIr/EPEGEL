<?php
// Fichier: check_session.php
// Vérifie si une session utilisateur est active.

header('Content-Type: application/json');
session_start();

if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
    echo json_encode([
        'isLoggedIn' => true,
        'user' => [
            'name' => $_SESSION['user_name'],
            'role' => $_SESSION['role']
        ]
    ]);
} else {
    echo json_encode(['isLoggedIn' => false]);
}
?>