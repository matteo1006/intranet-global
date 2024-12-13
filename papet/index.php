<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Formulaire</title>
</head>
<body>
    <h1>Formulaire</h1>
    <div class="container">
    <form id="myForm" action="submit.php" method="POST" enctype="multipart/form-data">
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required><br><br>

        <label for="team">Équipe:</label>
        <select id="team" name="team">
            <option value="1">Équipe 1</option>
            <option value="2">Équipe 2</option>
            <option value="3">Équipe 3</option>
            <option value="4">Équipe 4</option>
            <option value="5">Équipe 5</option>
        </select><br><br>

        <label for="text">Texte:</label>
        <textarea id="text" name="text" rows="4" cols="50"></textarea><br><br>

        <label for="category">Catégorie:</label>
        <select id="category" name="category">
            <option value="cat1">Catégorie 1</option>
            <option value="cat2">Catégorie 2</option>
            <option value="cat3">Catégorie 3</option>
        </select><br><br>

        <label for="image">Image:</label>
        <input type="file" id="image" name="image"><br><br>

        <input type="submit" value="Envoyer">
    </form>
    </div>
</body>
</html>
