<?php
// --- SCRIPT DE REMPLISSAGE DE LA BASE DE DONNÉES ---
// Accédez à ce fichier une seule fois dans votre navigateur (ex: http://localhost/dan/EPEGEL-main/seed_database.php)
// pour remplir la base de données avec des données de test propres et cohérentes.

header('Content-Type: text/plain; charset=utf-8');
require_once 'db_connect.php';

echo "Démarrage du script de remplissage...\n\n";

try {
    $conn->begin_transaction();

    // --- 1. Vider les tables existantes pour un nouveau départ ---
    echo "Nettoyage des anciennes données...\n";
    $conn->query("SET FOREIGN_KEY_CHECKS=0");
    $tables = ['utilisateurs', 'etudiants', 'enseignants', 'matieres', 'cours', 'notes', 'frais_scolarite', 'paiements', 'absences'];
    foreach ($tables as $table) {
        $conn->query("TRUNCATE TABLE `$table`");
        echo "Table `$table` vidée.\n";
    }
    $conn->query("SET FOREIGN_KEY_CHECKS=1");
    echo "Nettoyage terminé.\n\n";

    // --- 2. Création des utilisateurs et récupération de leurs IDs ---
    echo "Création des utilisateurs...\n";
    $users = [
        ['Admin', 'Principal', null, password_hash('password', PASSWORD_DEFAULT), 'administrateur', null],
        ['Jean', 'Dupont', 'jean.dupont@epegl.com', password_hash('password', PASSWORD_DEFAULT), 'enseignant', null],
        ['Alice', 'Martin', 'alice.martin@epegl.com', password_hash('password', PASSWORD_DEFAULT), 'etudiant', 'ETU2024-0003'],
        ['Paul', 'Durand', 'paul.durand@epegl.com', password_hash('password', PASSWORD_DEFAULT), 'etudiant', 'ETU2024-0004'],
        ['Marc', 'Bernard', 'marc.bernard@epegl.com', password_hash('password', PASSWORD_DEFAULT), 'enseignant', null],
    ];

    $stmt_user = $conn->prepare("INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hache, role, matricule) VALUES (?, ?, ?, ?, ?, ?)");
    $userIds = [];
    foreach ($users as $user) {
        $stmt_user->bind_param("ssssss", $user[0], $user[1], $user[2], $user[3], $user[4], $user[5]);
        $stmt_user->execute();
        $userId = $conn->insert_id;
        $userIds[$user[0]] = $userId; // Stocke l'ID par nom pour référence
        echo "Utilisateur créé: {$user[0]} {$user[1]} (ID: $userId)\n";
    }
    $stmt_user->close();
    echo "Utilisateurs créés.\n\n";

    // --- 3. Création des profils étudiants et enseignants liés ---
    echo "Création des profils spécifiques...\n";
    $stmt_student = $conn->prepare("INSERT INTO etudiants (id_etudiant, numero_etudiant, classe, annee_scolaire) VALUES (?, ?, ?, ?)");
    $stmt_teacher = $conn->prepare("INSERT INTO enseignants (id_enseignant, matiere_enseignee) VALUES (?, ?)");

    $stmt_student->bind_param("isss", $userIds['Alice'], 'ETU2024-0003', 'Terminale A', '2023-2024');
    $stmt_student->execute();
    $stmt_student->bind_param("isss", $userIds['Paul'], 'ETU2024-0004', 'Première B', '2023-2024');
    $stmt_student->execute();

    $stmt_teacher->bind_param("is", $userIds['Jean'], 'Mathématiques');
    $stmt_teacher->execute();
    $stmt_teacher->bind_param("is", $userIds['Marc'], 'Physique');
    $stmt_teacher->execute();
    
    $stmt_student->close();
    $stmt_teacher->close();
    echo "Profils créés.\n\n";

    // --- 4. Création des matières et des cours ---
    echo "Création des matières et des cours...\n";
    $stmt_matiere = $conn->prepare("INSERT INTO matieres (nom_matiere) VALUES (?)");
    $stmt_matiere->bind_param("s", $nom_matiere);
    $nom_matiere = 'Mathématiques'; $stmt_matiere->execute(); $mathId = $conn->insert_id;
    $nom_matiere = 'Physique'; $stmt_matiere->execute(); $physId = $conn->insert_id;
    $stmt_matiere->close();

    $stmt_cours = $conn->prepare("INSERT INTO cours (titre, id_enseignant, id_matiere) VALUES (?, ?, ?)");
    $stmt_cours->bind_param("sii", $titre, $id_enseignant, $id_matiere);
    $titre = 'Algèbre Linéaire'; $id_enseignant = $userIds['Jean']; $id_matiere = $mathId; $stmt_cours->execute(); $coursAlgebreId = $conn->insert_id;
    $titre = 'Mécanique Quantique'; $id_enseignant = $userIds['Marc']; $id_matiere = $physId; $stmt_cours->execute(); $coursMecaId = $conn->insert_id;
    $stmt_cours->close();
    echo "Matières et cours créés.\n\n";

    // --- 5. Ajout des notes ---
    echo "Ajout des notes...\n";
    $stmt_note = $conn->prepare("INSERT INTO notes (id_etudiant, id_cours, note, coefficient, appreciation) VALUES (?, ?, ?, ?, ?)");
    $stmt_note->bind_param("iidis", $id_etudiant, $id_cours, $note, $coeff, $appreciation);
    
    $id_etudiant = $userIds['Alice']; $id_cours = $coursAlgebreId; $note = 15.5; $coeff = 2; $appreciation = 'Excellent travail'; $stmt_note->execute();
    $id_etudiant = $userIds['Alice']; $id_cours = $coursMecaId; $note = 12.0; $coeff = 3; $appreciation = 'Peut mieux faire'; $stmt_note->execute();
    $id_etudiant = $userIds['Paul']; $id_cours = $coursAlgebreId; $note = 11.0; $coeff = 2; $appreciation = 'Passable'; $stmt_note->execute();
    $stmt_note->close();
    echo "Notes ajoutées.\n\n";

    // --- 6. Ajout des frais et paiements ---
    echo "Ajout des frais de scolarité et paiements...\n";
    $stmt_frais = $conn->prepare("INSERT INTO frais_scolarite (id_etudiant, annee_scolaire, montant_total, montant_paye) VALUES (?, ?, ?, ?)");
    $stmt_frais->bind_param("isdd", $id_etudiant, $annee, $total, $paye);
    
    $id_etudiant = $userIds['Alice']; $annee = '2023-2024'; $total = 500000; $paye = 250000; $stmt_frais->execute(); $fraisAliceId = $conn->insert_id;
    $id_etudiant = $userIds['Paul']; $annee = '2023-2024'; $total = 500000; $paye = 500000; $stmt_frais->execute(); $fraisPaulId = $conn->insert_id;
    $stmt_frais->close();

    $stmt_paiement = $conn->prepare("INSERT INTO paiements (id_frais_scolarite, montant_paiement) VALUES (?, ?)");
    $stmt_paiement->bind_param("id", $id_frais, $montant);
    $id_frais = $fraisAliceId; $montant = 250000; $stmt_paiement->execute();
    $id_frais = $fraisPaulId; $montant = 500000; $stmt_paiement->execute();
    $stmt_paiement->close();
    echo "Frais et paiements ajoutés.\n\n";

    $conn->commit();
    echo "SUCCÈS : La base de données a été remplie avec les données de test.\n";
    echo "Vous pouvez maintenant supprimer ce fichier (seed_database.php) de votre serveur.";

} catch (Exception $e) {
    $conn->rollback();
    echo "\nERREUR : Une exception a été rencontrée. Le remplissage a été annulé.\n";
    echo "Message d'erreur : " . $e->getMessage();
} finally {
    $conn->close();
}
?>
