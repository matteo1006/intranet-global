<?php include 'dbconfig.php'; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Tableau des Notes</title>
    <!-- Inclure CSS pour le style -->
</head>
<body>
    <form action="" method="get">
        Site: <select name="site_id">
            <option value="">Tous</option>
            <?php
            // Récupérer et afficher les options de site
            $sql = "SELECT id, name FROM notes.sites";
            $result = $conn->query($sql);
            while($row = $result->fetch_assoc()) {
                echo "<option value='".$row['id']."'>".$row['name']."</option>";
            }
            ?>
        </select>
        Ligne: <select name="ligne_id">
            <option value="">Tous</option>
            <!-- Remplir avec PHP -->
        </select>
        Service: <select name="service_id">
            <option value="">Tous</option>
            <!-- Remplir avec PHP -->
        </select>
        Recherche: <input type="text" name="search_text" />
        <input type="submit" value="Filtrer" />
    </form>

    <table>
        <thead>
            <tr>
                <th>Site</th>
                <th>Ligne</th>
                <th>Service</th>
                <th>Texte</th>
                <!-- Ajouter d'autres entêtes de colonne si nécessaire -->
            </tr>
        </thead>
        <tbody>
            <?php
            // Construire la requête SQL en fonction des filtres
            $sql = "SELECT * FROM notes.notes WHERE archived = 0";

            // Appliquer les filtres
            if (!empty($_GET['site_id'])) {
                $sql .= " AND site_id = " . $_GET['site_id'];
            }
            // Répéter pour ligne_id et service_id

            // Recherche dans le texte de la note
            if (!empty($_GET['search_text'])) {
                $sql .= " AND note_text LIKE '%" . $conn->real_escape_string($_GET['search_text']) . "%'";
            }

            $result = $conn->query($sql);

            // Afficher les lignes de tableau
            while($row = $result->fetch_assoc()) {
                echo "<tr>";
                // Afficher les données de chaque colonne
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>
