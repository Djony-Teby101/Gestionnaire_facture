<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$message = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_entreprise = trim($_POST['nom_entreprise']);
    $nom_agent = trim($_POST['nom_agent']);
    $telephone = trim($_POST['telephone']);
    
    // Validation des données
    if (empty($nom_entreprise) || empty($nom_agent) || empty($telephone)) {
        $message = '<div class="alert alert-danger">Veuillez remplir tous les champs obligatoires</div>';
    } else {
        $photo_path = null;
        
        // Traitement de l'upload de photo si fourni
        if (!empty($_FILES['photo']['name'])) {
            $photo_path = uploadFichier($_FILES['photo'], UPLOAD_PHOTOS, ['jpg', 'jpeg', 'png', 'gif']);
            if (!$photo_path) {
                $message = '<div class="alert alert-danger">Erreur lors de l\'upload de la photo. Formats acceptés: JPG, PNG, GIF.</div>';
            }
        }
        
        if (empty($message)) {
            try {
                // Insertion en base
                $stmt = $pdo->prepare("INSERT INTO prestataires (nom_entreprise, nom_agent, telephone, photo) 
                                      VALUES (?, ?, ?, ?)");
                if ($stmt->execute([$nom_entreprise, $nom_agent, $telephone, $photo_path])) {
                    $message = '<div class="alert alert-success">Prestataire ajouté avec succès!</div>';
                    // Réinitialisation des champs
                    $_POST = [];
                } else {
                    $message = '<div class="alert alert-danger">Erreur lors de l\'ajout du prestataire</div>';
                }
            } catch (PDOException $e) {
                $message = '<div class="alert alert-danger">Erreur base de données: ' . $e->getMessage() . '</div>';
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
    <title>Ajouter un Prestataire</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .required-label::after {
            content: " *";
            color: red;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">Ajouter un Prestataire</h2>
        
        <?= $message ?>
        
        <div class="card">
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nom_entreprise" class="form-label required-label">Nom de l'entreprise</label>
                            <input type="text" class="form-control" id="nom_entreprise" name="nom_entreprise" 
                                   value="<?= isset($_POST['nom_entreprise']) ? htmlspecialchars($_POST['nom_entreprise']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nom_agent" class="form-label required-label">Nom de l'agent</label>
                            <input type="text" class="form-control" id="nom_agent" name="nom_agent" 
                                   value="<?= isset($_POST['nom_agent']) ? htmlspecialchars($_POST['nom_agent']) : '' ?>" required>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="telephone" class="form-label required-label">Téléphone</label>
                            <input type="tel" class="form-control" id="telephone" name="telephone" 
                                   value="<?= isset($_POST['telephone']) ? htmlspecialchars($_POST['telephone']) : '' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="photo" class="form-label">Photo de l'agent</label>
                            <input type="file" class="form-control" id="photo" name="photo" accept="image/jpeg, image/png, image/gif">
                            <div class="form-text">Formats acceptés: JPG, PNG, GIF (max 2MB)</div>
                        </div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="index.php" class="btn btn-secondary me-md-2">Annuler</a>
                        <button type="submit" class="btn btn-primary">Ajouter le prestataire</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>