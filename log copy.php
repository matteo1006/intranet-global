
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Envoyer un Email</title>
    <link rel="stylesheet" href="style.css">
    <script>
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            if (status === 'success') {
                alert('Le message a été envoyé avec succès.');
            } else if (status === 'error') {
                alert('Il y a eu une erreur lors de l'envoi du message.');
            }
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Envoyer une Photo</h2>
<form action="send_email.php" method="post" enctype="multipart/form-data">
    Type d'opération:
    <select name="operation">
        <option value="chargement">Chargement</option>
        <option value="dechargement">Déchargement</option>
    </select><br>

    Message (optionnel):
    <textarea name="message"></textarea><br>

    Photo à joindre:
    <input type="file" name="photo[]" multiple required="required"><br>

    <input type="submit" name="submit" value="Envoyer">
</form>
</div>
</body>
</html>
