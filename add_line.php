<?php
session_start();
include 'db_config.php';

$message = ""; // Message de confirmation ou d'erreur
$sites = []; // Tableau pour stocker les sites

try {
    // Récupération des sites depuis la base de données
    $stmt = $pdo->prepare("SELECT id, name FROM sites ORDER BY name");
    $stmt->execute();
    $sites = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $message = "Erreur lors de la récupération des sites : " . $e->getMessage();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $line_name = trim($_POST['line_name']);
    $site_id = $_POST['site_id']; // Récupération de l'identifiant du site

    if (!empty($line_name) && !empty($site_id)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO lignes (name, site_id) VALUES (?, ?)");
            $stmt->execute([$line_name, $site_id]);
            $message = "La ligne a été ajoutée avec succès.";
        } catch (PDOException $e) {
            $message = "Erreur lors de l'ajout de la ligne : " . $e->getMessage();
        }
    } else {
        $message = "Les champs du nom de la ligne et du site sont requis.";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter une ligne</title>
    <link rel="stylesheet" href="styles.css">
</head>
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
<body>
<?php include 'menu.php'; ?>
<div class="container">
    <a href="admin.php"><img src="./images/boutonRetour.jpg" alt="Bouton Retour" style="width: 5%;" class="back-btn"></a>     
    <h2 id="title">Ajouter une ligne</h2>
    <form action="" method="post">
        <label for="line_name">Nom de la ligne :</label>
        <input type="text" id="line_name" name="line_name" required>

        <label for="site_id">Site :</label>
        <select id="site_id" name="site_id" required>
            <option value="">Sélectionner un site</option>
            <?php foreach ($sites as $site): ?>
                <option value="<?php echo htmlspecialchars($site['id']); ?>">
                    <?php echo htmlspecialchars($site['name']); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" style="margin-top: 5%; margin-left: 23%; width: 50%">Ajouter</button>
    </form>
    <?php if (!empty($message)) echo "<p>$message</p>"; ?>
    <br>
    </div>

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
</body>
</html>
