<?php
require 'db_config.php';

// Vérifier si le formulaire a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Extraction des données du formulaire
    $tache_id = isset($_POST['tache_id']) ? (int)$_POST['tache_id'] : null;
    $site_id = isset($_POST['site_id']) ? (int)$_POST['site_id'] : null;
    $ligne_id = isset($_POST['ligne_id']) ? (int)$_POST['ligne_id'] : null;
    $operateur_id = isset($_POST['operateur_id']) ? (int)$_POST['operateur_id'] : null;
    $commentaire = isset($_POST['commentaire']) ? trim($_POST['commentaire']) : '';

    // Gestion de l'upload de la photo
    $photo_path = '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
        $tmp_name = $_FILES['photo']['tmp_name'];
        $name = basename($_FILES['photo']['name']);
        $uploadDir = __DIR__ . '/uploads/';
        $uploadFile = $uploadDir . $name;
        if (move_uploaded_file($tmp_name, $uploadFile)) {
            $photo_path = 'uploads/' . $name;
        } else {
            echo "Échec de l'upload de l'image.";
        }
    }

    // Préparation de la requête d'insertion
    $sql = "INSERT INTO executions_taches (tache_id, site_id, ligne_id, operateur_id, commentaire, photo_path) VALUES (?, ?, ?, ?, ?, ?)";
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$tache_id, $site_id, $ligne_id, $operateur_id, $commentaire, $photo_path]);
        echo "Enregistrement réussi.";
    } catch (PDOException $e) {
        echo "Erreur lors de l'enregistrement : " . $e->getMessage();
    }
}

$taches_sql = "SELECT taches_nettoyage.id, taches_nettoyage.nom, MAX(executions_taches.date_execution) AS derniere_execution, taches_nettoyage.recurrence
FROM taches_nettoyage
LEFT JOIN executions_taches ON taches_nettoyage.id = executions_taches.tache_id
GROUP BY taches_nettoyage.id";
$taches_stmt = $pdo->query($taches_sql);
$taches = $taches_stmt->fetchAll();

$sites_sql = "SELECT id, name FROM sites";
$sites_stmt = $pdo->query($sites_sql);
$sites = $sites_stmt->fetchAll();

$lignes_sql = "SELECT id, name FROM lignes";
$lignes_stmt = $pdo->query($lignes_sql);
$lignes = $lignes_stmt->fetchAll();

$operateurs_sql = "SELECT id, name FROM operators";
$operateurs_stmt = $pdo->query($operateurs_sql);
$operateurs = $operateurs_stmt->fetchAll();

function estEnRetard($derniereExecution, $recurrence) {
    $aujourdhui = new DateTime();
    $derniereExecutionDate = new DateTime($derniereExecution);
    
    switch ($recurrence) {
        case 'quotidien':
            $derniereExecutionDate->modify('+1 day');
            break;
        case 'hebdomadaire':
            $derniereExecutionDate->modify('+1 week');
            break;
        case 'bihebdomadaire':
            $derniereExecutionDate->modify('+2 weeks');
            break;
        case 'mensuel':
            $derniereExecutionDate->modify('+1 month');
            break;
    }

    return $derniereExecutionDate < $aujourdhui;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enregistrement d'exécution de tâches</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <style>
        .collapse-row.collapsed + tr.expandable-row > td {
            padding: 0;
            border: none;
        }

        .expandable-row {
            display: none;
        }
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container mt-3">
    <h2>Enregistrement d'exécution de tâches</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="col-10">Nom de la tâche</th>
                <th class="col-2">Actions</th>
                <th class="col-2">Derniere execution</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($taches as $tache): ?>
                <?php
                // Déterminer si la tâche est en retard
                $enRetard = estEnRetard($tache['derniere_execution'], $tache['recurrence']);
                // Appliquer une classe de couleur basée sur l'état de la tâche
                $classeCouleur = $enRetard ? 'bg-danger text-white' : 'bg-success text-white';
                ?>
            <tr class="collapse-row <?= $classeCouleur; ?>">
                <td><?= htmlspecialchars($tache['nom']); ?></td>
                <td>
                    <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapse<?= $tache['id']; ?>" aria-expanded="false" aria-controls="collapse<?= $tache['id']; ?>">
                        Déployer
                    </button>
                </td>
                <td><?= htmlspecialchars($tache['derniere_execution']) ?: 'Jamais'; ?></td>
            </tr>
            <tr class="expandable-row">
                <td colspan="2">
                    <div class="collapse" id="collapse<?= $tache['id']; ?>">
                        <div class="card card-body">
                            <form method="post" enctype="multipart/form-data">
                                <input type="hidden" name="tache_id" value="<?= $tache['id']; ?>">
                                <div class="form-group">
                                    <label>Site</label>
                                    <select name="site_id" class="form-control">
                                        <?php foreach ($sites as $site): ?>
                                            <option value="<?= $site['id']; ?>"><?= htmlspecialchars($site['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Ligne</label>
                                    <select name="ligne_id" class="form-control">
                                        <?php foreach ($lignes as $ligne): ?>
                                            <option value="<?= $ligne['id']; ?>"><?= htmlspecialchars($ligne['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Opérateur</label>
                                    <select name="operateur_id" class="form-control">
                                        <?php foreach ($operateurs as $operateur): ?>
                                            <option value="<?= $operateur['id']; ?>"><?= htmlspecialchars($operateur['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Commentaire</label>
                                    <textarea name="commentaire" class="form-control"></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Photo</label>
                                    <input type="file" name="photo" class="form-control-file" required>
                                </div>
                                <button type="submit" class="btn btn-success">Enregistrer l'exécution</button>
                            </form>
                        </div>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('.collapse-row').on('click', function() {
            var target = $(this).next('.expandable-row');
            target.toggle();
            target.find('.collapse').collapse('toggle');
        });
    });
</script>
</body>
</html>