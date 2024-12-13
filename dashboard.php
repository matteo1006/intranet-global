<?php
include 'db_config.php';

// Fonction pour obtenir le nombre de notes créées sur le mois
function getMonthlyNotes($pdo) {
    $stmt = $pdo->query("SELECT DATE_FORMAT(created_at, '%Y-%m-%d') AS date, COUNT(*) AS note_count
                         FROM notes.notes
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

// Appeler les fonctions et stocker les résultats dans des variables
$monthlyNotes = getMonthlyNotes($pdo);
$notesByService = getNotesByService($pdo);
$totalNotes = getTotalNotes($pdo);
$notesBySite = getNotesBySite($pdo);
$notesByLine = getNotesByLine($pdo);
$averageTimeToArchive = getAverageTimeToArchive($pdo);

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Tableau de Bord</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1"></script>

    <style>
        /* Ici vous pouvez ajouter vos styles CSS */
        .chart-container {
            width: 50%;
            margin: auto;
        }
    </style>
</head>
<body>
<div class="chart-container">
        <canvas id="monthlyNotesChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="notesByServiceChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="notesBySiteChart"></canvas>
    </div>
    <div class="chart-container">
        <canvas id="notesByLineChart"></canvas>
    </div>
    <div>
        <p>Nombre total de notes: <?php echo htmlspecialchars($totalNotes['total_notes']); ?></p>
        <p>Moyenne du temps pour archiver une note (en secondes): <?php echo htmlspecialchars($averageTimeToArchive['average_time_diff']); ?></p>
    </div>

    <script>
        // Convertir les données PHP en JSON pour les utiliser dans les graphiques Chart.js
        const monthlyNotesData = <?php echo json_encode($monthlyNotes); ?>;
        const notesByServiceData = <?php echo json_encode($notesByService); ?>;
        const notesBySiteData = <?php echo json_encode($notesBySite); ?>;
        const notesByLineData = <?php echo json_encode($notesByLine); ?>;

        // Fonction pour créer un graphique en barres
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
                        yAxes: [{
                            ticks: {
                                beginAtZero: true
                            }
                        }]
                    }
                }
            });
        }

        // Créer les graphiques en utilisant les fonctions
        window.onload = function() {
            createBarChart('monthlyNotesChart', monthlyNotesData.map(data => ({ label: data.date, value: data.note_count })), 'Notes par jour', 'rgba(54, 162, 235, 0.6)');
            
            createBarChart('notesByServiceChart', notesByServiceData.map(data => ({ label: data.service_name, value: data.note_count })), 'Notes par service', 'rgba(75, 192, 192, 0.6)');
            
            createBarChart('notesBySiteChart', notesBySiteData.map(data => ({ label: data.site_name, value: data.note_count })), 'Notes par site', 'rgba(255, 206, 86, 0.6)');
            
            createBarChart('notesByLineChart', notesByLineData.map(data => ({ label: data.line_name, value: data.note_count })), 'Notes par ligne', 'rgba(153, 102, 255, 0.6)');
        };
    </script>
</body>
</html>
