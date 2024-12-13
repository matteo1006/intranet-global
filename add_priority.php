<?php
session_start();
include 'db_config.php'; // Assurez-vous que ce fichier contient les informations de connexion à la base de données
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Vérifier si le formulaire d'ajout a été soumis
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_priorite'])) {
    $nom_priorite = $_POST['nom_priorite'] ?? '';
    if (!empty($nom_priorite)) {
        $stmt = $pdo->prepare("INSERT INTO priorite (nom) VALUES (?)");
        $stmt->execute([$nom_priorite]);
    }
}

// Vérifier si une demande de suppression a été soumise
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM priorite WHERE id = ?");
    $stmt->execute([$delete_id]);
}

// Récupérer la liste des priorités
$stmt = $pdo->prepare("SELECT * FROM priorite");
$stmt->execute();
$priorites = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Priorités</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>

<?php include 'menu.php'; ?>
<div class="container">
    <h1>Gestion des Priorités</h1>
    <form action="" method="post">
        <label for="nom_priorite">Nom de la priorité :</label>
        <input type="text" id="nom_priorite" name="nom_priorite" required>
        <button type="submit" name="add_priorite">Ajouter</button>
    </form>

    <h2>Liste des Priorités</h2>
    <div class="container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom de la Priorité</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($priorites as $priorite): ?>
                <tr>
                    <td><?= htmlspecialchars($priorite['id']) ?></td>
                    <td><?= htmlspecialchars($priorite['nom']) ?></td>
                    <td>
                        <form action="" method="post">
                            <input type="hidden" name="delete_id" value="<?= $priorite['id'] ?>">
                            <input type="submit" value="Supprimer">
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <a href="admin.php">Retour au panneau d'administration</a>
</div>
</body>
</html>
