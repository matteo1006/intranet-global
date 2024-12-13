<?php
// Inclure le fichier de configuration de la base de données
require_once "db_config.php";

// Récupérer la liste des postes depuis la base de données
$stmtPostes = $pdo->prepare("SELECT * FROM postes");
$stmtPostes->execute();
$postes = $stmtPostes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des opérateurs depuis la base de données
$stmtOperators = $pdo->prepare("SELECT * FROM operators");
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Postes et Opérateurs</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <h2>Choisissez un Poste et un Opérateur</h2>

    <form>
        <label for="poste">Poste :</label>
        <select name="poste" id="poste">
            <option value="">Sélectionnez un poste</option>
            <?php foreach ($postes as $poste): ?>
                <option value="<?= $poste['id']; ?>"><?= $poste['name']; ?></option>
            <?php endforeach; ?>
        </select>

        <br><br>

        <label for="operateur">Opérateur :</label>
        <select name="operateur" id="operateur" disabled>
            <option value="">Sélectionnez un opérateur</option>
        </select>
    </form>

    <script>
        $(document).ready(function () {
            // Lorsque le choix de poste change
            $("#poste").change(function () {
                // Récupérer la valeur du poste sélectionné
                var selectedPoste = $(this).val();

                // Mettre à jour la liste des opérateurs en fonction du poste
                updateOperatorList(selectedPoste);
            });

            function updateOperatorList(selectedPoste) {
                // Réinitialiser la liste des opérateurs
                $("#operateur").empty().prop("disabled", true);

                // Ajouter une option par défaut
                $("#operateur").append('<option value="">Sélectionnez un opérateur</option>');

                // Si aucun poste n'est sélectionné, arrêter ici
                if (!selectedPoste) {
                    return;
                }

                // Récupérer la liste des opérateurs associés au poste via Ajax
                $.ajax({
                    url: 'get_operators.php', // Créez ce fichier pour récupérer les opérateurs en fonction du poste
                    type: 'POST',
                    data: { poste: selectedPoste },
                    dataType: 'json',
                    success: function (data) {
                        // Ajouter les opérateurs à la liste
                        data.forEach(function (operator) {
                            $("#operateur").append('<option value="' + operator.id + '">' + operator.name + '</option>');
                        });

                        // Activer la liste des opérateurs
                        $("#operateur").prop("disabled", false);
                    },
                    error: function (error) {
                        console.log('Erreur lors de la récupération des opérateurs:', error);
                    }
                });
            }
        });
    </script>
</body>

</html>
