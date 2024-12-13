<?php
session_start();
require_once "db_config.php";

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_mapping'])) {
    $service_id = $_POST['service_id'];

    // Ici, vous devriez idéalement utiliser des requêtes préparées pour éviter les injections SQL
    $stmt = $pdo->prepare("DELETE FROM service_email_map WHERE service_id = ?");
    if ($stmt->execute([$service_id])) {
        $_SESSION['message'] = "Mappage supprimé avec succès.";
    } else {
        $_SESSION['message'] = "Erreur lors de la suppression du mappage.";
    }

    // Rediriger vers la page de mappage
    header('Location: email_mapping.php');
    exit;
}
