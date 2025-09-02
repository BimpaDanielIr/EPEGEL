-- Version finale et corrigée de la base de données EPEGL.
-- Instructions :
-- 1. Allez sur phpMyAdmin.
-- 2. Supprimez votre base de données `epegl_db` actuelle.
-- 3. Créez une nouvelle base de données vide nommée `epegl_db`.
-- 4. Importez ce fichier SQL.

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `epegl_db` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `epegl_db`;

--
-- Structure de la table `utilisateurs`
--
CREATE TABLE `utilisateurs` (
  `id_utilisateur` int(11) NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `matricule` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mot_de_passe_hache` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('administrateur','etudiant','enseignant') COLLATE utf8mb4_unicode_ci NOT NULL,
  `est_actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `enseignants`
--
CREATE TABLE `enseignants` (
  `id_enseignant` int(11) NOT NULL,
  `matiere_enseignee` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `etudiants`
--
CREATE TABLE `etudiants` (
  `id_etudiant` int(11) NOT NULL,
  `numero_etudiant` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `classe` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `annee_scolaire` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `matieres`
--
CREATE TABLE `matieres` (
  `id_matiere` int(11) NOT NULL,
  `nom_matiere` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `cours`
--
CREATE TABLE `cours` (
  `id_cours` int(11) NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_enseignant` int(11) DEFAULT NULL,
  `id_matiere` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `notes`
--
CREATE TABLE `notes` (
  `id_note` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `id_cours` int(11) NOT NULL,
  `note` decimal(5,2) NOT NULL,
  `coefficient` int(2) DEFAULT 1,
  `appreciation` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_enregistrement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `frais_scolarite`
--
CREATE TABLE `frais_scolarite` (
  `id_frais` int(11) NOT NULL,
  `id_etudiant` int(11) NOT NULL,
  `annee_scolaire` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `montant_total` decimal(10,2) NOT NULL,
  `montant_paye` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `paiements`
--
CREATE TABLE `paiements` (
  `id_paiement` int(11) NOT NULL,
  `id_frais_scolarite` int(11) NOT NULL,
  `montant_paiement` decimal(10,2) NOT NULL,
  `date_paiement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `absences`
--
CREATE TABLE `absences` (
  `id_absence` int(11) NOT NULL,
  `id_etudiant` int(11) DEFAULT NULL,
  `id_cours` int(11) DEFAULT NULL,
  `date_absence` date NOT NULL,
  `motif` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `justifiee` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Structure de la table `documents`
--
CREATE TABLE `documents` (
  `id_document` int(11) NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` enum('support','devoir','autre') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'autre',
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_cours` int(11) DEFAULT NULL,
  `date_upload` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Index et Clés primaires
--
ALTER TABLE `utilisateurs` ADD PRIMARY KEY (`id_utilisateur`), ADD UNIQUE KEY `uk_email` (`email`), ADD UNIQUE KEY `uk_matricule` (`matricule`);
ALTER TABLE `enseignants` ADD PRIMARY KEY (`id_enseignant`);
ALTER TABLE `etudiants` ADD PRIMARY KEY (`id_etudiant`), ADD UNIQUE KEY `numero_etudiant` (`numero_etudiant`);
ALTER TABLE `matieres` ADD PRIMARY KEY (`id_matiere`);
ALTER TABLE `cours` ADD PRIMARY KEY (`id_cours`), ADD KEY `fk_cours_enseignant` (`id_enseignant`), ADD KEY `fk_cours_matiere` (`id_matiere`);
ALTER TABLE `notes` ADD PRIMARY KEY (`id_note`), ADD KEY `fk_notes_etudiant` (`id_etudiant`), ADD KEY `fk_notes_cours` (`id_cours`);
ALTER TABLE `frais_scolarite` ADD PRIMARY KEY (`id_frais`), ADD KEY `fk_frais_etudiant` (`id_etudiant`);
ALTER TABLE `paiements` ADD PRIMARY KEY (`id_paiement`), ADD KEY `fk_paiement_frais` (`id_frais_scolarite`);
ALTER TABLE `absences` ADD PRIMARY KEY (`id_absence`), ADD KEY `fk_absence_etudiant` (`id_etudiant`), ADD KEY `fk_absence_cours` (`id_cours`);
ALTER TABLE `documents` ADD PRIMARY KEY (`id_document`), ADD KEY `fk_document_cours` (`id_cours`);

--
-- AUTO_INCREMENT pour les tables
--
ALTER TABLE `utilisateurs` MODIFY `id_utilisateur` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `enseignants` MODIFY `id_enseignant` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `etudiants` MODIFY `id_etudiant` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `matieres` MODIFY `id_matiere` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `cours` MODIFY `id_cours` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `notes` MODIFY `id_note` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `frais_scolarite` MODIFY `id_frais` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `paiements` MODIFY `id_paiement` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `absences` MODIFY `id_absence` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `documents` MODIFY `id_document` int(11) NOT NULL AUTO_INCREMENT;

--
-- Contraintes pour les tables
--
ALTER TABLE `enseignants` ADD CONSTRAINT `fk_enseignant_utilisateur` FOREIGN KEY (`id_enseignant`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE;
ALTER TABLE `etudiants` ADD CONSTRAINT `fk_etudiant_utilisateur` FOREIGN KEY (`id_etudiant`) REFERENCES `utilisateurs` (`id_utilisateur`) ON DELETE CASCADE;
ALTER TABLE `cours` ADD CONSTRAINT `fk_cours_enseignant` FOREIGN KEY (`id_enseignant`) REFERENCES `enseignants` (`id_enseignant`) ON DELETE SET NULL, ADD CONSTRAINT `fk_cours_matiere` FOREIGN KEY (`id_matiere`) REFERENCES `matieres` (`id_matiere`) ON DELETE SET NULL;
ALTER TABLE `notes` ADD CONSTRAINT `fk_notes_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE CASCADE, ADD CONSTRAINT `fk_notes_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE CASCADE;
ALTER TABLE `frais_scolarite` ADD CONSTRAINT `fk_frais_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE CASCADE;
ALTER TABLE `paiements` ADD CONSTRAINT `fk_paiement_frais` FOREIGN KEY (`id_frais_scolarite`) REFERENCES `frais_scolarite` (`id_frais`) ON DELETE CASCADE;
ALTER TABLE `absences` ADD CONSTRAINT `fk_absence_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE SET NULL, ADD CONSTRAINT `fk_absence_etudiant` FOREIGN KEY (`id_etudiant`) REFERENCES `etudiants` (`id_etudiant`) ON DELETE CASCADE;
ALTER TABLE `documents` ADD CONSTRAINT `fk_document_cours` FOREIGN KEY (`id_cours`) REFERENCES `cours` (`id_cours`) ON DELETE SET NULL;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
