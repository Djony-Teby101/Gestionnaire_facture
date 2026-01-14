<?php
require_once '../includes/config.php';

if (!isset($_GET['id']) || !isset($_GET['action'])) {
    die("Paramètres manquants");
}

$id = intval($_GET['id']);
$action = $_GET['action'];

// Récupération de la facture
$stmt = $pdo->prepare("SELECT * FROM factures WHERE id = ?");
$stmt->execute([$id]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    die("Facture non trouvée");
}

// Vérification que le fichier existe
if (!file_exists($facture['fichier_depot'])) {
    die("Fichier non trouvé");
}

// Envoi du fichier selon l'action demandée
if ($action === 'view') {
    // Affichage dans le navigateur
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($facture['fichier_depot']) . '"');
} elseif ($action === 'download') {
    // Téléchargement
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . basename($facture['fichier_depot']) . '"');
} else {
    die("Action non valide");
}

header('Content-Length: ' . filesize($facture['fichier_depot']));
readfile($facture['fichier_depot']);
exit;