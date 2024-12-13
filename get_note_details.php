<?php
// get_note_details.php

$noteId = $_GET['id'] ?? null;
if ($noteId) {
    // Votre logique pour récupérer les détails de la note ici
    // Et puis renvoyez les données en JSON
    echo json_encode($noteDetails);
} else {
    // Retourner une erreur si l'ID de la note n'est pas fourni
    echo json_encode(['error' => 'Note ID is missing']);
}

header('Content-Type: application/json');

?>