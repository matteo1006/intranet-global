<?php
session_start();
require_once "db_config.php";
// Assurez-vous que la variable est définie et n'est pas null avant de l'utiliser
$ligneId = isset($_GET['ligne_id']) ? $_GET['ligne_id'] : 0; // Utilisez 0 ou une autre valeur par défaut si ligne_id n'est pas défini

// Récupération des filtres depuis l'URL
$siteFilter = $_GET['siteFilter'] ?? null; // Pas utilisé dans le code fourni, mais laissé pour une utilisation future
$serviceFilter = $_GET['serviceFilter'] ?? null;
$ligneFilter = $_GET['ligne_id'] ?? ''; // Correction ici: utilisation de 'ligne_id' au lieu de 'ligneFilter'

// Initialisation des conditions WHERE pour montrer uniquement les notes non archivées
$whereConditions = 'WHERE notes.archived = 0';

// Ajout du filtre par service si spécifié
if ($serviceFilter) {
    $whereConditions .= ' AND services.id = :serviceFilter';
}

// Ajout du filtre par ligne si spécifié
if ($ligneFilter !== '') {
    $whereConditions .= ' AND lignes.id = :ligneFilter'; // Le placeholder reste inchangé
}

// Construction de la requête avec les conditions dynamiques
$query = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name
          FROM notes 
          LEFT JOIN sites ON notes.site_id = sites.id 
          LEFT JOIN lignes ON notes.ligne_id = lignes.id
          LEFT JOIN services ON notes.service_id = services.id
          $whereConditions 
          ORDER BY notes.id DESC";

// Préparation de la requête
$stmtNotes = $pdo->prepare($query);

// Création d'un tableau de paramètres basé sur les filtres appliqués
$params = [];
if ($serviceFilter) {
    $params[':serviceFilter'] = $serviceFilter;
}
if ($ligneFilter !== '') {
    $params[':ligneFilter'] = $ligneFilter; // Le nom du placeholder dans le tableau de paramètres ne doit pas nécessairement correspondre au nom de la variable
}

// Exécution de la requête avec les paramètres applicables
$stmtNotes->execute($params);

// Récupération de toutes les notes filtrées
$notes = $stmtNotes->fetchAll();

// Récupération des lignes et des services pour les listes déroulantes de filtrage
$stmtLignes = $pdo->prepare("SELECT * FROM lignes ORDER BY name ASC");
$stmtLignes->execute();
$lignes = $stmtLignes->fetchAll();

$stmtServices = $pdo->prepare("SELECT services.*, sites.name as site_name FROM services JOIN sites ON services.site_id = sites.id ORDER BY services.name ASC");
$stmtServices->execute();
$services = $stmtServices->fetchAll();

// Gestion de la session utilisateur
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    $_SESSION['is_admin'] = 1;
} else {
    $_SESSION['is_admin'] = 0;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>
<div class="container mt-4">
    <!-- Assurez-vous de remplacer '0' par la variable PHP qui contient l'ID de la ligne -->
<a href="http://192.168.12.41/dashboardlignes.php?ligne_id=<?= $ligneId; ?>" class="btn btn-primary">Retour au Dashboard de la Ligne</a>

<form action="note_page.php" method="get" class="mb-4">
    <label for="serviceFilter">Filtre par Service:</label>
    <select name="serviceFilter" id="serviceFilter">
        <option value="">Tous les services</option>
        <?php foreach ($services as $service): ?>
            <option value="<?= $service['id']; ?>" <?= ($serviceFilter == $service['id']) ? 'selected' : ''; ?>>
                <?= htmlspecialchars($service['name'] . " (" . $service['site_name'] . ")", ENT_QUOTES, 'UTF-8'); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <input type="submit" value="Filtrer">
</form>

                <div class="container">
    <h2 class="mt-4">Notes</h2>
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>Note</th>
                <th>Site</th>
                <th>Service</th>
                <th>Ligne</th>
                <th>Créée le</th>
                
                     <th>Action</th>
                        <th hidden>Asana</th>
               
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notes as $note): ?>
                <tr>
                    <td><?= $note['note_text']; ?></td>
                    <td><?= $note['site_name']; ?></td>
                    <td><?= $note['service_name']; ?></td>
                    <td><?= $note['ligne_name']; ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($note['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td> <!-- Ajoutez cette cellule pour afficher la date de création -->
                    <td>
                        <!-- Bouton pour ouvrir le modal -->
                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modal<?= $note['id']; ?>">
                            Voir détails
                        </button>

                        <!-- Structure du modal -->
                        <div class="modal fade" id="modal<?= $note['id']; ?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h4 class="modal-title">Détails de la note</h4>
                                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    </div>
                                    <div class="modal-body">
                                        <p><strong>Site:</strong> <?= $note['site_name']; ?></p>
                                        <p><strong>Ligne:</strong> <?= $note['ligne_name']; ?></p>
                                        <p><strong>Créée le: </strong><?= htmlspecialchars(date('d/m/Y H:i', strtotime($note['created_at'])), ENT_QUOTES, 'UTF-8'); ?></p>
                                        <p><strong>Note:</strong> <?= $note['note_text']; ?></p>
                                        <?php if ($note['image_path']): ?>
                                            <img src="<?= $note['image_path']; ?>" alt="Image de note" width="100%">
                                        <?php endif; ?>
                                        <?php if ($_SESSION['is_admin']): ?>
                                        <form action="archive_note.php" method="post">
                                             <div class="form-group">
                                                <label for="initiales">Initiales:</label>
                                                <input type="text" class="form-control" id="initials" name="initials" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="commentaire">Commentaire :</label>
                                                <textarea class="form-control" id="commentaire" name="commentaire" rows="3" required></textarea>
                                            </div>
                                            <input type="hidden" name="note_id" value="<?= $note['id']; ?>">
                                            <input type="hidden" name="user_id" value="<?= $_SESSION['user_id']; ?>">
                                            <button type="submit" class="btn btn-primary">Archiver avec commentaire</button>
                                        </form>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                                        </div>
                         <!-- Ajouter ce bouton pour transférer vers Asana -->
                        <button hidden onclick="transfererVersAsana(<?= $note['id']; ?>)" class="btn btn-info btn-sm">Transférer vers Asana</button>
                    </td>
                    <?php endif; ?>
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
