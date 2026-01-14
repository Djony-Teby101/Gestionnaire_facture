<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Traitement de la recherche via AJAX
if (isset($_GET['search_term']) && !empty($_GET['search_term'])) {
    $search_term = $_GET['search_term'];
    $search_by = isset($_GET['search_by']) ? $_GET['search_by'] : 'numero';
    
    $whereConditions = [];
    $params = [];
    
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
    
    // Génération du HTML des résultats
    if (count($factures) > 0) {
        foreach ($factures as $facture) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($facture['numero_facture']) . '</td>';
            echo '<td>' . htmlspecialchars($facture['nom_entreprise']) . '</td>';
            echo '<td>' . formatDate($facture['date_depot']) . '</td>';
            echo '<td>';
            if ($facture['date_retour']) {
                echo formatDate($facture['date_retour']);
            } else {
                echo '<span class="text-muted fst-italic">Aucune date</span>';
            }
            echo '</td>';
            echo '<td>';
            echo '<span class="badge ';
            switch ($facture['statut']) {
                case 'Reception': echo 'bg-primary'; break;
                case 'Rdem': echo 'bg-warning'; break;
                case 'Engagement': echo 'bg-success'; break;
                case 'Retourner': echo 'bg-danger'; break;
            }
            echo '">' . ucfirst($facture['statut']) . '</span>';
            echo '</td>';
            echo '<td>';
            echo '<div class="btn-group" role="group">';
            echo '<a href="telecharger.php?id=' . $facture['id'] . '&action=view" class="btn btn-sm btn-info" target="_blank" title="Voir"><i class="fas fa-eye"></i></a>';
            echo '<a href="telecharger.php?id=' . $facture['id'] . '&action=download" class="btn btn-sm btn-primary" title="Télécharger"><i class="fas fa-download"></i></a>';
            echo '<a href="modifier.php?id=' . $facture['id'] . '" class="btn btn-sm btn-warning" title="Modifier"><i class="fas fa-edit"></i></a>';
            echo '<a href="index.php?action=supprimer&id=' . $facture['id'] . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Êtes-vous sûr de vouloir supprimer cette facture ?\')" title="Supprimer"><i class="fas fa-trash"></i></a>';
            echo '</div>';
            echo '</td>';
            echo '</tr>';
        }
    } else {
        echo '<tr><td colspan="6" class="text-center">Aucune facture trouvée</td></tr>';
    }
}
?>