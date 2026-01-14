<?php
// Configuration de la base de données
define('DB_HOST', 'localhost');
define('DB_NAME', 'facture_system');
define('DB_USER', 'root');
define('DB_PASS', '');

// Chemins des dossiers d'upload
define('UPLOAD_FACTURES', 'uploads/factures/');
define('UPLOAD_PHOTOS', 'uploads/photos/');

// Connexion à la base avec PDO
try {
    $pdo = new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8', DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
}
?>