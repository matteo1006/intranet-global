<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Assurez-vous que ce chemin correspond à votre structure de fichiers

if (isset($_POST['submit'])) {
    $operation = $_POST['operation'];
    $userMessage = $_POST['message'];
    $uploadDirectory = "uploads/"; // Répertoire de destination des fichiers téléchargés

    foreach ($_FILES["photo"]["error"] as $key => $error) {
        if ($error == UPLOAD_ERR_OK) {
            $tmp_name = $_FILES["photo"]["tmp_name"][$key];
            $name = basename($_FILES["photo"]["name"][$key]);
            move_uploaded_file($tmp_name, $uploadDirectory . $name);
            // Vous pouvez enregistrer le nom de fichier dans une base de données ou effectuer d'autres opérations ici.
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
        $mail->addAddress('', 'Antony VALLIN'); // Ajoutez un destinataire

        // Contenu de l'e-mail
        $mail->isHTML(true);
        $mail->isHTML(true);
        $mail->ContentType = 'text/html';
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Notification de ' . $operation;
        $mail->Body    = "Date et heure: " . date('Y-m-d H:i:s') . "<br>" .
                         "Opération: " . $operation . "<br>" .
                         "Message: " . nl2br($userMessage);

        // Pièce jointe
        if ($photo['error'] == UPLOAD_ERR_OK) {
            $mail->addAttachment($photo['tmp_name'], $photo['name']);
        }

        // ... après l'envoi de l'email

    $mail->send();
    header('Location: log.php?status=success'); // Assurez-vous que c'est 'index.html' ou le nom de votre fichier HTML
    exit;
} catch (Exception $e) {
    header('Location: log.php?status=error');
    exit;
}
}

?>