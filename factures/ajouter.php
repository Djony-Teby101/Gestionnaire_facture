<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$prestataires = getPrestataires($pdo);
$message = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $prestataire_id = $_POST['prestataire_id'];
    $numero_facture = $_POST['numero_facture'];
    $date_depot = $_POST['date_depot'];
    $date_retour = $_POST['date_retour'];
    $statut = $_POST['statut'];
    
    // Validation des données
    if (empty($prestataire_id) || empty($numero_facture) || empty($date_depot) || empty($_FILES['fichier_depot']['name'])) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires</div>';
    } else {
        // Vérification si le numéro de facture existe déjà
        $stmt = $pdo->prepare("SELECT id FROM factures WHERE numero_facture = ?");
        $stmt->execute([$numero_facture]);
        
        if ($stmt->rowCount() > 0) {
            $message = '<div class="alert alert-danger">Ce numéro de facture existe déjà</div>';
        } else {
            // Upload du fichier
            $fichier_path = uploadFichier($_FILES['fichier_depot'], UPLOAD_FACTURES, ['pdf']);
            
            if ($fichier_path) {
                // Convertir date_retour en NULL si vide
                $date_retour_value = (!empty($date_retour)) ? $date_retour : null;
                
                // Insertion en base
                $stmt = $pdo->prepare("INSERT INTO factures (prestataire_id, numero_facture, date_depot, date_retour, fichier_depot, statut) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                if ($stmt->execute([$prestataire_id, $numero_facture, $date_depot, $date_retour_value, $fichier_path, $statut])) {
                    $message = '<div class="alert alert-success">Facture ajoutée avec succès!</div>';
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'ajout de la facture</div>';
                }
            } else {
                $message = '<div class="alert alert-danger">Erreur lors de l\'upload du fichier. Vérifiez qu\'il s\'agit d\'un PDF valide.</div>';
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
    <title>Ajouter une Facture</title>
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
        <h2 class="mb-4">Ajouter une Facture</h2>
        
        <?= $message ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data" id="ajouterForm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="prestataire_id" class="form-label">Prestataire *</label>
                            <select class="form-select" id="prestataire_id" name="prestataire_id" required>
                                <option value="">Sélectionnez un prestataire</option>
                                <?php foreach ($prestataires as $prestataire): ?>
                                <option value="<?= $prestataire['id'] ?>" <?= (isset($_POST['prestataire_id']) && $_POST['prestataire_id'] == $prestataire['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($prestataire['nom_entreprise']) ?> (<?= htmlspecialchars($prestataire['nom_agent']) ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="numero_facture" class="form-label">Numéro de facture *</label>
                            <input type="text" class="form-control" id="numero_facture" name="numero_facture" 
                                   value="<?= isset($_POST['numero_facture']) ? htmlspecialchars($_POST['numero_facture']) : '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="date_depot" class="form-label">Date de dépôt *</label>
                            <input type="date" class="form-control" id="date_depot" name="date_depot" 
                                   value="<?= isset($_POST['date_depot']) ? $_POST['date_depot'] : date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="date_retour" class="form-label">Date de retour</label>
                            <input type="date" class="form-control" id="date_retour" name="date_retour" 
                                   value="<?= isset($_POST['date_retour']) ? $_POST['date_retour'] : '' ?>">
                            <div class="form-text">Laissez vide si aucune date de retour</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="statut" class="form-label">Statut *</label>
                            <select class="form-select" id="statut" name="statut" required>
                                <option value="Reception" <?= (isset($_POST['statut']) && $_POST['statut'] == 'Reception') ? 'selected' : '' ?>>Reception</option>
                                <option value="Rdem" <?= (isset($_POST['statut']) && $_POST['statut'] == 'Rdem') ? 'selected' : '' ?>>Rdem</option>
                                <option value="Engagement" <?= (isset($_POST['statut']) && $_POST['statut'] == 'Engagement') ? 'selected' : '' ?>>Engagement</option>
                                <option value="Retourner" <?= (isset($_POST['statut']) && $_POST['statut'] == 'Retourner') ? 'selected' : '' ?>>Retourner</option>
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
                        <label for="fichier_depot" class="form-label">Fichier PDF *</label>
                        <input type="file" class="form-control" id="fichier_depot" name="fichier_depot" accept=".pdf" required>
                        <div class="form-text">Seuls les fichiers PDF sont acceptés</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">Ajouter la facture</button>
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