<?php
session_start();
require_once "db_config.php";



$site_id = 3; // ID de MARENTON. Changez cette valeur pour chaque page/site.
$stmt = $pdo->prepare("SELECT * FROM lignes WHERE site_id = ?");
$stmt->execute([$site_id]);
$lignes = $stmt->fetchAll();

$stmt_site = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
$stmt_site->execute([$site_id]);
$site = $stmt_site->fetch();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title><?php echo $site['name']; ?></title>
    <style>
        .button {
            display: inline-block;
            width: 150px;
            height: 150px;
            margin: 10px;
            background-color: #0B3F65;
            color: white;
            text-align: center;
            border-radius: 8px;
            line-height: 150px;
            font-size: 1.5rem;
            cursor: pointer;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .button:hover {
            background-color: #ffffff;
            color: #0056b3;
        }

        .container {
    max-width: 900px;
    margin: 50px auto;
    padding: 30px;
    background-color: rgba(255, 255, 255, 0.85); /* Fond semi-transparent */
    border-radius: 15px; /* Coins arrondis */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Ombre subtile */
    transition: transform 0.3s ease, box-shadow 0.3s ease; /* Animation au survol */
}

/* Animation au survol */
.container:hover {
    transform: translateY(-5px); /* Légère élévation */
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3); /* Ombre plus prononcée */
}

/* Effet de saisie pour le titre */
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

/* Apparence des boutons de lignes */
.button {
    display: inline-block;
    width: 200px; /* Plus grand pour plus de lisibilité */
    height: 150px;
    margin: 15px;
    background-color: #0B3F65;
    color: white;
    text-align: center;
    border-radius: 12px; /* Coins arrondis plus doux */
    line-height: 150px;
    font-size: 1.2rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin-left: 17%;

}

/* Effet au survol des boutons */
.button:hover {
    background-color: #1E90FF;
    color: #ffffff;
    transform: scale(1.05); /* Légère mise à l'échelle */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Accentuation de l'ombre */
}
    </style>
</head>
<body>
<?php include 'menu.php'; ?>

    <div class="container">
        <h2>Lignes pour le site <?php echo $site['name']; ?></h2>
        <?php
        foreach ($lignes as $ligne) {
            echo "<a class='button' href='dashboardlignes.php?ligne_id=" . $ligne['id'] . "&site_id=" . $site_id . "'>" . $ligne['name'] . "</a>";
        }
        ?>
    </div>
</body>
</html>
