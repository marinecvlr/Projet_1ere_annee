<?php

/**
* \file valid_animation_supp.php
* \brief Page de confirmation de la suppression d'une question
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
*
* Cette page sert uniquement à confirmer visuellement au Rédacteur que le traitement pour supprimer une question s'est correctement déroulé.
*/

    session_start();
    

// On vérifie qu'il peut accéder à cette page:
if ($_SESSION['statut'] != '2') 
{
    header('Location: ../../Connexion/index.php');
    exit;
}
    
?>    
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>

<div id="validate" style="text-align: center; margin-top: 15%;">
    <img src="valid.gif" style="max-width: 10%; height: auto;" alt="valid">
    <p style="font-family: 'Script MT'; font-size: 30px;">Question supprimée avec succès</p>
</div>

<meta http-equiv="refresh" content="3; URL=modifier_questionnaire_redac.php">

</body>
</html>