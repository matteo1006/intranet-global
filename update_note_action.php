<?php
session_start();
require_once "db_config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_note'])) {
    $note_id = $_POST['note_id'];
    $note_text = $_POST['note_text'];

    $stmt = $pdo->prepare("SELECT image_path FROM notes WHERE id = ?");
    $stmt->execute([$note_id]);
    $existingNote = $stmt->fetch();
    $image_path = $existingNote['image_path'];

    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "uploads/";
        $image_path = $target_dir . basename($_FILES["image"]["name"]);
        move_uploaded_file($_FILES["image"]["tmp_name"], $image_path);
    }

    $stmt = $pdo->prepare("UPDATE notes SET note_text = ?, image_path = ? WHERE id = ?");
    $stmt->execute([$note_text, $image_path, $note_id]);

    header('Location: note_page.php'); // Redirection vers la page des notes
}
