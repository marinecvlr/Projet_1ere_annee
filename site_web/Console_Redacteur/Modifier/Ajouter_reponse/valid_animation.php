<?php

/**
* \file valid_animation.php
* \brief Page de confirmation de l'ajout d'une question
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page sert uniquement à confirmer visuellement au Rédacteur que le traitement pour ajouter une question s'est correctement déroulé.
*/

	session_start();   
	
	try 
	{
      $bdd = new PDO('mysql:host=localhost;dbname=site_web;charset=utf8', 'root', '');
	} 
	catch (Exception $e) 
	{
      die('Erreur :'.$e->getMessage());
    }


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
    <p style="font-family: 'Script MT'; font-size: 30px;">Question ajoutée avec succès</p>
</div>

<?php
unset($_SESSION['enonce']);
unset($_SESSION['type']);
unset($_SESSION['parent']);
unset($_SESSION['numero']);
unset($_SESSION['nb_cases']);
unset($_SESSION['reponse_parente']);
?>

<meta http-equiv="refresh" content="3; URL=../modifier_questionnaire_redac.php">

</body>
</html>