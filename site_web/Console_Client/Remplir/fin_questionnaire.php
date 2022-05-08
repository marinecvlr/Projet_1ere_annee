<?php

/**
* \file console_client.php
* \brief Page de signalement de fin d'un questionnaire
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page signale au Client que le questionnaire qu'il a rempli est terminé. Elle le remercie et lui présente le résultat qu'il a obtenu à l'issue de celui-ci.
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

// On vérifie que l'utilisateur est bien connecté:
if (!isset($_SESSION['userid'])) 
{
    header('Location: ../../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '1') 
    {
        header('Location: ../../Connexion/index.php');
        exit;
    }

// Si on appuie sur Quitter:
if (isset($_POST['annuler']))
{
    unset($_SESSION['id']);
    unset($_SESSION['numero']);
    unset($_SESSION['enonce']);
    unset($_SESSION['id_question_parent']);
    unset($_SESSION['type']);
    unset($_SESSION['nb_cases']);
    unset($_SESSION['position']);
    unset($_SESSION['id_questionnaire']);
    unset($_SESSION['nom_questionnaire']);
    
    header('Location: ../console_client.php');
    exit;
}

if (isset($_SESSION['chemin_client']))
{
    // On récupère le résultat:
    $text = "";
    $text = implode(",", $_SESSION['chemin_client']);

    $req = $bdd->prepare('SELECT resultat FROM `resultat_questionnaire` where chemin = :chemin');
    $req->execute(array('chemin' => $text));
    $donnees = $req->fetch();
    $resultat = $donnees['resultat'];
    $req->closeCursor();

    //on l'ajoute au questionnaire_client:
    $req = $bdd->prepare('UPDATE `questionnaire_client` SET `resultat`= :resultat where id_client = :id_client and id_questionnaire = :id_questionnaire');
    $req->execute(array('resultat'         => $resultat,
                        'id_client'        => $_SESSION['userid'],
                        'id_questionnaire' => $_SESSION['id_questionnaire'],
                        ));
    $req->closeCursor();
}
else
{
    header('Location: remplir_questionnaire_client.php');
    exit;
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="fin_questionnaire.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Remplir un questionnaire</title>
</head>
<body>
<div class="wrapper fadeInDown">
    <div id="formContent">
        <h1 class="titre"><?php echo $_SESSION['nom_questionnaire'];?> </h1>
        
        <p>Vous avez terminé ce questionnaire. Merci de votre participation !</p>

        <p class="resultat">Résultat : <?php echo $resultat?></p> 



    <!-- Bouton Quitter -->
    <form action="fin_questionnaire.php" method="post">
        <input type="submit" class="annuler" name="annuler" value="Fermer" />
    </form>   

</div>
</div>
</body>
</html>