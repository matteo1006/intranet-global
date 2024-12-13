<?php
session_start();
require_once 'db_config.php'; // Assurez-vous que ce fichier contient les informations de connexion à votre base de données

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ajouter un nouveau statut
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_status'])) {
    $new_status_name = $_POST['new_status_name'];
    if (!empty($new_status_name)) {
        $stmt = $pdo->prepare("INSERT INTO note_status (status_name) VALUES (?)");
        $stmt->execute([$new_status_name]);
    }
}

// Supprimer un statut
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_status'])) {
    $status_id_to_delete = $_POST['status_id_to_delete'];
    $stmt = $pdo->prepare("DELETE FROM note_status WHERE id = ?");
    $stmt->execute([$status_id_to_delete]);
}

// Récupérer les statuts pour l'affichage
$stmtStatus = $pdo->prepare("SELECT * FROM note_status");
$stmtStatus->execute();
$statuses = $stmtStatus->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Statuts des Notes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container">
    <h1>Gestion des Statuts des Notes</h1>
    
    <!-- Formulaire pour ajouter un nouveau statut -->
    <form action="" method="post">
        <input type="text" name="new_status_name" placeholder="Nom du statut" required>
        <button type="submit" name="add_status">Ajouter un statut</button>
    </form>
    
    <!-- Tableau pour afficher et supprimer les statuts -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom du Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($statuses as $status): ?>
            <tr>
                <td><?= htmlspecialchars($status['id']) ?></td>
                <td><?= htmlspecialchars($status['status_name']) ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="status_id_to_delete" value="<?= $status['id'] ?>">
                        <button type="submit" name="delete_status">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>
