<?php
// menu.php
?>
<div id="menu">
    <a href="index.php" class="menu-button">Accueil</a>
    <div class="dropdown">
            <a href="#" class="menu-button">Notes &#9662;</a>
            <div class="dropdown-content">
            <a href="notes.php">En cours</a>
            <a href="archive_page.php">Archivées</a>
            </div>
            </div>
            <a href="memos.php" class="menu-button">Relèves</a>
            <div class="dropdown">
            <a href="#" class="menu-button">Site &#9662;</a>
            <div class="dropdown-content">
             <a href="davezieux.php"><strike>Davezieux</strike></a>
             <a href="marenton.php">Marenton</a>
             <a href="pupil.php"><strike>Pupil</strike></a>
             <a href="strambert.php"><strike>ST Rambert</strike></a>
            </div>
            </div>
    
    <?php
    // Si l'utilisateur est connecté
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {

        // Si l'utilisateur est administrateur
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
            echo '<a href="admin.php" class="menu-button">Administration</a>';
        }

        echo '<a href="logout.php" class="menu-button">Déconnexion</a>';
    } else {
        echo '<a href="login.php" class="menu-button">Connexion</a>';
    }
    ?>
</div>
