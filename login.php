<?php
ob_start();
session_start();
require 'db_config.php';

include 'menu.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    $_SESSION['loggedin'] = true;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['is_admin'] = $user['is_admin']; // Stocker la valeur d'administrateur dans la session

    header('Location: index.php');
    exit;
} else {
    $login_error = "Nom d'utilisateur ou mot de passe incorrect!";
}
}
ob_end_flush();

?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="styles.css">
</head>
<style>
    @import url('https://fonts.googleapis.com/css?family=Poppins');

/* BASIC */





h2 {
  text-align: center;
  font-size: 16px;
  font-weight: 600;
  text-transform: uppercase;
  display:inline-block;
  margin: 40px 8px 10px 8px; 
  color: #cccccc;
}



/* STRUCTURE */

.wrapper {
  display: flex;
  align-items: center;
  flex-direction: column; 
  justify-content: center;
  width: 50%;
  min-height: 150%;
  padding: 20px;
}

#formContent {
  -webkit-border-radius: 10px 10px 10px 10px;
  border-radius: 10px 10px 10px 10px;
  background: #fff;
  padding: 30px;
  width: 90%;
  max-width: 700px;
  position: relative;
  padding: 0px;
  -webkit-box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
  box-shadow: 0 30px 60px 0 rgba(0,0,0,0.3);
  text-align: center;
  margin-left: 80%;
  margin-top: 10%;
}
#formContent:hover{
    transform: translateY(-5px); /* Légère élévation */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Accentuation de l'ombre */
    border: 2px solid #34495e;
}

#formFooter {
  background-color: #f6f6f6;
  border-top: 1px solid #dce8f1;
  padding: 25px;
  text-align: center;
  -webkit-border-radius: 0 0 10px 10px;
  border-radius: 0 0 10px 10px;
}



/* TABS */

h2.inactive {
  color: #cccccc;
}

h2.active {
  color: #0d0d0d;
  border-bottom: 2px solid #5fbae9;
}



/* FORM TYPOGRAPHY*/

input[type=button], input[type=submit], input[type=reset]  {
  background-color: #56baed;
  border: none;
  color: white;
  padding: 15px 80px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  text-transform: uppercase;
  font-size: 13px;
  -webkit-box-shadow: 0 10px 30px 0 rgba(95,186,233,0.4);
  box-shadow: 0 10px 30px 0 rgba(95,186,233,0.4);
  -webkit-border-radius: 5px 5px 5px 5px;
  border-radius: 5px 5px 5px 5px;
  margin: 5px 20px 40px 20px;
  -webkit-transition: all 0.3s ease-in-out;
  -moz-transition: all 0.3s ease-in-out;
  -ms-transition: all 0.3s ease-in-out;
  -o-transition: all 0.3s ease-in-out;
  transition: all 0.3s ease-in-out;
}

input[type=button]:hover, input[type=submit]:hover, input[type=reset]:hover  {
  background-color: #39ace7;
}

input[type=button]:active, input[type=submit]:active, input[type=reset]:active  {
  -moz-transform: scale(0.95);
  -webkit-transform: scale(0.95);
  -o-transform: scale(0.95);
  -ms-transform: scale(0.95);
  transform: scale(0.95);
}

input[type=text] {
  background-color: #f6f6f6;
  border: none;
  color: #0d0d0d;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 5px;
  width: 85%;
  border: 2px solid #f6f6f6;
  -webkit-transition: all 0.5s ease-in-out;
  -moz-transition: all 0.5s ease-in-out;
  -ms-transition: all 0.5s ease-in-out;
  -o-transition: all 0.5s ease-in-out;
  transition: all 0.5s ease-in-out;
  -webkit-border-radius: 5px 5px 5px 5px;
  border-radius: 5px 5px 5px 5px;
}

input[type=password] {
  background-color: #f6f6f6;
  border: none;
  color: #0d0d0d;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  margin: 5px;
  width: 85%;
  border: 2px solid #f6f6f6;
  -webkit-transition: all 0.5s ease-in-out;
  -moz-transition: all 0.5s ease-in-out;
  -ms-transition: all 0.5s ease-in-out;
  -o-transition: all 0.5s ease-in-out;
  transition: all 0.5s ease-in-out;
  -webkit-border-radius: 5px 5px 5px 5px;
  border-radius: 5px 5px 5px 5px;
}

input[type=text]:focus {
  background-color: #fff;
  border-bottom: 2px solid #5fbae9;
}

input[type=text]:placeholder {
  color: #cccccc;
}

input[type=password]:focus {
  background-color: #fff;
  border-bottom: 2px solid #5fbae9;
}

input[type=password]:placeholder {
  color: #cccccc;
}



/* ANIMATIONS */

/* Simple CSS3 Fade-in-down Animation */
.fadeInDown {
  -webkit-animation-name: fadeInDown;
  animation-name: fadeInDown;
  -webkit-animation-duration: 1s;
  animation-duration: 1s;
  -webkit-animation-fill-mode: both;
  animation-fill-mode: both;
}

@-webkit-keyframes fadeInDown {
  0% {
    opacity: 0;
    -webkit-transform: translate3d(0, -100%, 0);
    transform: translate3d(0, -100%, 0);
  }
  100% {
    opacity: 1;
    -webkit-transform: none;
    transform: none;
  }
}

@keyframes fadeInDown {
  0% {
    opacity: 0;
    -webkit-transform: translate3d(0, -100%, 0);
    transform: translate3d(0, -100%, 0);
  }
  100% {
    opacity: 1;
    -webkit-transform: none;
    transform: none;
  }
}

/* Simple CSS3 Fade-in Animation */
@-webkit-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@-moz-keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }

.fadeIn {
  opacity:0;
  -webkit-animation:fadeIn ease-in 1;
  -moz-animation:fadeIn ease-in 1;
  animation:fadeIn ease-in 1;

  -webkit-animation-fill-mode:forwards;
  -moz-animation-fill-mode:forwards;
  animation-fill-mode:forwards;

  -webkit-animation-duration:1s;
  -moz-animation-duration:1s;
  animation-duration:1s;
}

.fadeIn.first {
  -webkit-animation-delay: 0.4s;
  -moz-animation-delay: 0.4s;
  animation-delay: 0.4s;
}

.fadeIn.second {
  -webkit-animation-delay: 0.6s;
  -moz-animation-delay: 0.6s;
  animation-delay: 0.6s;
  width: 70%;
  margin-left: 15%;
  margin-top: 5%;
}

.fadeIn.third {
  -webkit-animation-delay: 0.8s;
  -moz-animation-delay: 0.8s;
  animation-delay: 0.8s;
  width: 70%;
  margin-left: 15%;
  margin-top: 2%;
  margin-bottom: 5%;
}

.fadeIn.fourth {
  -webkit-animation-delay: 1s;
  -moz-animation-delay: 1s;
  animation-delay: 1s;
}

.fadeIn.fourth:hover{
    color: white;
    background-color: #34495e;
}

/* Simple CSS3 Fade-in Animation */
.underlineHover:after {
  display: block;
  left: 0;
  bottom: -10px;
  width: 0;
  height: 2px;
  background-color: #56baed;
  content: "";
  transition: width 0.2s;
}

/* OTHERS */

*:focus {
    outline: none;
} 

#icon {
  width:60%;
}

* {
  box-sizing: border-box;
}


h3 {
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
    to { width: 87%; }
}

@keyframes blink-caret {
    from, to { border-color: transparent; }
    50% { border-color: #333; }
}
/* Apparence des boutons de lignes */
.button {
    display: inline-block;
    width: 200px; /* Plus grand pour plus de lisibilité */
    height: 150px;
    margin: 15px;
    background-color: #0B3F65;
    color: white;
    text-align: center;
    border-radius: 12px; /* Coins arrondis plus doux */
    line-height: 150px;
    font-size: 1.2rem;
    cursor: pointer;
    text-decoration: none;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    margin-left: 5%;

}

.no-cursor {
    user-select: none; /* Désactive la sélection du texte */
    pointer-events: none; /* Désactive toute interaction avec la souris */
    border-right: none; /* Retire le curseur clignotant */
}
</style>
<body>

<h3 id="title" style="margin-top: 7%; color: white; margin-right: 10%">Bienvenue sur l'espace Connexion</h3>


<div class="wrapper fadeInDown">
  <div id="formContent">
    <!-- Tabs Titles -->
    <h2 class="active"> Se connecter </h2>
    <h2 class="inactive underlineHover">Créer un compte </h2>

    <!-- Icon -->
    <div class="fadeIn first">
      
    </div>

    <!-- Formulaire de connexion -->
    <form action="login.php" method="post">
      <input type="text" id="username" class="fadeIn second" name="username" placeholder="Identifiant" required>
      <input type="password" id="password" class="fadeIn third" name="password" placeholder="Mot de passe" required>
      <input type="submit" class="fadeIn fourth" value="Connexion">
    </form>

    <!-- Afficher un message d'erreur si la connexion échoue -->
    <?php if (isset($login_error)): ?>
        <p style="color:red;"><?php echo $login_error; ?></p>
    <?php endif; ?>

    <!-- Remind Passowrd -->
    <div id="formFooter">
      <a class="underlineHover" onclick="showAlert()" href="#">Mot de passe oublié ?</a>
    </div>

  </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
    const titleElement = document.getElementById('title');

    // Ajouter un écouteur d'événement pour la fin de l'animation de saisie
    titleElement.addEventListener('animationend', function() {
        // Ajouter la classe pour désactiver le curseur après l'animation
        titleElement.classList.add('no-cursor');
    });
});

// Fonction pour afficher une alerte
function showAlert() {
        alert("Tans pis!");
    }
</script>
</body>
</html>
