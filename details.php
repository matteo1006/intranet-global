<?php
session_start();
require_once "db_config.php";
include "menu.php";

// Vérification de l'ID de la note dans l'URL
if (!isset($_GET['id'])) {
    echo "Aucune note spécifiée";
    exit;
}

// Récupération de l'ID de la note
$noteId = $_GET['id'];

// Requête pour récupérer les détails de la note
$query = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name
          FROM notes 
          LEFT JOIN sites ON notes.site_id = sites.id 
          LEFT JOIN lignes ON notes.ligne_id = lignes.id
          LEFT JOIN services ON notes.service_id = services.id
          WHERE notes.id = ?";
$stmt = $pdo->prepare($query);
$stmt->execute([$noteId]);
$note = $stmt->fetch();

if (!$note) {
    echo "Note introuvable.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détail de la note</title>
    <style>
    body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f9;
    padding: 20px;
    margin: 0;
}

/* Conteneur principal */
.container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    background-color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 8px;
}

/* Titre */
h2 {
    text-align: center;
    color: #34495e;
    font-size: 24px;
    margin-bottom: 20px;
}

/* Texte et paragraphes */
p {
    font-size: 16px;
    margin-bottom: 10px;
}

/* Bouton retour */
.back-btn {
    display: inline-block;
    padding: 10px 20px;
    background-color: #34495e;
    color: white;
    text-decoration: none;
    border-radius: 4px;
    margin-top: 20px;
}

.back-btn:hover {
    background-color: #2c3e50;
    cursor: pointer;
}

/* Images */
.note-images img {
    width: 200px;
    height: auto;
    margin-right: 10px;
    border-radius: 8px;
    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    cursor: pointer;
    transition: transform 0.3s;
}

/* Zoom */
.zoomed {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) scale(2); /* Zoom à 200% */
    z-index: 1000;
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
}

.overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 999;
}

/* Vidéo */
.note-images video {
    width: 100%;
    border-radius: 8px;
}

/* Responsive */

/* Mobile */
@media (max-width: 600px) {
    .container {
        padding: 15px;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 15px;
    }

    p {
        font-size: 14px;
    }

    .back-btn {
        font-size: 14px;
        padding: 8px 12px;
    }

    /* Les images s'étendent sur toute la largeur du conteneur */
    .note-images img {
        width: 100%;
        margin-bottom: 10px;
    }

    .note-images video {
        width: 100%;
        height: auto;
    }
}

/* Tablettes */
@media (max-width: 768px) {
    .container {
        padding: 20px;
    }

    h2 {
        font-size: 22px;
    }

    p {
        font-size: 15px;
    }

    .back-btn {
        font-size: 16px;
        padding: 9px 15px;
    }
}

        
    </style>
</head>
<body>

<div class="overlay" id="overlay"></div> <!-- Overlay pour l'effet foncé derrière l'image -->

<div class="container">
    <h2>Détail de la note</h2>
    <p><strong>Note :</strong> <?= htmlspecialchars($note['note_text']); ?></p>
    <p><strong>Site :</strong> <?= htmlspecialchars($note['site_name']); ?></p>
    <p><strong>Opérateur :</strong> <?= htmlspecialchars($note['operator_name']); ?></p>
    <p><strong>Service :</strong> <?= htmlspecialchars($note['service_name']); ?></p>
    <p><strong>Créée le :</strong> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($note['created_at']))); ?></p>
    <p><strong>Commentaire :</strong> <?= htmlspecialchars($note['comment']); ?></p>
    <p><strong>Images :</strong></p>
    <div class="note-images">
        <?php $imagePaths = explode(',', $note['image_path']); ?>
        <?php foreach ($imagePaths as $imagePath): ?>
            <img src="<?= htmlspecialchars($imagePath); ?>" alt="Image de note" style="width: 20%">
        <?php endforeach; ?>
    </div>

     <!-- Formulaire pour le bouton supprimer -->
     <form action="delete_notes.php" method="post" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette note ?');">
        <input type="hidden" name="note_id" value="<?= $noteId; ?>">
        <button type="submit" class="btn btn-danger" style="background-color: #e74c3c; color: white; border: none; padding: 10px 20px; margin-top: 20px; cursor: pointer;">Supprimer la note</button>
    </form>

    <center><button class="back-btn" onclick="window.history.back()">Revenir en arrière</button></center>
    </div>



<script>
    document.querySelectorAll('.note-images img').forEach(function(image) {
    image.addEventListener('click', function() {
        // Sélectionner l'overlay
        const overlay = document.getElementById('overlay');
        
        // Si l'image est déjà zoomée, on retire le zoom
        if (image.classList.contains('zoomed')) {
            image.classList.remove('zoomed');
            overlay.style.display = 'none';
        } else {
            // Si une autre image est déjà zoomée, on la réinitialise
            document.querySelectorAll('.zoomed').forEach(function(zoomedImage) {
                zoomedImage.classList.remove('zoomed');
            });
            
            // Appliquer le zoom sur l'image cliquée
            image.classList.add('zoomed');
            overlay.style.display = 'block';
        }
    });
});

// Cacher l'overlay et retirer le zoom lorsqu'on clique sur l'overlay
document.getElementById('overlay').addEventListener('click', function() {
    document.querySelectorAll('.zoomed').forEach(function(image) {
        image.classList.remove('zoomed');
    });
    this.style.display = 'none';
});

</script>

</body>
</html>
