<?php

/**
* \file remplir_questionnaire_client.php
* \brief Page de démarrage d'un questionnaire par le client
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet au Client de démarrer le questionnaire qu'il a choisi.
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
    header('Location: ../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '1') 
    {
        header('Location: ../../Connexion/index.php');
        exit;
    }

// Si on appuie sur Démarrer:
if (isset($_POST['termine']))
{
    header('Location: question_client.php');
    exit;
}

// Si on appuie sur Retour:
if (isset($_POST['annuler']))
{
    unset($_SESSION['id_questionnaire']);
    unset($_SESSION['nom_questionnaire']);
    header('Location: ../console_client.php');
    exit;
}


// Si on vient de la page des questionnaires en cours:
if (isset($_POST['modif']))
{
    $_SESSION['id_questionnaire'] = $_POST['modif'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="remplir_questionnaire_client.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Remplir un questionnaire</title>
</head>
<body>
<div class="wrapper fadeInDown">
    <div id="formContent">
        <?php
        $req = $bdd->prepare('SELECT nom FROM `questionnaire` WHERE id = :id_questionnaire');
        $req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
        $donnees = $req->fetch();
        $_SESSION['nom_questionnaire'] = $donnees['nom'];
        $req->closeCursor();

        // On récupère le nombre de questions:
        $req = $bdd->prepare('SELECT COUNT(*) FROM `question` WHERE id_questionnaire = :id_questionnaire');
        $req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
        $donnees = $req->fetch();
        $nb_questions_totales = $donnees[0];
        $req->closeCursor();

        // On récupère le nombre de questions remplies par le client:
        $req = $bdd->prepare('SELECT COUNT(*) FROM `reponse_client` WHERE id_client = :id_client and id_questionnaire = :id_questionnaire');
        $req->execute(array('id_client'        => $_SESSION['userid'],
                            'id_questionnaire' => $_SESSION['id_questionnaire']));
        $donnees = $req->fetch();
        $nb_questions_client = $donnees[0];
        $req->closeCursor();
        ?>
        
    
    <h1><?php echo $_SESSION['nom_questionnaire'];?> </h1>

    <div id="info">
        <p class="texte"> Vous avez répondu à <?php echo $nb_questions_client?> questions.</p>
        <p class="texte">Vous pouvez quitter et reprendre à tout moment le questionnaire à l'endroit où vous en étiez.</p>
        <p class="texte">Lorsque vous êtes prêt à commencer, cliquez sur Démarrer</p>
    </div>

    <!-- Bouton Valider -->
    <form action="remplir_questionnaire_client.php" method="post">
        <input type="submit" class="termine" name="termine" value="Démarrer" />
    </form>      
    
    <!-- Bouton Annuler -->
    <form action="remplir_questionnaire_client.php" method="post">
        <input type="submit" class="annuler" name="annuler" value="Retour" />
    </form>   

</div>
</div>
</body>
</html>