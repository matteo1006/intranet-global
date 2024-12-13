<?php
session_start();
require_once "db_config.php";

$serviceFilter = $_GET['serviceFilter'] ?? null;

$whereConditions = 'WHERE notes.archived = 1'; // Montrer seulement les notes archivées

if ($serviceFilter) {
    $whereConditions .= ' AND services.id = :serviceFilter';
}

$query = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name
          FROM notes 
          LEFT JOIN sites ON notes.site_id = sites.id 
          LEFT JOIN lignes ON notes.ligne_id = lignes.id
          LEFT JOIN services ON notes.service_id = services.id
          $whereConditions 
          ORDER BY notes.id DESC";

if ($serviceFilter) {
    $stmtNotes = $pdo->prepare($query);
    $stmtNotes->execute([':serviceFilter' => $serviceFilter]);
} else {
    $stmtNotes = $pdo->query($query); 
}

$notes = $stmtNotes->fetchAll();

$stmtServices = $pdo->prepare("SELECT services.*, sites.name as site_name FROM services JOIN sites ON services.site_id = sites.id ORDER BY services.name ASC");
$stmtServices->execute();
$services = $stmtServices->fetchAll();

// Requête pour récupérer les opérateurs
$stmtOperators = $pdo->prepare("SELECT DISTINCT operator_name FROM notes WHERE archived = 1 ORDER BY operator_name ASC");
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);

// Requête pour récupérer les sites
$stmtSites = $pdo->prepare("SELECT DISTINCT sites.name FROM sites JOIN notes ON sites.id = notes.site_id WHERE notes.archived = 1 ORDER BY sites.name ASC");
$stmtSites->execute();
$sites = $stmtSites->fetchAll(PDO::FETCH_ASSOC);

// Conversion des données en format JSON
$operators = json_encode(array_column($operators, 'operator_name'));
$services = json_encode(array_column($services, 'name'));
$sites = json_encode(array_column($sites, 'name'));

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archives des Notes</title>
    <link rel="stylesheet" href="styles.css">
    <style>
/* Conteneur principal avec animation au survol */
.container {
    max-width: 1200px;
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

/* Table de données */
.table {
    width: 100%;
    border-collapse: collapse;
    margin-bottom: 20px;
}

.table th, .table td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
    text-align: left;
    color: #333;
}

.table th {
    color: white;
    text-transform: uppercase;
    font-weight: bold;
    background-color: #34495e; /* Couleur Bleu Titre du tableau */

}

.table tr {
    transition: background-color 0.3s ease;

}

.table tr:hover {
    background-color: rgba(0, 123, 355, 0.1);
}

/* Style des boutons */
.btn {
    padding: 10px 20px;
    background-color: #34495e;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.btn:hover {
    background-color: #2c3e50;
}

/* Style des modales */
.modal-content {
    border-radius: 10px;
    padding: 20px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
}

.modal-header {
    background-color: #1E90FF;
    color: white;
    border-radius: 10px 10px 0 0;
    padding: 15px;
}

.modal-footer {
    text-align: right;
}

/* Ajustement de la section de filtre pour être en deux colonnes */
.filter-section {
    display: grid;
    grid-template-columns: repeat(2, 1fr); /* Deux colonnes */
    gap: 10px;
    margin-bottom: 20px;
    background-color: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.filter-section .form-group {
    display: flex;
    flex-direction: column;    }

label {
    font-weight: bold;
}

.form-control {
    width: 100%;
    padding: 8px;
    border: 2px solid #007BFF;
    border-radius: 4px;
}

@media (max-width: 768px) {
.filter-section .form-group {
    flex: 1 1 100%; /* Passe en une colonne sur les petits écrans */
}
}


/* Style de la pagination */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    background-color: #34495e; /* Couleur de fond */
    color: white; /* Couleur du texte */
    border: 1px solid #34495e;
    padding: 5px 10px;
    margin: 2px;
    border-radius: 4px;
    cursor: pointer;
    
}


.dataTables_wrapper .dataTables_paginate {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
}

.dataTables_wrapper .dataTables_paginate .pagination-center {
    display: flex;
    justify-content: center;
    flex-grow: 1;
}

/* Style des boutons "Précédent" et "Suivant" */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    background-color: #34495e;
    color: white;
    border: 1px solid #34495e;
    padding: 5px 10px;
    margin: 2px;
    border-radius: 4px;
    cursor: pointer;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: grey; /* Couleur de fond pour la page active */
    color: white; /* Couleur du texte */
    border: none; /* Enlever la bordure */
    border-radius: 4px; /* Bordure arrondie pour un effet visuel */
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #2c3e50; /* Couleur au survol */
}

/* Style de l'affichage des entrées */
.dataTables_wrapper .dataTables_length select {
    background-color: #f0f0f0; /* Couleur de fond */
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 4px;
}

/* Style de la barre de recherche */
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #ccc;
    padding: 5px;
    border-radius: 4px;
    width: 200px;
    background-color: #f0f0f0; /* Couleur de fond */
}

/* Style de l'affichage d'informations sur les entrées */
.dataTables_wrapper .dataTables_info {
    color: white;
}
.dataTables_wrapper .dataTables_paginate, 
.dataTables_wrapper .dataTables_info {
    text-align: center; /* Centrer la pagination */
    margin-top: 10px; /* Ajouter un petit espace en haut */
}

.dataTables_wrapper .dataTables_length, 
.dataTables_wrapper .dataTables_filter {
    text-align: center; /* Centrer les filtres et la longueur */
    margin-bottom: 10px; /* Ajouter un petit espace en bas */
}


/* Mise à jour pour le responsive */

/* Mobile */
@media (max-width: 600px) {
body {
    padding: 10px;
}

.container {
    padding: 15px;
}

.table th, .table td {
    padding: 8px;
    font-size: 12px;
}

.btn {
    padding: 8px 12px;
    font-size: 12px;
}

/* Aligner les champs de filtres sur une seule colonne */
.filter-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

/* Masquer certaines colonnes pour rendre la table plus lisible */
.table td:nth-child(4), /* Masque la colonne "Service" */
.table td:nth-child(5) { /* Masque la colonne "Créée le" */
    display: none;
}
}

/* Tablettes */
@media (max-width: 768px) {
.container {
    padding: 20px;
}

.table th, .table td {
    padding: 10px;
    font-size: 14px;
}

.btn {
    padding: 10px 16px;
    font-size: 14px;
}

/* Ajustement des filtres pour tablettes */
.filter-section {
    display: grid;
    grid-template-columns: 1fr; /* Une colonne */
    gap: 15px;
}
}

/* Ordinateurs */
@media (min-width: 769px) {
.filter-section {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Trois colonnes */
    gap: 20px;
}
}


.suggestions-list {
position: absolute;
background-color: #fff;
border-radius: 4px;
max-height: 150px;
overflow-y: auto;
z-index: 1000;
margin-top: 5px; 
margin-left: 9%;



}

.suggestions-list div {
    padding: 8px;
    cursor: pointer;
    
}

.suggestions-list div:hover {
    background-color: #f0f0f0;
}

#filterButton {
background-color: #007bff;
color: white;
padding: 10px 20px;
border: none;
border-radius: 4px;
cursor: pointer;
margin-top: 10px;
}

#filterButton:hover {
    background-color: #0056b3;
}

.form-row {
display: flex;
justify-content: space-between;
gap: 20px; /* Espace entre les champs */
margin-bottom: 20px;
}

.form-group {
    flex: 1;
}

.form-group label {
    font-weight: bold;
    margin-bottom: 5px;
}

.form-group input {
    width: 100%;
    padding: 8px;
    border: 2px solid #007BFF;
    border-radius: 4px;
}

#resetButton {
background-color: #f44336;
color: white;
padding: 10px 20px;
border: none;
border-radius: 4px;
cursor: pointer;
margin-top: 18px;

}

#resetButton:hover {
    background-color: #d32f2f;
}

.dataTables_filter {
    display: none; /* Masque la barre de recherche */
}

.dataTables_length {
    display: none; /* Masque le sélecteur du nombre d'entrées */
}

@media only screen and (min-width: 1080px) {
    .container {
        margin-left: 20%; /* Le menu est caché sur les tablettes et téléphones */
    }
}
</style>
</head>
<body>
<?php include 'menu.php'; ?>
    <div class="container" style="">

    <center><h2>Notes Archivées</h2></center>


    <table id="notesTable" class="table">
        <thead>
            <tr>
                <th>Note</th>
                <th>Site</th>
                <th>Ligne</th>
                <th>Service</th>
                <th>Operateur</th>
                <th>Créée le</th>
                <th>Archivée le</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notes as $note): ?>
                <tr>
                    <td><?= htmlspecialchars($note['note_text'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($note['site_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($note['ligne_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($note['service_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars($note['operator_name'], ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($note['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y H:i', strtotime($note['comment_time'])), ENT_QUOTES, 'UTF-8'); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

$(document).ready(function() {
    $.fn.dataTable.ext.errMode = 'none';

    var table = $('#notesTable').DataTable({
        "order": [[5, "desc"]],
        "searching": true,
        "paging": true,
        "info": true,
        "language": {
            "lengthMenu": "Afficher _MENU_ entrées",
            "zeroRecords": "Aucun enregistrement trouvé",
            "info": "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
            "infoEmpty": "Affichage de 0 à 0 sur 0 entrées",
            "infoFiltered": "(filtré de _MAX_ entrées au total)",
            "search": "Rechercher :",
            "paginate": {
                "previous": "Précédent",
                "next": "Suivant"
            }
        },
        "drawCallback": function(settings) {
            var pagination = $(this).closest('.dataTables_wrapper').find('.dataTables_paginate');

            // Centrer la pagination
            var paginationNumbers = pagination.find('.pagination-center');
            if (paginationNumbers.length === 0) {
                paginationNumbers = $('<div class="pagination-center"></div>');
                pagination.find('ul').wrap(paginationNumbers);
            }

            // Déplacer les boutons précédent et suivant
            var prevButton = pagination.find('.paginate_button.previous');
            var nextButton = pagination.find('.paginate_button.next');
            prevButton.detach().prependTo(pagination);
            nextButton.detach().appendTo(pagination);
        }
    });

    // Ajout de l'écouteur d'événements pour défiler vers le haut à chaque changement de page
    $('#notesTable').on('page.dt', function() {
        $('html, body').animate({
            scrollTop: $('#notesTable').offset().top
        }, 500);
    });
});


$(document).ready(function() {
    var table = $('#notesTable').DataTable({
        "order": [[5, "desc"]],
        "paging": true, 
        "searching": true
        
        
    });

    // Variables des données JSON
    var operators = <?php echo $operators; ?>;
    var services = <?php echo $services; ?>;
    var sites = <?php echo $sites; ?>;

    function setupSuggestions(input, suggestionsBox, data, columnNumber) {
        input.addEventListener("input", function() {
            var query = input.value.toLowerCase();
            suggestionsBox.innerHTML = "";

            if (query.length > 0) {
                var filteredData = data.filter(function(item) {
                    return item.toLowerCase().startsWith(query);
                });

                filteredData.forEach(function(item) {
                    var suggestion = document.createElement("div");
                    suggestion.textContent = item;
                    suggestion.addEventListener("click", function() {
                        input.value = item;
                        suggestionsBox.innerHTML = "";
                        filterTable(columnNumber, item);
                    });
                    suggestionsBox.appendChild(suggestion);
                });
            }
        });

        document.addEventListener("click", function(event) {
            if (!input.contains(event.target) && !suggestionsBox.contains(event.target)) {
                suggestionsBox.innerHTML = "";
            }
        });
    }

    function filterTable(columnNumber, value) {
        table.column(columnNumber).search(value).draw();
    }

    // Initialisation des suggestions
    setupSuggestions(document.getElementById("filterOperator"), document.getElementById("operatorSuggestions"), operators, 4);
    setupSuggestions(document.getElementById("filterService"), document.getElementById("serviceSuggestions"), services, 3);
    setupSuggestions(document.getElementById("filterSite"), document.getElementById("siteSuggestions"), sites, 1);

    // Bouton de réinitialisation
    document.getElementById("resetButton").addEventListener("click", function() {
        $('#filterOperator').val('');
        $('#filterService').val('');
        $('#filterSite').val('');
        table.search('').columns().search('').draw();
    });
});
</script>
</body>
</html>
