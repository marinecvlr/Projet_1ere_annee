<?php

/**
* \file add_question.php
* \brief Page d'ajout d'une question au questionnaire
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet au Rédacteur d'ajouter une question à son questionnaire, en insérant son énoncé, son type (texte, nombre, date, boutons à sélectionner), et sa question parente si elle existe.
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

    
// On teste la session:
if (empty($_SESSION['userid'])) 
{
    header('Location: ../../../Connexion/index.php');
    exit;
}

if (empty($_SESSION['id_questionnaire']) or empty($_SESSION['nom_questionnaire'])) 
{
    header('Location: ../../console_redac.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '2') 
    {
        header('Location: ../../../Connexion/index.php');
        exit;
    }

// Bouton Retour:
if (isset ($_POST['cancel']))
{
    header('Location: ../modifier_questionnaire_redac.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="add_question.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Ajouter une question</title>
</head>
<body>

<!-- On affiche le formulaire: -->
<div class="wrapper fadeInDown">
    <div id="formContent">
 
        <h1>Ajouter une nouvelle question</h1>
        <p class="nom_questionnaire"><?php echo $_SESSION['nom_questionnaire'];?> </p>
                
        <form action="../Ajouter_reponse/add_reponse.php" method="post"> 
            <label>Enoncé de la question: </label><input type="text"   name="enonce" required/> <br>

            <!-- On affiche la liste des questions parentes possibles, soit celles qui ont au moins une réponse avec l'id_question_suivante à NULL-->
            <label for="parent">Question parente: </label> 

            <select name="parent" id="parent">
                <option value="0">Aucune</option>

            <?php 
            $req = $bdd->prepare('SELECT DISTINCT id_question FROM reponse_question WHERE id_question_suivante is null and id_question IN (
                                  SELECT id FROM question WHERE id_questionnaire = :id)  ORDER BY id_question');
            $req->execute(array('id' => $_SESSION['id_questionnaire']));
            while ($donnees = $req->fetch())
            {
                $requete = $bdd->prepare('SELECT id, numero, enonce FROM `question` WHERE id = :id ORDER BY numero desc');
                $requete->execute(array('id' => $donnees['id_question']));
                while ($sortie = $requete->fetch())
                {
                    ?> <option value="<?php echo $sortie['id']?>"><?php echo $sortie['numero'] . '- ' . $sortie['enonce']?></option> <?php
                }
                $requete->closeCursor();
            }
            $req->closeCursor();
            ?>
            </select> <br>

            <label for="type">Type de question :</label> <select name="type" id="type">
                                                                <option value="1">Saisie de texte</option>
                                                                <option value="2">Saisie de nombre</option>
                                                                <option value="3">Saisie de date</option>
                                                                <option value="4">Boutons radio</option>
                                                          </select> <br>
			
            <input type="submit" class="termine" value="Terminer">
        </form>

        <!-- Bouton Annuler -->
        <form action="add_question.php" method="post">
            <input type="submit" class="annuler" name="cancel" value="Annuler" />
        </form>

    </div>
</div>
</body>
</html>