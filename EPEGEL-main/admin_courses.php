<?php
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
            $query = "SELECT c.id_cours, c.titre, u.nom, u.prenom, c.id_enseignant FROM cours c LEFT JOIN enseignants e ON c.id_enseignant = e.id_enseignant LEFT JOIN utilisateurs u ON e.id_enseignant = u.id_utilisateur";
            $params = [];
            $types = '';
            if (isset($_GET['q']) && !empty($_GET['q'])) {
                $q = '%' . $_GET['q'] . '%';
                $query .= " WHERE c.titre LIKE ?";
                $params = [$q];
                $types = 's';
            }
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $courses = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'courses' => $courses]);
            break;

        case 'read':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID manquant.');
            $stmt = $conn->prepare("SELECT c.id_cours, c.titre, u.nom, u.prenom, c.id_enseignant FROM cours c LEFT JOIN enseignants e ON c.id_enseignant = e.id_enseignant LEFT JOIN utilisateurs u ON e.id_enseignant = u.id_utilisateur WHERE c.id_cours = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $course = $result->fetch_assoc();
            echo json_encode(['success' => true, 'course' => $course]);
            break;

        case 'create':
            $titre = $data['titre'] ?? '';
            $id_enseignant = $data['id_enseignant'] ?? null;
            $id_matiere = $data['id_matiere'] ?? null;
            if (!$titre || !$id_enseignant) throw new Exception('Données manquantes.');

            if ($id_matiere) {
                $stmt = $conn->prepare("INSERT INTO cours (titre, id_enseignant, id_matiere) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $titre, $id_enseignant, $id_matiere);
            } else {
                $stmt = $conn->prepare("INSERT INTO cours (titre, id_enseignant) VALUES (?, ?)");
                $stmt->bind_param("si", $titre, $id_enseignant);
            }
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Cours ajouté avec succès.']);
            break;

        case 'update':
            $id_cours = $data['id_cours'] ?? null;
            $titre = $data['titre'] ?? '';
            $id_enseignant = $data['id_enseignant'] ?? null;
            if (!$id_cours) throw new Exception('ID manquant.');

            $stmt = $conn->prepare("UPDATE cours SET titre = ?, id_enseignant = ? WHERE id_cours = ?");
            $stmt->bind_param("sii", $titre, $id_enseignant, $id_cours);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Cours mis à jour avec succès.']);
            break;

        case 'delete':
            $id = $data['id'] ?? null;
            if (!$id) throw new Exception('ID manquant.');
            $stmt = $conn->prepare("DELETE FROM cours WHERE id_cours = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Cours supprimé avec succès.']);
            break;
        
        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Action non valide.']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
}

$conn->close();
?>