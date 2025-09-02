<?php
// Fichier: register.php
header('Content-Type: application/json');
require_once 'db_connect.php';

$data = json_decode(file_get_contents("php://input"));

if (!isset($data->nom) || !isset($data->prenom) || !isset($data->password) || !isset($data->classe)) {
    echo json_encode(['success' => false, 'message' => 'Veuillez remplir tous les champs.']);
    exit();
}

$nom = $data->nom;
$prenom = $data->prenom;
$password = $data->password;
$classe = $data->classe;
$email = isset($data->email) ? $data->email : null;
$role = 'etudiant';

// Extraire le niveau et la lettre de la classe
preg_match('/([A-Za-zéèêîïÉÈÊÎÏ]+)\s*([A-Z])?/u', $classe, $matches);
$niveau = isset($matches[1]) ? strtoupper(substr($matches[1], 0, 2)) : 'XX';
$lettre = isset($matches[2]) ? strtoupper($matches[2]) : 'X';

$conn->begin_transaction();

try {
    // 1. Vérifier si l'utilisateur existe déjà par nom et prénom
    $stmt_check = $conn->prepare("SELECT id_utilisateur FROM utilisateurs WHERE nom = ? AND prenom = ?");
    $stmt_check->bind_param("ss", $nom, $prenom);
    $stmt_check->execute();
    if ($stmt_check->get_result()->num_rows > 0) {
        throw new Exception('Cet utilisateur existe déjà.');
    }
    $stmt_check->close();

    // 2. Hacher le mot de passe
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // 3. Insérer dans la table `utilisateurs` (matricule temporaire)
    $stmt_user = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, mot_de_passe_hache, role, est_actif, email) VALUES (?, ?, ?, ?, 1, ?)");
    $stmt_user->bind_param("sssss", $nom, $prenom, $hashed_password, $role, $email);
    $stmt_user->execute();
    $new_user_id = $conn->insert_id;
    $stmt_user->close();

    // 4. Générer le matricule unique et persistant
    $matricule = $niveau . $lettre . date("Y") . "-" . str_pad($new_user_id, 4, '0', STR_PAD_LEFT);

    // 5. Mettre à jour le matricule dans la table utilisateurs
    $stmt_update = $conn->prepare("UPDATE utilisateurs SET matricule = ? WHERE id_utilisateur = ?");
    $stmt_update->bind_param("si", $matricule, $new_user_id);
    $stmt_update->execute();
    $stmt_update->close();

    // 6. Insérer dans la table `etudiants`
    $stmt_student = $conn->prepare("INSERT INTO etudiants (id_etudiant, numero_etudiant, classe) VALUES (?, ?, ?)");
    $stmt_student->bind_param("iss", $new_user_id, $matricule, $classe);
    $stmt_student->execute();
    $stmt_student->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Inscription réussie.', 'matricule' => $matricule]);
} catch (Exception $e) {
    if ($conn->in_transaction) $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription: ' . $e->getMessage()]);
}

$conn->close();
?>