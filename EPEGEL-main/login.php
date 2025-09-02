<?php
// Fichier: login.php
header('Content-Type: application/json');
session_start();
require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->identifier) || !isset($data->password)) {
    echo json_encode(['success' => false, 'message' => 'Identifiant ou mot de passe manquant.']);
    exit();
}

$identifier = $data->identifier;
$password = $data->password;

try {
    // Recherche par matricule étudiant
    $stmt = $conn->prepare("SELECT u.id_utilisateur, u.nom, u.prenom, u.mot_de_passe_hache, u.role FROM utilisateurs u JOIN etudiants e ON u.id_utilisateur = e.id_etudiant WHERE e.numero_etudiant = ? AND u.est_actif = 1");
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['mot_de_passe_hache'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            echo json_encode([
                'success' => true,
                'user' => [
                    'name' => $_SESSION['user_name'],
                    'role' => $user['role']
                ]
            ]);
            exit();
        }
    }

    // Recherche par nom ou prénom (enseignant ou administrateur)
    $stmt_admin = $conn->prepare("SELECT id_utilisateur, nom, prenom, mot_de_passe_hache, role FROM utilisateurs WHERE (nom = ? OR prenom = ?) AND est_actif = 1 AND (role = 'administrateur' OR role = 'enseignant')");
    $stmt_admin->bind_param("ss", $identifier, $identifier);
    $stmt_admin->execute();
    $result_admin = $stmt_admin->get_result();

    if ($result_admin->num_rows > 0) {
        $user = $result_admin->fetch_assoc();
        // La vérification du mot de passe doit être DANS la condition
        if (password_verify($password, $user['mot_de_passe_hache'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id_utilisateur'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['user_name'] = $user['prenom'] . ' ' . $user['nom'];
            echo json_encode(['success' => true, 'user' => ['name' => $_SESSION['user_name'], 'role' => $user['role']]]);
            exit();
        }
    }

    echo json_encode(['success' => false, 'message' => 'Identifiants incorrects.']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur du serveur.']);
}

$conn->close();
?>