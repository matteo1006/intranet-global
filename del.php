<?php
session_start();
require_once "db_config.php";

// Vérifiez si l'utilisateur est connecté et est administrateur
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    echo "Accès refusé. Vous n'êtes pas administrateur.";
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST['type'];
    $id = $_POST['id'];

    switch ($type) {
        case 'user':
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            break;

        case 'site':
            $stmt = $pdo->prepare("DELETE FROM sites WHERE id = ?");
            break;

        case 'service':
            $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
            break;

        case 'ligne':
            $stmt = $pdo->prepare("DELETE FROM lignes WHERE id = ?");
            break;

        case 'operator':
            $stmt = $pdo->prepare("DELETE FROM operators WHERE id = ?");
            break;

        default:
            echo "Type de suppression non valide.";
            exit;
    }

    $stmt->execute([$id]);
    header('Location: admin.php?message=Supprimé avec succès!');
}
?>