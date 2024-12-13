<?php
session_start();

require_once "db_config.php";

$ligne_name = 'Nexus 2';

$query = "SELECT 
    notes.note_text, 
    notes.created_at, 
    lignes.name as ligne_name, 
    sites.name as site_name, 
    operators.name as operator_name
FROM notes
JOIN lignes ON notes.ligne_id = lignes.id AND lignes.name = ?
JOIN sites ON notes.site_id = sites.id
JOIN operators ON notes.operator_name = operators.id
ORDER BY notes.created_at DESC LIMIT 10";

$stmt = $pdo->prepare($query);
$stmt->execute([$ligne_name]);
$recentNotes = $stmt->fetchAll();

$queryLigneId = "SELECT id FROM lignes WHERE name = ?";
$stmtLigneId = $pdo->prepare($queryLigneId);
$stmtLigneId->execute([$ligne_name]);
$ligne = $stmtLigneId->fetch();

$ligne_id = $ligne['id'] ?? null;


?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet de Notes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'menu.php'; ?>

<div class="container">
    <div class="col">
    <?php
    if ($ligne_id) {
    echo '<a href="create_note.php?ligne_id=' . $ligne_id . '" class="admin-button">Créer une note pour Nexus 2</a>';
    echo '<a href="create_memo.php?ligne_id=' . $ligne_id . '" class="admin-button">Créer un memo pour Nexus 2</a>';
    
} else {
    echo '<p>Erreur: Ligne Nexus2 non trouvée.</p>';
}
?>
        <button onclick="viewProcessedNotes()">Accéder aux notes traitées</button>
        

        <!-- Ajoutez d'autres boutons ici si nécessaire -->
    </div>
    <div class="col">
    <table>
        <h3>Notes Recemment ajoutées</h3>
    <thead>
        <tr>
            <th>Note</th>
            <th>Ligne</th>
            <th>Site</th>
            <th>Opérateur</th>
            <th>Date</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($recentNotes as $note): ?>
        <tr>
            <td><?= htmlspecialchars($note['note_text']); ?></td>
            <td><?= htmlspecialchars($note['ligne_name']); ?></td>
            <td><?= htmlspecialchars($note['site_name']); ?></td>
            <td><?= htmlspecialchars($note['operator_name']); ?></td>
            <td><?= htmlspecialchars($note['created_at']); ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
    </div>
    <div style="clear:both;"></div>
</div>

<script>
    

    function viewProcessedNotes() {
        // Logic to view processed notes
        alert("Fonction pour voir les notes traitées");
    }
</script>

</body>
</html>
