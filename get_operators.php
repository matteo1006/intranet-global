<?php
if (isset($_POST['poste']) && isset($_POST['site_id'])) {
    // Inclure le fichier de configuration de la base de données
    require_once "db_config.php";

    $poste_id = $_POST['poste'];
    $site_id = $_POST['site_id'];

    // Log des informations du poste et du site pour débogage
    error_log("Poste ID: " . $poste_id . ", Site ID: " . $site_id);

    // Récupérer les opérateurs associés au poste et au site depuis la base de données
    // Adaptation pour utiliser la table de jointure `operator_sites`
    $stmt = $pdo->prepare("SELECT operators.* FROM operators
                          JOIN operator_postes ON operators.id = operator_postes.operator_id
                          JOIN operator_sites ON operators.id = operator_sites.operator_id
                          WHERE operator_postes.poste_id = ? AND operator_sites.site_id = ?");
    $stmt->execute([$poste_id, $site_id]);
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Log des opérateurs pour débogage
    error_log("Operators: " . json_encode($operators));

    // Retourner les opérateurs au format JSON
    echo json_encode($operators);
} else {
    // Retourner une erreur si les paramètres nécessaires ne sont pas définis
    echo json_encode(['error' => 'Les paramètres "poste" et/ou "site_id" ne sont pas définis.']);
}
?>
