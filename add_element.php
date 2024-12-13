<?php
session_start();
require_once 'db_config.php'; // Assurez-vous que ce chemin est correct

ini_set('display_errors', 1);
error_reporting(E_ALL);

// Ajouter un nouvel élément et associer directement aux lignes
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_element'])) {
    $new_element_name = $_POST['new_element_name'];
    $ligne_ids = $_POST['ligne_ids'] ?? [];
    if (!empty($new_element_name)) {
        $stmt = $pdo->prepare("INSERT INTO elements (element_name) VALUES (?)");
        $stmt->execute([$new_element_name]);
        $element_id = $pdo->lastInsertId(); // Récupérer l'ID du nouvel élément inséré
        
        // Associer les lignes sélectionnées au nouvel élément
        $stmtInsert = $pdo->prepare("INSERT INTO ligne_elements (ligne_id, element_id) VALUES (?, ?)");
        foreach ($ligne_ids as $ligne_id) {
            $stmtInsert->execute([$ligne_id, $element_id]);
        }
    }
}

// Supprimer un élément
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_element'])) {
    $element_id_to_delete = $_POST['element_id_to_delete'];
    if (!empty($element_id_to_delete)) {
        // Supprimer les associations avec les lignes
        $stmtDeleteAssoc = $pdo->prepare("DELETE FROM ligne_elements WHERE element_id = ?");
        $stmtDeleteAssoc->execute([$element_id_to_delete]);

        // Supprimer l'élément
        $stmtDelete = $pdo->prepare("DELETE FROM elements WHERE id = ?");
        $stmtDelete->execute([$element_id_to_delete]);
    }
}

// Récupérer les éléments et leurs lignes associées pour l'affichage
$elementsQuery = $pdo->query("
    SELECT e.id, e.element_name, GROUP_CONCAT(l.name SEPARATOR ', ') AS lignes
    FROM elements e
    LEFT JOIN ligne_elements le ON e.id = le.element_id
    LEFT JOIN lignes l ON le.ligne_id = l.id
    GROUP BY e.id
");
$elements = $elementsQuery->fetchAll();

// Récupérer toutes les lignes pour le formulaire d'ajout
$lignesQuery = $pdo->query("SELECT * FROM lignes");
$lignes = $lignesQuery->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion des Éléments et Lignes</title>
    <link rel="stylesheet" href="styles.css"> <!-- Assurez-vous que le chemin vers votre fichier CSS est correct -->
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: auto;
            overflow: hidden;
        }
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {background-color: #f9f9f9;}
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container">
    <h1>Gestion des Éléments et de leurs Lignes</h1>
    
    <!-- Formulaire pour ajouter un nouvel élément et le lier aux lignes -->
    <form action="" method="post">
        <label for="new_element_name">Nom de l'élément :</label>
        <input type="text" id="new_element_name" name="new_element_name" required>
        <div class="container">
        <fieldset>
            <legend>Associer aux lignes :</legend>
            <?php foreach ($lignes as $ligne): ?>
                <div>
                    <input type="checkbox" id="ligne_<?= $ligne['id']; ?>" name="ligne_ids[]" value="<?= $ligne['id']; ?>">
                    <label for="ligne_<?= $ligne['id']; ?>"><?= htmlspecialchars($ligne['name']); ?></label>
                </div>
            <?php endforeach; ?>
        </fieldset>
        </div>
        <button type="submit" name="add_element">Ajouter et Associer</button>
    </form>
    <div class="container">
    <!-- Tableau des éléments avec lignes associées -->
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom de l'Élément</th>
                <th>Lignes Associées</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($elements as $element): ?>
            <tr>
                <td><?= htmlspecialchars($element['id']) ?></td>
                <td><?= htmlspecialchars($element['element_name']) ?></td>
                <td><?= htmlspecialchars($element['lignes']) ?></td>
                <td>
                    <form action="" method="post">
                        <input type="hidden" name="element_id_to_delete" value="<?= $element['id'] ?>">
                        <button type="submit" name="delete_element">Supprimer</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>

</body>
</html>
