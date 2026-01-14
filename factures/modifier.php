<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$id = intval($_GET['id']);
$prestataires = getPrestataires($pdo);
$message = '';

// Récupération de la facture à modifier
$stmt = $pdo->prepare("SELECT * FROM factures WHERE id = ?");
$stmt->execute([$id]);
$facture = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$facture) {
    die("Facture non trouvée");
}

// Traitement du formulaire de modification
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestataire_id = $_POST['prestataire_id'];
    $numero_facture = $_POST['numero_facture'];
    $date_depot = $_POST['date_depot'];
    $date_retour = $_POST['date_retour'];
    $statut = $_POST['statut'];
    
    // Validation des données
    if (empty($prestataire_id) || empty($numero_facture) || empty($date_depot)) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires</div>';
    } else {
        // Vérification si le numéro de facture existe déjà (sauf pour la facture courante)
        $stmt = $pdo->prepare("SELECT id FROM factures WHERE numero_facture = ? AND id != ?");
        $stmt->execute([$numero_facture, $id]);
        
        if ($stmt->rowCount() > 0) {
            $message = '<div class="alert alert-danger">Ce numéro de facture existe déjà</div>';
        } else {
            $fichier_path = $facture['fichier_depot'];
            
            // Traitement du nouvel upload de fichier si fourni
            if (!empty($_FILES['fichier_depot']['name'])) {
                $nouveau_fichier_path = uploadFichier($_FILES['fichier_depot'], UPLOAD_FACTURES, ['pdf']);
                if ($nouveau_fichier_path) {
                    // Suppression de l'ancien fichier
                    if (file_exists($fichier_path)) {
                        unlink($fichier_path);
                    }
                    $fichier_path = $nouveau_fichier_path;
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'upload du fichier. Vérifiez qu\'il s\'agit d\'un PDF valide.</div>';
                }
            }
            
            if (empty($message)) {
                // Mise à jour en base
                $stmt = $pdo->prepare("UPDATE factures SET prestataire_id = ?, numero_facture = ?, date_depot = ?, date_retour = ?, fichier_depot = ?, statut = ? WHERE id = ?");
                
                // Convertir date_retour en NULL si vide
                $date_retour_value = (!empty($date_retour)) ? $date_retour : null;
                
                if ($stmt->execute([$prestataire_id, $numero_facture, $date_depot, $date_retour_value, $fichier_path, $statut, $id])) {
                    $message = '<div class="alert alert-success">Facture modifiée avec succès!</div>';
                    // Rechargement des données
                    $stmt = $pdo->prepare("SELECT * FROM factures WHERE id = ?");
                    $stmt->execute([$id]);
                    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de la modification de la facture</div>';
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier une Facture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .form-check-label {
            font-weight: normal;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">Modifier la Facture</h2>
        
        <?= $message ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="modifierForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="prestataire_id" class="form-label">Prestataire *</label>
                            <select class="form-select" id="prestataire_id" name="prestataire_id" required>
                                <option value="">Sélectionnez un prestataire</option>
                                <?php foreach ($prestataires as $prestataire): ?>
                                <option value="<?= $prestataire['id'] ?>" <?= ($facture['prestataire_id'] == $prestataire['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prestataire['nom_entreprise']) ?> (<?= htmlspecialchars($prestataire['nom_agent']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="numero_facture" class="form-label">Numéro de facture *</label>
                            <input type="text" class="form-control" id="numero_facture" name="numero_facture" 
                                   value="<?= htmlspecialchars($facture['numero_facture']) ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_depot" class="form-label">Date de dépôt *</label>
                            <input type="date" class="form-control" id="date_depot" name="date_depot" 
                                   value="<?= $facture['date_depot'] ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="date_retour" class="form-label">Date de retour</label>
                            <input type="date" class="form-control" id="date_retour" name="date_retour" 
                                   value="<?= $facture['date_retour'] ?>">
                            <div class="form-text">Laissez vide si aucune date de retour</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="statut" class="form-label">Statut *</label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="Reception" <?= ($facture['statut'] == 'Reception') ? 'selected' : '' ?>>Reception</option>
                                <option value="Rdem" <?= ($facture['statut'] == 'Rdem') ? 'selected' : '' ?>>Rdem</option>
                                <option value="Engagement" <?= ($facture['statut'] == 'Engagement') ? 'selected' : '' ?>>Engagement</option>
                                <option value="Retourner" <?= ($facture['statut'] == 'Retourner') ? 'selected' : '' ?>>Retourner</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" id="set_date_retour">
                                <label class="form-check-label" for="set_date_retour">
                                    Définir la date de retour automatiquement
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fichier_depot" class="form-label">Fichier PDF</label>
                        <input type="file" class="form-control" id="fichier_depot" name="fichier_depot" accept=".pdf">
                        <div class="form-text">
                            Fichier actuel: <?= basename($facture['fichier_depot']) ?><br>
                            Laissez vide pour conserver le fichier actuel. Seuls les fichiers PDF sont acceptés.
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">Modifier la facture</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const statutSelect = document.getElementById('statut');
        const dateRetourInput = document.getElementById('date_retour');
        const setDateRetourCheckbox = document.getElementById('set_date_retour');
        
        // Fonction pour définir automatiquement la date de retour
        function setDateRetourAutomatically() {
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            dateRetourInput.value = formattedDate;
        }
        
        // Définir la date de retour automatiquement quand le statut est "Retourner"
        function updateDateRetour() {
            if (statutSelect.value === 'Retourner') {
                setDateRetourAutomatically();
                setDateRetourCheckbox.checked = true;
            } else {
                setDateRetourCheckbox.checked = false;
            }
        }
        
        // Événement de changement sur le statut
        statutSelect.addEventListener('change', updateDateRetour);
        
        // Événement sur la checkbox
        setDateRetourCheckbox.addEventListener('change', function() {
            if (this.checked) {
                setDateRetourAutomatically();
            } else {
                dateRetourInput.value = '';
            }
        });
        
        // Initialisation
        updateDateRetour();
    });
    </script>
</body>
</html>