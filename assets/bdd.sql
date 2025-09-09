-- administrateurs
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'checkin' ,'registration') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

ALTER TABLE admins
MODIFY COLUMN role ENUM('admin', 'checkin', 'registration') NOT NULL DEFAULT 'admin';


-- participants
CREATE TABLE participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    type ENUM('delegate', 'observer', 'organizing_committee', 'wosm_team', 'volunteer', 'staff', 'partner', 'guest') NOT NULL,
    qr_code VARCHAR(255) NULL,
    pays VARCHAR(100) NULL,
    photo VARCHAR(255) NULL,
    isPrinted BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

alter table participants add column isPrinted BOOLEAN DEFAULT FALSE;
alter table participants add column pays VARCHAR(100) NULL;


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

create table pays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    code VARCHAR(10) NOT NULL
);

alter table participants add column photo VARCHAR(255) NULL;


-- ========================================//===========================================






INSERT INTO pays (nom, code) VALUES
('Afghanistan', 'AF'),
('Afrique du Sud', 'ZA'),
('Albanie', 'AL'),
('Algérie', 'DZ'),
('Allemagne', 'DE'),
('Andorre', 'AD'),
('Angola', 'AO'),
('Arabie Saoudite', 'SA'),
('Argentine', 'AR'),
('Arménie', 'AM'),
('Australie', 'AU'),
('Autriche', 'AT'),
('Azerbaïdjan', 'AZ'),
('Bahamas', 'BS'),
('Bahreïn', 'BH'),
('Bangladesh', 'BD'),
('Barbade', 'BB'),
('Belgique', 'BE'),
('Belize', 'BZ'),
('Bénin', 'BJ'),
('Bhoutan', 'BT'),
('Biélorussie', 'BY'),
('Birmanie', 'MM'),
('Bolivie', 'BO'),
('Bosnie-Herzégovine', 'BA'),
('Botswana', 'BW'),
('Brésil', 'BR'),
('Brunei', 'BN'),
('Bulgarie', 'BG'),
('Burkina Faso', 'BF'),
('Burundi', 'BI'),
('Cambodge', 'KH'),
('Cameroun', 'CM'),
('Canada', 'CA'),
('Cap-Vert', 'CV'),
('Chili', 'CL'),
('Chine', 'CN'),
('Chypre', 'CY'),
('Colombie', 'CO'),
('Comores', 'KM'),
('Congo (Brazzaville)', 'CG'),
('Congo (Kinshasa)', 'CD'),
('Corée du Nord', 'KP'),
('Corée du Sud', 'KR'),
('Costa Rica', 'CR'),
('Côte d’Ivoire', 'CI'),
('Croatie', 'HR'),
('Cuba', 'CU'),
('Danemark', 'DK'),
('Djibouti', 'DJ'),
('Dominique', 'DM'),
('Égypte', 'EG'),
('Émirats arabes unis', 'AE'),
('Équateur', 'EC'),
('Érythrée', 'ER'),
('Espagne', 'ES'),
('Estonie', 'EE'),
('Eswatini', 'SZ'),
('États-Unis', 'US'),
('Éthiopie', 'ET'),
('Fidji', 'FJ'),
('Finlande', 'FI'),
('France', 'FR'),
('Gabon', 'GA'),
('Gambie', 'GM'),
('Géorgie', 'GE'),
('Ghana', 'GH'),
('Grèce', 'GR'),
('Grenade', 'GD'),
('Guatemala', 'GT'),
('Guinée', 'GN'),
('Guinée-Bissau', 'GW'),
('Guinée équatoriale', 'GQ'),
('Guyana', 'GY'),
('Haïti', 'HT'),
('Honduras', 'HN'),
('Hongrie', 'HU'),
('Inde', 'IN'),
('Indonésie', 'ID'),
('Irak', 'IQ'),
('Iran', 'IR'),
('Irlande', 'IE'),
('Islande', 'IS'),
('Israël', 'IL'),
('Italie', 'IT'),
('Jamaïque', 'JM'),
('Japon', 'JP'),
('Jordanie', 'JO'),
('Kazakhstan', 'KZ'),
('Kenya', 'KE'),
('Kirghizistan', 'KG'),
('Kiribati', 'KI'),
('Koweït', 'KW'),
('Laos', 'LA'),
('Lesotho', 'LS'),
('Lettonie', 'LV'),
('Liban', 'LB'),
('Libéria', 'LR'),
('Libye', 'LY'),
('Liechtenstein', 'LI'),
('Lituanie', 'LT'),
('Luxembourg', 'LU');






-- admin par défaut (mot de passe: admin123)
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

INSERT INTO admins (username, password, role) VALUES
('checkin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'checkin');

-- -------------------
-- DONNÉES DE TEST
-- -------------------


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


