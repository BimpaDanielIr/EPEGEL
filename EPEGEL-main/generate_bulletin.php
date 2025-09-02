<?php
session_start();
require_once 'db_connect.php';
require_once 'fpdf/fpdf.php'; // Assurez-vous que ce chemin est correct

if (!isset($_SESSION['user_id']) || !isset($_GET['student_id'])) {
    die('Accès non autorisé ou ID étudiant manquant.');
}

$student_id = intval($_GET['student_id']);

// --- Récupération des données ---
// Infos étudiant
$stmt_student = $conn->prepare("SELECT u.nom, u.prenom, e.classe, e.annee_scolaire FROM utilisateurs u JOIN etudiants e ON u.id_utilisateur = e.id_etudiant WHERE u.id_utilisateur = ?");
$stmt_student->bind_param("i", $student_id);
$stmt_student->execute();
$student_info = $stmt_student->get_result()->fetch_assoc();
$stmt_student->close();

// Notes
$stmt_notes = $conn->prepare("SELECT c.titre, n.note, n.coefficient, n.appreciation FROM notes n JOIN cours c ON n.id_cours = c.id_cours WHERE n.id_etudiant = ?");
$stmt_notes->bind_param("i", $student_id);
$stmt_notes->execute();
$notes_result = $stmt_notes->get_result();
$notes = $notes_result->fetch_all(MYSQLI_ASSOC);
$stmt_notes->close();

// --- Création du PDF ---
class PDF extends FPDF {
    function Header() {
        // Logo (optionnel)
        // $this->Image('images/logo.png',10,6,30);
        $this->SetFont('Arial','B',15);
        $this->Cell(80);
        $this->Cell(30,10,'Bulletin de Notes',1,0,'C');
        $this->Ln(20);
    }
    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Times','',12);

// Infos de l'étudiant
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10, 'Bulletin de: ' . utf8_decode($student_info['prenom'] . ' ' . $student_info['nom']), 0, 1);
$pdf->SetFont('Arial','',12);
$pdf->Cell(0,10, 'Classe: ' . utf8_decode($student_info['classe']), 0, 1);
$pdf->Cell(0,10, 'Annee Scolaire: ' . $student_info['annee_scolaire'], 0, 1);
$pdf->Ln(10);

// Tableau des notes
$pdf->SetFont('Arial','B',11);
$pdf->Cell(80,7,'Matiere',1);
$pdf->Cell(20,7,'Note',1);
$pdf->Cell(25,7,'Coefficient',1);
$pdf->Cell(65,7,'Appreciation',1);
$pdf->Ln();

$pdf->SetFont('Arial','',10);
$total_points = 0;
$total_coeffs = 0;
foreach ($notes as $note) {
    $pdf->Cell(80,6,utf8_decode($note['titre']),1);
    $pdf->Cell(20,6,$note['note'],1);
    $pdf->Cell(25,6,$note['coefficient'],1);
    $pdf->Cell(65,6,utf8_decode($note['appreciation']),1);
    $pdf->Ln();
    $total_points += $note['note'] * $note['coefficient'];
    $total_coeffs += $note['coefficient'];
}

// Moyenne générale
$moyenne = ($total_coeffs > 0) ? number_format($total_points / $total_coeffs, 2) : 'N/A';
$pdf->Ln(10);
$pdf->SetFont('Arial','B',12);
$pdf->Cell(0,10, 'Moyenne Generale: ' . $moyenne, 0, 1);

$pdf->Output('D', 'bulletin_' . $student_info['nom'] . '.pdf');
$conn->close();
?>
