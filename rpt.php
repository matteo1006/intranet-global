<?php
require_once "db_config.php";
require 'vendor/autoload.php';
require 'vendor/mpdf/mpdf/src/Mpdf.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Mpdf\Mpdf;

// ... Autres configurations et installations...
$currentDate = date('d/m/Y');
$report = "<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body>";
$report .= "<style>
            body {
                font-family: 'Arial', sans-serif;
            }

            h1, h2, h3 {
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
$report .= "<h1>Rapport Gambini 1 pour le {$currentDate}</h1>";

// Récupération des notes créées aujourd'hui pour la ligne Gambini
$notesCreatedTodayQuery = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name, notes.workstation
                           FROM notes 
                           LEFT JOIN sites ON notes.site_id = sites.id 
                           LEFT JOIN lignes ON notes.ligne_id = lignes.id
                           LEFT JOIN services ON notes.service_id = services.id
                           WHERE DATE(notes.created_at) = CURDATE() AND lignes.name = 'Gambini 1'
                           ORDER BY notes.workstation ASC, notes.id ASC";

// Récupération des notes archivées aujourd'hui pour la ligne Gambini
$notesArchivedTodayQuery = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name, notes.workstation
                            FROM notes 
                            LEFT JOIN sites ON notes.site_id = sites.id 
                            LEFT JOIN lignes ON notes.ligne_id = lignes.id
                            LEFT JOIN services ON notes.service_id = services.id
                            WHERE notes.archived = 1 AND DATE(notes.comment_time) = CURDATE() AND lignes.name = 'Gambini 1'
                            ORDER BY notes.workstation ASC, notes.id ASC";

// Récupération des notes créées mais non archivées des jours précédents pour la ligne Gambini
$notesCreatedNotArchivedQuery = "SELECT notes.*, sites.name as site_name, lignes.name as ligne_name, services.name as service_name, notes.workstation
                                 FROM notes 
                                 LEFT JOIN sites ON notes.site_id = sites.id 
                                 LEFT JOIN lignes ON notes.ligne_id = lignes.id
                                 LEFT JOIN services ON notes.service_id = services.id
                                 WHERE notes.archived = 0 AND DATE(notes.created_at) < CURDATE() AND lignes.name = 'Gambini 1'
                                 ORDER BY notes.workstation ASC, notes.id DESC";

// Récupération des mémos pour la ligne Gambini
$memosCreatedTodayQuery = "SELECT memos.*, lignes.name as ligne_name 
                            FROM memos 
                            JOIN lignes ON memos.ligne_id = lignes.id
                            WHERE memos.created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR) AND lignes.name = 'Gambini 1'
                            ORDER BY memos.created_at ASC";

// Exécutez les requêtes et générez le rapport
$pdo = new PDO($dsn, $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));

$notesCreatedToday = generateNoteSection($pdo, $notesCreatedTodayQuery);
$archivedToday = generateNoteSection($pdo, $notesArchivedTodayQuery);
$createdNotArchived = generateNoteSection($pdo, $notesCreatedNotArchivedQuery);

$memosData = generateMemoSection($pdo, $memosCreatedTodayQuery);

// Assurez-vous que $memosData['memos'] est un tableau avant de l'utiliser
$createdMemosToday = is_array($memosData['memos']) ? $memosData['memos'] : [];


// Ajoutez les sections à votre rapport
// Assurez-vous que $notesCreatedToday est un tableau avant de l'utiliser
$createdNotesToday = is_array($notesCreatedToday) ? $notesCreatedToday : [];

$report .= generateNoteSectionHtml("Notes créées aujourd'hui", $createdNotesToday);

$report .= generateMemoSectionHtml("Relèves créées c'est dernière 24H", $createdMemosToday);

$report .= generateNoteSectionHtml("Notes archivées aujourd'hui", $archivedToday);
// Assurez-vous que $createdNotArchived est un tableau avant de l'utiliser
$createdNotArchivedNotes = is_array($createdNotArchived) ? $createdNotArchived : [];

// Assurez-vous que $createdNotArchived est un tableau avant de l'utiliser
$createdNotArchivedNotes = is_array($createdNotArchived) ? $createdNotArchived : [];

// Assurez-vous que $createdNotArchivedNotes['Notes en cours'] est un tableau avant de l'utiliser
$notesEnCours = isset($createdNotArchivedNotes['Notes en cours']) ? $createdNotArchivedNotes['Notes en cours'] : [];

$report .= generateNoteSectionHtml("Notes en cours", $notesEnCours);


$report .= "</body></html>";

// Initialize mPDF after $report is defined
$mpdf = new Mpdf();
$mpdf->WriteHTML($report);
$pdfFilePath = 'rapportgambini1{$currentDate}.pdf';
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
$mail->Subject = "Rapport quotidien - Gambini 1 - Date: {$currentDate}";
$mail->CharSet = 'UTF-8';
$mail->AddAttachment($pdfFilePath, 'rapportgambini1{$currentDate}.pdf');

$mail->Body = "Bonjour<br>Ci-Joint le rapport quotidien pour la Gamibini 1 de la journée du {$currentDate} ";

if(!$mail->send()) {
    echo 'Le message n\'a pas pu être envoyé.';
    echo 'Erreur PHPMailer: ' . $mail->ErrorInfo;
} else {
    echo 'Le message a été envoyé';
}

function generateNoteSection($pdo, $query) {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Groupement des résultats par workstation et catégorie
    $groupedResults = [];
    foreach ($results as $note) {
        $workstation = $note['workstation'];
        $category = determineCategory($note);
        $groupedResults[$category][$workstation][] = $note;
    }

    return $groupedResults;
}

function generateMemoSection($pdo, $query) {
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = count($results);

    return array('memos' => $results, 'count' => $count);
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

function generateNoteSectionHtml($sectionTitle, $groupedNotes) {
    $sectionHtml = "<h2>{$sectionTitle}</h2>";
    
    // Parcours des catégories
    foreach ($groupedNotes as $category => $workstationGroups) {
        $sectionHtml .= "<h3>{$category}</h3>";

        // Parcours des sous-groupes de workstations
        foreach ($workstationGroups as $workstation => $notes) {
            $sectionHtml .= "<h4>Workstation: {$workstation}</h4>";
            $sectionHtml .= "<ul>";
            foreach ($notes as $note) {
                $archivedInfo = getArchivedInfoHtml($note);
                $createdDate = getFormattedDate($note['created_at']);

                $sectionHtml .= "<li><i>- Créée le:</i> " . $createdDate . $archivedInfo . "<br><i> - Operateur:</i> {$note['operator_name']}<br><i>- Site:</i> {$note['site_name']}<br> <i>- Ligne:</i> {$note['ligne_name']}<br><i> - Service:</i> {$note['service_name']}<br> - <b>" . nl2br(htmlspecialchars($note['note_text'])) . "</b></li><br>";
            }
            $sectionHtml .= "</ul>";
        }
    }

    return $sectionHtml;
}

function determineCategory($note) {
    // Déterminez la catégorie en fonction de vos critères
    // Dans cet exemple, la catégorie est basée sur la date et l'archivage
    if ($note['archived']) {
        return "Archivées Aujourd'hui";
    } elseif (strtotime($note['created_at']) >= strtotime('today midnight')) {
        return "Créées Aujourd'hui";
    } else {
        return "Créées mais non Archivées";
    }
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
