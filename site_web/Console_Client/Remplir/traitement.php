<?php

/**
* \file traitement.php
* \brief Page de traitement de la réponse du client au questionnaire
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page contient les traitements effectués lorsque le Client répond à une question, et le renvoie sur la page des questions pour saisir la suivante.
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

if (!isset($_SESSION['chemin_client'])) 
{
    header('Location: remplir_questionnaire_client.php');
    exit;
}



if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    if (isset($_POST['reponse']))
    {
        array_push($_SESSION['chemin_client'], $_SESSION['id']);
        
        $req = $bdd->prepare('SELECT id_question_parent FROM `question` WHERE id = :id');
        $req->execute(array('id' => $_SESSION['id']));
        $donnees = $req->fetch();
        if ($donnees['id_question_parent'] == NULL)
        {
            // Si c'est sa première question, on ajoute questionnaire_client:
            $requete = $bdd->prepare('INSERT INTO `questionnaire_client`(`id_client`, `id_questionnaire`) VALUES (:id_client, :id_questionnaire)');
            $requete->execute(array('id_client'    => $_SESSION['userid'],
                                    'id_questionnaire' => $_SESSION['id_questionnaire']
                                    ));
            $requete->closeCursor();                    
        }
        $req->closeCursor();
        
        
                    
        if ( ($_SESSION['type'] == 1) or ($_SESSION['type'] == 2) or ($_SESSION['type'] == 3) )
        {
            $reponse = 1;
            
            array_push($_SESSION['chemin_client'], $reponse);
              
            // On ajoute la réponse du client:
            $req = $bdd->prepare('INSERT INTO `reponse_client`(`id_client`, `id_question`, `numero`, `id_questionnaire`, `reponse`) VALUES (:id_client, :id_question, :numero, :id_questionnaire, :reponse)');
            $req->execute(array('id_client'        => $_SESSION['userid'],
                                'id_question'      => $_SESSION['id'],
                                'numero'           => $_SESSION['position'],
                                'id_questionnaire' => $_SESSION['id_questionnaire'],
                                'reponse'          => $reponse
                                ));
            $donnees = $req->fetch();
            $req->closeCursor();
        }
        else if ($_SESSION['type'] == 4)
        {
            $reponse = $_POST['reponse'];
            
            array_push($_SESSION['chemin_client'], $reponse);

            // On ajoute la réponse du client:
            $req = $bdd->prepare('INSERT INTO `reponse_client`(`id_client`, `id_question`, `numero`, `id_questionnaire`, `reponse`) VALUES (:id_client, :id_question, :numero, :id_questionnaire, :reponse)');
            $req->execute(array('id_client'        => $_SESSION['userid'],
                                'id_question'      => $_SESSION['id'],
                                'numero'           => $_SESSION['position'],
                                'id_questionnaire' => $_SESSION['id_questionnaire'],
                                'reponse'          => $reponse
                                ));
            $req->closeCursor();
        }
            
        // On vérifie s'il a terminé le questionnaire:
        $req = $bdd->prepare('SELECT id_question_suivante FROM `reponse_question` WHERE id_question = :id_question and numero_case = :numero_case');
        $req->execute(array('id_question' => $_SESSION['id'],
                            'numero_case'  => $reponse
                            ));
        $donnees = $req->fetch();

        if ($donnees['id_question_suivante'] == NULL)
        {
            $req->closeCursor();
            
            // On termine:
            header('Location: fin_questionnaire.php');
            exit;
        }
        else
        {
            $req->closeCursor();
            
            // On passe à la question suivante:
            header('Location: question_client.php');
            exit;
        }

    }
    else
    {
        header('Location: question_client.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="question_client.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Remplir un questionnaire</title>
</head>
<body>

</body>
</html>