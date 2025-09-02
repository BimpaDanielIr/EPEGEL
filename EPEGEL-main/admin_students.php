<?php
// Fichier: admin_students.php

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
            $query = "SELECT u.id_utilisateur, u.nom, u.prenom, u.est_actif, e.numero_etudiant, e.classe FROM utilisateurs u JOIN etudiants e ON u.id_utilisateur = e.id_etudiant WHERE u.role = 'etudiant'";
            $params = [];
            $types = '';
            if (isset($_GET['q']) && !empty($_GET['q'])) {
                $q = '%' . $_GET['q'] . '%';
                $query .= " AND (u.nom LIKE ? OR u.prenom LIKE ? OR e.numero_etudiant LIKE ?)";
                $params = [$q, $q, $q];
                $types = 'sss';
            }
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $students = [];
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
            echo json_encode(['success' => true, 'students' => $students]);
            break;

        case 'get':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID manquant.');
            $stmt = $conn->prepare("SELECT u.id_utilisateur, u.nom, u.prenom, u.est_actif, e.numero_etudiant, e.classe FROM utilisateurs u JOIN etudiants e ON u.id_utilisateur = e.id_etudiant WHERE u.id_utilisateur = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $student = $result->fetch_assoc();
            echo json_encode(['success' => true, 'student' => $student]);
            break;
            
        case 'update':
            $id = $data['id'] ?? null;
            $nom = $data['nom'] ?? '';
            $prenom = $data['prenom'] ?? '';
            $numero_etudiant = $data['numero_etudiant'] ?? '';
            $classe = $data['classe'] ?? '';
            $est_actif = $data['est_actif'] ?? 0;
            if (!$id) throw new Exception('ID manquant.');

            $conn->begin_transaction();

            $stmt_user = $conn->prepare("UPDATE utilisateurs SET nom = ?, prenom = ?, est_actif = ? WHERE id_utilisateur = ?");
            $stmt_user->bind_param("ssii", $nom, $prenom, $est_actif, $id);
            $stmt_user->execute();
            $stmt_user->close();

            $stmt_student = $conn->prepare("UPDATE etudiants SET numero_etudiant = ?, classe = ? WHERE id_etudiant = ?");
            $stmt_student->bind_param("ssi", $numero_etudiant, $classe, $id);
            $stmt_student->execute();
            $stmt_student->close();

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Étudiant mis à jour avec succès.']);
            break;

        case 'delete':
            $id = $data['id'] ?? null;
            if (!$id) throw new Exception('ID manquant.');
            $conn->begin_transaction();
            $conn->query("DELETE FROM etudiants WHERE id_etudiant = $id");
            $conn->query("DELETE FROM utilisateurs WHERE id_utilisateur = $id");
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Étudiant supprimé avec succès.']);
            break;
        
        case 'create':
            $nom = $data['nom'] ?? '';
            $prenom = $data['prenom'] ?? '';
            $numero_etudiant = $data['numero_etudiant'] ?? '';
            $classe = $data['classe'] ?? '';
            $est_actif = $data['est_actif'] ?? 1;
            if (!$nom || !$prenom || !$numero_etudiant || !$classe) throw new Exception('Données manquantes.');

            $conn->begin_transaction();

            // Ajout d'un mot de passe par défaut
            $default_password = password_hash('password', PASSWORD_DEFAULT);

            $stmt_user = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, role, est_actif, matricule, mot_de_passe_hache) VALUES (?, ?, 'etudiant', ?, ?, ?)");
            $stmt_user->bind_param("ssiss", $nom, $prenom, $est_actif, $numero_etudiant, $default_password);
            $stmt_user->execute();
            $new_id = $conn->insert_id;
            $stmt_user->close();

            $stmt_student = $conn->prepare("INSERT INTO etudiants (id_etudiant, numero_etudiant, classe) VALUES (?, ?, ?)");
            $stmt_student->bind_param("iss", $new_id, $numero_etudiant, $classe);
            $stmt_student->execute();
            $stmt_student->close();

            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Étudiant ajouté avec succès.']);
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