<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Traitement de l'export Excel
if (isset($_GET['action']) && $_GET['action'] == 'export_excel') {
    // Récupération de toutes les factures
    $sql = "SELECT f.*, p.nom_entreprise, p.nom_agent 
            FROM factures f 
            JOIN prestataires p ON f.prestataire_id = p.id 
            ORDER BY f.date_creation DESC";
    $stmt = $pdo->query($sql);
    $factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // En-têtes pour téléchargement Excel
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="factures_' . date('Y-m-d') . '.xls"');
    
    // Début du fichier Excel
    echo "<table border='1'>";
    echo "<tr>
            <th>Numéro de facture</th>
            <th>Prestataire</th>
            <th>Date de dépôt</th>
            <th>Date de retour</th>
            <th>Statut</th>
            <th>Date création</th>
          </tr>";
    
    foreach ($factures as $facture) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($facture['numero_facture']) . "</td>";
        echo "<td>" . htmlspecialchars($facture['nom_entreprise']) . "</td>";
        echo "<td>" . formatDate($facture['date_depot']) . "</td>";
        echo "<td>" . ($facture['date_retour'] ? formatDate($facture['date_retour']) : 'Aucune') . "</td>";
        echo "<td>" . $facture['statut'] . "</td>";
        echo "<td>" . formatDate($facture['date_creation']) . "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
    exit;
}

// Traitement de la suppression
if (isset($_GET['action']) && $_GET['action'] == 'supprimer' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Récupération du chemin du fichier avant suppression
    $stmt = $pdo->prepare("SELECT fichier_depot FROM factures WHERE id = ?");
    $stmt->execute([$id]);
    $facture = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($facture) {
        // Suppression de la base
        $stmt = $pdo->prepare("DELETE FROM factures WHERE id = ?");
        if ($stmt->execute([$id])) {
            // Suppression du fichier physique
            if (file_exists($facture['fichier_depot'])) {
                unlink($facture['fichier_depot']);
            }
            $message_success = "Facture supprimée avec succès!";
        }
    }
}

// Traitement de la recherche
$whereConditions = [];
$params = [];
$search_term = '';
$search_by = '';

if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
    $search_term = $_GET['search_term'];
    $search_by = isset($_GET['search_by']) ? $_GET['search_by'] : 'numero';
    
    $search_param = '%' . $search_term . '%';
    
    switch ($search_by) {
        case 'numero':
            $whereConditions[] = "f.numero_facture LIKE ?";
            $params[] = $search_param;
            break;
        case 'prestataire':
            $whereConditions[] = "p.nom_entreprise LIKE ?";
            $params[] = $search_param;
            break;
        case 'date':
            $whereConditions[] = "f.date_depot = ?";
            // Conversion de la date au format MySQL
            $params[] = date('Y-m-d', strtotime(str_replace('/', '-', $search_term)));
            break;
        case 'statut':
            $whereConditions[] = "f.statut LIKE ?";
            $params[] = $search_param;
            break;
    }
}

// Construction de la requête
$sql = "SELECT f.*, p.nom_entreprise, p.nom_agent 
        FROM factures f 
        JOIN prestataires p ON f.prestataire_id = p.id";
        
if (!empty($whereConditions)) {
    $sql .= " WHERE " . implode(" AND ", $whereConditions);
}

$sql .= " ORDER BY f.date_creation DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$factures = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Factures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container mt-4">
        <h2 class="mb-4">Gestion des Factures</h2>
        
        <!-- Affichage des messages -->
        <?php if (isset($message_success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $message_success ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Carte de recherche et export -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recherche et Export</h5>
                    <a href="index.php?action=export_excel" class="btn btn-success btn-sm">
                        <i class="fas fa-file-excel me-1"></i> Exporter Excel
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Formulaire de recherche -->
                <form method="GET" id="searchForm" class="row g-3">
                    <div class="col-md-3">
                        <select name="search_by" class="form-select" id="search_by">
                            <option value="numero" <?= ($search_by == 'numero') ? 'selected' : '' ?>>Numéro de facture</option>
                            <option value="prestataire" <?= ($search_by == 'prestataire') ? 'selected' : '' ?>>Nom du prestataire</option>
                            <option value="date" <?= ($search_by == 'date') ? 'selected' : '' ?>>Date de dépôt</option>
                            <option value="statut" <?= ($search_by == 'statut') ? 'selected' : '' ?>>Statut</option>
                        </select>
                    </div>
                    <div class="col-md-7">
                        <div class="input-group">
                            <input type="text" name="search_term" class="form-control" 
                                   placeholder="Rechercher..." value="<?= htmlspecialchars($search_term) ?>" 
                                   id="search_input">
                            <button type="submit" name="search" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <a href="index.php" class="btn btn-secondary w-100">
                            <i class="fas fa-redo me-1"></i> Réinitialiser
                        </a>
                    </div>
                </form>
                
                <!-- Indicateur de recherche -->
                <div id="search_indicator" class="mt-2" style="display: none;">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Recherche en cours...</span>
                    </div>
                    <small class="text-muted ms-2">Recherche en cours...</small>
                </div>
            </div>
        </div>
        
        <!-- Bouton d'ajout -->
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4>Liste des factures</h4>
            <div>
                <span class="badge bg-info me-2">Total: <?= count($factures) ?> facture(s)</span>
                <a href="ajouter.php" class="btn btn-success">
                    <i class="fas fa-plus me-1"></i> Ajouter une facture
                </a>
            </div>
        </div>
        
        <!-- Tableau des factures -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Numéro</th>
                        <th>Prestataire</th>
                        <th>Date de dépôt</th>
                        <th>Date de retour</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="factures_body">
                    <?php if (count($factures) > 0): ?>
                        <?php foreach ($factures as $facture): ?>
                        <tr>
                            <td><?= htmlspecialchars($facture['numero_facture']) ?></td>
                            <td><?= htmlspecialchars($facture['nom_entreprise']) ?></td>
                            <td><?= formatDate($facture['date_depot']) ?></td>
                            <td>
                                <?php if ($facture['date_retour']): ?>
                                    <?= formatDate($facture['date_retour']) ?>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Aucune date</span>
                                <?php endif; ?>
                            </td>
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
                                    <a href="telecharger.php?id=<?= $facture['id'] ?>&action=view" 
                                       class="btn btn-sm btn-info" target="_blank" title="Voir">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="telecharger.php?id=<?= $facture['id'] ?>&action=download" 
                                       class="btn btn-sm btn-primary" title="Télécharger">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <a href="modifier.php?id=<?= $facture['id'] ?>" 
                                       class="btn btn-sm btn-warning" title="Modifier">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="index.php?action=supprimer&id=<?= $facture['id'] ?>" 
                                       class="btn btn-sm btn-danger" 
                                       onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette facture ?')"
                                       title="Supprimer">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Aucune facture trouvée</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        let searchTimeout;
        let currentSearchTerm = '';
        
        // Fonction de recherche automatique
        $('#search_input').on('input', function() {
            const searchTerm = $(this).val().trim();
            const searchBy = $('#search_by').val();
            
            // Annuler la recherche précédente si elle n'est pas terminée
            clearTimeout(searchTimeout);
            
            // Si le terme de recherche est vide, réinitialiser immédiatement
            if (searchTerm === '') {
                window.location.href = 'index.php';
                return;
            }
            
            // Attendre 500ms après la dernière frappe avant de lancer la recherche
            searchTimeout = setTimeout(function() {
                if (searchTerm !== currentSearchTerm) {
                    currentSearchTerm = searchTerm;
                    performSearch(searchTerm, searchBy);
                }
            }, 500);
        });
        
        // Recherche lors du changement du critère de recherche
        $('#search_by').on('change', function() {
            const searchTerm = $('#search_input').val().trim();
            const searchBy = $(this).val();
            
            if (searchTerm !== '') {
                performSearch(searchTerm, searchBy);
            }
        });
        
        function performSearch(searchTerm, searchBy) {
            // Afficher l'indicateur de chargement
            $('#search_indicator').show();
            
            // Effectuer la recherche via AJAX
            $.ajax({
                url: 'search_handler.php',
                method: 'GET',
                data: {
                    search_term: searchTerm,
                    search_by: searchBy
                },
                success: function(response) {
                    $('#factures_body').html(response);
                    $('#search_indicator').hide();
                    
                    // Mettre à jour l'URL dans la barre d'adresse sans recharger la page
                    const url = new URL(window.location);
                    url.searchParams.set('search_term', searchTerm);
                    url.searchParams.set('search_by', searchBy);
                    window.history.pushState({}, '', url);
                },
                error: function() {
                    $('#search_indicator').hide();
                    alert('Erreur lors de la recherche. Veuillez réessayer.');
                }
            });
        }
        
        // Gestion du bouton d'annulation de recherche
        $('.btn-secondary[href="index.php"]').on('click', function(e) {
            e.preventDefault();
            window.location.href = 'index.php';
        });
    });
    </script>
</body>
</html>