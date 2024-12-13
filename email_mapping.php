<?php
session_start();
require_once "db_config.php";

// Vérifier si l'utilisateur est connecté et est un administrateur
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || $_SESSION['is_admin'] != 1) {
    header('Location: login.php');
    exit;
}

$message = "";

// Gérer la soumission du formulaire de mappage
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_mapping'])) {
    $service_id = $_POST['service_id'];
    $emails_input = $_POST['email']; // Récupère la chaîne d'e-mails
    $cc_email = $_POST['cc_email'] ?? ''; // Utiliser une chaîne vide si aucun CC

    // Séparer et valider les e-mails principaux
    $emails = explode(',', $emails_input);
    $validated_emails = [];
    foreach ($emails as $em) {
        $em_trimmed = trim($em);
        if (filter_var($em_trimmed, FILTER_VALIDATE_EMAIL)) {
            $validated_emails[] = $em_trimmed;
        } else {
            $message = "Erreur : une adresse e-mail principale est invalide.";
            break;
        }
    }

    $email = implode(',', $validated_emails);

    // Validation pour les e-mails CC
    $cc_emails = explode(',', $cc_email);
    $validated_cc_emails = [];
    foreach ($cc_emails as $cc) {
        $cc_trimmed = trim($cc);
        if (filter_var($cc_trimmed, FILTER_VALIDATE_EMAIL)) {
            $validated_cc_emails[] = $cc_trimmed;
        }
    }

    $cc_email = implode(',', $validated_cc_emails);

    $stmt = $pdo->prepare("REPLACE INTO service_email_map (service_id, email, cc_email) VALUES (?, ?, ?)");
    if ($stmt->execute([$service_id, $email, $cc_email])) {
        $message = "Mapping mis à jour avec succès.";
    } else {
        $message = "Erreur lors de la mise à jour du mapping.";
    }
}

$stmt = $pdo->query("SELECT service_email_map.service_id, service_email_map.email, service_email_map.cc_email, services.name as service_name, sites.name as site_name FROM service_email_map LEFT JOIN services ON service_email_map.service_id = services.id LEFT JOIN sites ON services.site_id = sites.id");
$mappings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->query("SELECT services.id as service_id, services.name as service_name, sites.name as site_name FROM services LEFT JOIN sites ON services.site_id = sites.id");
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mappage des Emails de Service</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</head>

<style>
        

        h2 {
            font-size: 2rem;
            margin-bottom: 20px;
            color: #333;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            border-right: 3px solid #333; /* Curseur clignotant */
            width: 0;
            animation: typing 3s steps(30, end) forwards, blink-caret 0.75s step-end infinite;
            padding-bottom: 2%;
        }

        @keyframes typing {
            from { width: 0; }
            to { width: 100%; }
        }

        @keyframes blink-caret {
            from, to { border-color: transparent; }
            50% { border-color: #333; }
        }

        .no-cursor {
            user-select: none; /* Désactive la sélection du texte */
            pointer-events: none; /* Désactive toute interaction avec la souris */
            border-right: none; /* Retire le curseur clignotant */
        }

        
    </style>
<body>
<?php
       if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        // Si l'utilisateur est un administrateur, afficher le bouton pour accéder à l'administration
        if ($_SESSION['is_admin'] == 1) { include 'menu.php';
        }
    }
    ?>
    <div class="containermap mt-4" style="background-color: white; border: 4px solid grey">
    <a href="admin.php"><img src="./images/boutonRetour.jpg" alt="Bouton Retour" style="width: 5%;" class="back-btn"></a>     

        <h2 id="title">Mappage des Emails de Service</h2>
        <?php if ($message): ?>
            <div class="alert alert-info">
                <?= htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <!-- Bouton pour ouvrir la modale d'ajout -->
<div class="mb-3">
    <button class="btn btn-primary" data-toggle="modal" data-target="#modalAdd" style="margin-left: 43%">Ajouter un Mappage</button>
</div>

        <div class="modal fade" id="modalAdd" tabindex="-1" aria-labelledby="modalAddLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalAddLabel">Ajouter un Mappage</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="email_mapping.php" method="post">
                <div class="modal-body" >
                    <div class="form-group">
                        <label for="service_id_add">Service</label>
                        <select class="form-control" id="service_id_add" name="service_id" required>
                            <option value="">Sélectionnez un service</option>
                            <?php foreach ($services as $service): ?>
                                <option value="<?= htmlspecialchars($service['service_id']); ?>">
                                    <?= htmlspecialchars($service['service_name']) . " (" . htmlspecialchars($service['site_name']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Email Principal</label>
                        <input type="text" class="form-control" name="email" placeholder="Email Principal" required>
                    </div>
                    <div class="form-group">
                        <label>Email CC (séparer par une virgule)</label>
                        <textarea class="form-control email-cc" name="cc_email" placeholder="Email CC"></textarea>

                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                    <button type="submit" name="update_mapping" class="btn btn-primary">Ajouter</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="containermap mt-4">
        <table class="table">
            <thead>
                <tr>
                    <th>Service</th>
                    <th>Site</th>
                    <th>Email Principal</th>
                    <th>Email CC</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($mappings as $map): ?>
                <tr>
                    <td><?= htmlspecialchars($map['service_name']); ?></td>
                    <td><?= htmlspecialchars($map['site_name']); ?></td>
                    <td><?= htmlspecialchars($map['email']); ?></td>
                    <td><?= htmlspecialchars($map['cc_email']); ?></td>
                    <td>
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalEdit-<?= $map['service_id']; ?>">
                            Modifier
                        </button>
                        <form action="delete_mapping.php" method="post" style="display:inline-block;">
                            <input type="hidden" name="service_id" value="<?= htmlspecialchars($map['service_id']); ?>">
                            <button type="submit" name="delete_mapping" class="btn btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <!-- Modales de modification pour chaque service -->
        <?php foreach ($mappings as $map): ?>
        <div class="modal fade" id="modalEdit-<?= $map['service_id']; ?>" tabindex="-1" aria-labelledby="modalEditLabel-<?= $map['service_id'];?>" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEditLabel-<?= $map['service_id']; ?>">Modifier Mappage: <?= htmlspecialchars($map['service_name']); ?> - <?= htmlspecialchars($map['site_name']); ?></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form action="email_mapping.php" method="post">
                        <div class="modal-body">
                            <input type="hidden" name="service_id" value="<?= htmlspecialchars($map['service_id']); ?>">
                            <div class="form-group">
                                <label>Email Principal</label>
                                <input type="text" class="form-control" name="email" placeholder="Email Principal" value="<?= htmlspecialchars($map['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Email CC (séparer par une virgule)</label>
                                <textarea class="form-control email-cc" name="cc_email" placeholder="Email CC"><?= htmlspecialchars($map['cc_email']); ?></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Fermer</button>
                            <button type="submit" name="update_mapping" class="btn btn-primary">Enregistrer les changements</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</body>

<script>
        document.addEventListener('DOMContentLoaded', function() {
    const titleElement = document.getElementById('title');

    // Ajouter un écouteur d'événement pour la fin de l'animation de saisie
    titleElement.addEventListener('animationend', function() {
        // Ajouter la classe pour désactiver le curseur après l'animation
        titleElement.classList.add('no-cursor');
    });
});
</script>
</html>
