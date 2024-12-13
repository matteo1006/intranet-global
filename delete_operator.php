<?php
include 'db_config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['id'])) {
    $id = $_POST['id'];

    // Préparez la déclaration de suppression
    $stmt = $pdo->prepare("DELETE FROM operators WHERE id = ?");
    $stmt->execute([$id]);

    // Redirigez après la suppression
    header("Location: add_operator.php"); // Remplacez 'votre_page.php' par le nom de votre fichier actuel.
    exit;
} else {
    // Redirigez si l'accès direct à ce fichier est fait ou si l'ID est vide
    header("Location: add_operator.php"); // Remplacez 'votre_page.php' par le nom de votre fichier actuel.
    exit;
}
?>
