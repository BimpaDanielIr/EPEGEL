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
            $query = "SELECT a.id_absence, u.nom, u.prenom, c.titre AS cours, a.date_absence, a.motif, a.justifiee 
                      FROM absences a 
                      JOIN etudiants e ON a.id_etudiant = e.id_etudiant
                      JOIN utilisateurs u ON e.id_etudiant = u.id_utilisateur
                      LEFT JOIN cours c ON a.id_cours = c.id_cours
                      ORDER BY a.date_absence DESC";
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $result = $stmt->get_result();
            $absences = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'absences' => $absences]);
            break;

        case 'create':
            $id_etudiant = $data['id_etudiant'] ?? null;
            $id_cours = $data['id_cours'] ?? null;
            $date_absence = $data['date_absence'] ?? '';
            $motif = $data['motif'] ?? '';
            $justifiee = $data['justifiee'] ?? 0;

            if (!$id_etudiant || !$date_absence) {
                throw new Exception('Données manquantes.');
            }

            $stmt = $conn->prepare("INSERT INTO absences (id_etudiant, id_cours, date_absence, motif, justifiee) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("iisss", $id_etudiant, $id_cours, $date_absence, $motif, $justifiee);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Absence enregistrée.']);
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
