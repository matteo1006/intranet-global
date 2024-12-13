<?php
if (isset($_POST['poste'])) {
    // Inclure le fichier de configuration de la base de données
    require_once "db_config.php";

    $poste_id = $_POST['poste'];

    // Log des informations du poste pour débogage
    error_log("Poste ID: " . $poste_id);

    // Récupérer les opérateurs associés au poste depuis la base de données
    $stmt = $pdo->prepare("SELECT * FROM operator_postes 
                          JOIN operators ON operator_postes.operator_id = operators.id 
                          WHERE operator_postes.poste_id = ?");
    $stmt->execute([$poste_id]);
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log des opérateurs pour débogage
    error_log("Operators: " . json_encode($operators));

    // Retourner les opérateurs au format JSON
    echo json_encode($operators);
} else {
    // Retourner une erreur si le paramètre 'poste' n'est pas défini
    echo json_encode(['error' => 'Le paramètre "poste" n\'est pas défini.']);
}
// get_operators_by_site.php
require_once "db_config.php"; // Assurez-vous d'avoir la configuration de la base de données

$site_id = $_POST['site_id'] ?? null; // Récupérer le site_id envoyé par la requête AJAX

$response = [];

if ($site_id) {
    $stmt = $pdo->prepare("SELECT * FROM operators WHERE site_id = ?");
    $stmt->execute([$site_id]);
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = $operators; // Assigne les opérateurs récupérés à la réponse
}

echo json_encode($response); // Encode la réponse en JSON


?>