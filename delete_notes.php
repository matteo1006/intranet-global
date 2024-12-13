<?php
session_start();
require_once "db_config.php";

// Vérifier si l'ID de la note est passé en POST
if (isset($_POST['note_id'])) {
    $noteId = $_POST['note_id'];

    // Requête pour supprimer la note de la base de données
    $query = "DELETE FROM notes WHERE id = ?";
    $stmt = $pdo->prepare($query);

    if ($stmt->execute([$noteId])) {
        // Redirection après suppression réussie
        $_SESSION['message'] = "Note supprimée avec succès.";
        header("Location: notes.php");
        exit;
    } else {
        $_SESSION['error'] = "Erreur lors de la suppression de la note.";
        header("Location: details.php?id=" . $noteId);
        exit;
    }
} else {
    // Si aucun ID de note n'est fourni
    $_SESSION['error'] = "Aucune note spécifiée.";
    header("Location: notes.php");
    exit;
}
?>
