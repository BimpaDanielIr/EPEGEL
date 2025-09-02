<?php
// Fichier: get_student_data.php

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'etudiant') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

require_once 'db_connect.php';

$student_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? 'all';

try {
    $response = ['success' => true];

    switch ($action) {
        case 'dashboard_stats':
            // Infos de l'étudiant
            $stmt = $conn->prepare("SELECT u.nom, u.prenom, e.classe, u.matricule, e.annee_scolaire FROM utilisateurs u JOIN etudiants e ON u.id_utilisateur = e.id_etudiant WHERE u.id_utilisateur = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $stats = $result->fetch_assoc() ?: [];
            $stmt->close();

            // Total cours
            $stmt = $conn->prepare("SELECT COUNT(*) AS total_cours, COUNT(note) AS total_notes FROM notes WHERE id_etudiant = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            $stats['total_cours'] = $row['total_cours'];
            $stats['total_notes'] = $row['total_notes'];
            $stmt->close();

            // Moyenne générale
            $stmt = $conn->prepare("SELECT AVG(note) AS moyenne_generale FROM notes WHERE id_etudiant = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $avg_row = $result->fetch_assoc();
            $stats['moyenne_generale'] = $avg_row && $avg_row['moyenne_generale'] !== null ? number_format($avg_row['moyenne_generale'], 2) : 'N/A';
            $stmt->close();

            // Frais de scolarité
            $stmt = $conn->prepare("SELECT montant_total, montant_paye FROM frais_scolarite WHERE id_etudiant = ? ORDER BY annee_scolaire DESC LIMIT 1");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $frais = $result->fetch_assoc();
            $stats['montant_total'] = $frais ? $frais['montant_total'] : 'N/A';
            $stats['montant_paye'] = $frais ? $frais['montant_paye'] : 'N/A';
            $stats['solde_du'] = $frais ? $frais['montant_total'] - $frais['montant_paye'] : 'N/A';
            $stmt->close();

            $response['stats'] = $stats;
            break;

        case 'notes':
            $stmt = $conn->prepare("SELECT n.note, n.coefficient, n.appreciation, c.titre AS cours_titre, n.date_enregistrement
                                    FROM notes n JOIN cours c ON n.id_cours = c.id_cours
                                    WHERE n.id_etudiant = ?");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $notes = [];
            while ($row = $result->fetch_assoc()) {
                $notes[] = $row;
            }
            $stmt->close();
            $response['notes'] = $notes;
            break;

        case 'billing':
            $stmt = $conn->prepare("SELECT f.annee_scolaire, f.montant_total, f.montant_paye, (f.montant_total - f.montant_paye) AS solde FROM frais_scolarite f WHERE f.id_etudiant = ? ORDER BY f.annee_scolaire DESC");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $payments = [];
            while ($row = $result->fetch_assoc()) {
                $payments[] = $row;
            }
            $stmt->close();
            $response['payments'] = $payments;
            break;

        case 'schedule':
            $stmt = $conn->prepare("SELECT jour, CONCAT(heure_debut, ' - ', heure_fin) AS heure, m.nom_matiere AS matiere, salle FROM emplois_du_temps e JOIN matieres m ON e.id_matiere = m.id_matiere WHERE id_classe = (SELECT classe FROM etudiants WHERE id_etudiant = ?)");
            $stmt->bind_param("i", $student_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $schedule = [];
            while ($row = $result->fetch_assoc()) {
                $schedule[] = $row;
            }
            $stmt->close();
            $response['schedule'] = $schedule;
            break;

        default:
            http_response_code(400);
            $response = ['success' => false, 'message' => 'Action non valide.'];
            break;
    }

    echo json_encode($response);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur serveur: ' . $e->getMessage()]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>