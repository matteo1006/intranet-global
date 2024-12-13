<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Assurez-vous que ce chemin correspond à votre structure de fichiers

if (isset($_POST['submit'])) {
    $operation = $_POST['operation'];
    $userMessage = $_POST['message'];
    $uploadDirectory = "uploads/"; // Répertoire de destination des fichiers téléchargés

    // Tableau pour stocker les noms de fichiers téléchargés
    $uploadedFiles = [];

    foreach ($_FILES["photo"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["photo"]["tmp_name"][$key];
            $name = basename($_FILES["photo"]["name"][$key]);
            $destination = $uploadDirectory . $name;
            
            // Déplacez le fichier téléchargé vers le répertoire de destination
            move_uploaded_file($tmp_name, $destination);
            
            // Stockez le nom de fichier dans le tableau des fichiers téléchargés
            $uploadedFiles[] = $destination;
        }
    }

    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = 'mphygiene-com.mail.protection.outlook.com';
        $mail->SMTPAuth = false;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 25;

        // Destinataires
        $mail->setFrom('tablogsr2@mphygiene.com', 'Tablette SR2');  // L'adresse d'expéditeur
        $mail->addAddress('', 'Pierre Antoine QUIBLIER'); // Ajoutez un destinataire   'pierre-antoine.quiblier@mphygiene.com'

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->isHTML(true);
        $mail->ContentType = 'text/html';
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Notification de ' . $operation;
        $mail->Body    = "Date et heure: " . date('Y-m-d H:i:s') . "<br>" .
                         "Opération: " . $operation . "<br>" .
                         "Message: " . nl2br($userMessage);

        // Pièces jointes
        foreach ($uploadedFiles as $file) {
            $mail->addAttachment($file);
        }

        // Envoyer l'e-mail
        $mail->send();
        
        // Redirection en cas de succès
        header('Location: log.php?status=success'); // Assurez-vous que c'est 'index.html' ou le nom de votre fichier HTML
        exit;
    } catch (Exception $e) {
        // En cas d'erreur, rediriger vers une page d'erreur
        header('Location: log.php?status=error');
        exit;
    }
}
?>
