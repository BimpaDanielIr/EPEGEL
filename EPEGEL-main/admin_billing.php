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
            $query = "SELECT f.id_frais, u.nom, u.prenom, f.annee_scolaire, f.montant_total, SUM(COALESCE(p.montant_paiement, 0)) AS montant_paye, (f.montant_total - SUM(COALESCE(p.montant_paiement, 0))) AS solde FROM frais_scolarite f JOIN etudiants e ON f.id_etudiant = e.id_etudiant JOIN utilisateurs u ON e.id_etudiant = u.id_utilisateur LEFT JOIN paiements p ON f.id_frais = p.id_frais_scolarite GROUP BY f.id_frais";
            $params = [];
            $types = '';
            if (isset($_GET['q']) && !empty($_GET['q'])) {
                $q = '%' . $_GET['q'] . '%';
                $query .= " WHERE u.nom LIKE ? OR u.prenom LIKE ?";
                $params = [$q, $q];
                $types = 'ss';
            }
            $stmt = $conn->prepare($query);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            $stmt->execute();
            $result = $stmt->get_result();
            $billings = $result->fetch_all(MYSQLI_ASSOC);
            echo json_encode(['success' => true, 'billings' => $billings]);
            break;

        case 'read':
            $id = $_GET['id'] ?? null;
            if (!$id) throw new Exception('ID manquant.');
            $query = "SELECT f.id_frais, u.nom, u.prenom, f.annee_scolaire, f.montant_total, SUM(COALESCE(p.montant_paiement, 0)) AS montant_paye, (f.montant_total - SUM(COALESCE(p.montant_paiement, 0))) AS solde FROM frais_scolarite f JOIN etudiants e ON f.id_etudiant = e.id_etudiant JOIN utilisateurs u ON e.id_etudiant = u.id_utilisateur LEFT JOIN paiements p ON f.id_frais = p.id_frais_scolarite WHERE f.id_frais = ? GROUP BY f.id_frais";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $billing = $result->fetch_assoc();
            echo json_encode(['success' => true, 'billing' => $billing]);
            break;
            
        case 'add_payment':
            $id_frais = $data['id_frais'] ?? null;
            $montant_paiement = $data['montant_paiement'] ?? null;
            if (!$id_frais || !$montant_paiement || !is_numeric($montant_paiement) || $montant_paiement <= 0) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Données de paiement invalides ou manquantes.']);
                exit();
            }

            $stmt = $conn->prepare("INSERT INTO paiements (id_frais_scolarite, montant_paiement) VALUES (?, ?)");
            $stmt->bind_param("id", $id_frais, $montant_paiement);
            $stmt->execute();
            echo json_encode(['success' => true, 'message' => 'Paiement enregistré avec succès.']);
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