<?php
/**
 * Upload un fichier avec vérifications de sécurité
 * @param array $file Fichier à uploader ($_FILES['nom_du_champ'])
 * @param string $dossier Dossier de destination
 * @param array $extensions Extensions autorisées
 * @return string|false Chemin du fichier uploadé ou false en cas d'erreur
 */
function uploadFichier($file, $dossier, $extensions = ['pdf']) {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }
    
    // Vérification de l'extension
    $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($extension, $extensions)) {
        return false;
    }
    
    // Vérification du type MIME (sécurité supplémentaire)
    $mime_types = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif'
    ];
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    // Vérifier si l'extension existe dans le tableau des types MIME
    if (!isset($mime_types[$extension]) || $mime !== $mime_types[$extension]) {
        return false;
    }
    
    // Génération d'un nom de fichier unique
    $nom_fichier = uniqid() . '_' . preg_replace('/[^a-zA-Z0-9\.]/', '_', $file['name']);
    $chemin = $dossier . $nom_fichier;
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($dossier)) {
        mkdir($dossier, 0777, true);
    }
    
    // Déplacement du fichier
    if (move_uploaded_file($file['tmp_name'], $chemin)) {
        return $chemin;
    }
    
    return false;
}

/**
 * Formate une date au format français
 * @param string $date Date à formater
 * @return string Date formatée
 */
function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

/**
 * Récupère tous les prestataires
 * @param PDO $pdo Instance PDO
 * @return array Liste des prestataires
 */
function getPrestataires($pdo) {
    $stmt = $pdo->query("SELECT * FROM prestataires ORDER BY nom_entreprise");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un prestataire par son ID
 * @param PDO $pdo Instance PDO
 * @param int $id ID du prestataire
 * @return array|false Prestataire ou false si non trouvé
 */
function getPrestataireById($pdo, $id) {
    $stmt = $pdo->prepare("SELECT * FROM prestataires WHERE id = ?");
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Récupère les factures d'un prestataire
 * @param PDO $pdo Instance PDO
 * @param int $prestataire_id ID du prestataire
 * @return array Liste des factures
 */
function getFacturesByPrestataire($pdo, $prestataire_id) {
    $stmt = $pdo->prepare("SELECT * FROM factures WHERE prestataire_id = ? ORDER BY date_creation DESC");
    $stmt->execute([$prestataire_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>