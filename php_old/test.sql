

-- Jour 1
INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, nb_participation, ouvert_a) VALUES
(1, 'Accueil des participants', 'Enregistrement et remise des badges', '08:00:00', '09:00:00', 100, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(1, 'Cérémonie d’ouverture', 'Ouverture officielle de l’événement', '09:00:00', '10:30:00', 150, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(1, 'Présentation des objectifs', 'Présentation des grandes lignes du programme', '11:00:00', '12:00:00', 120, 'Delegue,Observateur'),
(1, 'Pause déjeuner', 'Déjeuner collectif', '12:00:00', '13:30:00', 150, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(1, 'Atelier d’intégration', 'Activités de cohésion entre participants', '14:00:00', '15:30:00', 80, 'Delegue,Comité d\'organisation'),
(1, 'Soirée culturelle', 'Découverte des cultures locales', '18:00:00', '20:00:00', 200, 'Delegue,Observateur,Comité d\'organisation,WOSM Team');

-- Jour 2
INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, nb_participation, ouvert_a) VALUES
(2, 'Atelier Leadership', 'Développement des compétences en leadership', '09:00:00', '10:30:00', 60, 'Delegue,Comité d\'organisation'),
(2, 'Conférence Climat', 'Enjeux climatiques mondiaux', '11:00:00', '12:30:00', 100, 'Delegue,Observateur,WOSM Team'),
(2, 'Pause déjeuner', 'Déjeuner collectif', '12:30:00', '14:00:00', 150, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(2, 'Atelier Communication', 'Techniques de communication efficace', '14:00:00', '15:30:00', 50, 'Delegue,Comité d\'organisation'),
(2, 'Débat sur l’éducation', 'Débat interactif entre participants', '16:00:00', '17:30:00', 80, 'Delegue,Observateur'),
(2, 'Projection documentaire', 'Film éducatif suivi d’un échange', '19:00:00', '20:30:00', 100, 'Delegue,Observateur,WOSM Team');

-- Jour 3
INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, nb_participation, ouvert_a) VALUES
(3, 'Session sport et bien-être', 'Activité physique collective', '08:00:00', '09:00:00', 70, 'Delegue,Observateur'),
(3, 'Atelier Développement durable', 'Pratiques écoresponsables', '09:30:00', '11:00:00', 60, 'Delegue,Comité d\'organisation'),
(3, 'Conférence Inclusion sociale', 'Égalité et diversité', '11:30:00', '12:30:00', 90, 'Delegue,Observateur,WOSM Team'),
(3, 'Pause déjeuner', 'Déjeuner collectif', '12:30:00', '14:00:00', 150, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(3, 'Travail en groupe', 'Réalisation de projets collaboratifs', '14:00:00', '16:00:00', 80, 'Delegue,Comité d\'organisation'),
(3, 'Soirée libre', 'Espace détente et échanges libres', '18:00:00', '20:00:00', 120, 'Delegue,Observateur');

-- Jour 4
INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, nb_participation, ouvert_a) VALUES
(4, 'Présentation des projets', 'Exposé des travaux réalisés', '09:00:00', '10:30:00', 80, 'Delegue,Comité d\'organisation'),
(4, 'Conférence Innovation', 'Nouvelles technologies et impacts', '11:00:00', '12:30:00', 100, 'Delegue,Observateur,WOSM Team'),
(4, 'Pause déjeuner', 'Déjeuner collectif', '12:30:00', '14:00:00', 150, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(4, 'Atelier Gestion de projet', 'Méthodologies pratiques', '14:00:00', '15:30:00', 60, 'Delegue,Comité d\'organisation'),
(4, 'Session artistique', 'Peinture, musique et créativité', '16:00:00', '17:30:00', 90, 'Delegue,Observateur'),
(4, 'Veillée internationale', 'Partage culturel entre pays', '19:00:00', '21:00:00', 200, 'Delegue,Observateur,Comité d\'organisation,WOSM Team');

-- Jour 5
INSERT INTO evenements (jour_id, titre, description, horaire_debut, horaire_fin, nb_participation, ouvert_a) VALUES
(5, 'Marche écologique', 'Randonnée avec sensibilisation environnementale', '08:00:00', '09:30:00', 80, 'Delegue,Observateur'),
(5, 'Atelier Collaboration internationale', 'Échanges entre délégations', '10:00:00', '11:30:00', 60, 'Delegue,Comité d\'organisation'),
(5, 'Pause déjeuner', 'Déjeuner collectif', '12:00:00', '13:30:00', 150, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(5, 'Conférence Jeunesse et avenir', 'Vision pour les jeunes leaders', '14:00:00', '15:30:00', 120, 'Delegue,Observateur,WOSM Team'),
(5, 'Cérémonie de clôture', 'Clôture officielle de l’événement', '16:00:00', '17:30:00', 200, 'Delegue,Observateur,Comité d\'organisation,WOSM Team'),
(5, 'Soirée de célébration', 'Concert et festivités de fin', '19:00:00', '21:00:00', 250, 'Delegue,Observateur,Comité d\'organisation,WOSM Team');
