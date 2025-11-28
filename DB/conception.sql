-- 🔥 1. Création de la base
CREATE DATABASE IF NOT EXISTS mahaymg1_checkscoutmada2025
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- 🔥 2. Création de l'utilisateur (même nom, même mot de passe qu'en production)
CREATE USER IF NOT EXISTS 'mahaymg1_checkscoutmada2025'@'localhost'
IDENTIFIED BY 'yfJ4CtmnnexBLYCuLt4Y';

-- 🔥 3. Donner tous les droits sur la base
GRANT ALL PRIVILEGES ON mahaymg1_checkscoutmada2025.*
TO 'mahaymg1_checkscoutmada2025'@'localhost';

-- 🔥 4. Appliquer les changements
FLUSH PRIVILEGES;



create table roles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  label VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

create table admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  role_id INT NOT NULL,
  CONSTRAINT fk_admin_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

create table participant_types (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

create table organizations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

create table participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  last_name VARCHAR(100) NOT NULL,
  first_name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  qr_code VARCHAR(255) NULL,
  country VARCHAR(100) NULL,
  photo VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NOT NULL,
  participant_type_id INT NOT NULL,
  CONSTRAINT fk_participant_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT,
  CONSTRAINT fk_participant_type FOREIGN KEY (participant_type_id) REFERENCES participant_types(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

create table programs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  organization_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NOT NULL,
  CONSTRAINT fk_program_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE program_registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  program_id INT NOT NULL,
  CONSTRAINT fk_reg_participant FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
  CONSTRAINT fk_reg_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
) ENGINE=InnoDB;

create table events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  organization_id INT NOT NULL,
  program_id INT NOT NULL,
  title VARCHAR(255) NOT NULL,
  description TEXT,
  start_time TIME NOT NULL,
  end_time TIME NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by INT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  updated_by INT NOT NULL,
  CONSTRAINT fk_event_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE RESTRICT,
  CONSTRAINT fk_event_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT,
  CONSTRAINT fk_event_updated_by FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE RESTRICT,
  CONSTRAINT fk_event_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

create table event_accreditations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_type_id INT NOT NULL,
  event_id INT NOT NULL,
  status VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NOT NULL,
  CONSTRAINT fk_event_accr_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT,
  CONSTRAINT fk_event_accr_participant_type FOREIGN KEY (participant_type_id) REFERENCES participant_types(id) ON DELETE CASCADE,
  CONSTRAINT fk_event_accr_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- etape a suivre pour completer l'inscription a un programme
create table registration_steps (
  id INT AUTO_INCREMENT PRIMARY KEY,
  organization_id INT NOT NULL,
  step VARCHAR(255) NOT NULL,
  step_order INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_step_organization FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
) ENGINE=InnoDB;


create table registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  program_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NOT NULL,
  CONSTRAINT fk_registration_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT,
  CONSTRAINT fk_registration_participant FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
  CONSTRAINT fk_registration_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE
) ENGINE=InnoDB;


create table registration_followups (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL, -- Le participant
  program_id INT NOT NULL, -- 💡 NOUVEAU : Le programme pour lequel l'étape est requise
  step_id INT NOT NULL, 
  status INT NOT NULL,
  status_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  created_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_followup_program FOREIGN KEY (program_id) REFERENCES programs(id) ON DELETE CASCADE,
  CONSTRAINT fk_followup_participant FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
  CONSTRAINT fk_followup_step FOREIGN KEY (step_id) REFERENCES registration_steps(id) ON DELETE CASCADE,
  CONSTRAINT fk_followup_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT
) ENGINE=InnoDB;



CREATE TABLE event_attendances (
  id INT AUTO_INCREMENT PRIMARY KEY,
  participant_id INT NOT NULL,
  event_id INT NOT NULL,
  checkin_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  checkout_time TIMESTAMP NULL,
  scan_type ENUM('checkin', 'checkout') NOT NULL,
  created_by INT NOT NULL,
  CONSTRAINT fk_event_attendance_participant FOREIGN KEY (participant_id) REFERENCES participants(id) ON DELETE CASCADE,
  CONSTRAINT fk_event_attendance_event FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
  CONSTRAINT fk_event_attendance_created_by FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE RESTRICT
) ENGINE=InnoDB;


CREATE TABLE badge_templates (
  id INT AUTO_INCREMENT PRIMARY KEY,
  organization_id INT NOT NULL,
  name VARCHAR(255) NOT NULL,
  file_path VARCHAR(255) NOT NULL,      -- chemin vers le PDF ou image
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_badge_template_org FOREIGN KEY (organization_id) REFERENCES organizations(id) ON DELETE CASCADE
);

CREATE TABLE badge_fields (
  id INT AUTO_INCREMENT PRIMARY KEY,
  badge_template_id INT NOT NULL,
  field_name VARCHAR(100) NOT NULL,      -- ex: "first_name", "last_name", "qr_code"
  pos_x INT NOT NULL,                     -- position X sur le badge
  pos_y INT NOT NULL,                     -- position Y sur le badge
  font_size INT DEFAULT 12,
  font_color VARCHAR(20) DEFAULT '#000000',
  width INT NULL,                         -- largeur du champ si besoin
  height INT NULL,                        -- hauteur du champ si besoin
  CONSTRAINT fk_badge_field_template FOREIGN KEY (badge_template_id) REFERENCES badge_templates(id) ON DELETE CASCADE
);


insert into S_admins (username, password, created_at, updated_at, role_id) values
('superadmin', '$2y$13$OrPm7S0Y5R2ftqUNdnlIEOXyIDHxWBi96nmtuoh4LRS.bVhxm6jjq', NOW(), NOW(), 4); -- mot de passe: superpassword

insert into S_roles (label, description, created_at, updated_at) values
('ROLE_ADMIN', 'Accès complet à toutes les fonctionnalités et données.', NOW(), NOW()),
('ROLE_MODERATOR', 'Peut surveiller les inscriptions et la participation aux événements.', NOW(), NOW());

insert into S_program_registrations (participant_id, program_id,created_by) values
(2, 2 , 1);