<?php
session_start();
require_once "db_config.php";

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    die("Accès refusé. Vous n'êtes pas administrateur.");
}

$message = "";

function insertInto($table, $data, $pdo) {
    $columns = implode(", ", array_keys($data));
    $placeholders = ":" . implode(", :", array_keys($data));
    $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($data);
}

$sitesDropdown = $pdo->query("SELECT * FROM sites")->fetchAll();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        if (isset($_POST['add_site'])) {
            $site_name = filter_input(INPUT_POST, 'site_name', FILTER_SANITIZE_STRING);
            insertInto('sites', ['name' => $site_name], $pdo);
            $message = "Site ajouté avec succès!";
            
        } elseif (isset($_POST['add_service'])) {
            $service_name = filter_input(INPUT_POST, 'service_name', FILTER_SANITIZE_STRING);
            insertInto('services', ['name' => $service_name], $pdo);
            $message = "Service ajouté avec succès!";
            
        } elseif (isset($_POST['add_line'])) {
            $line_name = filter_input(INPUT_POST, 'ligne_name', FILTER_SANITIZE_STRING);
            insertInto('lignes', ['name' => $line_name], $pdo);
            $message = "Ligne ajoutée avec succès!";
            
        } elseif (isset($_POST['add_operator_to_site'])) {
            $operator_name = filter_input(INPUT_POST, 'operator_name', FILTER_SANITIZE_STRING);
            $site_id = $_POST['site_for_operator'];

            if (!$operator_name || !$site_id) {
                $message = "Veuillez remplir tous les champs!";
            } else {
                $existing_operator = $pdo->prepare("SELECT * FROM operators WHERE name = :name");
                $existing_operator->execute([':name' => $operator_name]);
                $operator = $existing_operator->fetch();

                if (!$operator) {
                    insertInto('operators', ['name' => $operator_name], $pdo);
                    $operator_id = $pdo->lastInsertId();
                } else {
                    $operator_id = $operator['id'];
                }

                $add_to_site = $pdo->prepare("INSERT INTO some_table (operator_id, site_id) VALUES (:operator_id, :site_id)");
                $add_to_site->execute([':operator_id' => $operator_id, ':site_id' => $site_id]);

                $message = "Opérateur ajouté avec succès au site!";
            }
            
        } elseif (isset($_POST['add_user'])) {
            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $password = filter_input(INPUT_POST, 'password', FILTER_DEFAULT);
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $is_admin = filter_input(INPUT_POST, 'is_admin', FILTER_VALIDATE_INT);

            insertInto('users', ['username' => $username, 'password' => $hashed_password, 'is_admin' => $is_admin], $pdo);
            $message = "Utilisateur ajouté avec succès!";
            
        } else {
            $message = "Action non reconnue!";
        }

        $pdo->commit();

    } catch (Exception $e) {
        $pdo->rollback();
        $message = "Une erreur est survenue : " . $e->getMessage();
    }
}

$servicesData = $pdo->query("SELECT services.*, sites.name as site_name FROM services LEFT JOIN sites ON services.site_id = sites.id")->fetchAll();
$sitesData = $pdo->query("SELECT * FROM sites")->fetchAll();

$stmtLignes = $pdo->prepare("SELECT lignes.*, sites.name as site_name FROM lignes LEFT JOIN sites ON lignes.site_id = sites.id");
$stmtLignes->execute();
$lignesData = $stmtLignes->fetchAll();

$stmtUsers = $pdo->prepare("SELECT * FROM users");
$stmtUsers->execute();
$usersData = $stmtUsers->fetchAll();

$stmt = $pdo->prepare("SELECT * FROM operators");
$stmt->execute();
$operators = $stmt->fetchAll();


$stmt = $pdo->prepare("SELECT * FROM sites");
$stmt->execute();
$operators = $stmt->fetchAll();
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

<style>
:root {
  --bgcolor: #6e7684;
  --fcolor: #fff;
  --iconbgcolor: #fff;
}

html {
  box-sizing: border-box;
}

html *,
html *::before,
html *::after {
  box-sizing: inherit;
  padding: 0;
  margin: 0;
}

body {
  margin: 0;
  display: flex;
  height: 100vh;
  overflow: hidden;
  width: 100%;
  align-items: center;
  justify-content: center;
  background: var(--bgcolor);
  color: var(--fcolor);
}
.color {
  width: 100%;
  height: 100vh;
  position: fixed;
  top: 0;
  left: 0;
  opacity: 0;
}
.bubble {
  transition: all 2s cubic-bezier(0.3, 0.27, 0.07, 1.64);
  position: absolute;
  top: 10%;
  left: 10%;
  height: 1px;
  width: 1px;
  border-radius: 50%;
  cursor: pointer;
  background: radial-gradient(
    at 30% 20%,
    rgba(255, 255, 255, 0.9) 0%,
    rgba(255, 255, 255, 0) 50%,
    rgba(0, 0, 0, 0.1) 100%
  );

  box-shadow: 0 0 2px rgba(255, 255, 255, 0.7),
    inset 1px 1px 3px 1px rgba(255, 255, 255, 0.3),
    inset -1px -1px 3px 2px rgba(0, 0, 0, 0.1);
}
span.material-icons {
  display: block;
  position: absolute;
  right: 10px;
  bottom: 10px;
  font-size: 40px;
  border-radius: 100%;
  background: var(--iconbgcolor);
  color: var(--bgcolor);
  padding: 5px;
  cursor: pointer;
}
.bubble.pulsed {
  background: transparent;
  animation: pulse 1s 1 forwards;
}
.bubble.brandnew {
  opacity: 0;
  animation: show 1s 1 forwards;
}
@keyframes show {
  to {
    opacity: 1;
  }
}
@keyframes pulse {
  0% {
    box-shadow: 0 0 0 0 rgba(255, 255, 255, 0.4);
  }
  70% {
    box-shadow: 0 0 0 10px rgba(255, 255, 255, 0);
  }
  100% {
    box-shadow: 0 0 0 0 rgba(255, 255, 255, 0);
  }
}

main {
  position: relative;
  width: 35%;
  height: 75%;
  box-shadow: 0 3px 10px rgba(0,0,0,0.3);
  
  margin-top: -5%;
  
}

.item {
  width: 200px;
  height: 200px;
  list-style-type: none;
  position: absolute;
  top: 110%;
  transform: translateY(-50%);
  z-index: 1;
  background-position: center;
  background-size: cover;
  border-radius: 20px;
  box-shadow: 0 20px 30px rgba(255,255,255,0.3) inset;
  transition: transform 0.1s, left 0.75s, top 0.75s, width 0.75s, height 0.75s;

  &:nth-child(1), &:nth-child(2) {
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    transform: none;
    border-radius: 0;
    box-shadow: none;
    opacity: 1;

  }

  &:nth-child(3) { left: 50%; }
  &:nth-child(4) { left: calc(50% + 220px); }
  &:nth-child(5) { left: calc(50% + 440px); }
  &:nth-child(6) { left: calc(50% + 660px); opacity: 0; }
}

.content {
  width: min(30vw,400px);
  position: absolute;
  top: 75%;
  left: 1rem;
  transform: translateY(-50%);
  font: 400 0.85rem helvetica,sans-serif;
  color: white;
  text-shadow: 0 3px 8px rgba(0,0,0,0.5);
  opacity: 0;
  display: none;
  

  & .title {
    font-family: 'arial-black';
    text-transform: uppercase;
    margin-top: 80%;
  }

  & .description {
    line-height: 1.7;
    margin: 1rem 0 1.5rem;
    font-size: 0.8rem;
  }

  & button {
    width: fit-content;
    background-color: rgba(0,0,0,0.1);
    color: white;
    border: 2px solid white;
    border-radius: 0.25rem;
    padding: 0.75rem;
    cursor: pointer;
  }
}

.item:nth-of-type(2) .content {
  display: block;
  animation: show 0.75s ease-in-out 0.3s forwards;
  
}

@keyframes show {
  0% {
    filter: blur(5px);
    transform: translateY(calc(-50% + 75px));
  }
  100% {
    opacity: 1;
    filter: blur(0);
  }
}

.nav {
  position: absolute;
  bottom: 4rem;
  left: 50%;
  transform: translateX(-50%);
  z-index: 5;
  user-select: none;
  

  & .btn {
    background-color: rgba(255,255,255,0.5);
    color: rgba(0,0,0,0.7);
    border: 2px solid rgba(0,0,0,0.6);
    margin: 0 0.25rem;
    padding: 0.75rem;
    border-radius: 50%;
    cursor: pointer;

    &:hover {
      background-color: rgba(255,255,255,0.3);
    }
  }
}

@media (width > 650px) and (width < 900px) {
  .content {
    & .title        { font-size: 1rem; }
    & .description  { font-size: 0.7rem; }
    & button        { font-size: 0.7rem; }
  }
  .item {
    width: 160px;
    height: 270px;

    &:nth-child(3) { left: 50%; }
    &:nth-child(4) { left: calc(50% + 170px); }
    &:nth-child(5) { left: calc(50% + 340px); }
    &:nth-child(6) { left: calc(50% + 510px); opacity: 0; }
  }
}

@media (width < 650px) {
  .content {
    & .title        { font-size: 0.9rem; }
    & .description  { font-size: 0.65rem; }
    & button        { font-size: 0.7rem; }
  }
  .item {
    width: 130px;
    height: 220px;

    &:nth-child(3) { left: 50%; }
    &:nth-child(4) { left: calc(50% + 140px); }
    &:nth-child(5) { left: calc(50% + 280px); }
    &:nth-child(6) { left: calc(50% + 420px); opacity: 0; }
  }
}

        /* Conteneur principal avec animation au survol */
        .container {
            max-width: 350px;
            height: 40%;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 2px solid grey;
        }



        /* Animation au survol */
        .container:hover {
            transform: translateY(-10px); /* Légère élévation */
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2); /* Accentuation de l'ombre */
        }

        h1 {
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
        h5 {
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

</head>
<body>
<div class="color"></div>

<?php include 'menu.php'; ?>


<main>
  <ul class='slider'>
    <li class='item' style="background-image: url('./images/ajouterUser.webp')">
      <div class='content' >
        <h2 class='title'>"Ajouter un Utilisateur"</h2>
        <a href="add_user.php"><button>Accéder à la page</button></a>
      </div>
    </li>
    <li class='item' style="background-image: url('./images/ajouterLigne.webp')">
      <div class='content'>
        <h2 class='title'>"Ajouter une ligne"</h2>
        <a href="add_line.php"><button>Accéder à la page</button></a>
      </div>
    </li>
    <li class='item' style="background-image: url('./images/ajouterOperateur.webp')">
      <div class='content'>
        <h2 class='title'>"Ajouter un Opérateur"</h2>
        <a href="add_operator.php"><button>Accéder à la page</button></a>
      </div>
    </li>
    <li class='item' style="background-image: url('./images/ajouterSite.webp')">
      <div class='content'>
        <h2 class='title'>"Ajouter un Site"</h2>
        <a href="add_site.php"><button>Accéder à la page</button></a>
      </div>
    </li>
    <li class='item' style="background-image: url('./images/mapperEmails.webp')">
      <div class='content'>
        <h2 class='title'>"Mappage des Emails"</h2>
        <a href="email_mapping.php"><button>Accéder à la page</button></a>
      </div>
    </li>

  </ul>
  <nav class='nav'>
    <ion-icon class='btn prev' name="arrow-back-outline"></ion-icon>
    <ion-icon class='btn next' name="arrow-forward-outline"></ion-icon>
  </nav>
</main>


<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const titleElement = document.getElementById('title');

    // Ajouter un écouteur d'événement pour la fin de l'animation de saisie
    titleElement.addEventListener('animationend', function() {
        // Ajouter la classe pour désactiver le curseur après l'animation
        titleElement.classList.add('no-cursor');
    });
});
    const slider = document.querySelector('.slider');

function activate(e) {
  const items = document.querySelectorAll('.item');
  e.target.matches('.next') && slider.append(items[0])
  e.target.matches('.prev') && slider.prepend(items[items.length-1]);
}

document.addEventListener('click',activate,false);

// set globals
var interval = 6000,
  maxbubbles = 23,
  minbubblesize = 6, // pixels
  maxbubblesize = 90, //pixels
  minbubblespeed = 1, // seconds
  maxbubblespeed = 12, //seconds
  oddpop = 6, // odds (1 out of) that a bubble will pop
  oddblur = 3, // odds (1 out of ) of a bubble blur
  centerx = 0, // may use later
  centery = 0; // to animate direction of bubble highlights
function getRandomInt(min, max) {
  // this is doing a ton of work
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min + 1)) + min;
}
function getColor() {
  // simple function to create random rgb value
  var color = {};
  var r = getRandomInt(0, 255);
  var g = getRandomInt(0, 255);
  var b = getRandomInt(0, 255);
  color.avg = (r + g + b) / 3;
  color.rgb = "rgb(" + r + ", " + g + ", " + b + ")";
  return color;
}
function compare(a, b) {
  // returns list of colors ranked by lightness
  const avgA = a.avg;
  const avgB = b.avg;

  let comparison = 0;
  if (avgA < avgB) {
    comparison = 1;
  } else if (avgA > avgB) {
    comparison = -1;
  }
  return comparison;
}

function loadBackground() {
  // interval = getRandomInt(4000, 10000);
  var colors = [];
  for (var i = 0; 3 > i; i++) {
    colors.push(getColor());
  }
  colors.sort(compare);

  var x = getRandomInt(-90, 90);
  var y = getRandomInt(-90, 90);
  centerx = x;
  centery = y;
  // build radial gradient definition
  var bgstring = "radial-gradient( at ";
  bgstring += x + "% " + y + "%,";
  for (var i = 0; colors.length > i; i++) {
    var tperc = 100;
    if (i == 0) {
      tperc = 0;
    } else if (i == 1) {
      tperc = getRandomInt(20, 80);
    }
    bgstring += colors[i].rgb + " " + tperc + "%";
    if (i !== 2) {
      bgstring += ",";
    }
  }
  bgstring += ")";

  // in order to "animate" from one gradient to another
  // we apply the gradient to a div in front of background
  // animate it's opacity up
  // change the background of the body
  // then hide the div again
  $(".color").css({
    background: bgstring,
    opacity: 0
  });
  $(".color")
    .stop()
    .animate({ opacity: 1 }, interval - 50, function () {
      document.querySelector(":root").style.setProperty("--bgcolor", bgstring);
      $("body").css({
        background: bgstring
      });
      $(".color").css("opacity", 0);
    });

  // run again in a few seconds
  setTimeout(function () {
    loadBackground();
  }, interval);
}

function loadBubbles(firstload) {
  var newhtml = "";
  var addnewcount = getRandomInt(10, maxbubbles);
  var currbubblecount = $(".bubble").length;
  if (currbubblecount < maxbubbles) {
    // write bubbles to screen, renew if popped
    for (var i = 0; addnewcount > i; i++) {
      var t = getRandomInt(0, 100); // distance from top
      var l = getRandomInt(0, 100); // distance from left
      var tspeed = getRandomInt(minbubblespeed, maxbubblespeed);
      var tstr =
        "transition: all " + tspeed + "s cubic-bezier(0.3, 0.27, 0.07, 1.64);";
      newhtml +=
        '<div class="bubble brandnew" style="top:' +
        t +
        "%;left:" +
        l +
        "%;width:0px; height:0px; " +
        tstr +
        '"></div>';
    }
    $("body").append(newhtml);
  }
  // create collection of bubbles to "animate"
  var collection = $(".bubble");
  var next = getRandomInt(2, 10);
  collection.each(function () {
    var x = getRandomInt(-80, 80);
    var y = getRandomInt(-80, 80);
    var s = getRandomInt(minbubblesize, maxbubblesize);
    var mtime = getRandomInt(minbubblespeed, maxbubblespeed);
    var d = getRandomInt(1, 5); // delay before transition
    var f = getRandomInt(1, oddblur);
    if ($(this).hasClass("clicked")) {
      f = 2;
      $(this).removeClass("brandnew");
    }
    if (f == 1) {
      // 1 in 5 chance of blurring
      var b = getRandomInt(2, 15);
      $(this).css("filter", "blur(" + b + "px)");
    } else {
      $(this).css("filter", "none"); // turn off blur if it's already blurred
      if ($(this).hasClass("brandnew")) {
        $(this).removeClass("brandnew");
      } else {
        // nested in two else's
        // because I don't want blurred
        // or new bubbles to "pop"
        // and ya, this is where they pop
        var p = getRandomInt(1, oddpop);
        if ($(this).hasClass("clicked")) {
          p = 1;
        }
        if (p == 1) {
          $(this).addClass("pulsed");
          setTimeout(function () {
            console.log("remove test");
            $(".pulsed").remove();
          }, 1000);
        }
      }
    }
    $(this).css({
      // the actual animation
      transform: "translate(" + x + "px, " + y + "px)",
      width: s + "px",
      height: s + "px",
      "transition-duration": mtime + "s",
      "transition-delay": d + "s"
    });
    if (!$(this).is(":visible")) {
      // if bubble has drifted off page, kill it
      $(this)
        .stop()
        .animate({ opacity: 0 }, interval - 50, function () {
          $(this).remove();
        });
    }
  });
  next = next * 1000; // convert next from seconds to milliseconds
  if (firstload == true) {
    next = 50; // we just started, let's get started fast
  }
  // run again soon
  setTimeout(function () {
    loadBubbles();
  }, next);
}
$(function () {
  // where we start
  loadBackground();
  loadBubbles(true);
  $("body")
    .off("click", ".bubble")
    .on("click", ".bubble", function () {
      $(this).addClass("clicked");
    });
});

</script>
</body>
</html>
