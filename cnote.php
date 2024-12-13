<?php
ini_set('upload_max_filesize', '100M');
ini_set('post_max_size', '100M');
session_start();
require_once "db_config.php";
$sansMargin = true;
ini_set('display_errors', 1);
error_reporting(E_ALL);
$ligne_id = isset($_GET['ligne_id']) ? $_GET['ligne_id'] : (isset($_POST['ligne_id']) ? $_POST['ligne_id'] : null);

if (!$ligne_id) {
    die('Ligne non spécifiée.');
}

$site_id = $_POST['site_id'] ?? null;
$poste_id = $_POST['poste_id'] ?? null;
$element_id = $_POST['element_id'] ?? null;
$response = [];

if ($site_id && $poste_id) {
    // Adapter la requête pour utiliser la table de jointure `operator_sites`
    // La requête ci-dessous est un exemple basé sur l'hypothèse que vous avez une table `operator_sites` qui relie les opérateurs aux sites
    $stmt = $pdo->prepare("SELECT operators.* FROM operators 
                            JOIN operator_sites ON operators.id = operator_sites.operator_id 
                            JOIN postes ON operators.poste_id = postes.id 
                            WHERE operator_sites.site_id = ? AND operators.poste_id = ?");
    $stmt->execute([$site_id, $poste_id]);
    $operators = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = $operators;
}


echo json_encode($response);
// Récupérer la liste des postes depuis la base de données
$stmtPostes = $pdo->prepare("SELECT * FROM postes");
$stmtPostes->execute();
$postes = $stmtPostes->fetchAll(PDO::FETCH_ASSOC);

// Récupérer la liste des opérateurs depuis la base de données
$stmtOperators = $pdo->prepare("SELECT * FROM operators");
$stmtOperators->execute();
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);
$poste = isset($_POST['poste']) ? $_POST['poste'] : null;

$stmtPriorite = $pdo->prepare("SELECT * FROM priorite");
$stmtPriorite->execute();
$priorites = $stmtPriorite->fetchAll(PDO::FETCH_ASSOC);

$stmtElements = $pdo->prepare("SELECT elements.* FROM elements 
JOIN ligne_elements ON elements.id = ligne_elements.element_id 
WHERE ligne_elements.ligne_id = ?");
$stmtElements->execute([$ligne_id]);
$elements = $stmtElements->fetchAll(PDO::FETCH_ASSOC);

// Après avoir récupéré $poste_id depuis $_POST
$poste_id = $_POST['selected_poste'] ?? null;

// Vérifiez si $poste_id n'est pas null et récupérez le nom du poste
if ($poste_id) {
    $stmtPoste = $pdo->prepare("SELECT name FROM postes WHERE id = ?");
    $stmtPoste->execute([$poste_id]);
    $posteData = $stmtPoste->fetch(PDO::FETCH_ASSOC);
    $posteName = $posteData ? $posteData['name'] : 'Poste inconnu';
} else {
    $posteName = 'Poste non spécifié';
}
$element_id = $_POST['element_id'] ?? null;
if ($element_id) {
    $stmtElement = $pdo->prepare("SELECT element_name FROM elements WHERE id = ?");
    $stmtElement->execute([$element_id]);
    $elementData = $stmtElement->fetch(PDO::FETCH_ASSOC);
    $elementName = $elementData ? $elementData['element_name'] : 'Élément inconnu';
} else {
    $elementName = 'Élément non spécifié';
}

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
    $poste = $_POST['selected_poste'] ?? null;

    $stmtEmails = $pdo->prepare("SELECT * FROM service_email_map WHERE service_id = ?");
    $stmtEmails->execute([$service_id]);
    $emails = $stmtEmails->fetchAll();

    $operator_id = $_POST['operator_name']; // Assuming this is how you get the operator's ID from the form submission

    $stmtOperator = $pdo->prepare("SELECT * FROM operators WHERE id = ?");
    $stmtOperator->execute([$operator_id]);
    $operator = $stmtOperator->fetch(PDO::FETCH_ASSOC);



    $priorite_id = $_POST['priorite_id'] ?? null; // Use null or a sensible default if not set

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

    // Loop through each uploaded file
    for ($i = 0; $i < count($_FILES['image']['name']); $i++) {
        if ($_FILES['image']['error'][$i] == 0) {
            // Générer un nom de fichier unique
            $new_filename = uniqid() . '_' . $_FILES["image"]["name"][$i];
            $target_dir = "uploads/";
            $image_path = $target_dir . $new_filename;
    
            // Déplacer le fichier téléchargé avec le nouveau nom de fichier
            if (move_uploaded_file($_FILES["image"]["tmp_name"][$i], $image_path)) {
                // Fichier téléchargé et déplacé avec succès
                $image_paths[] = $image_path;
            } else {
                // Erreur lors du déplacement du fichier
                die('Erreur lors du téléchargement du fichier. Impossible de déplacer le fichier.');
            }
        } else {
            // Gérer les autres erreurs de téléchargement de fichier
            switch ($_FILES['image']['error'][$i]) {
                case UPLOAD_ERR_INI_SIZE:
                    die('Erreur lors du téléchargement du fichier. Le fichier est trop volumineux (dépasse la taille maximale autorisée).');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    die('Erreur lors du téléchargement du fichier. Le fichier est trop volumineux (dépasse la taille maximale spécifiée dans le formulaire HTML).');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    die('Erreur lors du téléchargement du fichier. Le fichier n\'a été que partiellement téléchargé.');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    // Aucun fichier n'a été téléchargé
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    die('Erreur lors du téléchargement du fichier. Aucun répertoire temporaire disponible.');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    die('Erreur lors du téléchargement du fichier. Impossible d\'écrire le fichier sur le disque.');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    die('Erreur lors du téléchargement du fichier. Une extension PHP a empêché le téléchargement du fichier.');
                    break;
                default:
                    die('Erreur lors du téléchargement du fichier. Code d\'erreur: ' . $_FILES['image']['error'][$i]);
                    break;
            }
        }
    }
    


    $stmt = $pdo->prepare("INSERT INTO notes (site_id, ligne_id, service_id, operator_name, note_text, image_path, poste, priorite_id, element_id, CREATED_AT) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");



    // Assuming image_path is a comma-separated list of image paths
    $image_path_string = implode(',', $image_paths);

    $stmt->execute([$site_id, $ligne_id, $service_id, $operator_name, $note_text, $image_path_string, $poste, $priorite_id, $element_id]);

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
        $email_body .= "<p><strong>Ligne :</strong> {$line['name']}</p>";
        $email_body .= "<p><strong>Élément :</strong> {$elementName}</p>";
        $email_body .= "<p><strong>Poste :</strong> {$posteName}</p>";
        $email_body .= "<p><strong>Opérateur :</strong> {$operator_name}</p>";
        $email_body .= "<p><strong>Service :</strong> {$service_name}</p>";
        $email_body .= "<p><strong>Texte de la note :</strong><br/>{$note_text}</p>";
        $email_body .= "<p><strong><a href='http://192.168.12.41/index.php'>Vers les notes</a></strong></p>";
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

// Récupérer le nom de l'opérateur à partir de l'ID
// Cette requête doit être modifiée car 'site_id' n'existe plus directement dans 'operators'
// Supposons que vous avez une table de jointure 'operator_sites'
$stmtOperators = $pdo->prepare("
    SELECT operators.* 
    FROM operators 
    JOIN operator_sites ON operators.id = operator_sites.operator_id 
    WHERE operator_sites.site_id = ?
");
$stmtOperators->execute([$site_id]);
$operators = $stmtOperators->fetchAll(PDO::FETCH_ASSOC);


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
    
</head>
<body class="<?php echo $sansMargin ? 'sansMargin' : ''; ?>">

<div id="loading" class="loading-overlay" style="display:none;">
    <div class="loading-spinner"></div>
</div>


<div class="containercrn">
    <h2>Note pour la ligne <?= $line['name']; ?></h2>

    <?php if ($message): ?>
        <div class="message">
            <?= $message; ?>
        </div>
    <?php endif; ?>

    <form action="create_note.php?ligne_id=<?= $ligne_id; ?>" method="post" enctype="multipart/form-data">
        <p><strong>Site:</strong> <?= $site['name']; ?> 
        <strong>Ligne:</strong> <?= $line['name']; ?></p><br>
        <input type="hidden" name="site_id" value="<?= $site['id']; ?>">
        <div class="form-row">
    <div class="form-group">
        <label for="poste"><strong>Poste :</strong></label>
        <select name="selected_poste" id="poste">
            <option value="">Sélectionnez un poste</option>
            <?php foreach ($postes as $poste): ?>
                <option value="<?= $poste['id']; ?>"><?= $poste['name']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="operateur"><strong>Opérateur :</strong></label>
        <select name="operator_name" id="operateur" disabled>
            <option value="">Sélectionnez un opérateur</option>
        </select>
    </div>

        
    <div class="form-group">
        <label for="priorite"><strong>Priorité :</strong></label>
        <select name="priorite_id" id="priorite" required>
            <option value="">Sélectionnez une priorité</option>
            <?php foreach ($priorites as $priorite): ?>
                <option value="<?= $priorite['id']; ?>"><?= $priorite['nom']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    </div>
        <div class="radio-group">
            <p><strong>Service:</strong></p>
            <?php foreach ($services as $service): ?>
                <input type="radio" id="service<?= $service['id']; ?>" name="service_id"
                       value="<?= $service['id']; ?>" required>
                <label for="service<?= $service['id']; ?>"><?= $service['name']; ?></label>
            <?php endforeach; ?>
        </div><br>
        <div class="radio-group">
        <p><strong>Eléments:</strong></p>
            <select name="element_id" id="elementSelect" required>
                <option value="">--Choisissez un élément--</option>
                <?php foreach ($elements as $element): ?>
                    <option value="<?= htmlspecialchars($element['id']); ?>"><?= htmlspecialchars($element['element_name']); ?></option>
                <?php endforeach; ?>
            </select>
            </div><br>

        <textarea name="note_text" placeholder="Texte de la note" rows="5" required></textarea>

        <div class="form-group file-upload-group">
        <label for="file-upload"><strong>Fichiers :</strong></label>
        <div id="photo-uploads-container" style="display:none;">
  <input type="file" capture="environment" name="image[]" id="file-upload1" accept="image/*">
  <input type="file" name="image[]" id="file-upload2" accept="image/*">
  <input type="file" name="image[]" id="file-upload3" accept="image/*">
</div>

<button class="buttonpicture" type="button" onclick="triggerFileUpload()">
<svg xmlns="http://www.w3.org/2000/svg" width="24" viewBox="0 0 24 24" height="24" fill="none" class="svg-icon"><g stroke-width="2" stroke-linecap="round" stroke="#fff" fill-rule="evenodd" clip-rule="evenodd"><path d="m4 9c0-1.10457.89543-2 2-2h2l.44721-.89443c.33879-.67757 1.03131-1.10557 1.78889-1.10557h3.5278c.7576 0 1.4501.428 1.7889 1.10557l.4472.89443h2c1.1046 0 2 .89543 2 2v8c0 1.1046-.8954 2-2 2h-12c-1.10457 0-2-.8954-2-2z"></path><path d="m15 13c0 1.6569-1.3431 3-3 3s-3-1.3431-3-3 1.3431-3 3-3 3 1.3431 3 3z"></path></g></svg>
  <span class="lable">Prendre une photo</span>
</button>
    </div>
    <div id="preview-container"></div>

    <script>
$(document).ready(function () {
    // Gestion de la soumission du formulaire
    $('form').on('submit', function(e) {
        if (!$(this).data('is-submitting')) {
            $(this).data('is-submitting', true);
            $('#loading').show();  // Assurez-vous que #loading est configuré pour être affiché correctement
        } else {
            e.preventDefault();
        }
    });

    // Mise à jour de la liste des opérateurs basée sur le poste sélectionné
    $("#poste").change(function() {
        var selectedPoste = $(this).val();
        $("#operateur").empty().append('<option value="">Sélectionnez un opérateur</option>').prop("disabled", true);
        if (selectedPoste) {
            $.ajax({
                url: 'get_operators.php',
                type: 'POST',
                data: { poste: selectedPoste, site_id: '<?= $site['id']; ?>' },
                dataType: 'json',
                success: function(data) {
                    $.each(data, function(i, operator) {
                        $("#operateur").append('<option value="' + operator.id + '">' + operator.name + '</option>');
                    });
                    $("#operateur").prop("disabled", false);
                },
                error: function(error) {
                    console.log('Erreur lors de la récupération des opérateurs:', error);
                }
            });
        }
    });
});

function previewImage(file, containerId) {
    const reader = new FileReader();
    reader.onload = function(event) {
        const imgElement = document.createElement("img");
        imgElement.src = event.target.result;
        imgElement.style.maxWidth = '200px';
        imgElement.style.maxHeight = '200px';
        imgElement.style.objectFit = 'contain';
        document.getElementById(containerId).appendChild(imgElement);
    };
    reader.readAsDataURL(file);
}

document.querySelectorAll('#photo-uploads-container input[type="file"]').forEach(input => {
    input.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            previewImage(this.files[0], 'preview-container');
        }
    });
});
</script>

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
        </button>

            <button class="buttonm" onclick="window.location.href='http://192.168.12.41/dashboardlignes.php?ligne_id=<?= $ligne_id; ?>'">
                <span>Retour Menu</span>
             </button>

    </form>
</div>

</body>
</html>
