<?php
session_start();
require_once 'db_config.php'; // Assurez-vous que ce fichier contient les informations de connexion à votre base de données

header('Content-Type: application/json');

$requestData = $_REQUEST;

$columns = array( 
    0 => 'note_text',
    1 => 'site_name',
    2 => 'operator_name',
    3 => 'service_name',
    4 => 'created_at',
);

// Construction de la requête SQL de base
$sql = "SELECT notes.*, sites.name AS site_name, services.name AS service_name, operators.name AS operator_name 
        FROM notes 
        LEFT JOIN sites ON notes.site_id = sites.id
        LEFT JOIN services ON notes.service_id = services.id
        LEFT JOIN operators ON notes.operator_id = operators.id";

// Préparation de la requête pour obtenir le total des données sans filtre
$stmt = $pdo->prepare($sql);
$stmt->execute();
$totalData = $stmt->rowCount();
$totalFiltered = $totalData; // Par défaut, sans filtre

// Ajout de la logique de filtrage
$sql .= " WHERE 1=1";
if (!empty($requestData['search']['value'])) {
    $sql .= " AND (notes.note_text LIKE ? OR sites.name LIKE ? OR services.name LIKE ? OR operators.name LIKE ?)";
    $searchValue = "%{$requestData['search']['value']}%";
}

// Préparation de la requête pour obtenir le total des données filtrées
$stmt = $pdo->prepare($sql);
if (!empty($requestData['search']['value'])) {
    $stmt->execute([$searchValue, $searchValue, $searchValue, $searchValue]);
} else {
    $stmt->execute();
}
$totalFiltered = $stmt->rowCount();

// Ajout du tri et de la pagination
$sql .= " ORDER BY " . $columns[$requestData['order'][0]['column']] . " " . $requestData['order'][0]['dir'] . " LIMIT ?, ?";
$start = $requestData['start'];
$length = $requestData['length'];
$stmt = $pdo->prepare($sql);

// Exécution de la requête finale avec tous les paramètres
if (!empty($requestData['search']['value'])) {
    $stmt->execute([$searchValue, $searchValue, $searchValue, $searchValue, $start, $length]);
} else {
    $stmt->bindParam(1, $start, PDO::PARAM_INT);
    $stmt->bindParam(2, $length, PDO::PARAM_INT);
    $stmt->execute();
}

$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $nestedData = [];
    $nestedData[] = $row["note_text"];
    $nestedData[] = $row["site_name"];
    $nestedData[] = $row["operator_name"];
    $nestedData[] = $row["service_name"];
    $nestedData[] = date('d/m/Y H:i', strtotime($row["created_at"]));
    $nestedData[] = '<button class="btn btn-primary btn-sm">Voir détails</button>'; // Modifiez selon vos besoins
    $data[] = $nestedData;
}

$json_data = [
    "draw" => intval($requestData['draw']),
    "recordsTotal" => intval($totalData),
    "recordsFiltered" => intval($totalFiltered),
    "data" => $data
];

echo json_encode($json_data);
?>
