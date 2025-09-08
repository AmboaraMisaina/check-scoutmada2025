-- administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'checkin') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- participants
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    type ENUM('delegate', 'observer', 'organizing_comittee', 'wosm_team', 'volunteer', 'staff', 'partner', 'guest') NOT NULL,
    qr_code VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


-- Table pour les événements d'une journée
CREATE TABLE evenements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(255) NOT NULL,
    description TEXT,
    horaire_debut TIME NOT NULL,
    horaire_fin TIME NOT NULL,
    nb_participation BOOLEAN DEFAULT NULL,
    ouvert_a VARCHAR(255), -- CSV : delegue,observateur
    date_evenement DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);


CREATE TABLE enregistrement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    participant_id INT NOT NULL,
    evenement_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
    FOREIGN KEY (evenement_id) REFERENCES evenements(id) ON DELETE CASCADE
);



-- ========================================//===========================================












-- admin par défaut (mot de passe: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO admins (username, password, role) VALUES
('checkin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'checkin');

-- -------------------
-- DONNÉES DE TEST
-- -------------------

-- Journées programmées
INSERT INTO jours_programmes (titre, date_jour) VALUES
('Journée Test', CURDATE()),  -- Aujourd'hui
('Journée Passée', DATE_SUB(CURDATE(), INTERVAL 1 DAY)),
('Journée Futur', DATE_ADD(CURDATE(), INTERVAL 1 DAY));

-- Événements
INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, ouvert_a) VALUES
(1, 'Événement Matin ohatra misakafo maraina', 'Test événement aujourd\'hui matin', '09:00:00', '12:00:00', 'delegue,observateur'),
(1, 'Événement Après-midi ohatra milalao', 'Test événement aujourd\'hui après-midi', '14:00:00', '16:00:00', 'delegue'),
(2, 'Événement Passé', 'Événement d\'hier', '10:00:00', '11:00:00', 'observateur'),
(3, 'Événement Futur', 'Événement demain', '15:00:00', '17:00:00', 'delegue,observateur');


INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, ouvert_a) VALUES
(1, 'Événement Soir', 'Test événement ce soir', '18:00:00', '23:59:00', 'delegue,observateur');

-- Participants
INSERT INTO participants (nom, prenom, email, type, qr_code) VALUES
('Rakoto', 'Jean', 'jean.rakoto@test.com', 'delegue', NULL),
('Rabe', 'Marie', 'marie.rabe@test.com', 'observateur', NULL),
('Andrian', 'Luc', 'luc.andrian@test.com', 'delegue', NULL),
('Rasolon', 'Sofia', 'sofia.rasolon@test.com', 'observateur', NULL);


