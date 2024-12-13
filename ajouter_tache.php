<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once "db_config.php";

// Gestion de la soumission du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $nom = $_POST['nom'] ?? '';
    $arret_machine = isset($_POST['arret_machine']) ? 1 : 0;
    $a_executer = $_POST['a_executer'] ?? '';
    $recurrence = $_POST['recurrence'] ?? '';

    // Préparer et exécuter la requête SQL pour insérer la nouvelle tâche
    $sql = "INSERT INTO taches_nettoyage (nom, arret_machine, a_executer, recurrence) VALUES (?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$nom, $arret_machine, $a_executer, $recurrence]);

    echo "Nouvelle tâche ajoutée avec succès !";
}
?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Intranet de Notes - <?= htmlspecialchars($ligneName); ?></title>

    <link rel="stylesheet" href="styles.css">

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <style>

        body, html {

            margin: 0;

            padding: 0;

            width: 100%;

            height: 85%;

        }

        .sticky-top {

            position: sticky;

            top: 0;

            background-color: white;

            z-index: 1000;

            box-shadow: 0 2px 5px -2px rgba(0,0,0,.2);

        }

        .flex-container {

            display: flex;

            justify-content: space-between;

            height: calc(100% - 60px); /* Adjust based on header height */

            overflow-y: auto;

            margin-bottom: 0;

            margin-top: 10px;

        }

        .notes-section, .memos-section {

            width: 48%;

            padding: 10px;

            height: 100%;

            overflow-y: auto;

        }

        .note-item, .memo-item {

            border: 1px solid #ddd;

            margin-bottom: 10px;

            padding: 10px;

            background-color: #f9f9f9;

            border-radius: 5px;

        }

        .note-item p:first-child, .memo-item p:first-child {

            font-weight: bold;

        }

        .modal, .memoModal {

            display: none;

            position: fixed;

            z-index: 1;

            padding-top: 100px;

            left: 0;

            top: 0;

            width: 100%;

            height: 100%;

            overflow: auto;

            background-color: rgb(0,0,0);

            background-color: rgba(0,0,0,0.4);

        }

        .modal-content, .memoModal-content {

            background-color: #fefefe;

            margin: auto;

            padding: 20px;

            border: 1px solid #888;

            width: 80%;

        }

        .close {

            color: #aaaaaa;

            float: right;

            font-size: 28px;

            font-weight: bold;

        }

        .close:hover,

        .close:focus {

            color: #000;

            text-decoration: none;

            cursor: pointer;

        }

        .admin-button {

            margin: 10px 0;

            display: inline-block;

            padding: 10px 15px;

            background-color: #007bff;

            color: white;

            text-decoration: none;

            border-radius: 5px;

        }

        .container {

            padding: 15px;

            margin-bottom: 5px;

        }
        
.header-content {
    display: flex;
    align-items: center;
    flex-grow: 1;
}

h1 {
    margin-right: 20px; /* Ajustez selon vos besoins pour l'espacement */
}

.datetime-display {
    margin-left: auto; /* Pousse l'affichage de la date et de l'heure à la droite du titre */
    white-space: nowrap; /* Empêche la date et l'heure de passer à la ligne */
}

h3 {
    margin-left: auto; /* Pousse l'affichage de la date et de l'heure à la droite du titre */
    white-space: nowrap; /* Empêche la date et l'heure de passer à la ligne */
}

    </style>

</head>

<body>

<?php include 'menu.php'; ?>

<div class="container">
 
<form action="ajouter_tache.php" method="post">
    Nom de la tâche: <input type="text" name="nom" required><br>
    Arrêt machine: <input type="checkbox" name="arret_machine"><br>
    À exécuter:
    <select name="a_executer" required>
        <option value="matin">Matin</option>
        <option value="apres_midi">Après-midi</option>
        <option value="soir">Soir</option>
    </select><br>
    Récurrence:
    <select name="recurrence" required>
        <option value="quotidien">Tous les jours</option>
        <option value="hebdomadaire">1 fois par semaine</option>
        <option value="bihebdomadaire">1 fois toutes les 2 semaines</option>
        <option value="mensuel">1 fois par mois</option>
    </select><br>
    <input type="submit" value="Ajouter la tâche">
</form>

</div>




<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>



