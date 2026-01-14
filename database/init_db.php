<?php
// Script d'initialisation de la base de données
require_once '../includes/config.php';

try {
    // Création de la table prestataires
    $sqlPrestataires = "CREATE TABLE IF NOT EXISTS prestataires (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        nom_entreprise VARCHAR(255) NOT NULL,
        nom_agent VARCHAR(255) NOT NULL,
        telephone VARCHAR(20) NOT NULL,
        photo VARCHAR(255),
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    $pdo->exec($sqlPrestataires);
    
    // Création de la table factures avec les nouveaux statuts et date_retour
    $sqlFactures = "CREATE TABLE IF NOT EXISTS factures (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        prestataire_id INT(11) NOT NULL,
        numero_facture VARCHAR(100) NOT NULL UNIQUE,
        date_depot DATE NOT NULL,
        date_retour DATE NULL,
        fichier_depot VARCHAR(255) NOT NULL,
        statut ENUM('Reception', 'Rdem', 'Engagement', 'Retourner') DEFAULT 'Reception',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (prestataire_id) REFERENCES prestataires(id) ON DELETE CASCADE
    )";
    
    $pdo->exec($sqlFactures);
    
    // Ajout de la colonne date_retour si elle n'existe pas déjà
    $checkColumn = $pdo->query("SHOW COLUMNS FROM factures LIKE 'date_retour'");
    if ($checkColumn->rowCount() == 0) {
        $pdo->exec("ALTER TABLE factures ADD COLUMN date_retour DATE NULL AFTER date_depot");
    }
    
    echo "Base de données initialisée avec succès!";
    
} catch(PDOException $e) {
    die("Erreur lors de l'initialisation: " . $e->getMessage());
}
?>