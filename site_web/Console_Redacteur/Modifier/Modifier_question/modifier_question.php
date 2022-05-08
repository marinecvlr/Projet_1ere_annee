<?php

/**
* \file modifier_question.php
* \brief Page de modification d'une question existante
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet au Rédacteur de modifier une question existante, et chacune de ses réponses. La page demande au Rédacteur de remplir chaque champ, de la même manière que pour Ajouter une question.
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

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '2') 
    {
        header('Location: ../../../Connexion/index.php');
        exit;
    }

if (empty($_SESSION['id_questionnaire']) or empty($_SESSION['nom_questionnaire'])) 
{
    header('Location: ../../console_redac.php');
    exit;
}


 // On récupère les infos de la question à modifier:
if (!empty($_SESSION['id_question_modif']))
{
    $req = $bdd->prepare('SELECT * FROM question WHERE id= :id');
    $req->execute(array('id' => $_SESSION['id_question_modif']));
    $donnees = $req->fetch();
    
    $_SESSION['numero'] = $donnees['numero'];
    $_SESSION['enonce'] = $donnees['enonce'];
    $_SESSION['parent'] = $donnees['id_question_parent'];
    $_SESSION['type']   = $donnees['type'];

    $req->closeCursor();
    
}
else
{
    header('Location: ../modifier_questionnaire_redac.php');
    exit;
}

$count = 1;

// Bouton Retour:
if (isset ($_POST['cancel']))
{
    unset($_SESSION['id_question_modif']);
    header('Location: ../modifier_questionnaire_redac.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="modifier_question.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Modifier une question</title>
</head>
<body>

<!-- On affiche le formulaire: -->
<div class="wrapper fadeInDown">
    <div id="formContent">
                    
        <h1>Modifier une question</h1>
        <p class="nom_questionnaire"><?php echo $_SESSION['nom_questionnaire'];?> </p>
                
        <form action="../Modifier_reponse/modifier_reponse.php" method="post"> 

            <label>Enoncé de la question: </label><input type="text" name="enonce" placeholder="<?php echo $_SESSION['enonce'] ?>" required/> <br>

            <!-- On affiche la liste des questions qui ont au moins une réponse à NULL-->
            <label for="parent">Question parente</label> 
            <select name="parent" id="parent">
                <option value="0">Aucune</option>
            <?php 
            $req = $bdd->prepare('SELECT DISTINCT id_question FROM reponse_question WHERE id_question != :id and (id_question_suivante = :id or id_question_suivante is null and id_question IN (
                                  SELECT id FROM question WHERE id_questionnaire = :id_questionnaire))  ORDER BY id_question');
            $req->execute(array('id'               => $_SESSION['id_question_modif'],
                                'id_questionnaire' => $_SESSION['id_questionnaire']
                                ));
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
                                                                <option value="1" <?php if ($_SESSION['type'] == 1)
                                                                                        {
                                                                                            ?> selected="selected"<?php
                                                                                        }
                                                                                        ?> >Saisie de texte</option>

                                                                <option value="2" <?php if ($_SESSION['type'] == 2)
                                                                                        {
                                                                                            ?> selected="selected"<?php
                                                                                        }
                                                                                        ?> >Saisie de nombre</option>

                                                                <option value="3" <?php if ($_SESSION['type'] == 3)
                                                                                        {
                                                                                            ?> selected="selected"<?php
                                                                                        }
                                                                                        ?> >Saisie de date</option>

                                                                <option value="4" <?php if ($_SESSION['type'] == 4)
                                                                                        {
                                                                                            ?> selected="selected"<?php
                                                                                        }
                                                                                        ?> >Boutons radio</option>
                                                          </select> <br>
			
            <input type="submit" class="termine" value="Terminer">
        </form>

        <!-- Bouton Annuler -->
        <form action="modifier_question.php" method="post">
            <input type="submit" class="annuler" name="cancel" value="Annuler" />
        </form>

    </div>
</div>
</body>
</html>