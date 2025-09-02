<?php
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['administrateur', 'enseignant'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

require_once 'db_connect.php';

$action = $_POST['action'] ?? $_GET['action'] ?? null;

try {
    switch ($action) {
        case 'list':
            $id_cours = $_GET['id_cours'] ?? null;
            if (!$id_cours) throw new Exception("ID de cours manquant.");
            
            $stmt = $conn->prepare("SELECT id_document, titre, url, date_upload FROM documents WHERE id_cours = ? ORDER BY date_upload DESC");
            $stmt->bind_param("i", $id_cours);
            $stmt->execute();
            $result = $stmt->get_result();
            $documents = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'documents' => $documents]);
            break;

        case 'upload':
            $titre = $_POST['titre'] ?? '';
            $id_cours = $_POST['id_cours'] ?? null;
            
            if (!$titre || !$id_cours || !isset($_FILES['document'])) {
                throw new Exception('Données ou fichier manquant.');
            }

            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            
            $fileName = uniqid() . '-' . basename($_FILES['document']['name']);
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['document']['tmp_name'], $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO documents (titre, url, id_cours, type) VALUES (?, ?, ?, 'support')");
                $stmt->bind_param("ssi", $titre, $targetFile, $id_cours);
                $stmt->execute();
                echo json_encode(['success' => true, 'message' => 'Document téléversé avec succès.']);
            } else {
                throw new Exception('Erreur lors du téléversement du fichier.');
            }
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
