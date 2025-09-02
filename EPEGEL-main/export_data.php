<?php
// Fichier: export_data.php

session_start();
header('Content-Type: text/csv; charset=utf-8');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'administrateur') {
    http_response_code(403);
    die("Accès refusé.");
}

require_once 'db_connect.php';

$action = $_GET['action'] ?? null;
$filename = "export_" . $action . "_" . date("Y-m-d") . ".csv";

// Préparation des données pour l'export
switch ($action) {
    case 'students':
        $query = "SELECT u.id_utilisateur AS ID, u.nom, u.prenom, e.numero_etudiant AS Matricule, e.classe, u.est_actif AS Statut FROM utilisateurs u JOIN etudiants e ON u.id_utilisateur = e.id_etudiant WHERE u.role = 'etudiant'";
        $headers = ['ID', 'Nom', 'Prénom', 'Matricule', 'Classe', 'Statut'];
        break;
    case 'teachers':
        $query = "SELECT u.id_utilisateur AS ID, u.nom, u.prenom, e.matiere_enseignee AS Matière FROM utilisateurs u JOIN enseignants e ON u.id_utilisateur = e.id_enseignant";
        $headers = ['ID', 'Nom', 'Prénom', 'Matière Enseignée'];
        break;
    case 'courses':
        $query = "SELECT c.id_cours AS ID, c.titre, u.nom AS Nom_Enseignant, u.prenom AS Prénom_Enseignant FROM cours c JOIN enseignants e ON c.id_enseignant = e.id_enseignant JOIN utilisateurs u ON e.id_enseignant = u.id_utilisateur";
        $headers = ['ID', 'Titre du Cours', 'Nom Enseignant', 'Prénom Enseignant'];
        break;
    case 'payments':
        $query = "SELECT p.id_paiement AS ID, u.nom AS Nom_Etudiant, u.prenom AS Prénom_Etudiant, p.montant_paiement AS Montant, p.date_paiement AS Date FROM paiements p JOIN frais_scolarite f ON p.id_frais_scolarite = f.id_frais JOIN etudiants e ON f.id_etudiant = e.id_etudiant JOIN utilisateurs u ON e.id_etudiant = u.id_utilisateur";
        $headers = ['ID', 'Nom Etudiant', 'Prénom Etudiant', 'Montant', 'Date'];
        break;
    default:
        http_response_code(404);
        die("Action d'exportation non valide.");
}

$result = $conn->query($query);
if (!$result) {
    http_response_code(500);
    die("Erreur de requête: " . $conn->error);
}

// Définir les en-têtes du fichier
header('Content-Disposition: attachment; filename="' . $filename . '"');
$output = fopen('php://output', 'w');
fputcsv($output, $headers);

while ($row = $result->fetch_assoc()) {
    fputcsv($output, $row);
}
fclose($output);

$conn->close();
?>