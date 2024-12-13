<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);


session_start();

require_once "db_config.php";
// Fonction pour obtenir le nombre de notes créées sur le mois
function getMonthlyNotes($pdo) {
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS date, COUNT(*) AS note_count
                         FROM notes.notes
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                         GROUP BY date");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir le nombre de mémos créés sur le mois
function getMonthlyMemos($pdo) {
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS date, COUNT(*) AS memo_count
                         FROM notes.memos
                         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 MONTH)
                         GROUP BY date");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}


// Fonction pour obtenir le nombre de notes créées par service
function getNotesByService($pdo) {
    $stmt = $pdo->query("SELECT serv.name AS service_name, COUNT(n.id) AS note_count
                         FROM notes.services serv
                         LEFT JOIN notes.notes n ON serv.id = n.service_id
                         GROUP BY serv.name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir le nombre total de notes
function getTotalNotes($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) AS total_notes
                         FROM notes.notes");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir le nombre de notes par site
function getNotesBySite($pdo) {
    $stmt = $pdo->query("SELECT s.name AS site_name, COUNT(n.id) AS note_count
                         FROM notes.sites s
                         LEFT JOIN notes.notes n ON s.id = n.site_id
                         GROUP BY s.name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir le nombre de notes par ligne
function getNotesByLine($pdo) {
    $stmt = $pdo->query("SELECT l.name AS line_name, COUNT(n.id) AS note_count
                         FROM notes.lignes l
                         LEFT JOIN notes.notes n ON l.id = n.ligne_id
                         GROUP BY l.name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour obtenir la moyenne du temps entre une note créée et une note archivée
function getAverageTimeToArchive($pdo) {
    $stmt = $pdo->query("SELECT AVG(TIMESTAMPDIFF(SECOND, created_at, comment_time)) AS average_time_diff
                         FROM notes.notes
                         WHERE archived = 1");
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getNonArchivedNotesCount($pdo) {
    $stmt = $pdo->query("SELECT COUNT(*) AS non_archived_count FROM notes.notes WHERE archived = 0");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['non_archived_count'];
}

$monthlyMemos = getMonthlyMemos($pdo);
$nonArchivedNotesCount = getNonArchivedNotesCount($pdo);
$monthlyNotes = getMonthlyNotes($pdo);
$notesByService = getNotesByService($pdo);
$totalNotes = getTotalNotes($pdo);
$notesBySite = getNotesBySite($pdo);
$notesByLine = getNotesByLine($pdo);
$averageTimeToArchive = getAverageTimeToArchive($pdo);
$formattedTimeDiff = formatTimeDiff($averageTimeToArchive['average_time_diff']);

function formatTimeDiff($seconds) {
    $days = floor($seconds / 86400);
    $hours = floor(($seconds - ($days * 86400)) / 3600);


    $result = "";
    if ($days > 0) {
        $result .= $days . "j " ;
    }
    if ($hours > 0) {
        $result .= $hours . "h " ;
    }
    
    return $result;
}


?>


<!DOCTYPE html>
<html lang="fr">
<head>
<link rel="stylesheet" href="styles.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet de Notes</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1"></script>

    <style>
        /* Conteneur principal */

        .container {
            max-width: 900px;
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

        .containerdash {
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background-color: #f9f9f9;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        /* Animation au survol */
        .containerdash:hover {
            transform: translateY(-5px); /* Légère élévation */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Accentuation de l'ombre */
        }



        .containerdash {
            max-width: 1200px;
            margin: 50px auto;
            padding: 20px;
        }

        /* Conteneur global pour aligner les graphiques */
        .stats-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
        }

        /* Style pour les conteneurs de graphiques */
        .containergraph {
            background-color: #fff;
            padding: 20px;
            margin: 20px 0;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            width: calc(50% - 20px); /* 50% de la largeur moins la marge */
        }

        /* Effet de survol sur les conteneurs */
        .containergraph:hover {
            background-color: #f0f8ff; /* Couleur au survol */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-40px); /* Légère élévation */
        }

        /* Style des titres de graphiques */
        h2 {
            text-align: center;
            font-size: 1.5rem;
            color: #0B3F65;
        }

        /* Conteneurs pour l'affichage des statistiques générales */
        .non-archived-container {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 20px;
            color: white;
        }

        /* Style pour les statistiques */
        .non-archived-notes-count {
            background-color: #34495e;
            color: white;
            border-radius: 12px;
            text-align: center;
            padding: 20px;
            flex: 1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .non-archived-notes-count:hover {
            background-color: #34495e; /* Changement de couleur au survol */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
        }

        /* Style du texte pour les statistiques */
        .non-archived-notes-count span {
            font-size: 2.5rem;
            font-weight: bold;
        }

        .non-archived-notes-count p {
            font-size: 1.2rem;
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


/* Responsiveness for smaller screens */
@media screen and (max-width: 992px) {
    .containergraph {
        width: calc(50% - 10px); /* Adjust for medium screens */
    }
}

@media screen and (max-width: 768px) {
    .containergraph {
        width: 100%; /* Take full width for smaller screens */
    }

    .non-archived-notes-count {
        width: 100%; /* Full width for stats on smaller screens */
    }
}

@media screen and (max-width: 576px) {
    .containergraph {
        padding: 15px; /* Reduce padding for smaller devices */
    }
}

#returnNotes {
    cursor: pointer; /* Curseur en forme de main */
    transition: all 0.3s ease; /* Transition douce pour les effets */
}

#returnNotes:hover {
    background-color: blue; /* Changement de couleur de fond au survol */
    transform: translateY(-5px); /* Légère élévation */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Ombre pour effet de profondeur */
}

@media only screen and (min-width: 1080px) {
    .container {
        margin-left: 30%; /* Le menu est caché sur les tablettes et téléphones */
    }
}
@media only screen and (min-width: 1080px) {
    .containerdash {
        margin-left: 20%; /* Le menu est caché sur les tablettes et téléphones */
    }
}
    </style>
</head>

<body>

<?php include 'menu.php'; ?>
    <div class="container">
        <h2 id="title" style="padding-bottom: 2%">Visualisation graphique des Notes & Relèves</h2>
    </div>
    <div class="containerdash">
        <div class="stats-container">
            <div class="containergraph">
                <h3 id="title">Notes & Relèves</h3>
                <canvas id="monthlyNotesChart"></canvas>
            </div>
            <div class="containergraph">
                <h3>Notes par services</h3>
                <canvas id="notesByServiceChart"></canvas>
            </div>
        </div>

        <div class="stats-container">
            <div class="containergraph">
                <h3>Notes par sites</h3>
                <canvas id="notesBySiteChart"></canvas>
            </div>
            <div class="containergraph">
                <h3>Notes par lignes</h3>
                <canvas id="notesByLineChart"></canvas>
            </div>
        </div>

        <div class="stats-container">
            <div class="non-archived-notes-count" >
                <span  style="color: white" id="returnNotes"><?php echo $nonArchivedNotesCount; ?></span>
                <p  style="color: white">Notes Non Archivées</p>
            </div>

            <div class="non-archived-notes-count">
                <span  style="color: white"><?php echo $formattedTimeDiff; ?></span>
                <p  style="color: white">Moyenne pour archiver une note</p>
            </div>
        </div>
    </div>



<script>
function returnNotes() {
    let button = document.getElementById("returnNotes");
    button.addEventListener("click", function (){
    window.location.href = "notes.php";
    });
}

document.addEventListener("DOMContentLoaded", function() {
    returnNotes(); // Assurez-vous que la fonction est appelée après le chargement du DOM
});


    window.onload = function() {
        // Convertir les données PHP en JSON pour les utiliser dans les graphiques Chart.js
        const monthlyNotesData = <?php echo json_encode($monthlyNotes); ?>;
        const monthlyMemosData = <?php echo json_encode($monthlyMemos); ?>;
        const notesByServiceData = <?php echo json_encode($notesByService); ?>;
        const notesBySiteData = <?php echo json_encode($notesBySite); ?>;
        const notesByLineData = <?php echo json_encode($notesByLine); ?>;

        // Créer le graphique linéaire pour les notes et mémos par jour
        createLineChart('monthlyNotesChart', monthlyNotesData, monthlyMemosData, 'Notes par jour', 'Relèves par jour');

        // Créer les autres graphiques en barres
        createBarChart('notesByServiceChart', notesByServiceData.map(data => ({ label: data.service_name, value: data.note_count })), 'Notes par service', 'rgba(75, 192, 192, 0.6)');
        createBarChart('notesBySiteChart', notesBySiteData.map(data => ({ label: data.site_name, value: data.note_count })), 'Notes par site', 'rgba(255, 206, 86, 0.6)');
        createBarChart('notesByLineChart', notesByLineData.map(data => ({ label: data.line_name, value: data.note_count })), 'Notes par ligne', 'rgba(153, 102, 255, 0.6)');
    };

    // Ajustez la fonction createLineChart pour accepter et traiter les données des mémos
    function createLineChart(chartId, notesData, memosData, notesLabel, memosLabel) {
        const ctx = document.getElementById(chartId).getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: notesData.map(data => data.date), // Assumant que notesData et memosData ont les mêmes labels
                datasets: [{
                    label: notesLabel,
                    data: notesData.map(data => data.note_count),
                    borderColor: 'rgba(54, 162, 235, 0.6)',
                    fill: false,
                    tension: 0.1
                }, {
                    label: memosLabel,
                    data: memosData.map(data => data.memo_count),
                    borderColor: 'rgba(255, 99, 132, 0.6)',
                    fill: false,
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    function createBarChart(chartId, chartData, label, color) {
        const ctx = document.getElementById(chartId).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartData.map(data => data.label),
                datasets: [{
                    label: label,
                    data: chartData.map(data => data.value),
                    backgroundColor: color,
                    borderColor: color,
                }],
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

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
