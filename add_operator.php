<?php
session_start();
include 'db_config.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$stmt = $pdo->query("SELECT * FROM operators");
$operators = $stmt->fetchAll(PDO::FETCH_ASSOC);




// Récupérer la liste des sites
$stmt = $pdo->prepare("SELECT id, name FROM sites");
$stmt->execute();
$sites = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialiser les variables
$operator_name = "";
$site_id = "";
$postes = [];

// Récupérer la liste des postes
$stmt = $pdo->prepare("SELECT id, name FROM postes");
$stmt->execute();
$postes = $stmt->fetchAll(PDO::FETCH_ASSOC);


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['name'])) {
    // Vérifier si le nom de l'opérateur est fourni
    $operator_name = $_POST['name'];

    if (!empty($operator_name)) {
        // Insérer le nouvel opérateur dans la table operators
        $stmt = $pdo->prepare("INSERT INTO operators (name) VALUES (?)");
        $stmt->execute([$operator_name]);

        // Récupérer l'ID de l'opérateur inséré
        $lastInsertId = $pdo->lastInsertId();

        // Insérer les sites sélectionnés dans operator_sites
        if (isset($_POST['site_id'])) {
            foreach ($_POST['site_id'] as $site_id) {
                $stmt = $pdo->prepare("INSERT INTO operator_sites (operator_id, site_id) VALUES (?, ?)");
                $stmt->execute([$lastInsertId, $site_id]);
            }
        }

        // Insérer les postes sélectionnés dans operator_postes
        if (isset($_POST['postes'])) {
            foreach ($_POST['postes'] as $poste_id) {
                $stmt = $pdo->prepare("INSERT INTO operator_postes (operator_id, poste_id) VALUES (?, ?)");
                $stmt->execute([$lastInsertId, $poste_id]);
            }
        }
    }
}

// Vérifier si une demande de suppression a été soumise
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM operators WHERE id = ?");
    $stmt->execute([$delete_id]);
}

// Récupérer la liste des opérateurs avec leurs sites et postes associés
$stmt = $pdo->prepare("
    SELECT 
        operators.id, 
        operators.name, 
        GROUP_CONCAT(DISTINCT postes.name ORDER BY postes.name ASC SEPARATOR ', ') AS postes, 
        GROUP_CONCAT(DISTINCT sites.name ORDER BY sites.name ASC SEPARATOR ', ') AS sites
    FROM 
        operators
        LEFT JOIN operator_postes ON operators.id = operator_postes.operator_id
        LEFT JOIN postes ON operator_postes.poste_id = postes.id
        LEFT JOIN operator_sites ON operators.id = operator_sites.operator_id
        LEFT JOIN sites ON operator_sites.site_id = sites.id
    GROUP BY 
        operators.id
");
$stmt->execute();
$operators = $stmt->fetchAll();

ini_set('memory_limit', '256M');
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '10M');
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un opérateur</title>
    <link rel="stylesheet" href="styles.css">

    <style>
        /* Conteneur principal avec animation au survol */
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid grey;
        }



        /* Animation au survol */
        .container:hover {
            transform: translateY(-10px); /* Légère élévation */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Accentuation de l'ombre */
        }

        h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            border-right: 3px solid #333; /* Curseur clignotant */
            width: 0;
            animation: typing 3s steps(30, end) forwards, blink-caret 0.75s step-end infinite;
            padding-bottom: 2%;
        }

        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: #333; }
        }

        .no-cursor {
            user-select: none; /* Désactive la sélection du texte */
            pointer-events: none; /* Désactive toute interaction avec la souris */
            border-right: none; /* Retire le curseur clignotant */
        }

        .form-group {
            flex: 1;
            margin-right: 20px;
        }

        select {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
            height: auto; /* Ajuste automatiquement la hauteur */
        }

        button[type="submit"] {
            background-color: #1a3b5a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background-color: #16304a;
        }


        label {
            font-weight: bold;
            margin-bottom: 5px;
            display: inline-block;
        }


        /* Cacher le tableau par défaut */
        #operatorsTable {
            display: none;
        }

        /* Style du bouton */
        #showOperatorsBtn {
            margin-top: 20px;
            background-color: #34495e;
            color: white;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
            border-radius: 5px;
            font-size: 1rem;
        }

        #showOperatorsBtn:hover {
            background-color: #1f2d3a;
        }

        
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container">
<a href="admin.php"><img src="./images/boutonRetour.jpg" alt="Bouton Retour" style="width: 5%;" class="back-btn"></a>     

    <h2 id="title">Ajouter un opérateur</h2>
    <form action="" method="post">
        <input type="text" style="width: 60%; margin-left: 20%" id="name" placeholder="Saisir le nom du nouvel opérateur" name="name" required>

        <div style="display: flex; justify-content: space-between; align-items: center;">

            <!-- Liste déroulante pour Sites -->
        <div class="form-group" style="flex: 1; margin-right: 20px;">
            <label for="site_id">Sites :</label>
            <select name="site_id[]" id="site_id"  class="form-control" style="width: 100%;">
                <option value="" disabled>Choisir un ou plusieurs sites</option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?= htmlspecialchars($site['id']) ?>"><?= htmlspecialchars($site['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Liste déroulante pour Postes -->
        <div class="form-group" style="flex: 1;">
            <label for="postes">Postes :</label>
            <select name="postes[]" id="postes"  class="form-control" style="width: 100%;">
                <option value="" disabled>Choisir un ou plusieurs postes</option>
                <?php foreach ($postes as $poste): ?>
                    <option value="<?= htmlspecialchars($poste['id']) ?>"><?= htmlspecialchars($poste['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

    </div>
    <button type="submit">Ajouter</button>

</div>
<button id="showOperatorsBtn" style="margin-left: 40%; width: 20%; background-color: white; color: black" type="button">Voir les opérateurs</button>

    </form>


    <div id="operatorsTable" class="container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom de l'opérateur</th>
                    <th>Site</th>
                    <th>Postes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($operators as $operator): ?>
                    <tr>
                        <td><?= htmlspecialchars($operator['id']) ?></td>
                        <td><?= htmlspecialchars($operator['name']) ?></td>
                        <td><?= htmlspecialchars($operator['sites'] ?? 'Aucun site attribué') ?></td>
                        <td><?= htmlspecialchars($operator['postes'] ?? 'Aucun poste attribué') ?></td>

                        <td>
                            <form action="" method="post">
                                <input type="hidden" name="delete_id" value="<?= $operator['id'] ?>">
                                <input type="submit" value="Supprimer">
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <br>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function() {
        const titleElement = document.getElementById('title');
        const operatorsTable = document.getElementById('operatorsTable');
        const showOperatorsBtn = document.getElementById('showOperatorsBtn');

        // Ajouter un écouteur d'événement pour la fin de l'animation de saisie
        titleElement.addEventListener('animationend', function() {
            titleElement.classList.add('no-cursor');
        });

        // Ajouter un événement pour afficher/masquer le tableau
        showOperatorsBtn.addEventListener('click', function() {
            if (operatorsTable.style.display === 'none') {
                operatorsTable.style.display = 'block';
                showOperatorsBtn.textContent = "Masquer les opérateurs";
            } else {
                operatorsTable.style.display = 'none';
                showOperatorsBtn.textContent = "Voir les opérateurs";
            }
        });
    });
</script>
</body>
</html>
