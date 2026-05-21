-- ============================================================
-- Vite & Gourmand — Données de test
-- À exécuter après schema.sql
-- ============================================================

-- Rôles
INSERT INTO role (id, name) VALUES
(1, 'ADMIN'),
(2, 'MANAGER'),
(3, 'CUISINIER'),
(4, 'LIVREUR'),
(5, 'CLIENT'),
(6, 'EMPLOYE_POLYVALENT');

-- Utilisateurs
-- admin@test.com      → Admin1234!
-- manager@test.com    → password
-- cook@test.com       → password
-- driver@test.com     → password
-- client@test.com     → password
INSERT INTO users (id, email, password, last_name, first_name, phone, role_id, create_at, is_blocked) VALUES
(1, 'admin@test.com',   '$2y$10$pzDq5yWSorqgP7l7KfMqJutUomDz7D/JCsvBXNJTjgbHTPNyvEpGS', 'Bordeaux',  'José', '0600000001', 1, NOW(), 0),
(2, 'manager@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Martin',  'Julie',  '0600000002', 2, NOW(), 0),
(3, 'cook@test.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dupont',  'Pierre',  '0600000003', 3, NOW(), 0),
(4, 'driver@test.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Bernard', 'Marc',    '0600000004', 4, NOW(), 0),
(5, 'client@test.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Durand',  'Alice',   '0600000005', 5, NOW(), 0),
(6, 'client2@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Moreau',  'Jean',    '0600000006', 5, NOW(), 0);

-- Thèmes
INSERT INTO themes (id, name) VALUES
(1, 'Noël'),
(2, 'Pâques'),
(3, 'Classique'),
(4, 'Événement');

-- Adresses
INSERT INTO addresses (user_id, name, number, street, postal_code, city, country) VALUES
(5, 'Domicile', '12', 'Rue des Fleurs',          '33000', 'Bordeaux', 'France'),
(5, 'Bureau',   '45', 'Avenue de la République', '33100', 'Bordeaux', 'France'),
(6, 'Domicile', '3',  'Rue Victor Hugo',          '33200', 'Bordeaux', 'France');

-- Menus
INSERT INTO menus (id, name, min_guests, price_per_person, preparation_time, theme_id, description, is_active) VALUES
(1, 'Menu Noël Festif',         10, 40.00, 120, 1, 'Un menu festif pour les fêtes de Noël',    1),
(2, 'Menu Végétarien',           5, 37.00,  90, 3, 'Un menu 100% végétarien et équilibré',     1),
(3, 'Menu Classique Prestige',   8, 36.00, 100, 3, 'Notre menu classique intemporel',           1);

-- Plats
INSERT INTO dishes (id, name, category, diet, unit_price, preparation_time, is_active) VALUES
(1, 'Velouté de potiron',        'ENTREE', 'VEGETARIEN', 12.00, 30, 1),
(2, 'Filet de boeuf',            'PLAT',   'CLASSIQUE',  18.00, 45, 1),
(3, 'Tarte Tatin',               'DESSERT','VEGETARIEN', 10.00, 40, 1),
(4, 'Salade Caesar',             'ENTREE', 'CLASSIQUE',   8.00, 15, 1),
(5, 'Risotto aux champignons',   'PLAT',   'VEGETARIEN', 15.00, 35, 1);

-- Composition menus
INSERT INTO composition_menu (menu_id, dish_id) VALUES
(1, 1), (1, 2), (1, 3),
(2, 1), (2, 5), (2, 3),
(3, 4), (3, 2), (3, 3);

-- Commandes
INSERT INTO orders (order_number, user_id, delivery_address_id, billing_address_id, menu_id, order_date, delivery_date, guest_count, menu_price, option_price, delivery_price, discount, total_price, status, cook_id, driver_id) VALUES
('VG-20260521-AAA001', 5, 1, 1, 1, NOW(), '2026-06-15 12:00:00', 10, 400.00, 0.00, 0.00, 0, 400.00, 'TERMINEE',      3, 4),
('VG-20260521-AAA002', 5, 2, 2, 2, NOW(), '2026-06-20 19:00:00', 8,  296.00, 0.00, 9.49, 0, 305.49, 'ACCEPTEE',      NULL, NULL),
('VG-20260521-AAA003', 6, 3, 3, 3, NOW(), '2026-06-25 13:00:00', 15, 540.00, 0.00, 9.49, 1, 549.49, 'EN_ATTENTE',    NULL, NULL),
('VG-20260521-AAA004', 5, 1, 1, 1, NOW(), '2026-07-01 12:00:00', 20, 720.00, 0.00, 9.49, 1, 729.49, 'EN_PREPARATION', 3, NULL);

-- Historique statuts
INSERT INTO order_status_history (order_id, old_status, new_status, author_id) VALUES
(1, NULL,        'EN_ATTENTE',    5),
(1, 'EN_ATTENTE','ACCEPTEE',      1),
(1, 'ACCEPTEE',  'EN_PREPARATION',3),
(1, 'EN_PREPARATION','PRET',      3),
(1, 'PRET',      'EN_LIVRAISON',  4),
(1, 'EN_LIVRAISON','LIVREE',      4),
(1, 'LIVREE',    'TERMINEE',      1),
(2, NULL,        'EN_ATTENTE',    5),
(2, 'EN_ATTENTE','ACCEPTEE',      2),
(3, NULL,        'EN_ATTENTE',    6),
(4, NULL,        'EN_ATTENTE',    5),
(4, 'EN_ATTENTE','ACCEPTEE',      1),
(4, 'ACCEPTEE',  'EN_PREPARATION',3);

-- Avis
INSERT INTO reviews (user_id, order_id, note, comment, is_validated) VALUES
(5, 1, 5, 'Excellent service, repas délicieux !', 1),
-- (pas de commande TERMINEE pour client 6 dans ces données de test)

-- Planning de production
INSERT INTO production_planning (date, max_capacity, used_capacity) VALUES
('2026-06-15', 840, 10),
('2026-06-20', 840, 8),
('2026-06-25', 840, 15),
('2026-07-01', 840, 20);

-- Settings
INSERT INTO settings (setting_key, value) VALUES
('daily_capacity_minutes', '840');