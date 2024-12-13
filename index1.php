<?php
session_start();

require_once "db_config.php";

$query = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name
          FROM notes 
          LEFT JOIN sites ON notes.site_id = sites.id 
          LEFT JOIN lignes ON notes.ligne_id = lignes.id
          LEFT JOIN services ON notes.service_id = services.id
          ORDER BY notes.id DESC";

$stmt = $pdo->prepare($query);
$stmt->execute();
$recentNotes = $stmt->fetchAll();

$query = "SELECT id, name FROM lignes"; 
$stmt = $pdo->prepare($query);
$stmt->execute();
$lignes = $stmt->fetchAll();

$ligneFilter = isset($_GET['ligne_id']) ? $_GET['ligne_id'] : null;

// Récupération des mémos pour l'affichage
$memoQuery = "
SELECT 
    memos.*,
    lignes.name as ligne_name 
FROM memos 
JOIN lignes ON memos.ligne_id = lignes.id";
if ($ligneFilter) {
    $memoQuery .= " WHERE memos.ligne_id = ?";
}
$memoStmt = $pdo->prepare($memoQuery);
$ligneFilter ? $memoStmt->execute([$ligneFilter]) : $memoStmt->execute();
$memos = $memoStmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet de Notes</title>
    <link rel="stylesheet" href="styles.css">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <div class="row">
        <div class="col-md-6">
            <h3>Notes Récemment Ajoutées</h3>
            <!-- Votre code pour la section des notes récemment ajoutées ici -->
            <div class="legend">
                <div><span class="legend-box archived"></span> Note Archivée</div>
                <div><span class="legend-box not-archived"></span> Note Non Archivée</div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th class="col-*">Note</th>
                        <th class="col-*">Ligne</th>
                        <th class="col-*">Site</th>
                        <th class="col-*">Opérateur</th>
                        <th class="col-*">Date</th>
                        <th class="col-*">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentNotes as $note): ?>
                        <tr class="<?= $note['archived'] == 1 ? 'archived' : 'not-archived'; ?>">
                            <td><?= htmlspecialchars($note['note_text']); ?></td>
                            <td><?= htmlspecialchars($note['ligne_name']); ?></td>
                            <td><?= htmlspecialchars($note['site_name']); ?></td>
                            <td><?= htmlspecialchars($note['operator_name']); ?></td>
                            <td><?= htmlspecialchars($note['created_at']); ?></td>
                            <td>
                                <!-- Bouton pour ouvrir le modal -->
                                <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal<?= $note['id']; ?>">
                                    Voir détails
                                </button>

                                <!-- Structure du modal -->
                                <div class="modal fade" id="modal<?= $note['id']; ?>">
                                    <!-- ...Contenu du modal... -->
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="col-md-6">
            <h3>Dernières Relèves</h3>
            <form action="index.php" method="get">
                <select name="ligne_id" onchange="this.form.submit()">
                    <option value="">Sélectionnez une ligne</option>
                    <?php foreach ($lignes as $ligne): ?>
                        <option value="<?= $ligne['id']; ?>" <?= $ligne['id'] == $ligneFilter ? 'selected' : ''; ?>>
                            <?= $ligne['name']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
            <!-- Votre code pour la section des dernières relèves ici -->
            <div class="memos">
                <!-- ...Contenu des mémos... -->
            </div>
        </div>
    </div>
</div>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JavaScript -->
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    // Fonctions JavaScript ici...
</script>

</body>
</html>
