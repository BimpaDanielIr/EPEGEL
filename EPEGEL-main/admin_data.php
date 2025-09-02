<?php
// Fichier: admin_data.php

header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrateur') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
    exit();
}

require_once 'db_connect.php';

$action = $_GET['action'] ?? null;

try {
    if ($action === 'stats') {
        $stats = [];
        
        // Total étudiants
        $result = $conn->query("SELECT COUNT(*) as count FROM etudiants");
        $stats['students'] = $result->fetch_assoc()['count'];
        
        // Total enseignants
        $result = $conn->query("SELECT COUNT(*) as count FROM enseignants");
        $stats['teachers'] = $result->fetch_assoc()['count'];
        
        // Total cours
        $result = $conn->query("SELECT COUNT(*) as count FROM cours");
        $stats['courses'] = $result->fetch_assoc()['count'];
        
        // Revenus annuels (somme des paiements)
        $result = $conn->query("SELECT SUM(montant_paiement) as revenue FROM paiements WHERE YEAR(date_paiement) = YEAR(CURRENT_DATE())");
        $stats['revenue'] = $result->fetch_assoc()['revenue'] ?? 0;
        
        echo json_encode(['success' => true, 'stats' => $stats]);
    } elseif ($action === 'admin_info') {
        $id = $_SESSION['user_id'];
        $stmt = $conn->prepare("SELECT nom, prenom, role FROM utilisateurs WHERE id_utilisateur = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $info = $result->fetch_assoc();
        $stmt->close();
        echo json_encode(['success' => true, 'info' => $info]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Action non valide.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
} finally {
    $conn->close();
}
?>