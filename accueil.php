<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fond Vidéo Plein Écran</title>
    <style>
        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            width: 100%;
            overflow: hidden;
        }

        .video-container {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1; /* Assurez-vous que la vidéo reste à l'arrière-plan */
        }

        .video-container video {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100%;
            height: 100%;
            object-fit: cover; /* Assure que la vidéo couvre toute la zone */
            transform: translate(-50%, -50%);
        }

        .content {
            position: relative;
            z-index: 1; /* Assurez-vous que le contenu est au-dessus de la vidéo */
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            color: white;
            text-align: center;
            background-color: rgba(0, 0, 0, 0.3); /* Légère teinte pour améliorer la lisibilité */
        }

        h1 {
            font-size: 48px;
            margin: 0;
        }

        p {
            font-size: 24px;
            margin: 10px 0 0;
        }


    @import "color-schemer";
    @import "compass";
    @import "breakpoint";
    @import "susy";


    .button {
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translateX(-50%) translateY(-50%);
    background: #fff;
    width: 100px;
    padding: 20px 30px;
    border: 5px solid #4ac0ee;
    color: #4ac0ee;
    font-weight: bold;
    text-transform: uppercase;
    text-align: center;
    z-index: 2;
    border-radius: 25px; /* Ajout d'un arrondi */
    transition: all .15s .15s ease-out;
}

.button:hover {
    background: #34495e;
    color: #fff;
    margin-left: -7.5px;
    margin-bottom: -7.5px;
    border-radius: 25px; /* Assurez-vous que l'arrondi reste au survol */
}

    
    z-index: 2;
    @include transition(all .15s .15s ease-out);

        &:after,
        &:before {
            position: absolute;
            width: 100%;
            max-width: 100%;
            top: 100%;
            left: 0;
            bottom: -10px;
            content: '';
            z-index: 1;
            @include transition(all .15s .15s ease-out);
        }

        &:before {
            background: #4ac0ee;
            top: 10px;
            left: -15px;
            height: 100%;
            width: 15px;
        }

        &:after {
            width:100%;
            background: #4ac0ee;
            right: 0px;
            left: -15px;
            height: 15px;
        }

        &:hover {
        background: #4ac0ee;
        color: #fff;
        margin-left: -7.5px;
        margin-bottom: -7.5px;

        &:after,
        &:before {
            top: 100%;
            left: 0;
            bottom: 0px;
        }

        &:before {
            top: 0px;
            left: 0px;
            width: 0px;
        }

        &:after {
            right: 0px;
            left: 0px;
            height: 0px;
        }

        }
    }

    
        /* Styles pour mobile et tablettes */
        @media screen and (max-width: 768px) {
            h1 {
                font-size: 32px;
            }

            .button {
                width: 120px;
                padding: 12px 18px;
                font-size: 14px;
                top: 85%; /* Descend le bouton encore plus bas sur petit écran */
            }
        }

        @media screen and (max-width: 480px) {
            h1 {
                font-size: 28px;
            }

            .button {
                width: 100px;
                padding: 10px 15px;
                font-size: 12px;
                top: 70%; /* Ajuste la position sur petits écrans */
            }
        }

    </style>
</head>
<body>

<div class="video-container">
    <video autoplay muted loop>
        <source src="./images/MP hygiene - 10 ans de la papeterie.mp4" type="video/mp4">
        
    </video>
</div>

<div class="content">
    <h1>Bienvenue sur l'Intranet</h1>
</div>

<a href="notes.php"><div class="button" style="margin-top: 10%">Accéder à l'Intranet</div></a>



</body>
</html>
