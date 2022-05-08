<?php

/**
* \file creer_questionnaire_redac.php
* \brief Page de création d'un nouveau questionnaire
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet au Rédacteur de créer un nouveau questionnaire.
* \nA partir de celle-ci, il peut rédiger le titre de son nouveau questionnaire, avant d'être redirigé vers la partie Modification.
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

// On vérifie que l'utilisateur est connecté:
if (!isset($_SESSION['userid'])) 
{
    header('Location: ../../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
if ($_SESSION['statut'] != '2') 
{
    header('Location: ../../Connexion/index.php');
    exit;
}
            
// On vérifie qu'on a rempli le formulaire:
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if(isset($_POST['titre']))
    {
        // On récupère l'info:
        $titre = htmlspecialchars($_POST['titre']);

        // On vérifie que le titre entré n'existe pas déjà:
        $req = $bdd->prepare('SELECT nom FROM questionnaire WHERE nom = :nom');
        $req->execute(array('nom' => $titre));
        $donnees = $req->fetch();

        if ($donnees != NULL)
        {
            $erreur = 'Ce questionnaire existe déjà';
            $req->closeCursor();
        }
        else
        {
            $req->closeCursor();
            
            // On crée le nouveau questionnaire en BDD:
            $today = date("Y-m-d");
            $req = $bdd->prepare('INSERT INTO questionnaire (nom, date_creation, id_auteur) VALUES(:nom, :date_creation, :userId)');
		    $req->execute(array(
							    'nom'           => $titre,
							    'date_creation' => $today,
							    'userId'       => $_SESSION['userid']
                                ));
                            
            $req->closeCursor();
        
            // On récupère l'id du questionnaire qu'on vient de créer
            $req = $bdd->prepare('SELECT id FROM questionnaire WHERE nom = :nom');
            $req->execute(array('nom' => $titre));
            $donnees = $req->fetch();
            $_SESSION['id_questionnaire'] = $donnees['id'];
            $req->closeCursor();

            // Redirection:
            header('Location: ../Modifier/modifier_questionnaire_redac.php');
            exit;
        }                        
    }
}

if (isset ($_POST['cancel']))
{
    if (!empty($_SESSION['id_questionnaire']))
    {
        unset($_SESSION['id_questionnaire']);
    }
    header('Location: ../console_redac.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="creer_questionnaire_redac.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Créer un questionnaire</title>
</head>
<body>

<!-- On affiche le formulaire: -->
<div class="wrapper fadeInDown">
    <div id="formContent">
                    
        <h1>Créer un nouveau questionnaire</h1>
                
        <form action="creer_questionnaire_redac.php" method="post"> 
                    
            <input type="text"   name="titre"  placeholder="Titre" required>

            <?php
            if(!empty($erreur)) 
            {
                echo('<p class="msg">'. $erreur. '</p>');
            }
            ?>
			
            <input type="submit" class="creer" value="Go !">
        
        </form>

        <!-- Bouton Annuler -->
        <form action="creer_questionnaire_redac.php" method="post">
            <input type="submit" class="annuler" name="cancel" value="Annuler" />
        </form>

    </div>
</div>
</body>
</html>