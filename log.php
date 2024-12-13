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
                alert('Il y a eu une erreur lors de l\'envoi du message.');
            }

            const maxPhotos = 6;
            let photoCounter = 1;

            function createPhotoInput() {
                const div = document.createElement('div');
                
                const label = document.createElement('label');
                label.textContent = 'Photo ' + photoCounter + ' à joindre:';
                label.for = 'photo' + photoCounter;

                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'photo[]';
                input.id = 'photo' + photoCounter;
                input.accept = 'image/*'; // Accepte tous les formats d'image
                input.required = true;

                div.appendChild(label);
                div.appendChild(input);

                photoCounter++;

                return div;
            }

            const photoContainer = document.getElementById('photoContainer');

            function addPhotoInput() {
                if (photoCounter <= maxPhotos) {
                    const newInput = createPhotoInput();
                    photoContainer.appendChild(newInput);
                }
            }

            addPhotoInput(); // Ajoute le premier champ de téléchargement de photo

            const addButton = document.getElementById('addPhotoButton');
            addButton.addEventListener('click', addPhotoInput);
        };


        document.querySelector('form').addEventListener('submit', function (e) {
    e.preventDefault(); // Empêche l'envoi du formulaire par défaut

    const formData = new FormData(this); // Récupère les données du formulaire

    // Affiche la barre de progression
    const progressBar = document.getElementById('progress-bar');
    progressBar.style.width = '0%';

    // Effectue une requête AJAX pour télécharger les fichiers
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'send_email.php', true);

    // Surveille la progression du téléchargement
    xhr.upload.addEventListener('progress', function (event) {
        if (event.lengthComputable) {
            const percentComplete = (event.loaded / event.total) * 100;
            progressBar.style.width = percentComplete + '%';

            if (percentComplete === 100) {
                // Tous les fichiers ont été téléchargés, vous pouvez maintenant soumettre le formulaire
                formData.append('submit', 'true'); // Ajoutez une valeur pour indiquer que le formulaire est soumis
                xhr.abort(); // Arrête la requête AJAX actuelle
                document.querySelector('form').submit(); // Soumettez le formulaire
            }
        }
    });

    // Envoie la requête AJAX avec les données du formulaire
    xhr.send(formData);
});

    </script>
</head>
<body>
    <div class="container">
        <h2>Envoyer des Photos</h2>
        <form action="send_email.php" method="post" enctype="multipart/form-data">
            Type d'opération:
            <select name="operation">
                <option value="chargement">Chargement</option>
                <option value="dechargement">Déchargement</option>
            </select><br>

            Message (optionnel):
            <textarea name="message"></textarea><br>

            <div id="photoContainer">
                <!-- Les champs de téléchargement de photos seront ajoutés ici dynamiquement -->
            </div>

            <button type="button" id="addPhotoButton">Ajouter une autre photo</button>
            <div id="progress-container">
    <div id="progress-bar"></div>
</div>
            <input type="submit" name="submit" value="Envoyer">
        </form>
    </div>
</body>
</html>
