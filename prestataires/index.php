<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$prestataires = getPrestataires($pdo);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Prestataires</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestion des Prestataires</h2>
            <a href="ajouter.php" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Ajouter un prestataire
            </a>
        </div>
        
        <div class="row">
            <?php if (count($prestataires) > 0): ?>
                <?php foreach ($prestataires as $prestataire): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <div class="card-body text-center">
                            <?php if (!empty($prestataire['photo']) && file_exists($prestataire['photo'])): ?>
                                <img src="<?= $prestataire['photo'] ?>" class="rounded-circle mb-3" 
                                     alt="<?= htmlspecialchars($prestataire['nom_agent']) ?>" 
                                     style="width: 100px; height: 100px; object-fit: cover;">
                            <?php else: ?>
                                <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center mb-3" 
                                     style="width: 100px; height: 100px;">
                                    <i class="fas fa-user text-white fa-2x"></i>
                                </div>
                            <?php endif; ?>
                            
                            <h5 class="card-title"><?= htmlspecialchars($prestataire['nom_entreprise']) ?></h5>
                            <p class="card-text">
                                <strong>Agent:</strong> <?= htmlspecialchars($prestataire['nom_agent']) ?><br>
                                <strong>Téléphone:</strong> <?= htmlspecialchars($prestataire['telephone']) ?>
                            </p>
                        </div>
                        <div class="card-footer bg-transparent">
                            <div class="d-grid gap-2">
                                <a href="voir.php?id=<?= $prestataire['id'] ?>" class="btn btn-primary">
                                    <i class="fas fa-list me-1"></i> Voir les factures
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info text-center">
                        Aucun prestataire enregistré. <a href="ajouter.php">Ajoutez-en un maintenant</a>.
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>