<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

require_once "db_config.php";

include "menu.php";

// Récupération du filtre de ligne

$ligneFilter = isset($_GET['ligne_id']) ? $_GET['ligne_id'] : null;



$ligneName = "";

if ($ligneFilter) {

    $ligneQuery = "SELECT name FROM lignes WHERE id = ?";

    $ligneStmt = $pdo->prepare($ligneQuery);

    $ligneStmt->execute([$ligneFilter]);

    $ligneData = $ligneStmt->fetch();

    $ligneName = $ligneData['name'] ?? 'Toutes les lignes';

}



$query = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name, postes.name as poste_name, notes.image_path FROM notes LEFT JOIN sites ON notes.site_id = sites.id LEFT JOIN lignes ON notes.ligne_id = lignes.id LEFT JOIN services ON notes.service_id = services.id LEFT JOIN postes ON notes.poste = postes.id";


if ($ligneFilter) {

    $query .= " WHERE notes.ligne_id = :ligneFilter";

}

$query .= " ORDER BY created_at DESC LIMIT 10";



$stmt = $pdo->prepare($query);

if ($ligneFilter) {

    $stmt->bindParam(':ligneFilter', $ligneFilter, PDO::PARAM_INT);

}

$stmt->execute();

$recentNotes = $stmt->fetchAll();



$memoQuery = "SELECT memos.*, lignes.name as ligne_name, postes.name as postes_name, operators.name as operator_name, memos.image_path FROM memos LEFT JOIN lignes ON memos.ligne_id = lignes.id LEFT JOIN postes ON memos.poste_id = postes.id LEFT JOIN operators ON memos.operator_id = operators.id WHERE memos.created_at >= DATE_SUB(NOW(), INTERVAL 96 HOUR)";
if ($ligneFilter) {

    $memoQuery .= " AND memos.ligne_id = ?";

}

$memoQuery .= " ORDER BY memos.created_at DESC";

$memoStmt = $pdo->prepare($memoQuery);

if ($ligneFilter) {

    $memoStmt->execute([$ligneFilter]);

} else {

    $memoStmt->execute();

}

$memos = $memoStmt->fetchAll();


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

       /* Style du conteneur principal */
.container {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background-color: rgba(255, 255, 255, 0.9);
    border-radius: 15px;
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}



.flex-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap; /* Permet l'affichage en colonne si l'espace est insuffisant */
            width: 80%;
        }
/* Style des sections de notes et memos */
.notes-section, .memos-section {
    background-color: rgba(240, 248, 255, 0.8);
    padding: 10px;
    border-radius: 12px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-bottom: 20px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    width: 49%;
}



/* Style des items (notes et memos) */
.note-item, .memo-item {
    border: 1px solid #ddd;
    margin-bottom: 10px;
    padding: 15px;
    background-color: #ffffff;
    border-radius: 8px;
    transition: background-color 0.2s;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Effet au survol des items */
.note-item:hover, .memo-item:hover {
    background-color: #f1f1f1;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Style des titres de section */
.notes-section h2, .memos-section h2 {
    font-size: 1.8rem;
    color: #333;
    margin-bottom: 15px;
}



/* Style des modales */
.modal-content, .memoModal-content {
    background-color: #fefefe;
    margin: auto;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    width: 60%;
    height: 80%;
    margin-top: 5%;
    margin-bottom: -10%;
}

/* Style du bouton de fermeture des modales */
.close {
    color: #aaaaaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover, .close:focus {
    color: #000;
}

.header-content{
    display: flex;
    justify-content: space-between;
    
}

.button-container {
        display: flex;
        gap: 20px; /* Espace entre les boutons */
        align-items: center; /* Centre les boutons verticalement */
    }

    .admin-button {
        padding: 10px 20px;
        background-color: #34495e;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background-color 0.3s;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .admin-button:hover {
        background-color: #2c3e50;
        color: white;
    }

 

.back-btn:hover {
    background-color: #2c3e50;
    cursor: pointer;
}

.back-btn{
    border-radius: 20%; /* Cela rendra l'image complètement circulaire si elle est carrée */
    width: 5%; /* Tu peux ajuster la taille selon tes besoins */
    height: 40px; /* Assure-toi que la hauteur est la même pour un effet circulaire */
    object-fit: cover; 
}

    /* Style de base pour les images miniatures */
    .zoomable-image {
        width: 150px;
        height: 150px;
        object-fit: cover;
        cursor: pointer;
        transition: transform 0.2s ease;
    }

    /* Style pour le zoom plein écran */
    .zoomed-in {
        position: fixed;
        top: 0;
        left: 50;
        width: 75vw;
        height: 100vh;
        object-fit: contain;
        z-index: 1000;
        background-color: rgba(0, 0, 0, 0.8);
        cursor: zoom-out;
        transition: transform 0.2s ease, opacity 0.2s ease;
    }



/* Masquer le menu pour les écrans de taille inférieure à 1280px  (taille des tablettes format PAYSAGE) */
@media only screen and (max-width: 1280px) {
    #menu1 {
        display: none; /* Le menu est caché sur les tablettes et téléphones */
    }
    .flex-container{
        width: 100%;
    }
}




    </style>

</head>

<body>



<div class="container">
    <div class="header-content">
        <h1 style="text-align: right; margin-right: 10px;"><?= htmlspecialchars($ligneName); ?></h1>
        <h3 style="text-align: right; margin-right: 20px;"><div id="datetime" class="datetime-display"></div></h3>

    </div>
    <div class="button-container" style="padding-top: 5%; margin-left: 38%">
        <a href="create_note.php?ligne_id=<?= $ligneFilter; ?>" class="admin-button">Créer une note</a>
        <a href="create_memo.php?ligne_id=<?= $ligneFilter; ?>" class="admin-button">Créer une relève</a>
    </div>
</div>
</div>





<div class="container flex-container">

    <div class="notes-section">

        <h2>Notes récemment ajoutées</h2>

        <?php foreach ($recentNotes as $note): ?>

        <div class="note-item" onclick="toggleModal('noteModal<?= $note['id']; ?>')">

            <p><?= htmlspecialchars($note['operator_name']); ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($note['created_at']))); ?> pour <?= htmlspecialchars($note['service_name']); ?></p>

            <p><?= substr(htmlspecialchars($note['note_text']), 0, 50); ?>...</p>

        </div>

        <div id="noteModal<?= $note['id']; ?>" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('noteModal<?= $note['id']; ?>')">&times;</span>
                <center><h3>Détails de la note</h3></center>
                <p>Créée le: <?= htmlspecialchars($note['created_at']); ?></p>
                <p>Service: <?= htmlspecialchars($note['service_name']); ?></p>
                <p>Poste: <?= htmlspecialchars($note['poste_name']); ?></p>
                <p>Texte: <?= htmlspecialchars($note['note_text']); ?></p>
                
                <div class="overlay" id="overlay"></div> <!-- Overlay pour l'effet foncé derrière l'image -->

                <?php if (!empty($note['image_path'])): ?>
    <?php
    // Divisez les chemins d'image en un tableau
    $images = explode(',', $note['image_path']);
    ?>
    <center>
    <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
        <?php foreach ($images as $image): ?>
            <?php if (file_exists(trim($image))): ?>
                <img src="<?= htmlspecialchars(trim($image)); ?>" alt="Image de la note" class="zoomable-image">
            <?php else: ?>
                <p>Image non disponible pour : <?= htmlspecialchars(trim($image)); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    </center>
<?php endif; ?>

                <form action="#" method="post">
                    <div class="form-group">
                        <label for="initiales">Initiales:</label>
                        <input type="text" class="form-control" id="initials" name="initials" required>
                    </div>
                    <div class="form-group">
                        <label for="commentaire">Commentaire :</label>
                        <textarea class="form-control" id="commentaire" name="commentaire" rows="3" required></textarea>
                    </div>
                    <input type="hidden" name="note_id" value="<?= $note['id']; ?>">
                    <button type="submit" class="btn btn-primary">Archiver avec commentaire</button>
                </form>
            </div>
        </div>

        <?php endforeach; ?>

        <!-- Ajouter un bouton "Voir tous" pour rediriger vers toutes les notes -->
    <div style="text-align: center; margin-top: 20px;">
    </div>

    </div>

    <div class="memos-section">

        <h2>Relevés des dernières 96H</h2>

        <?php foreach ($memos as $memo): ?>

        <div class="memo-item" onclick="toggleModal('memoModal<?= $memo['id']; ?>')">

            <p><?= htmlspecialchars($memo['operator_name']); ?> le <?= htmlspecialchars(date('d/m/Y', strtotime($memo['created_at']))); ?> - <?= htmlspecialchars($memo['postes_name']); ?></p>

            <p><?= substr(htmlspecialchars($memo['memo_text']), 0, 50); ?>...</p>

        </div>

        <div id="memoModal<?= $memo['id']; ?>" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('memoModal<?= $memo['id']; ?>')">&times;</span>
                <center><h3>Détails de la relève</h3></center>
                <p>Créé le: <?= htmlspecialchars($memo['created_at']); ?></p>
                <p>Poste: <?= htmlspecialchars($memo['postes_name']); ?></p>
                <p>Opérateur: <?= htmlspecialchars($memo['operator_name']); ?></p>
                <p>Texte: <?= htmlspecialchars($memo['memo_text']); ?></p>
                <?php if (!empty($memo['image_path'])): ?>
    <?php
    // Divisez les chemins d'image en un tableau
    $images = explode(',', $memo['image_path']);
    ?>
    <center>
    <div style="display: flex; flex-wrap: wrap; gap: 10px; justify-content: center;">
        <?php foreach ($images as $image): ?>
            <?php if (file_exists(trim($image))): ?>
                <img src="<?= htmlspecialchars(trim($image)); ?>" alt="Image de la relève" class="zoomable-image">
            <?php else: ?>
                <p>Image non disponible pour : <?= htmlspecialchars(trim($image)); ?></p>
            <?php endif; ?>
        <?php endforeach; ?>
    </div>
    </center>
<?php endif; ?>

                
            </div>
        </div>

        <?php endforeach; ?>

    </div>

</div>



<script>

        // Fonction pour activer/désactiver le zoom sur une image
        document.querySelectorAll('.zoomable-image').forEach(img => {
        img.addEventListener('click', function() {
            if (this.classList.contains('zoomed-in')) {
                // Si l'image est déjà agrandie, on la réduit
                this.classList.remove('zoomed-in');
            } else {
                // Sinon, on ajoute la classe pour agrandir l'image
                this.classList.add('zoomed-in');
            }
        });
    });

    function toggleModal(modalId) {

        var modal = document.getElementById(modalId);

        modal.style.display = "block";

    }



    function closeModal(modalId) {

        var modal = document.getElementById(modalId);

        modal.style.display = "none";

    }



    window.onclick = function(event) {

        if (event.target.className.includes("modal")) {

            event.target.style.display = "none";

        }

    }
    function updateDateTime() {
    var now = new Date();
    var datetimeString = now.toLocaleDateString('fr-FR', {
        weekday: 'long', // "lundi"
        year: 'numeric', // 2024
        month: 'long', // "mars"
        day: 'numeric', // 18
        hour: '2-digit', // 2 chiffres pour l'heure
        minute: '2-digit', // 2 chiffres pour les minutes
        second: '2-digit' // 2 chiffres pour les secondes
    });

    document.getElementById('datetime').innerText = datetimeString;
}

// Mise à jour initiale au chargement de la page
updateDateTime();

// Mise à jour de l'heure/date chaque seconde
setInterval(updateDateTime, 1000);
</script>



<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

</body>

</html>



