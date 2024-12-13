<?php
require_once "db_config.php";
require 'vendor/autoload.php';
require 'vendor/mpdf/mpdf/src/Mpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Mpdf\Mpdf;

// ... Autres configurations et installations...
$currentDate = date('d/m/Y');
$yesterdayDate = date('d/m/Y', strtotime('-1 day'));
$report = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body>";
$report .= "<style>
            body {
                font-family: 'Arial', sans-serif;
            }

            h1, h2 {
                color: #333; /* Couleur du texte */
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 15px;
            }

            th, td {
                border: 1px solid #ddd; /* Bordures */
                padding: 8px; /* Espacement interne */
                text-align: left;
            }

            th {
                background-color: #f2f2f2; /* Couleur de fond pour les en-têtes de tableau */
            }

            ul {
                list-style: none;
                padding: 0;
            }

            li {
                border-bottom: 1px solid #ddd; /* Trait fin entre chaque note ou mémo */
                padding-bottom: 10px;
                margin-bottom: 10px;
            }
            </style>";
$report .= "<h1>Rapport Gambini 1 pour le {$yesterdayDate}</h1>";

// Récupération des notes créées aujourd'hui pour la ligne Gambini
$notesCreatedTodayQuery = "SELECT notes.*, sites.name AS site_name, lignes.name AS ligne_name, services.name AS service_name, postes.name AS poste_name
                            FROM notes 
                            LEFT JOIN sites ON notes.site_id = sites.id 
                            LEFT JOIN lignes ON notes.ligne_id = lignes.id
                            LEFT JOIN services ON notes.service_id = services.id
                            LEFT JOIN postes ON notes.poste = postes.id
                            WHERE notes.created_at >= DATE_SUB(NOW(), INTERVAL 25 HOUR) AND lignes.name = 'Gambini 1'
                            ORDER BY notes.id ASC";



// Récupération des notes archivées aujourd'hui pour la ligne Gambini
$notesArchivedTodayQuery = "SELECT notes.*, sites.name AS site_name, lignes.name AS ligne_name, services.name AS service_name
                                FROM notes 
                                LEFT JOIN sites ON notes.site_id = sites.id 
                                LEFT JOIN lignes ON notes.ligne_id = lignes.id
                                LEFT JOIN services ON notes.service_id = services.id
                                WHERE notes.archived = 1 
                                    AND notes.created_at >= DATE_SUB(NOW(), INTERVAL 25 HOUR) AND lignes.name = 'Gambini 1'
                                ORDER BY notes.id ASC";


// Récupération des notes créées mais non archivées des jours précédents pour la ligne Gambini
$notesCreatedNotArchivedQuery = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name
                                 FROM notes 
                                 LEFT JOIN sites ON notes.site_id = sites.id 
                                 LEFT JOIN lignes ON notes.ligne_id = lignes.id
                                 LEFT JOIN services ON notes.service_id = services.id
                                 WHERE notes.archived = 0 AND DATE(notes.created_at) < CURDATE() AND lignes.name = 'Gambini 1' AND notes.poste IN ('Bobineur', 'Emballeur')
                                 ORDER BY notes.id DESC";


// Récupération des mémos pour la ligne Gambini
$memosCreatedTodayQuery = "SELECT memos.*, lignes.name as ligne_name 
                            FROM memos 
                            JOIN lignes ON memos.ligne_id = lignes.id
                            WHERE memos.created_at >= DATE_SUB(NOW(), INTERVAL 25 HOUR) AND lignes.name = 'Gambini 1'
                            ORDER BY memos.created_at ASC";

// Exécutez les requêtes et générez le rapport
$pdo = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

$notesCreatedToday = generateNoteSection($pdo, $notesCreatedTodayQuery);
$archivedToday = generateNoteSection($pdo, $notesArchivedTodayQuery);
$createdNotArchived = generateNoteSection($pdo, $notesCreatedNotArchivedQuery);

$createdMemosToday = generateMemoSection($pdo, $memosCreatedTodayQuery);

// Ajoutez les sections à votre rapport
$report .= "<h2><img src='logonote.png' alt='Logonote' style='max-width: 30px;'> - Notes créées aujourd'hui ({$notesCreatedToday['count']}):</h2>";
$report .= generateNoteSectionHtml($notesCreatedToday['notes']);
$report .= "<hr>"; // Séparateur horizontal

$report .= "<h2><img src='logo24.png' alt='Logo24' style='max-width: 30px;'> - Relèves créées c'est derniere 24H ({$createdMemosToday['count']}):</h2>";
$report .= generateMemoSectionHtml($createdMemosToday['memos']);
$report .= "<hr>"; // Séparateur horizontal;

$report .= "<h2><img src='logook.png' alt='Logoprod' style='max-width: 30px;'> - Notes archivées aujourd'hui ({$archivedToday['count']}):</h2>";
$report .= generateNoteSectionHtml($archivedToday['notes']);
$report .= "<hr>"; // Séparateur horizontal

$report .= "<h2><img src='logoprod.png' alt='Logoprod' style='max-width: 30px;'> - Notes en cours ({$createdNotArchived['count']}):</h2>";
$report .= generateNoteSectionHtml($createdNotArchived['notes']);
$report .= "</body></html>";

// Initialize mPDF after $report is defined
$mpdf = new Mpdf();
$mpdf->WriteHTML($report);

// Définir le nom du fichier avec la date de la veille
$pdfFileName = 'rapportgambini1_' . date('Ymd', strtotime('-1 day')) . '.pdf';
$pdfFilePath = $pdfFileName;

// Output avec le nom de fichier correct
$mpdf->Output($pdfFilePath, 'F');


// Créez une instance de PHPMailer
$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = 'mphygiene-com.mail.protection.outlook.com';
$mail->SMTPAuth = false;
$mail->SMTPSecure = 'tls';
$mail->Port = 25;
$mail->setFrom('rapportprod@mphygiene.com', 'Rapport Quotidien Gambini 1');

$mail->addAddress('antony.vallin@mphygiene.com', 'Antony Vallin');
$mail->isHTML(true);
$mail->Subject = "Rapport quotidien - Gambini 1 - Date: {$yesterdayDate}";
$mail->CharSet = 'UTF-8';
$mail->AddAttachment($pdfFilePath);

$mail->Body = "Bonjour<br>Ci-Joint le rapport quotidien pour la Gambini 1 de la journée du {$yesterdayDate} ";

if(!$mail->send()) {
    echo 'Le message n\'a pas pu être envoyé.';
    echo 'Erreur PHPMailer: ' . $mail->ErrorInfo;
} else {
    echo 'Le message a été envoyé, vous pouvez fermer cet onglet';
}

function generateNoteSection($pdo, $query) {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($results);

    return array('notes' => $results, 'count' => $count);
}

function generateMemoSection($pdo, $query) {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($results);

    return array('memos' => $results, 'count' => $count);
}

function generateNoteSectionHtml($notes) {
    $sectionHtml = "<ul>";
    $currentWorkstation = null;

    foreach ($notes as $note) {
        // Vérifier si la workstation a changé
        if ($note['poste'] != $currentWorkstation) {
            // Fermer la liste précédente s'il y en a une
            if ($currentWorkstation !== null) {
                $sectionHtml .= "</ul>";
            }


        }

        // Continuer à générer le HTML pour chaque note
        $archivedInfo = getArchivedInfoHtml($note);
        $createdDate = getFormattedDate($note['created_at']);

        $sectionHtml .= "<li><i>- Créée le:</i> " . $createdDate . $archivedInfo . "<br><i> - Operateur:</i> {$note['operator_name']}<br><i>- Site:</i> {$note['site_name']}<br> <i>- Ligne:</i> {$note['ligne_name']}<br><i> - Service:</i> {$note['service_name']}<br> - <b>" . nl2br(htmlspecialchars($note['note_text'])) . "</b></li><br>";
    }

    $sectionHtml .= "</ul>";

    return $sectionHtml;
}

function generateMemoSectionHtml($memos) {
    $sectionHtml = "<ul>";
    foreach ($memos as $memo) {
        $formattedDate = getFormattedDate($memo['created_at']);

        $sectionHtml .= "<li><i>- Créée le</i> " . $formattedDate . "<br><i>- Ligne:</i> {$memo['ligne_name']}<br> - <b>" . nl2br(htmlspecialchars($memo['memo_text'])) . "</b></li><br>";
    }
    $sectionHtml .= "</ul>";

    return $sectionHtml;
}

function getArchivedInfoHtml($note) {
    $archivedInfo = '';

    // Vérifier si la date est non nulle avant de créer l'objet DateTime
    $commentDate = $note['comment_time'] ? new DateTime($note['comment_time']) : null;

    if ($note['archived'] && $commentDate) {
        $archivedInfo = " <i>- Archivée le:</i> " . $commentDate->format('d/m/Y') . 
                        " <i>- Initiales: </i>" . htmlspecialchars($note['commenter_initials']) . 
                        " <i>- Commentaire:</i> " . nl2br(htmlspecialchars($note['comment']));
    }

    return $archivedInfo;
}

function getFormattedDate($date) {
    // Vérifier si la date est non nulle avant de créer l'objet DateTime
    $dateTime = $date ? new DateTime($date) : null;

    // Formatage de la date avec l'heure
    return $dateTime ? $dateTime->format('d/m/Y H:i') : 'Date non disponible';
}
?>
