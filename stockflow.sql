-- ============================================================
--  StockFlow – Base de données XAMPP/MySQL
--  Projet BD – ESISA 2ème Année
-- ============================================================

CREATE DATABASE IF NOT EXISTS stockflow CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE stockflow;

-- -------------------------------------------------------
-- Table : categories_produits
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS categories_produits (
    id   INT AUTO_INCREMENT PRIMARY KEY,
    nom  VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Table : produits
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS produits (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id   INT NOT NULL,
    reference      VARCHAR(50) UNIQUE NOT NULL,
    nom            VARCHAR(200) NOT NULL,
    description    TEXT,
    prix_unitaire  DECIMAL(10,2) NOT NULL DEFAULT 0,
    seuil_alerte   INT NOT NULL DEFAULT 5,
    FOREIGN KEY (categorie_id) REFERENCES categories_produits(id)
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Table : mouvements_stock
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS mouvements_stock (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    produit_id       INT NOT NULL,
    type_mouvement   ENUM('entree','sortie') NOT NULL,
    quantite         INT NOT NULL,
    date_mouvement   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    commentaire      VARCHAR(255),
    utilisateur      VARCHAR(50),
    FOREIGN KEY (produit_id) REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -------------------------------------------------------
-- Données de test
-- -------------------------------------------------------
INSERT INTO categories_produits (nom) VALUES
  ('Alimentation'),
  ('Boissons'),
  ('Hygiène'),
  ('Papeterie'),
  ('Électronique');

INSERT INTO produits (categorie_id, reference, nom, description, prix_unitaire, seuil_alerte) VALUES
  (1, 'ALI-001', 'Huile olive 1L',        'Huile extra vierge',            8.50,  10),
  (1, 'ALI-002', 'Farine de ble 1kg',    'Farine type 55',                1.20,  15),
  (2, 'BOI-001', 'Eau minerale 1.5L',    'Pack de 6',                     0.80,  20),
  (2, 'BOI-002', 'Jus orange 1L',        'Pur jus sans sucres ajoutes',   2.40,   8),
  (3, 'HYG-001', 'Savon liquide 500ml',  'Savon antibactérien',           3.90,   5),
  (4, 'PAP-001', 'Ramette papier A4',    '80g/m², 500 feuilles',          4.50,  10),
  (5, 'ELE-001', 'Câble USB-C 1m',       'Charge rapide 60W',             9.99,   3);

-- Entrées initiales (stock de départ)
INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, commentaire, utilisateur) VALUES
  (1, 'entree', 50, 'Stock initial',     'Admin'),
  (2, 'entree', 80, 'Stock initial',     'Admin'),
  (3, 'entree', 120,'Stock initial',     'Admin'),
  (4, 'entree', 40, 'Stock initial',     'Admin'),
  (5, 'entree', 30, 'Stock initial',     'Admin'),
  (6, 'entree', 25, 'Stock initial',     'Admin'),
  (7, 'entree', 15, 'Stock initial',     'Admin');

-- Quelques sorties de test
INSERT INTO mouvements_stock (produit_id, type_mouvement, quantite, commentaire, utilisateur) VALUES
  (1, 'sortie', 45, 'Vente comptoir',    'Marie'),
  (2, 'sortie', 70, 'Vente comptoir',    'Karim'),
  (3, 'sortie', 110,'Vente en gros',     'Marie'),
  (5, 'sortie', 27, 'Vente comptoir',    'Karim'),
  (7, 'sortie', 13, 'Vente en ligne',    'Admin');

-- -------------------------------------------------------
-- Table : utilisateurs (ajoutée pour la connexion / inscription)
-- Aucune table existante n'a été modifiée : ceci est un AJOUT.
-- -------------------------------------------------------
CREATE TABLE IF NOT EXISTS utilisateurs (
    id             INT AUTO_INCREMENT PRIMARY KEY,
    nom            VARCHAR(100) NOT NULL,
    email          VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe   VARCHAR(255) NOT NULL,
    role           ENUM('user','admin') NOT NULL DEFAULT 'user',
    date_creation  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Vue pratique : stock actuel par produit
CREATE OR REPLACE VIEW vue_stock AS
SELECT
    p.id,
    p.reference,
    p.nom,
    c.nom                                                                AS categorie,
    p.prix_unitaire,
    p.seuil_alerte,
    COALESCE(SUM(CASE WHEN m.type_mouvement = 'entree' THEN m.quantite
                      ELSE -m.quantite END), 0)                         AS stock_actuel
FROM produits p
LEFT JOIN mouvements_stock m     ON p.id = m.produit_id
LEFT JOIN categories_produits c  ON p.categorie_id = c.id
GROUP BY p.id;
