<?php
// Fichier: admin_teachers.php

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrateur') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

require_once 'db_connect.php';

$action = $_GET['action'] ?? null;
$data = json_decode(file_get_contents("php://input"), true) ?? [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $data['action'] ?? $action;
}

try {
    switch ($action) {
        case 'list':
            $query = "SELECT u.id_utilisateur, u.nom, u.prenom, u.est_actif, e.matiere_enseignee FROM utilisateurs u JOIN enseignants e ON u.id_utilisateur = e.id_enseignant WHERE u.role = 'enseignant'";
            $params = [];
            $types = '';
            if (isset($_GET['q']) && !empty($_GET['q'])) {
                $q = '%' . $_GET['q'] . '%';
                $query .= " AND (u.nom LIKE ? OR u.prenom LIKE ?)";
                $params = [$q, $q];
                $types = 'ss';
            }
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $teachers = [];
            while ($row = $result->fetch_assoc()) {
                $teachers[] = $row;
            }
            echo json_encode(['success' => true, 'teachers' => $teachers]);
            break;
            
        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID manquant.');
            $stmt = $conn->prepare("SELECT u.id_utilisateur, u.nom, u.prenom, u.est_actif, e.matiere_enseignee FROM utilisateurs u JOIN enseignants e ON u.id_utilisateur = e.id_enseignant WHERE u.id_utilisateur = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $teacher = $result->fetch_assoc();
            echo json_encode(['success' => true, 'teacher' => $teacher]);
            break;

        case 'update':
            $id = $data['id'] ?? null;
            $nom = $data['nom'] ?? '';
            $prenom = $data['prenom'] ?? '';
            $matiere_enseignee = $data['matiere_enseignee'] ?? '';
            $est_actif = $data['est_actif'] ?? 0;
            if (!$id) throw new Exception('ID manquant.');

            $conn->begin_transaction();

            $stmt_user = $conn->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, est_actif = ? WHERE id_utilisateur = ?");
            $stmt_user->bind_param("ssii", $nom, $prenom, $est_actif, $id);
            $stmt_user->execute();
            $stmt_user->close();

            $stmt_teacher = $conn->prepare("UPDATE enseignants SET matiere_enseignee = ? WHERE id_enseignant = ?");
            $stmt_teacher->bind_param("si", $matiere_enseignee, $id);
            $stmt_teacher->execute();
            $stmt_teacher->close();

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Enseignant mis à jour avec succès.']);
            break;

        case 'delete':
            $id = $data['id'] ?? null;
            if (!$id) throw new Exception('ID manquant.');
            $conn->begin_transaction();
            $conn->query("DELETE FROM enseignants WHERE id_enseignant = $id");
            $conn->query("DELETE FROM utilisateurs WHERE id_utilisateur = $id");
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Enseignant supprimé avec succès.']);
            break;
        
        case 'create':
            $nom = $data['nom'] ?? '';
            $prenom = $data['prenom'] ?? '';
            $matiere_enseignee = $data['matiere_enseignee'] ?? '';
            $est_actif = $data['est_actif'] ?? 1;
            if (!$nom || !$prenom || !$matiere_enseignee) throw new Exception('Données manquantes.');

            $conn->begin_transaction();

            // Ajout d'un mot de passe par défaut
            $default_password = password_hash('password', PASSWORD_DEFAULT);

            $stmt_user = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, role, est_actif, mot_de_passe_hache) VALUES (?, ?, 'enseignant', ?, ?)");
            $stmt_user->bind_param("ssis", $nom, $prenom, $est_actif, $default_password);
            $stmt_user->execute();
            $new_id = $conn->insert_id;
            $stmt_user->close();

            $stmt_teacher = $conn->prepare("INSERT INTO enseignants (id_enseignant, matiere_enseignee) VALUES (?, ?)");
            $stmt_teacher->bind_param("is", $new_id, $matiere_enseignee);
            $stmt_teacher->execute();
            $stmt_teacher->close();

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Enseignant ajouté avec succès.']);
            break;
        
        default:
            echo json_encode(['success' => false, 'message' => 'Action non valide.']);
            break;
    }
} catch (Exception $e) {
    if ($conn->in_transaction) $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur du serveur: ' . $e->getMessage()]);
}

$conn->close();
?>