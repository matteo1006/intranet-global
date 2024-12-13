<?php
// Inclure le fichier de configuration de la base de données
require_once "db_config.php";

// Récupérer la liste des postes depuis la base de données
$stmtPostes = $pdo->prepare("SELECT * FROM postes");
$stmtPostes->execute();
$postes = $stmtPostes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des opérateurs depuis la base de données
$stmtOperators = $pdo->prepare("SELECT * FROM operators");
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Postes et Opérateurs</title>
</head>

<body>
    <h2>Choisissez un Poste et un Opérateur</h2>

    <form>
        <label for="poste">Poste :</label>
        <select name="poste" id="poste">
            <option value="">Sélectionnez un poste</option>
            <?php foreach ($postes as $poste): ?>
                <option value="<?= $poste['id']; ?>"><?= $poste['name']; ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="operateur">Opérateur :</label>
        <select name="operateur" id="operateur">
            <option value="">Sélectionnez un opérateur</option>
            <?php foreach ($operators as $operator): ?>
                <option value="<?= $operator['id']; ?>"><?= $operator['name']; ?></option>
            <?php endforeach; ?>
        </select>
    </form>
</body>

</html>
