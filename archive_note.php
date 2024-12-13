<?php
session_start();
require_once "db_config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $commenter_initials = filter_input(INPUT_POST, 'initials', FILTER_SANITIZE_STRING);
    $commentaire = filter_input(INPUT_POST, 'commentaire', FILTER_SANITIZE_STRING);
    $note_id = filter_input(INPUT_POST, 'note_id', FILTER_SANITIZE_NUMBER_INT);
    $user_id = $_SESSION['user_id']; // Supposé que l'ID de l'utilisateur est dans la session
    $comment_time = date('Y-m-d H:i:s'); // L'heure actuelle

    var_dump($note_id, $commentaire, $commenter_initials);


    // Préparer la requête pour archiver la note
    $sql = "UPDATE notes SET comment = ?, commenter_initials = ?, user_id = ?, comment_time = ?, archived = 1  WHERE id = ? AND archived = 0";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$commentaire, $commenter_initials, $user_id, $comment_time, $note_id]);
    var_dump($commentaire, $commenter_initials, $user_id, $comment_time, $note_id);


    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$commentaire, $commenter_initials, $user_id, $comment_time, $note_id]);

        if ($stmt->rowCount() > 0) {
            // Si la requête a modifié une ligne, afficher un message de succès
            $_SESSION['flash_message'] = 'Note archivée avec succès.';
        } else {
            // Si aucune ligne n'a été modifiée, afficher un message d'erreur
            $_SESSION['flash_message'] = 'Erreur: La note n\'a pas pu être archivée. Veuillez vérifier si la note existe ou est déjà archivée.';
        }
        
        // Vérifier si la requête a affecté des lignes
        if ($stmt->rowCount() > 0) {
            // La note a été archivée avec succès
            $_SESSION['flash_message'] = 'Note archivée avec succès.';

            // Récupérer les détails de la note
            $stmtNote = $pdo->prepare("SELECT * FROM notes WHERE id = ?");
            $stmtNote->execute([$note_id]);
            $note = $stmtNote->fetch();

            if ($note) {
                // Récupérer les emails associés au service
                $service_id = $note['service_id'];
                $stmtEmails = $pdo->prepare("SELECT * FROM service_email_map WHERE service_id = ?");
                $stmtEmails->execute([$service_id]);
                $emails = $stmtEmails->fetchAll();

                // Préparer l'email à envoyer
                $email_subject = "Commentaire ajouté et note archivée";
                $email_body = "<html><body>";
                $email_body .= "<p>Un commentaire a été ajouté à la note suivante :</p>";
                $email_body .= "<p><strong>Operateur :</strong> {$note['operator_name']}</p>";
                $email_body .= "<p><strong>Contenu de la note :</strong><br/>{$note['note_text']}</p>";
                $email_body .= "<p><strong>Commentaire :</strong> {$commentaire}</p>";
                $email_body .= "<p><strong>Par :</strong> {$commenter_initials}</p>";
                $email_body .= "<p><strong>Date du commentaire :</strong> {$comment_time}</p>";
                $email_body .= "</body></html>";

                // Envoi de l'email
                $mail = new PHPMailer(true);
                try {
                    $mail->isHTML(true);
                    $mail->ContentType = 'text/html';
                    $mail->CharSet = 'UTF-8';
                    $mail->isSMTP();
                    $mail->Host = 'mphygiene-com.mail.protection.outlook.com'; // Modifier avec votre serveur SMTP
                    $mail->SMTPAuth = false;
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 25;
                    $mail->setFrom('Noteprod@mphygiene.com', 'Note de Production');

                    // Ajouter les adresses email des destinataires
                    foreach ($emails as $email) {
                        $primary_emails = explode(',', $email['email']);
                        foreach ($primary_emails as $primary_email) {
                            if (!empty($primary_email)) {
                                $mail->addAddress(trim($primary_email));
                            }
                        }
                        if (!empty($email['cc_email'])) {
                            $cc_emails = explode(',', $email['cc_email']);
                            foreach ($cc_emails as $cc_email) {
                                if (!empty($cc_email)) {
                                    $mail->addCC(trim($cc_email));
                                }
                            }
                        }
                    }

                    $mail->Subject = $email_subject;
                    $mail->Body = $email_body;

                    $mail->send();
                    $_SESSION['flash_message'] .= " Email de notification envoyé.";
                } catch (Exception $e) {
                    $_SESSION['flash_message'] .= " Erreur lors de l'envoi de l'email: {$mail->ErrorInfo}";
                }
            } else {
                $_SESSION['flash_message'] .= " Note non trouvée pour l'envoi de l'email.";
            }
        } else {
            // Si aucune ligne n'a été mise à jour, la note est déjà archivée ou l'ID est incorrect
            $_SESSION['flash_message'] = 'Aucune note mise à jour. Vérifiez que la note existe et n\'est pas déjà archivée.';
        }
    } catch (PDOException $e) {
        // Capture des erreurs de la base de données
        $_SESSION['flash_message'] = "Erreur de base de données: " . $e->getMessage();
    }

    // Redirection
    header("Location: notes.php");
    exit();
}
?>
