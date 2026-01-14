<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$prestataire_id = intval($_GET['id']);
$prestataire = getPrestataireById($pdo, $prestataire_id);

if (!$prestataire) {
    die("Prestataire non trouvé");
}

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id_facture'])) {
    $id_facture = intval($_GET['id_facture']);
    
    // Récupération du chemin du fichier avant suppression
    $stmt = $pdo->prepare("SELECT fichier_depot FROM factures WHERE id = ? AND prestataire_id = ?");
    $stmt->execute([$id_facture, $prestataire_id]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($facture) {
        // Suppression de la base
        $stmt = $pdo->prepare("DELETE FROM factures WHERE id = ?");
        if ($stmt->execute([$id_facture])) {
            // Suppression du fichier physique
            if (file_exists($facture['fichier_depot'])) {
                unlink($facture['fichier_depot']);
            }
            $message_success = "Facture supprimée avec succès!";
        }
    }
}

// Récupération des factures du prestataire
$stmt = $pdo->prepare("SELECT * FROM factures WHERE prestataire_id = ? ORDER BY date_creation DESC");
$stmt->execute([$prestataire_id]);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factures de <?= htmlspecialchars($prestataire['nom_entreprise']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Prestataires</a></li>
                <li class="breadcrumb-item active"><?= htmlspecialchars($prestataire['nom_entreprise']) ?></li>
            </ol>
        </nav>
        
        <!-- Affichage des messages -->
        <?php if (isset($message_success)): ?>
            <div class="alert alert-success"><?= $message_success ?></div>
        <?php endif; ?>
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Factures de <?= htmlspecialchars($prestataire['nom_entreprise']) ?></h2>
            <a href="../factures/ajouter.php?prestataire_id=<?= $prestataire['id'] ?>" class="btn btn-success">
                <i class="fas fa-plus me-1"></i> Ajouter une facture
            </a>
        </div>
        
        <!-- Informations du prestataire -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2 text-center">
                        <?php if (!empty($prestataire['photo']) && file_exists($prestataire['photo'])): ?>
                            <img src="<?= $prestataire['photo'] ?>" class="rounded-circle" 
                                 alt="<?= htmlspecialchars($prestataire['nom_agent']) ?>" 
                                 style="width: 100px; height: 100px; object-fit: cover;">
                        <?php else: ?>
                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                 style="width: 100px; height: 100px;">
                                <i class="fas fa-user text-white fa-2x"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-10">
                        <h4><?= htmlspecialchars($prestataire['nom_entreprise']) ?></h4>
                        <p class="mb-1"><strong>Agent:</strong> <?= htmlspecialchars($prestataire['nom_agent']) ?></p>
                        <p class="mb-0"><strong>Téléphone:</strong> <?= htmlspecialchars($prestataire['telephone']) ?></p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Liste des factures -->
        <?php if (count($factures) > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Numéro de facture</th>
                            <th>Date de dépôt</th>
                            <th>Statut</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($factures as $facture): ?>
                        <tr>
                            <td><?= htmlspecialchars($facture['numero_facture']) ?></td>
                            <td><?= formatDate($facture['date_depot']) ?></td>
                            <td>
                                <span class="badge 
                                    <?= $facture['statut'] == 'Reception' ? 'bg-primary' : 
                                       ($facture['statut'] == 'Rdem' ? 'bg-warning' : 
                                       ($facture['statut'] == 'Engagement' ? 'bg-success' : 'bg-danger')) ?>">
                                    <?= ucfirst($facture['statut']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="../factures/telecharger.php?id=<?= $facture['id'] ?>&action=view" 
                                       class="btn btn-sm btn-info" target="_blank" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="../factures/telecharger.php?id=<?= $facture['id'] ?>&action=download" 
                                       class="btn btn-sm btn-primary" title="Télécharger">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="../factures/modifier.php?id=<?= $facture['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="voir.php?id=<?= $prestataire_id ?>&action=supprimer&id_facture=<?= $facture['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')"
                                       title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                Aucune facture trouvée pour ce prestataire.
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>