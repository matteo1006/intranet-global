<?php
session_start();
include 'db_config.php';

if($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un utilisateur</title>
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
    <h2 id="title">Ajouter un utilisateur</h2>
    
    <form action="" method="post">
        <label for="username">Nom d'utilisateur :</label>
        <input type="text" id="username" name="username">
        <label for="password">Mot de passe :</label>
        <input type="password" id="password" name="password">
        <button type="submit" style="margin-top: 5%; margin-left: 23%; width: 50%">Ajouter</button>
    </form>
    </div>
    <br>

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
