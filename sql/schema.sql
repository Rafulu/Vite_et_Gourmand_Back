-- ============================================================
-- Vite & Gourmand — Structure de la base de données (MariaDB)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS reserved_resources;
DROP TABLE IF EXISTS order_status_history;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS orders;
DROP TABLE IF EXISTS tokens;
DROP TABLE IF EXISTS addresses;
DROP TABLE IF EXISTS composition_menu;
DROP TABLE IF EXISTS allergen_dish;
DROP TABLE IF EXISTS allergens;
DROP TABLE IF EXISTS dish_images;
DROP TABLE IF EXISTS dishes;
DROP TABLE IF EXISTS menu_image;
DROP TABLE IF EXISTS images;
DROP TABLE IF EXISTS menus;
DROP TABLE IF EXISTS themes;
DROP TABLE IF EXISTS condition_dish;
DROP TABLE IF EXISTS condition_menu;
DROP TABLE IF EXISTS conditions;
DROP TABLE IF EXISTS production_planning;
DROP TABLE IF EXISTS reserved_resources;
DROP TABLE IF EXISTS resources;
DROP TABLE IF EXISTS schedules;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS role;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE role (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(50) NOT NULL
);

CREATE TABLE users (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) DEFAULT NULL,
    role_id INT(11) NOT NULL,
    create_at DATETIME NOT NULL,
    is_blocked TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (role_id) REFERENCES role(id) ON UPDATE CASCADE
);

CREATE TABLE tokens (
    id UUID NOT NULL,
    value VARCHAR(255) NOT NULL,
    user_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    expires_at DATETIME NOT NULL,
    is_used TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE addresses (
    id INT(10) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    number VARCHAR(10) DEFAULT NULL,
    street VARCHAR(255) NOT NULL,
    complement VARCHAR(255) DEFAULT NULL,
    postal_code VARCHAR(20) NOT NULL,
    city VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL DEFAULT 'France',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE themes (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE menus (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    min_guests INT(11) NOT NULL,
    price_per_person DECIMAL(10,2) NOT NULL,
    preparation_time INT(11) NOT NULL,
    theme_id INT(11) DEFAULT NULL,
    description TEXT NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    FOREIGN KEY (theme_id) REFERENCES themes(id) ON UPDATE CASCADE
);

CREATE TABLE images (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    path VARCHAR(255) NOT NULL,
    alt VARCHAR(255) NOT NULL
);

CREATE TABLE menu_image (
    menu_id INT(11) NOT NULL,
    image_id INT(11) NOT NULL,
    FOREIGN KEY (image_id) REFERENCES images(id) ON UPDATE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON UPDATE CASCADE
);

CREATE TABLE dishes (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    category ENUM('ENTREE','PLAT','DESSERT','') NOT NULL,
    diet ENUM('VEGAN','VEGETARIEN','CLASSIQUE','') NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    preparation_time INT(11) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE dish_images (
    dish_id INT(11) NOT NULL,
    image_id INT(11) NOT NULL,
    FOREIGN KEY (dish_id) REFERENCES dishes(id),
    FOREIGN KEY (image_id) REFERENCES images(id)
);

CREATE TABLE allergens (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    icon VARCHAR(255) NOT NULL
);

CREATE TABLE allergen_dish (
    allergen_id INT(11) NOT NULL,
    dish_id INT(11) NOT NULL,
    FOREIGN KEY (allergen_id) REFERENCES allergens(id) ON UPDATE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON UPDATE CASCADE
);

CREATE TABLE composition_menu (
    menu_id INT(11) NOT NULL,
    dish_id INT(11) NOT NULL,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON UPDATE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON UPDATE CASCADE
);

CREATE TABLE conditions (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    type ENUM('TEMPORELLE','MATERIELLE','QUANTITATIVE','') NOT NULL,
    value VARCHAR(255) NOT NULL,
    unit VARCHAR(50) DEFAULT NULL,
    description TEXT NOT NULL
);

CREATE TABLE condition_dish (
    condition_id INT(11) NOT NULL,
    dish_id INT(11) NOT NULL,
    FOREIGN KEY (condition_id) REFERENCES conditions(id) ON UPDATE CASCADE,
    FOREIGN KEY (dish_id) REFERENCES dishes(id) ON UPDATE CASCADE
);

CREATE TABLE condition_menu (
    condition_id INT(11) NOT NULL,
    menu_id INT(11) NOT NULL,
    FOREIGN KEY (condition_id) REFERENCES conditions(id) ON UPDATE CASCADE,
    FOREIGN KEY (menu_id) REFERENCES menus(id) ON UPDATE CASCADE
);

CREATE TABLE orders (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    order_number VARCHAR(20) NOT NULL,
    user_id INT(11) NOT NULL,
    delivery_address_id INT(11) NOT NULL,
    billing_address_id INT(11) DEFAULT NULL,
    menu_id INT(11) NOT NULL,
    order_date DATETIME NOT NULL,
    delivery_date DATETIME NOT NULL,
    detail TEXT DEFAULT NULL,
    guest_count INT(11) NOT NULL,
    menu_price DECIMAL(10,2) NOT NULL,
    option_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    delivery_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount TINYINT(1) NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    equipement_loan TINYINT(1) NOT NULL DEFAULT 0,
    equipement_return TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('EN_ATTENTE','ACCEPTEE','EN_PREPARATION','PRET','EN_LIVRAISON','LIVREE','ATTENTE_MATERIEL','TERMINEE','ANNULEE') NOT NULL DEFAULT 'EN_ATTENTE',
    cook_id INT(11) DEFAULT NULL,
    driver_id INT(11) DEFAULT NULL,
    update_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    internal_comment TEXT DEFAULT NULL,
    FOREIGN KEY (menu_id) REFERENCES menus(id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE,
    FOREIGN KEY (delivery_address_id) REFERENCES addresses(id) ON UPDATE CASCADE,
    FOREIGN KEY (billing_address_id) REFERENCES addresses(id) ON UPDATE CASCADE,
    FOREIGN KEY (cook_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE order_status_history (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    old_status ENUM('EN_ATTENTE','ACCEPTEE','EN_PREPARATION','PRET','EN_LIVRAISON','LIVREE','ATTENTE_MATERIEL','TERMINEE','ANNULEE') DEFAULT NULL,
    new_status ENUM('EN_ATTENTE','ACCEPTEE','EN_PREPARATION','PRET','EN_LIVRAISON','LIVREE','ATTENTE_MATERIEL','TERMINEE','ANNULEE') NOT NULL,
    changed_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    author_id INT(11) NOT NULL,
    cancellation_reason TEXT DEFAULT NULL,
    contact_channel VARCHAR(100) DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE,
    FOREIGN KEY (author_id) REFERENCES users(id) ON UPDATE CASCADE
);

CREATE TABLE reviews (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    order_id INT(11) NOT NULL,
    note INT(11) NOT NULL,
    comment TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    is_validated TINYINT(1) NOT NULL DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id) ON UPDATE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE
);

CREATE TABLE production_planning (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    date DATE NOT NULL,
    max_capacity INT(11) NOT NULL,
    used_capacity INT(11) NOT NULL DEFAULT 0,
    UNIQUE KEY unique_date (date)
);

CREATE TABLE resources (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(150) NOT NULL,
    type ENUM('MATERIEL_LOURD','MATERIEL_LEGER','CONSOMMABLE','PERSONNEL') NOT NULL,
    total_quantity INT(11) NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL
);

CREATE TABLE reserved_resources (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    resource_id INT(11) NOT NULL,
    quantity INT(11) NOT NULL,
    delivery_date DATETIME NOT NULL,
    return_date DATETIME DEFAULT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON UPDATE CASCADE,
    FOREIGN KEY (resource_id) REFERENCES resources(id) ON UPDATE CASCADE
);

CREATE TABLE schedules (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    day ENUM('LUNDI','MARDI','MERCREDI','JEUDI','VENDREDI','SAMEDI','DIMANCHE') NOT NULL,
    opening_time TIME NOT NULL,
    closing_time TIME NOT NULL,
    is_open TINYINT(1) NOT NULL DEFAULT 1
);

CREATE TABLE settings (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL,
    value VARCHAR(255) NOT NULL
);