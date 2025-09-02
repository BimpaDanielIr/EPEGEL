-- Utilisateurs (admin, enseignants, étudiants)
INSERT INTO utilisateurs (nom, prenom, email, mot_de_passe_hache, role, est_actif, matricule)
VALUES
('Admin', 'Principal', 'admin@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'administrateur', 1, NULL),
('Jean', 'Dupont', 'jean.dupont@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'enseignant', 1, NULL),
('Alice', 'Martin', 'alice.martin@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0001'),
('Paul', 'Durand', 'paul.durand@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0002'),
('Sophie', 'Leroy', 'sophie.leroy@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0003'),
('Marc', 'Bernard', 'marc.bernard@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'enseignant', 1, NULL),
('Julie', 'Petit', 'julie.petit@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'enseignant', 1, NULL),
('Luc', 'Moreau', 'luc.moreau@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0004'),
('Emma', 'Fournier', 'emma.fournier@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0005'),
('Hugo', 'Girard', 'hugo.girard@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0006'),
('Chloe', 'Roux', 'chloe.roux@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0007'),
('Louis', 'Blanc', 'louis.blanc@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0008'),
('Camille', 'Henry', 'camille.henry@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0009'),
('Mathieu', 'Gauthier', 'mathieu.gauthier@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'enseignant', 1, NULL),
('Laura', 'Chevalier', 'laura.chevalier@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'enseignant', 1, NULL),
('Antoine', 'Barbier', 'antoine.barbier@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0010'),
('Sarah', 'Perrot', 'sarah.perrot@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0011'),
('Nicolas', 'Leclerc', 'nicolas.leclerc@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0012'),
('Manon', 'Benoit', 'manon.benoit@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0013'),
('Thomas', 'Renard', 'thomas.renard@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0014'),
('Eva', 'Marchand', 'eva.marchand@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'etudiant', 1, 'TA2024-0015'),
('Pierre', 'Guillaume', 'pierre.guillaume@epegl.com', '$2y$10$abcdefghijklmnopqrstuv', 'enseignant', 1, NULL);

-- Etudiants
INSERT INTO etudiants (id_etudiant, numero_etudiant, classe, annee_scolaire)
VALUES
(3, 'TA2024-0001', 'Terminale A', '2023-2024'),
(4, 'TA2024-0002', 'Terminale A', '2023-2024'),
(5, 'TA2024-0003', 'Terminale A', '2023-2024'),
(8, 'TA2024-0004', 'Terminale A', '2023-2024'),
(9, 'TA2024-0005', 'Terminale A', '2023-2024'),
(10, 'TA2024-0006', 'Terminale A', '2023-2024'),
(11, 'TA2024-0007', 'Terminale A', '2023-2024'),
(12, 'TA2024-0008', 'Terminale A', '2023-2024'),
(13, 'TA2024-0009', 'Terminale A', '2023-2024'),
(16, 'TA2024-0010', 'Terminale A', '2023-2024'),
(17, 'TA2024-0011', 'Terminale A', '2023-2024'),
(18, 'TA2024-0012', 'Terminale A', '2023-2024'),
(19, 'TA2024-0013', 'Terminale A', '2023-2024'),
(20, 'TA2024-0014', 'Terminale A', '2023-2024'),
(21, 'TA2024-0015', 'Terminale A', '2023-2024');

-- Enseignants
INSERT INTO enseignants (id_enseignant, matiere_enseignee)
VALUES
(2, 'Mathématiques'),
(6, 'Physique'),
(7, 'Français'),
(14, 'Histoire'),
(15, 'SVT'),
(23, 'Anglais');

-- Matières
INSERT INTO matieres (nom_matiere, description)
VALUES
('Mathématiques', 'Mathématiques générales'),
('Physique', 'Physique appliquée'),
('Français', 'Langue française'),
('Histoire', 'Histoire contemporaine'),
('SVT', 'Sciences de la vie et de la terre'),
('Anglais', 'Langue anglaise'),
('Philosophie', 'Philosophie générale'),
('Espagnol', 'Langue espagnole'),
('Informatique', 'Programmation et algorithmique'),
('Economie', 'Economie générale'),
('Géographie', 'Géographie mondiale'),
('Arts', 'Arts plastiques'),
('EPS', 'Education physique et sportive'),
('Chimie', 'Chimie organique'),
('Musique', 'Musique classique'),
('Allemand', 'Langue allemande'),
('Latin', 'Langue latine'),
('Grec', 'Langue grecque'),
('Technologie', 'Technologie générale'),
('Droit', 'Droit civil');

-- Cours
INSERT INTO cours (titre, id_enseignant, id_matiere)
VALUES
('Algèbre', 2, 1),
('Mécanique', 6, 2),
('Grammaire', 7, 3),
('Révolution', 14, 4),
('Biologie', 15, 5),
('Conversation', 23, 6),
('Logique', 2, 7),
('Lecture', 7, 3),
('Chimie', 6, 14),
('Sport', 2, 13),
('Programmation', 2, 9),
('Economie', 23, 10),
('Géographie', 14, 11),
('Arts', 15, 12),
('Musique', 23, 15),
('Allemand', 23, 16),
('Latin', 23, 17),
('Grec', 23, 18),
('Technologie', 6, 19),
('Droit', 14, 20);

-- Notes
INSERT INTO notes (id_etudiant, id_cours, note, coefficient, appreciation)
VALUES
(3, 1, 15.5, 2, 'Très bien'),
(4, 2, 12.0, 1, 'Bien'),
(5, 3, 14.0, 2, 'Bien'),
(8, 4, 10.5, 1, 'Moyen'),
(9, 5, 16.0, 2, 'Excellent'),
(10, 6, 9.0, 1, 'A améliorer'),
(11, 7, 13.5, 2, 'Bien'),
(12, 8, 17.0, 2, 'Excellent'),
(13, 9, 11.0, 1, 'Moyen'),
(16, 10, 18.0, 2, 'Excellent'),
(17, 11, 14.5, 2, 'Bien'),
(18, 12, 15.0, 2, 'Très bien'),
(19, 13, 12.5, 1, 'Bien'),
(20, 14, 13.0, 2, 'Bien'),
(21, 15, 16.5, 2, 'Excellent'),
(3, 16, 17.5, 2, 'Excellent'),
(4, 17, 10.0, 1, 'Moyen'),
(5, 18, 11.5, 1, 'Moyen'),
(8, 19, 13.0, 2, 'Bien'),
(9, 20, 14.0, 2, 'Bien');

-- Frais de scolarité
INSERT INTO frais_scolarite (id_etudiant, annee_scolaire, montant_total, montant_paye)
VALUES
(3, '2023-2024', 500000, 250000),
(4, '2023-2024', 500000, 500000),
(5, '2023-2024', 500000, 400000),
(8, '2023-2024', 500000, 300000),
(9, '2023-2024', 500000, 500000),
(10, '2023-2024', 500000, 200000),
(11, '2023-2024', 500000, 500000),
(12, '2023-2024', 500000, 500000),
(13, '2023-2024', 500000, 500000),
(16, '2023-2024', 500000, 500000),
(17, '2023-2024', 500000, 500000),
(18, '2023-2024', 500000, 500000),
(19, '2023-2024', 500000, 500000),
(20, '2023-2024', 500000, 500000),
(21, '2023-2024', 500000, 500000);

-- Paiements
INSERT INTO paiements (id_frais_scolarite, montant_paiement)
VALUES
(1, 250000),
(2, 500000),
(3, 400000),
(4, 300000),
(5, 500000),
(6, 200000),
(7, 500000),
(8, 500000),
(9, 500000),
(10, 500000),
(11, 500000),
(12, 500000),
(13, 500000),
(14, 500000),
(15, 500000);

-- Emploi du temps
INSERT INTO emplois_du_temps (id_classe, jour, heure_debut, heure_fin, id_matiere, salle)
VALUES
('Terminale A', 'Lundi', '08:00', '10:00', 1, 'A01'),
('Terminale A', 'Lundi', '10:15', '12:15', 2, 'A02'),
('Terminale A', 'Mardi', '08:00', '10:00', 3, 'A01'),
('Terminale A', 'Mardi', '10:15', '12:15', 4, 'A02'),
('Terminale A', 'Mercredi', '08:00', '10:00', 5, 'A01'),
('Terminale A', 'Mercredi', '10:15', '12:15', 6, 'A02'),
('Terminale A', 'Jeudi', '08:00', '10:00', 7, 'A01'),
('Terminale A', 'Jeudi', '10:15', '12:15', 8, 'A02'),
('Terminale A', 'Vendredi', '08:00', '10:00', 9, 'A01'),
('Terminale A', 'Vendredi', '10:15', '12:15', 10, 'A02'),
('Terminale A', 'Samedi', '08:00', '10:00', 11, 'A01'),
('Terminale A', 'Samedi', '10:15', '12:15', 12, 'A02'),
('Terminale A', 'Lundi', '13:00', '15:00', 13, 'A03'),
('Terminale A', 'Mardi', '13:00', '15:00', 14, 'A03'),
('Terminale A', 'Mercredi', '13:00', '15:00', 15, 'A03'),
('Terminale A', 'Jeudi', '13:00', '15:00', 16, 'A03'),
('Terminale A', 'Vendredi', '13:00', '15:00', 17, 'A03'),
('Terminale A', 'Samedi', '13:00', '15:00', 18, 'A03'),
('Terminale A', 'Lundi', '15:15', '17:15', 19, 'A04'),
('Terminale A', 'Mardi', '15:15', '17:15', 20, 'A04');

-- Notifications
INSERT INTO notifications (titre, message, destinataire_role)
VALUES
('Bienvenue', 'Bienvenue sur la plateforme EPEGL !', 'tous'),
('Rappel', 'N\'oubliez pas de payer vos frais de scolarité.', 'etudiant'),
('Réunion', 'Réunion des enseignants mardi à 14h.', 'enseignant'),
('Résultats', 'Les résultats du trimestre sont disponibles.', 'etudiant'),
('Maintenance', 'Maintenance prévue samedi matin.', 'tous'),
('Nouveau cours', 'Un nouveau cours de technologie est disponible.', 'etudiant'),
('Absence', 'Veuillez signaler toute absence.', 'enseignant'),
('Export', 'Export des données disponible pour les admins.', 'administrateur'),
('Stage', 'Stage obligatoire en entreprise pour les Terminales.', 'etudiant'),
('Sport', 'Tournoi sportif vendredi.', 'tous'),
('Changement', 'Changement de salle pour le cours de SVT.', 'etudiant'),
('Sécurité', 'Consignes de sécurité mises à jour.', 'tous'),
('Bourse', 'Bourse d\'études disponible.', 'etudiant'),
('Formation', 'Formation continue pour les enseignants.', 'enseignant'),
('Finances', 'Nouvelle fonctionnalité de suivi des paiements.', 'administrateur'),
('Projet', 'Projet interclasse à rendre avant le 30.', 'etudiant'),
('Vacances', 'Vacances scolaires du 20 au 30.', 'tous'),
('Orientation', 'Séance d\'orientation pour les nouveaux.', 'etudiant'),
('Forum', 'Forum de discussion ouvert.', 'tous'),
('Mise à jour', 'Mise à jour de la plateforme effectuée.', 'tous');
