<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet de Notes</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<?php include 'menu.php'; ?>

<div class="container">
        <a href="davezieux.php" class="admin-button">DAVEZIEUX</a>
        <a href="marenton.php" class="admin-button">MARENTON</a>
        <a href="strambert.php" class="admin-button">ST RAMBERT</a>
        <a href="pupil.php" class="admin-button">PUPIL</a>
</div>

<script>
    

    function viewProcessedNotes() {
        // Logic to view processed notes
        alert("Fonction pour voir les notes traitées");
    }
</script>

</body>
</html>
