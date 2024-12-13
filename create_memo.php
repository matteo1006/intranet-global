<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
ob_start();
require_once "db_config.php";
include "menu.php";
$sansMargin = true;

// Récupération des lignes pour le select
$query = "SELECT id, name FROM lignes";
$stmt = $pdo->prepare($query);
$stmt->execute();
$lignes = $stmt->fetchAll();
$ligneSelected = isset($_GET['ligne_id']) ? $_GET['ligne_id'] : null;

// Récupération des postes et opérateurs depuis la base de données
$stmtPostes = $pdo->prepare("SELECT * FROM postes");
$stmtPostes->execute();
$postes = $stmtPostes->fetchAll(PDO::FETCH_ASSOC);

$stmtOperators = $pdo->prepare("SELECT * FROM operators");
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $memoText = $_POST['memo_text'];
    $ligneId = $_POST['ligne_id'];
    $posteId = $_POST['poste_id'];
    $operatorId = $_POST['operator_id'];
    $image_paths = [];

    // Types de fichiers autorisés pour les images
    $allowed_file_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/heic', 'image/BMP', 'image/heif'];

    // Boucle pour chaque fichier téléchargé
    for ($i = 0; $i < count($_FILES['memo_image']['name']); $i++) {
        if ($_FILES['memo_image']['error'][$i] == 0 && in_array($_FILES['memo_image']['type'][$i], $allowed_file_types)) {
            $new_filename = uniqid() . '_' . basename($_FILES["memo_image"]["name"][$i]);
            $target_dir = "uploads/";
            $image_path = $target_dir . $new_filename;

            // Déplacement du fichier téléchargé
            if (move_uploaded_file($_FILES["memo_image"]["tmp_name"][$i], $image_path)) {
                $image_paths[] = $image_path;
            } else {
                die('Erreur lors du téléchargement du fichier.');
            }
        }
    }

    // Concaténation des chemins d'images en une seule chaîne
    $image_path_string = implode(',', $image_paths);

    // Insertion dans la base de données
    $query = "INSERT INTO memos (memo_text, ligne_id, poste_id, operator_id, image_path) VALUES (?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$memoText, $ligneId, $posteId, $operatorId, $image_path_string]);

    header("Location: dashboardlignes.php?ligne_id=" . $ligneId);
    exit;
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une Relève</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <style>
    /* Conteneur principal avec bordure arrondie et ombre */
.container {
    max-width: 1200px;
    margin: 50px auto;
    padding: 20px;
    background-color: #f9f9f9;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 15px;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

/* Animation au survol */
.container:hover {
    transform: translateY(-5px); /* Légère élévation */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Accentuation de l'ombre */
}

/* Style des titres */
h4 {
    font-size: 1.3rem;
    color: #34495e;
    margin-bottom: 10px;
}

/* Champs du formulaire */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
    display: block;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    border-color: #2980b9; /* Couleur bleue au focus */
}

/* Boutons */
button {
    padding: 12px 20px;
    background-color: #2980b9;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    cursor: pointer;
    transition: background-color 0.3s, transform 0.3s;
}

button:hover {
    background-color: #3498db;
    transform: translateY(-3px);
}

/* Image input style */
.form-group input[type="file"] {
    padding: 5px;
    font-size: 1rem;
    cursor: pointer;
}

/* Alignement horizontal des boutons */
.file-upload-group {
    display: flex;
    align-items: center;
    gap: 15px;
}

/* Espace entre les sections */
textarea {
    resize: vertical;
}

/* Style général pour un look moderne */
body {
    font-family: 'Arial', sans-serif;
    background-color: #eef2f7;
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
    margin-left: 5%;

}

.no-cursor {
    user-select: none; /* Désactive la sélection du texte */
    pointer-events: none; /* Désactive toute interaction avec la souris */
    border-right: none; /* Retire le curseur clignotant */
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

.header-content{
    display: flex;
    justify-content: space-between;
    padding-bottom: 5%;
}

.back-icon{
  font-size: 40px;
}
.back-icon:hover{
    cursor: pointer;
}


/* Style pour le bouton "Sélectionner un fichier" */
.file-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 1rem;


}

.file-button {
    padding: 10px 20px;
    font-size: 1rem;
    background-color:  #28a745;
    color: white;
    border-radius: 8px;
    transition: background-color 0.3s, transform 0.3s;
    cursor: pointer;
    height: 100%;

}

.file-button:hover {
    background-color: #0056b3;
    transform: translateY(-3px);
}

  /* Style pour aligner chaque bouton et aperçu en ligne */
  .photo-container {
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    

    /* Style pour les aperçus d'image */
    .image-preview img {
        max-width: 50px;
        max-height: 50px;
        object-fit: contain;
        align-items: center;
    }

        /* Centrer les images dans leurs conteneurs */
        .image-preview {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    /* Style pour la première image */
    #preview-container1 img {
        max-width: 300px;
        max-height: 100px;
        object-fit: contain;
        margin-top: 5%;
    }

    /* Style pour la deuxième image */
    #preview-container2 img {
        max-width: 300px;
        max-height: 100px;
        object-fit: contain;
        margin-top: 5%;
    }

    /* Style pour la troisième image */
    #preview-container3 img {
        max-width: 300px;
        max-height: 100px;
        object-fit: contain;
        margin-top: 5%;
    }

    
/* Masquer le menu pour les écrans de taille inférieure à 1280px (taille des tablettes) */
@media only screen and (max-width: 1280px) {
    #menu1 {
        display: none; /* Le menu est caché sur les tablettes et téléphones */
    }

    .container{
        width: 250%;
    }

    .form-group{
        margin-top: -3%;
    }

    h4{
        margin-top: -2%;
    }

    button{
        margin-top: -8%;

    }

    #btnResp{
        margin-top: 2%;

    }
}


</style>

<script>
$(document).ready(function () {
            // Lorsque le choix de poste change
            $("#poste").change(function () {
                // Récupérer la valeur du poste sélectionné
                var selectedPoste = $(this).val();

                // Mettre à jour la liste des opérateurs en fonction du poste
                updateOperatorList(selectedPoste);
            });

            function updateOperatorList(selectedPoste) {
                // Réinitialiser la liste des opérateurs
                $("#operateur").empty().prop("disabled", true);

                // Ajouter une option par défaut
                $("#operateur").append('<option value="">Sélectionnez un opérateur</option>');

                // Si aucun poste n'est sélectionné, arrêter ici
                if (!selectedPoste) {
                    return;
                }

                // Récupérer la liste des opérateurs associés au poste via Ajax
                $.ajax({
                    url: 'get_operators.php', // Créez ce fichier pour récupérer les opérateurs en fonction du poste
                    type: 'POST',
                    data: {
                        poste: selectedPoste,
                        site_id: '<?= $site['id']; ?>' // Assurez-vous que cette variable PHP est correctement définie et accessible
                            },
                    dataType: 'json',
                    success: function (data) {
                        // Ajouter les opérateurs à la liste
                        data.forEach(function (operator) {
                            $("#operateur").append('<option value="' + operator.id + '">' + operator.name + '</option>');
                        });

                        // Activer la liste des opérateurs
                        $("#operateur").prop("disabled", false);
                    },
                    error: function (error) {
                        console.log('Erreur lors de la récupération des opérateurs:', error);
                    }
                });
            }
        });


 
</script>
</head>
<body class="<?php echo $sansMargin ? 'sansMargin' : ''; ?>">
<div class="container">
    <div class="header-content">
        <div class="back-icon"><i class="fa fa-angle-left" onclick="window.history.back()" aria-hidden="true"></i></div>     
        <h2 id="title" style="padding-bottom: 0%">Créer une Relève</h2>
    </div>
    <form action="create_memo.php" method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="ligne_id"><h4>Ligne :</h4></label>
            <select name="ligne_id" disabled>
                <?php foreach ($lignes as $ligne): ?>
                    <option value="<?= $ligne['id']; ?>" <?= $ligne['id'] == $ligneSelected ? 'selected' : ''; ?>>
                        <?= $ligne['name']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="hidden" name="ligne_id" value="<?= $ligneSelected; ?>">
        </div>
        <div class="form-row" style="margin-left: 25%">
            <div class="form-group">
                <label style="margin-left: 20%" for="poste_id"><h4>Poste:</h4></label>
                <select name="poste_id" required>
                    <option value="" selected disabled>Sélectionnez un poste</option>
                    <?php foreach ($postes as $poste): ?>
                        <option value="<?= $poste['id']; ?>"><?= $poste['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label style="margin-left: 100%" for="operator_input"><h4>Opérateur:</h4></label>
                <input type="text" id="operator_input" name="operator_name" placeholder="Saisissez l'opérateur" style="margin-left: 80%">
                <input type="hidden" id="operator_id" name="operator_id">
            </div>
        </div>
        <center><label for="memo_text" id="TextResponsive"><h4>Texte de la relève:</h4></label></center>
        <textarea name="memo_text" id="memo_text" rows="5" placeholder="Contenu de la relève" required></textarea>

        <!-- Gestion des images avec 3 champs d'upload et aperçu -->
        <div class="form-group file-upload-group" style="display: flex; align-items: center; justify-content: center; margin-bottom: 3%;     margin-top: 2%">
            <div class="photo-container" style="text-align: center;">
                <label class="file-label" for="file-upload1">
                    <input type="file" name="memo_image[]" id="file-upload1" accept="image/*" style="display: none;">
                    <span class="file-button">Sélectionner une photo</span>
                </label>
                <div id="preview-container1" class="image-preview"></div>
            </div>
            <div class="photo-container" id="second-photo-container" style="display: none; text-align: center;">
                <label class="file-label" for="file-upload2">
                    <input type="file" name="memo_image[]" id="file-upload2" accept="image/*" style="display: none;">
                    <span class="file-button">Sélectionner une deuxième photo</span>
                </label>
                <div id="preview-container2" class="image-preview"></div>
            </div>
            <div class="photo-container" id="third-photo-container" style="display: none; text-align: center;">
                <label class="file-label" for="file-upload3">
                    <input type="file" name="memo_image[]" id="file-upload3" accept="image/*" style="display: none;">
                    <span class="file-button">Sélectionner une troisième photo</span>
                </label>
                <div id="preview-container3" class="image-preview"></div>
            </div>

                 <!-- Modale pour l'affichage en plein écran -->
    <div id="image-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.8); align-items: center; justify-content: center;">
        <span id="close-modal" style="position: absolute; top: 10px; right: 20px; font-size: 30px; color: white; cursor: pointer;">&times;</span>
        <img id="modal-image" src="" style="max-width: 90%; max-height: 90%;">
    </div>
        </div>
        
   

        <center><button type="submit" style="width: 50%;"><h4 style="color: white; " id="btnResp">Créer</h4></button></center>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

<script>

$(document).ready(function() {
    var operators = [
        <?php foreach ($operators as $operator): ?>,
        {
            label: "<?= $operator['name']; ?>",
            value: <?= $operator['id']; ?>
        },
        <?php endforeach; ?>
    ];

    $("#operator_input").autocomplete({
        source: operators,
        select: function(event, ui) {
            $("#operator_input").val(ui.item.label);  // Mettre le nom de l'opérateur sélectionné dans l'input
            $("#operator_id").val(ui.item.value);     // Mettre l'ID de l'opérateur dans l'input caché
            return false;
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const titleElement = document.getElementById('title');

    // Ajouter un écouteur d'événement pour la fin de l'animation de saisie
    titleElement.addEventListener('animationend', function() {
        // Ajouter la classe pour désactiver le curseur après l'animation
        titleElement.classList.add('no-cursor');
    });
});
document.getElementById("file-upload1").addEventListener("change", function () {
    const previewContainer = document.getElementById("preview-container1");
    previewContainer.innerHTML = ""; 
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            previewContainer.appendChild(imgElement);
        };
        reader.readAsDataURL(file);
    });
    document.getElementById("second-photo-container").style.display = "block";
});

document.getElementById("file-upload2").addEventListener("change", function () {
    const previewContainer = document.getElementById("preview-container2");
    previewContainer.innerHTML = ""; 
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            previewContainer.appendChild(imgElement);
        };
        reader.readAsDataURL(file);
    });
    document.getElementById("third-photo-container").style.display = "block";
});

document.getElementById("file-upload3").addEventListener("change", function () {
    const previewContainer = document.getElementById("preview-container3");
    previewContainer.innerHTML = ""; 
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            previewContainer.appendChild(imgElement);
        };
        reader.readAsDataURL(file);
    });
});

// Fonction pour ouvrir la modale avec l'image agrandie
function openModal(src) {
    const modal = document.getElementById("image-modal");
    const modalImage = document.getElementById("modal-image");
    modalImage.src = src;
    modal.style.display = "flex";
}

// Fonction pour fermer la modale
document.getElementById("close-modal").addEventListener("click", function () {
    document.getElementById("image-modal").style.display = "none";
});

document.getElementById("file-upload1").addEventListener("change", function () {
    const previewContainer = document.getElementById("preview-container1");
    previewContainer.innerHTML = ""; 
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            imgElement.onclick = () => openModal(event.target.result); // Ouvre la modale lors du clic sur l'image
            previewContainer.appendChild(imgElement);
        };
        reader.readAsDataURL(file);
    });
    document.getElementById("second-photo-container").style.display = "block";
});

document.getElementById("file-upload2").addEventListener("change", function () {
    const previewContainer = document.getElementById("preview-container2");
    previewContainer.innerHTML = ""; 
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            imgElement.onclick = () => openModal(event.target.result); // Ouvre la modale lors du clic sur l'image
            previewContainer.appendChild(imgElement);
        };
        reader.readAsDataURL(file);
    });
    document.getElementById("third-photo-container").style.display = "block";
});

document.getElementById("file-upload3").addEventListener("change", function () {
    const previewContainer = document.getElementById("preview-container3");
    previewContainer.innerHTML = ""; 
    Array.from(this.files).forEach(file => {
        const reader = new FileReader();
        reader.onload = function (event) {
            const imgElement = document.createElement("img");
            imgElement.src = event.target.result;
            imgElement.onclick = () => openModal(event.target.result); // Ouvre la modale lors du clic sur l'image
            previewContainer.appendChild(imgElement);
        };
        reader.readAsDataURL(file);
    });
});


</script>
</body>
</html>
