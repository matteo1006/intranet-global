<?php
session_start();
require_once "db_config.php";

// Récupération de toutes les notes, sans appliquer de filtre par défaut
$query = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name
          FROM notes 
          LEFT JOIN sites ON notes.site_id = sites.id 
          LEFT JOIN lignes ON notes.ligne_id = lignes.id
          LEFT JOIN services ON notes.service_id = services.id
          ORDER BY notes.created_at DESC"; // Trier par date de création

$stmtNotes = $pdo->prepare($query);
$stmtNotes->execute();
$notes = $stmtNotes->fetchAll();

// Récupérer les lignes, services, opérateurs et sites pour les filtres
$stmtLignes = $pdo->prepare("SELECT * FROM lignes ORDER BY name ASC");
$stmtLignes->execute();
$lignes = $stmtLignes->fetchAll();

$stmtServices = $pdo->prepare("SELECT services.*, sites.name as site_name FROM services JOIN sites ON services.site_id = sites.id ORDER BY services.name ASC");
$stmtServices->execute();
$services = $stmtServices->fetchAll();

$queryOperators = "SELECT DISTINCT name FROM operators ORDER BY name ASC";
$stmtOperators = $pdo->prepare($queryOperators);
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);

$querySites = "SELECT DISTINCT name FROM sites ORDER BY name ASC";
$stmtSites = $pdo->prepare($querySites);
$stmtSites->execute();
$sites = $stmtSites->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<style>
    /* Styles pour la page */
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

    .container:hover {
        transform: translateY(-10px);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
    }

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
        background-color: #34495e;
        color: white;
        text-transform: uppercase;
        font-weight: bold;
    }

    .table tr:hover {
        background-color: rgba(0, 123, 355, 0.1);
    }

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

    .form-control {
        width: 100%;
        padding: 8px;
        border: 2px solid #007BFF;
        border-radius: 4px;
        position: relative; /* S'assurer que le champ est bien positionné */
        z-index: 1; /* S'assurer que le champ de saisie reste cliquable */
    }

    .suggestions-list {
        position: absolute;
        background-color: #fff;
        border-radius: 4px;
        max-height: 150px;
        overflow-y: auto;
        z-index: 1000;
        width: 80%;


    }
    .suggestions-list div {
        padding: 8px;
        cursor: pointer;
        border: none;
    }
    .suggestions-list div:hover {
        background-color: #f0f0f0;
    }
    .form-group {
        position: relative; /* S'assurer que les suggestions s'affichent correctement */
    }

    .mt-4 {
        padding-bottom: 5%;
    }

    #resetButton {
    background-color: #f44336;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    margin-top: 45px;
    
    }

    #resetButton:hover {
        background-color: #d32f2f;
    }

    /* Masquer le menu pour les écrans de taille inférieure à 1024px (taille des tablettes) */
@media only screen and (min-width: 1080px) {
    .container {
        margin-left: 20%; /* Le menu est caché sur les tablettes et téléphones */
    }
}
</style>

<?php include 'menu.php' ?>
<div class="container">



    <center><h2 class="mt-4">Toutes les Notes </h2></center>

    <!-- Filtres de recherche -->
    <div class="form-row">
        <div class="form-group" style="position: relative;">
            <input type="text" class="form-control" id="filterOperator" style="margin-left: 58%; width: 80%" placeholder="Saisir un opérateur">
            <div id="operatorSuggestions" class="suggestions-list" style ="margin-left: 60%"></div>
        </div>

        <div class="form-group" style="position: relative;">
            <input type="text" class="form-control" id="filterService" style="margin-left: 118%; width: 80%" placeholder="Saisir un service">
            <div id="serviceSuggestions" class="suggestions-list" style ="margin-left: 125%"></div>
        </div>

        <div class="form-group" style="position: relative;">
            <input type="text" class="form-control" id="filterSite" style="margin-left: 178%; width: 80%" placeholder="Saisir un site">
            <div id="siteSuggestions" class="suggestions-list" style ="margin-left: 190%"></div>
        </div>
    </div>
    <center> <button id="resetButton" class="btn" style="width: 20%">Reset les filtres</button></center>


    <table class="table table-bordered table-striped" id="notesTable">
        <thead>
            <tr>
                <th>Note</th>
                <th>Site</th>
                <th>Operateur</th>
                <th>Service</th>
                <th>Créée le</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($notes as $note): ?>
                <tr>
                    <td><?= $note['note_text']; ?></td>
                    <td><?= $note['site_name']; ?></td>
                    <td><?= $note['operator_name']; ?></td>
                    <td><?= $note['service_name']; ?></td>
                    <td><?= htmlspecialchars(date('d/m/Y', strtotime($note['created_at'])), ENT_QUOTES, 'UTF-8'); ?></td>
                    <td>
                        <a href="details.php?id=<?= $note['id']; ?>" class="btn btn-primary btn-sm">Voir détails</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (isset($_SESSION['message'])): ?>
    <div class="alert alert-success">
        <?= $_SESSION['message']; unset($_SESSION['message']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-danger">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
      document.addEventListener("DOMContentLoaded", function() {
        // Utilisation des opérateurs récupérés depuis la base de données
        var operators = <?php echo json_encode(array_map(function($operator) {
            return htmlspecialchars($operator['name']);
        }, $operators)); ?>;

        var input = document.getElementById("filterOperator");
        var suggestionsBox = document.getElementById("operatorSuggestions");

        input.addEventListener("input", function() {
            var query = input.value.toLowerCase();
            suggestionsBox.innerHTML = ""; // Vider les suggestions précédentes

            if (query.length > 0) {
                // Filtrer les opérateurs en fonction de l'entrée et éviter les doublons
                var filteredOperators = operators.filter(function(operator) {
                    return operator.toLowerCase().startsWith(query);
                });

                // Éviter les doublons
                filteredOperators = [...new Set(filteredOperators)];

                if (filteredOperators.length > 0) {
                    filteredOperators.forEach(function(operator) {
                        var suggestion = document.createElement("div");
                        suggestion.textContent = operator;
                        suggestion.addEventListener("click", function() {
                            input.value = operator; // Mettre à jour l'input avec la suggestion choisie
                            suggestionsBox.innerHTML = ""; // Vider les suggestions après sélection
                            filterTableByOperator(operator); // Filtrer le tableau
                        });
                        suggestionsBox.appendChild(suggestion);
                    });
                }
            }
        });

        // Fonction pour filtrer le tableau par opérateur
        function filterTableByOperator(operator) {
            var rows = document.querySelectorAll("#notesTable tbody tr");
            rows.forEach(function(row) {
                var operatorCell = row.querySelector("td:nth-child(3)"); // La colonne des opérateurs
                if (operatorCell.textContent.includes(operator)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Cacher la liste des suggestions lorsque l'utilisateur clique ailleurs
        document.addEventListener("click", function(event) {
            if (!input.contains(event.target) && !suggestionsBox.contains(event.target)) {
                suggestionsBox.innerHTML = ""; // Vider les suggestions
            }
        });

        var table = $('#notesTable').DataTable();

        // Appliquer les filtres dès que l'utilisateur tape dans l'un des champs
        $('#filterService, #filterSite, #dateStart, #dateEnd').on('change', function () {
            table.draw();
        });

        // Fonction pour filtrer les données du tableau DataTable
        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var service = $('#filterService').val().toLowerCase();
            var site = $('#filterSite').val().toLowerCase();
            var operator = $('#filterOperator').val().toLowerCase();
            var startDate = $('#dateStart').val();
            var endDate = $('#dateEnd').val();
            var rowService = data[3].toLowerCase();
            var rowSite = data[1].toLowerCase();
            var rowOperator = data[2].toLowerCase();
            var rowDate = new Date(data[4].split('/').reverse().join('-')); // Convertir la date en format JS

            var matchService = !service || rowService.includes(service);
            var matchSite = !site || rowSite.includes(site);
            var matchOperator = !operator || rowOperator.includes(operator);

            var matchDate = true;
            if (startDate && endDate) {
                var start = new Date(startDate);
                var end = new Date(endDate);
                matchDate = rowDate >= start && rowDate <= end;
            }

            return matchService && matchSite && matchOperator && matchDate;
        });

        // Appliquer les filtres automatiquement à la saisie
        input.addEventListener('input', function() {
            table.draw();
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Utilisation des services récupérés depuis la base de données
        var services = <?php echo json_encode(array_map(function($service) {
            return htmlspecialchars($service['name']);
        }, $services)); ?>;

        var serviceInput = document.getElementById("filterService");
        var serviceSuggestionsBox = document.getElementById("serviceSuggestions");

        serviceInput.addEventListener("input", function() {
            var query = serviceInput.value.toLowerCase();
            serviceSuggestionsBox.innerHTML = ""; // Vider les suggestions précédentes

            if (query.length > 0) {
                // Filtrer les services en fonction de l'entrée et éviter les doublons
                var filteredServices = services.filter(function(service) {
                    return service.toLowerCase().startsWith(query);
                });

                // Éviter les doublons
                filteredServices = [...new Set(filteredServices)];

                if (filteredServices.length > 0) {
                    filteredServices.forEach(function(service) {
                        var suggestion = document.createElement("div");
                        suggestion.textContent = service;
                        suggestion.addEventListener("click", function() {
                            serviceInput.value = service; // Mettre à jour l'input avec la suggestion choisie
                            serviceSuggestionsBox.innerHTML = ""; // Vider les suggestions après sélection
                            filterTableByService(service); // Filtrer le tableau par service
                        });
                        serviceSuggestionsBox.appendChild(suggestion);
                    });
                }
            }
        });

        // Fonction pour filtrer le tableau par service
        function filterTableByService(service) {
            var rows = document.querySelectorAll("#notesTable tbody tr");
            rows.forEach(function(row) {
                var serviceCell = row.querySelector("td:nth-child(4)"); // La colonne des services
                if (serviceCell.textContent.includes(service)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Cacher la liste des suggestions lorsque l'utilisateur clique ailleurs
        document.addEventListener("click", function(event) {
            if (!serviceInput.contains(event.target) && !serviceSuggestionsBox.contains(event.target)) {
                serviceSuggestionsBox.innerHTML = ""; // Vider les suggestions
            }
        });
    });

    $(document).ready(function() {
        var table = $('#notesTable').DataTable({
            "order": [[4, "desc"]]  // Colonne 4 correspond à la colonne "Créée le", triée par défaut du plus récent au plus ancien
        });

        $('#filterService, #filterSite, #dateStart, #dateEnd, #filterOperator').on('input change', function () {
            table.draw();
        });

        $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
            var service = $('#filterService').val().toLowerCase();
            var site = $('#filterSite').val().toLowerCase();
            var operator = $('#filterOperator').val().toLowerCase();
            var startDate = $('#dateStart').val();
            var endDate = $('#dateEnd').val();
            var rowService = data[3].toLowerCase();
            var rowSite = data[1].toLowerCase();
            var rowOperator = data[2].toLowerCase();
            var rowDate = new Date(data[4].split('/').reverse().join('-')); // Convertir la date en format JS

            var matchService = !service || rowService.includes(service);
            var matchSite = !site || rowSite.includes(site);
            var matchOperator = !operator || rowOperator.includes(operator);

            var matchDate = true;
            if (startDate && endDate) {
                var start = new Date(startDate);
                var end = new Date(endDate);
                matchDate = rowDate >= start && rowDate <= end;
            }

            return matchService && matchSite && matchOperator && matchDate;
        });

        $('#filterOperator').on('input', function () {
            table.draw();
        });
    });

    document.addEventListener("DOMContentLoaded", function() {
        // Utilisation des sites récupérés depuis la base de données
        var sites = <?php echo json_encode(array_map(function($site) {
            return htmlspecialchars($site['name']);
        }, $sites)); ?>;

        var siteInput = document.getElementById("filterSite");
        var siteSuggestionsBox = document.getElementById("siteSuggestions");

        siteInput.addEventListener("input", function() {
            var query = siteInput.value.toLowerCase();
            siteSuggestionsBox.innerHTML = ""; // Vider les suggestions précédentes

            if (query.length > 0) {
                // Filtrer les sites en fonction de l'entrée et éviter les doublons
                var filteredSites = sites.filter(function(site) {
                    return site.toLowerCase().startsWith(query);
                });

                if (filteredSites.length > 0) {
                    filteredSites.forEach(function(site) {
                        var suggestion = document.createElement("div");
                        suggestion.textContent = site;
                        suggestion.addEventListener("click", function() {
                            siteInput.value = site; // Mettre à jour l'input avec la suggestion choisie
                            siteSuggestionsBox.innerHTML = ""; // Vider les suggestions après sélection
                            filterTableBySite(site); // Filtrer le tableau en fonction du site
                        });
                        siteSuggestionsBox.appendChild(suggestion);
                    });
                }
            }
        });

        // Fonction pour filtrer le tableau par site
        function filterTableBySite(site) {
            var rows = document.querySelectorAll("#notesTable tbody tr");
            rows.forEach(function(row) {
                var siteCell = row.querySelector("td:nth-child(2)"); // La colonne des sites
                if (siteCell.textContent.includes(site)) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        // Cacher la liste des suggestions lorsque l'utilisateur clique ailleurs
        document.addEventListener("click", function(event) {
            if (!siteInput.contains(event.target) && !siteSuggestionsBox.contains(event.target)) {
                siteSuggestionsBox.innerHTML = ""; // Vider les suggestions
            }
        });
    });

        
    document.addEventListener("DOMContentLoaded", function() {
        var resetButton = document.getElementById("resetButton");

        resetButton.addEventListener("click", function() {
            // Réinitialiser les valeurs des champs de filtres
            document.getElementById("filterOperator").value = "";
            document.getElementById("filterSite").value = "";
            document.getElementById("filterService").value = "";
            
            // Vider les suggestions
            document.getElementById("operatorSuggestions").innerHTML = "";
            document.getElementById("siteSuggestions").innerHTML = "";

            // Afficher toutes les lignes du tableau
            var rows = document.querySelectorAll("#notesTable tbody tr");
            rows.forEach(function(row) {
                row.style.display = "";
            });
        });
    });
</script>

</body>
</html>

