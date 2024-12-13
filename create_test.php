<?php
session_start();
require_once "db_config.php";

// Récupérer la liste des postes depuis la base de données
$stmtPostes = $pdo->prepare("SELECT * FROM postes");
$stmtPostes->execute();
$postes = $stmtPostes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des opérateurs depuis la base de données
$stmtOperators = $pdo->prepare("SELECT * FROM operators");
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);
$workstation = $_POST['workstation'];

// Inclure les fichiers de la bibliothèque PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

$message = "";
$ligne_id = $_GET['ligne_id'] ?? null;

if (!$ligne_id) {
    die('Ligne non spécifiée.');
}

$stmt = $pdo->prepare("SELECT * FROM lignes WHERE id = ?");
$stmt->execute([$ligne_id]);
$line = $stmt->fetch();

if (!$line) {
    die('Ligne non trouvée.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['create_note'])) {
    $site_id = $line['site_id'];
    $service_id = $_POST['service_id'];
    $operator_id = $_POST['operator_name'];
    $note_text = $_POST['note_text'];
    $workstation = $_POST['workstation'];

    $stmtEmails = $pdo->prepare("SELECT * FROM service_email_map WHERE service_id = ?");
    $stmtEmails->execute([$service_id]);
    $emails = $stmtEmails->fetchAll();

    // Récupérer le nom de l'opérateur à partir de l'ID
    $stmtOperator = $pdo->prepare("SELECT name FROM operators WHERE id = ?");
    $stmtOperator->execute([$operator_id]);
    $operator = $stmtOperator->fetch(PDO::FETCH_ASSOC);

    // Vérifier que l'opérateur a été trouvé
    if ($operator) {
        $operator_name = $operator['name'];
    } else {
        // Gérer l'erreur si l'opérateur n'est pas trouvé
        die("Opérateur non trouvé.");
    }

    $image_paths = [];

    // Formats de fichier autorisés
    $allowed_file_types = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg', 'image/heic', 'image/BMP', 'image/heif'];

    for ($i = 0; $i < count($_FILES['image']['name']); $i++) {
        if ($_FILES['image']['error'][$i] == 0) {
            // Récupérer le type MIME du fichier
            $file_type = $_FILES['image']['type'][$i];

            // Vérifier si le type de fichier est autorisé
            if (in_array($file_type, $allowed_file_types)) {
                // Le fichier est un type d'image autorisé
                $target_dir = "uploads/";
                $image_path = $target_dir . basename($_FILES["image"]["name"][$i]);

                // Déplacez le fichier téléchargé
                if (move_uploaded_file($_FILES["image"]["tmp_name"][$i], $image_path)) {
                    // Fichier téléchargé et déplacé avec succès
                    $image_paths[] = $image_path;
                } else {
                    // Erreur lors du déplacement du fichier
                    die('Erreur lors du téléchargement du fichier.');
                }
            } else {
                // Le fichier n'est pas un type d'image autorisé
                die('Type de fichier non autorisé. Seuls les formats JPEG, PNG et GIF sont acceptés.');
            }
        } else {
            // Gérer les autres erreurs liées au téléchargement de fichiers
            if ($_FILES['image']['error'][$i] != UPLOAD_ERR_NO_FILE) {
                die('Erreur lors du téléchargement du fichier. Code d\'erreur: ' . $_FILES['image']['error'][$i]);
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO notes (site_id, ligne_id, service_id, operator_name, note_text, image_path, workstation) VALUES (?, ?, ?, ?, ?, ?, ?)");

    // Assuming image_path is a comma-separated list of image paths
    $image_path_string = implode(',', $image_paths);

    $stmt->execute([$site_id, $ligne_id, $service_id, $operator_name, $note_text, $image_path_string, $workstation]);

    // Récupérer le nom du service à partir de la base de données
    $stmt = $pdo->prepare("SELECT name FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    $service = $stmt->fetch(PDO::FETCH_ASSOC);

    // Vérifiez que le service a été trouvé
    if ($service) {
        $service_name = $service['name'];

        // Préparation du contenu de l'e-mail avec le nom du service
        $email_subject = "{$line['name']} service {$service_name}";
        $email_body = "<html><body>";
        $email_body .= "<p><strong>Contenu de la note :</strong></p>";
        $email_body .= "<p><strong>Opérateur :</strong> {$operator_name}</p>";
        $email_body .= "<p><strong>Service :</strong> {$service_name}</p>";
        $email_body .= "<p><strong>Texte de la note :</strong><br/>{$note_text}</p>";
        $email_body .= "<p><strong><a href='http://192.168.12.38/cd/notes.php'>Vers les notes</a></strong></p>";
        $email_body .= "</body></html>";

        // Ensuite, vous pouvez continuer avec l'envoi de l'e-mail comme d'habitude.
    } else {
        // Gérer l'erreur si le service n'est pas trouvé
        echo "Service non trouvé.";
    }

    // Envoi de l'e-mail
    $mail = new PHPMailer(true);
    try {
        $mail->isHTML(true);
        $mail->ContentType = 'text/html';
        $mail->CharSet = 'UTF-8';
        $mail->isSMTP();
        $mail->Host = 'mphygiene-com.mail.protection.outlook.com';
        $mail->SMTPAuth = false;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 25;

        $mail->setFrom('Noteprod@mphygiene.com', 'Note de Production');

        foreach ($emails as $email) {
            // Supposons que $mapping['email'] contienne des e-mails principaux séparés par des virgules
            $primary_emails = explode(',', $email['email']);
            foreach ($primary_emails as $primary_email) {
                if (!empty($primary_email)) {
                    $mail->addAddress(trim($primary_email)); // Ajoute l'email principal
                }
            }

            // Ajout des e-mails CC si présents
            if (!empty($email['cc_email'])) {
                $cc_emails = explode(',', $email['cc_email']);
                foreach ($cc_emails as $cc_email) {
                    if (!empty($cc_email)) {
                        $mail->addCC(trim($cc_email)); // Ajoute les e-mails CC
                    }
                }
            }
        }

        $mail->Subject = $email_subject;
        $mail->Body = $email_body;

        foreach ($image_paths as $attachment) {
            $mail->addAttachment($attachment);
        }

        $mail->send();

        $message = "Note créée avec succès et e-mail envoyé!";
    } catch (Exception $e) {
        $message = "Erreur lors de l'envoi de l'e-mail : {$mail->ErrorInfo}";
    }
}

$stmt = $pdo->prepare("SELECT * FROM sites WHERE id = ?");
$stmt->execute([$line['site_id']]);
$site = $stmt->fetch();

$stmtServices = $pdo->prepare("SELECT * FROM services WHERE site_id = ?");
$stmtServices->execute([$site['id']]);
$services = $stmtServices->fetchAll();

$stmtOperators = $pdo->prepare("SELECT * FROM operators");
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll();

// Utilisez usort avec une fonction de rappel pour trier le tableau par le champ 'name'
usort($operators, function ($a, $b) {
    return strcmp($a['name'], $b['name']);
});
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer une note pour la ligne <?= $line['name']; ?></title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        document.querySelectorAll(".photo-upload").forEach(function (div) {
            div.querySelector(".remove-photo").addEventListener("click", function () {
                div.remove();
            });
        });

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
</head>
<body>

<div class="container">
    <h2>Note pour la ligne <?= $line['name']; ?></h2>

    <?php if ($message): ?>
        <div class="message">
            <?= $message; ?>
        </div>
    <?php endif; ?>

    <form action="create_test.php?ligne_id=<?= $ligne_id; ?>" method="post" enctype="multipart/form-data">
        <p><strong>Site:</strong> <?= $site['name']; ?></p>
        <p><strong>Ligne:</strong> <?= $line['name']; ?></p><br>

        <input type="hidden" name="site_id" value="<?= $site['id']; ?>">
        <label for="poste">Poste :</label>
<select name="workstation" id="poste">
    <option value="">Sélectionnez un poste</option>
    <?php foreach ($postes as $poste): ?>
        <option value="<?= $poste['id']; ?>"><?= $poste['name']; ?></option>
    <?php endforeach; ?>
</select>
        <br><br>
        <div class="radio-group">
            <p><strong>Service:</strong></p>
            <?php foreach ($services as $service): ?>
                <input type="radio" id="service<?= $service['id']; ?>" name="service_id"
                       value="<?= $service['id']; ?>" required>
                <label for="service<?= $service['id']; ?>"><?= $service['name']; ?></label>
            <?php endforeach; ?>
        </div><br><br>

        <label for="operateur">Opérateur :</label>
        <select name="operator_name" id="operateur" disabled>
            <option value="">Sélectionnez un opérateur</option>
        </select><br><br>

        <textarea name="note_text" placeholder="Texte de la note" rows="5" required></textarea><br><br>

        <div class="photo-uploads" id="photo-uploads-container">
            <div class="photo-upload">
                <input type="file" name="image[]" accept="image/*" multiple>
                <br><br>
            </div>

            <div class="photo-upload">
                <input type="file" name="image[]" accept="image/*" multiple>
                <br><br>
            </div>

            <div class="photo-upload">
                <input type="file" name="image[]" accept="image/*" multiple>
                <br><br>
            </div>
        </div>

        <button class="button" type="submit" name="create_note">
            <div class="svg-wrapper-1">
                <div class="svg-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24">
                        <path fill="none" d="M0 0h24v24H0z"></path>
                        <path fill="currentColor"
                              d="M1.946 9.315c-.522-.174-.527-.455.01-.634l19.087-6.362c.529-.176.832.12.684.638l-5.454 19.086c-.15.529-.455.547-.679.045L12 14l6-8-8 6-8.054-2.685z"></path>
                    </svg>
                </div>
            </div>
            <span>Envoyer la note</span>
        </button><br><br>

        <button class="buttonm" onclick="window.location.href='http://192.168.12.38/cd/dashboardlignes.php?ligne_id=9'">
            <span>Retour menu</span>
        </button>
    </form>
</div>

</body>
</html>
