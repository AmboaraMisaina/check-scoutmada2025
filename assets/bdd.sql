CREATE DATABASE checking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE checking;

-- administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE admins ADD COLUMN role ENUM('admin', 'checkin') NOT NULL DEFAULT 'admin';


-- participants
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    type ENUM('Delegue', 'Observateur', 'Comité d\'organisation', 'WOSM Team') NOT NULL,
    qr_code VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- programmes
CREATE TABLE programmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    horaire_debut TIME NOT NULL,
    horaire_fin TIME NOT NULL,
    ouvert_a VARCHAR(50) NOT NULL, -- ex: "delegue,observateur"
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table pour les journées (avec titre)
CREATE TABLE jours_programmes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    date_jour DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table pour les événements d'une journée
CREATE TABLE evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    jour_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    horaire_debut TIME NOT NULL,
    horaire_fin TIME NOT NULL,
    nb_participation INT DEFAULT NULL,
    ouvert_a VARCHAR(255), -- CSV : delegue,observateur
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (jour_id) REFERENCES jours_programmes(id) ON DELETE CASCADE
);


CREATE TABLE planing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    evenement_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE
);

-- Add column nb_participation to the evenements table
ALTER TABLE evenements
ADD COLUMN nb_participation INT DEFAULT NULL;
ALTER TABLE participants 
MODIFY COLUMN type ENUM('delegate', 'observer', 'organizing_comittee', 'wosm_team', 'volunteer', 'staff', 'partner', 'guest') NOT NULL;

-- admin par défaut (mot de passe: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');


insert into admins (username, password, role) values
('checkin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'checkin');

-- -------------------
-- DONNÉES DE TEST
-- -------------------

-- Journées programmées
INSERT INTO jours_programmes (titre, date_jour) VALUES
('Journée Test', CURDATE()),  -- Aujourd'hui
('Journée Passée', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
('Journée Futur', DATE_ADD(CURDATE(), INTERVAL 1 DAY));



