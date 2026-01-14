<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Système de Suivi de Factures</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .card:hover {
            transform: translateY(-5px);
            transition: transform 0.3s;
        }
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container mt-5">
        <div class="row text-center">
            <h1 class="mb-5">Système de Suivi de Factures</h1>
            
            <div class="col-md-4 mb-4">
                <div class="card shadow">
                    <div class="card-body">
                        <i class="fas fa-file-invoice-dollar fa-3x mb-3 text-primary"></i>
                        <h5 class="card-title">Gestion des Factures</h5>
                        <p class="card-text">Ajoutez, consultez et gérez toutes vos factures</p>
                        <a href="factures/index.php" class="btn btn-primary">Voir les factures</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card shadow">
                    <div class="card-body">
                        <i class="fas fa-handshake fa-3x mb-3 text-success"></i>
                        <h5 class="card-title">Prestataires</h5>
                        <p class="card-text">Gérez vos prestataires et leurs informations</p>
                        <a href="prestataires/index.php" class="btn btn-success">Voir les prestataires</a>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card shadow">
                    <div class="card-body">
                        <i class="fas fa-search fa-3x mb-3 text-info"></i>
                        <h5 class="card-title">Recherche</h5>
                        <p class="card-text">Recherchez des factures par critères multiples</p>
                        <a href="factures/index.php" class="btn btn-info">Rechercher</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>