<?php
session_start();
require_once "db_config.php";

$siteFilter = $_GET['siteFilter'] ?? null;
$serviceFilter = $_GET['serviceFilter'] ?? null;
$ligneFilter = $_GET['ligneFilter'] ?? '';

$query = "SELECT memos.*, lignes.name as ligne_name
          FROM memos 
          LEFT JOIN lignes ON memos.ligne_id = lignes.id
          WHERE 1"; // Clause WHERE de base

// Ajouter des conditions de filtre si elles sont définies
if ($serviceFilter) {
    $query .= " AND memos.service_id = :serviceFilter";
}

$query .= " ORDER BY memos.id DESC";

$stmtMemos = $pdo->prepare($query);

// Lier les valeurs des filtres conditionnels s'ils sont définis
if ($serviceFilter) {
    $stmtMemos->bindParam(':serviceFilter', $serviceFilter, PDO::PARAM_INT);
}

$stmtMemos->execute();
$memos = $stmtMemos->fetchAll();

$stmtLignes = $pdo->prepare("SELECT * FROM lignes ORDER BY name ASC");
$stmtLignes->execute();
$lignes = $stmtLignes->fetchAll();

// Le reste de votre code HTML...
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memos</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<style>
        /* Conteneur principal avec animation au survol */
        .container {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
            background-color: white;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid grey;
        }

       

        /* Table de données */
        .table th, .table td {
            padding: 12px 15px;
            border-bottom: 1px solid #ddd;
            text-align: left;
            color: #333;
        }

        .table th {
            background-color: #34495e; /* Couleur Bleu Titre du tableau */
            color: #34495e;
            text-transform: uppercase;
            font-weight: bold;
        }

        .table tr:hover {
            background-color: rgba(0, 123, 255, 0.1);
        }

        .btn{
            color: white;
        }
        /* Style des boutons */
        .btn-primary {
            background-color: #34495e;
            border: none;
            transition: background-color 0.3s ease;
            margin-left: 25%;

        }

        .btn-primary:hover {
            background-color: #2c3e50;
        }

        button{
        }

    /* Masquer le sélecteur du nombre d'entrées */
    .dataTables_length {
        display: none;
    }

    /* Masquer la barre de recherche */
    .dataTables_filter {
        display: none;
    }



    .dataTables_wrapper .dataTables_paginate .paginate_button {
        background-color: #34495e;
        color: white;
        border: 1px solid #34495e;
        padding: 5px 10px;
        margin: 2px;
        border-radius: 4px;
        cursor: pointer;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background-color: grey;
        border: none;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background-color: #2c3e50;
    }

    .dataTables_wrapper .dataTables_paginate, 
    .dataTables_wrapper .dataTables_info {
    text-align: center; /* Centrer la pagination */
    margin-top: 10px; /* Ajouter un petit espace en haut */
    color: white;
    }

    @media only screen and (min-width: 1080px) {
    .container.mt-4 {
        margin-left: 20%; /* Le menu est caché sur les tablettes et téléphones */
    }
}
    </style>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memos</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<?php include 'menu.php' ?>
<div class="container mt-4">
<center><h2 class="mt-4">Relèves</h2></center>

    <div class="container">
        <table class="table table-bordered table-striped">
            <thead>
            <tr>
                <th>Relèves</th>
                <th>Ligne</th>
                <th>Créé le</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($memos as $memo): ?>
                <tr>
                    <td><?= $memo['memo_text']; ?></td>
                    <td><?= $memo['ligne_name']; ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($memo['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <!-- Bouton pour ouvrir le modal -->
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal<?= $memo['id']; ?>">
                            Voir détails
                        </button>

                        <!-- Structure du modal -->
                        <div class="modal fade" id="modal<?= $memo['id']; ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Détails du mémo</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Ligne:</strong> <?= $memo['ligne_name']; ?></p>
                                        <p><strong>Créé le:</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($memo['created_at'])), ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p><strong>Memo:</strong> <?= $memo['memo_text']; ?></p>
                                        <?php if ($memo['image_path']): ?>
                                            <img src="<?= $memo['image_path']; ?>" alt="Image du mémo" style="max-width:100%;">
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Scripts pour Bootstrap -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>
</html>
