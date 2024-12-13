<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<script src="https://kit.fontawesome.com/c33bc23108.js" crossorigin="anonymous"></script>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Menu de Navigation</title>
<style>
  body {
    font-family: 'Arial', sans-serif;
    margin: 0;
    padding: 0;
  }
  #menu1 {
    width: 250px;
    background-color: #2c3e50;
    color: white;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
  }
  #menu1 img {
    width: 80px;
    display: block;
    margin: 20px auto;
  }
  #menu1 a.menu-link, .dropdown-content1 a {
    padding: 15px 20px;
    display: block;
    color: white;
    text-decoration: none;
    transition: background-color 0.3s ease, padding-left 0.3s ease;
  }
  #menu1 a.menu-link:hover, .dropdown-content1 a:hover {
    background-color: #34495e;
    padding-left: 30px;
  }
  .dropdown-content1 {
    display: none;
    background-color: #34495e;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    margin-left: 10px;
  }
  .dropdown1:hover .dropdown-content1 {
    display: block;
  }
  .date-time {
    padding: 10px;
    background: #34495e;
    text-align: center;
    color: #ecf0f1;
    font-size: 0.85em;
  }
  .menu-link i {
    margin-right: 10px;
  }
  /* Transition pour l'ouverture des menus déroulants */
  .dropdown1:hover .dropdown-content1 {
    display: block;
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }
  /* Animation des éléments */
  .dropdown-content1 a {
    opacity: 0;
    visibility: hidden;
    transition: all 0.2s ease-in;
    transform: translateY(-20px);
  }
  .dropdown1:hover .dropdown-content1 a {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
  }

  /* Responsive design */
  @media (max-width: 768px) {
    #menu1 {
        width: 100%; /* Le menu occupera toute la largeur de l'écran */
        height: auto; /* Laisser le menu s'adapter en hauteur */
        position: relative; /* Pour éviter les chevauchements */
    }
    #menu1 img {
      width: 30px;
    }
    #menu1 a.menu-link, .dropdown-content1 a {
      padding: 12px 10px;
    }
  }
</style>
</head>
<body>

<div id="menu1">
  <a href="index.php"><img src="./images/MPH1865-Logo-Blanc (002).png" alt="Logo" style="width: 50%; margin-bottom: 25%; margin-top: 25%"></a>
  <a href="404.php"><div id="date-time" class="date-time"></div></a>

  <?php
    if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
            echo '<div class="dropdown1">
                        <a href="admin.php" class="menu-link"><i class="fas fa-toolbox"></i> Administration &#9662;</a>
                        <div class="dropdown-content1">
                        <a href="add_user.php"><i class="fas fa-user-cog"></i> Ajouter un utilisateur
                        <a href="add_line.php"><i class="fas fa-ruler-horizontal"></i> Ajouter une ligne
                        <a href="add_operator.php"><i class="fas fa-user-tie"></i> Ajouter un opérateur
                        <a href="add_site.php"><i class="fas fa-industry"></i> Ajouter un site
                        <a href="email_mapping.php"><i class="fas fa-at"></i> Mappage adresse e-mail</a>
                        </div>
                    </div>';
        }

        echo '<a href="logout.php" class="menu-link"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>';
    } else {
        echo '<a href="login.php" class="menu-link"><i class="fas fa-sign-in-alt"></i> Connexion</a>';
    }  
  ?>
  <div class="dropdown1">
    <a href="#" class="menu-link"><i class="fas fa-book"></i> Notes &#9662;</a>
    <div class="dropdown-content1">
        <a href="notes.php"><i class="fas fa-spinner"></i> Toutes les notes </a>
        <a href="archive_page.php"><i class="fas fa-check-circle"></i> Archivées</a>
    </div>
  </div>
  <a href="memos.php" class="menu-link"><i class="fas fa-clipboard"></i> Relèves</a>
  <div class="dropdown1">
    <a href="#" class="menu-link"><i class="fas fa-warehouse"></i> Site &#9662;</a>
    <div class="dropdown-content1">
        <a href="davezieux.php"><i class="fas fa-d"></i> Davezieux</a>
        <a href="marenton.php"><i class="fas fa-m"></i> Marenton</a>
        <a href="pupil.php"><i class="fas fa-p"></i> Pupil</a>
        <a href="strambert.php"><i class="fas fa-s"></i> ST Rambert</a>
    </div>
  </div>
  <a href="dash.php" class="menu-link"><i class="fas fa-desktop"></i> Dashboard</a>
  

</div>

<script>
function updateDateTime() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();
    var day = now.getDate();
    var month = now.getMonth() + 1; // Les mois commencent à 0
    var year = now.getFullYear();

    // Ajoute un zéro devant les nombres < 10
    hours = hours < 10 ? '0' + hours : hours;
    minutes = minutes < 10 ? '0' + minutes : minutes;
    seconds = seconds < 10 ? '0' + seconds : seconds;
    day = day < 10 ? '0' + day : day;
    month = month < 10 ? '0' + month : month;

    var dateTimeString = day + '/' + month + '/' + year + ' ' + hours + ':' + minutes + ':' + seconds;

    // Met à jour le contenu de la div avec l'heure et la date actuelles
    document.getElementById('date-time').textContent = dateTimeString;
}

// Met à jour l'heure/date chaque seconde
setInterval(updateDateTime, 1000);
</script>

</body>
</html>
