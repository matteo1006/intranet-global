<?php
session_start();
include 'db_config.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $site_name = $_POST['site_name'];

    $stmt = $pdo->prepare("INSERT INTO sites (site_name) VALUES (?)");
    $stmt->execute([$site_name]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un site</title>
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
    </style>
</head>
<body>
<?php include 'menu.php'; ?>
<div class="container">
<a href="admin.php"><img src="./images/boutonRetour.jpg" alt="Bouton Retour" style="width: 5%;" class="back-btn"></a>     

    <h2 id="title">Ajouter un site</h2>
    <form action="" method="post">
        <label for="site_name">Nom du site :</label>
        <input type="text" id="site_name" name="site_name">
        <button type="submit" style="width: 40%; margin-left: 30%">Ajouter</button>
    </form>
    <br>
    </div>
</body>

<script>
        document.addEventListener('DOMContentLoaded', function() {
    const titleElement = document.getElementById('title');

    // Ajouter un écouteur d'événement pour la fin de l'animation de saisie
    titleElement.addEventListener('animationend', function() {
        // Ajouter la classe pour désactiver le curseur après l'animation
        titleElement.classList.add('no-cursor');
    });
});
</script>
</html>
