<?php

/**
* \file supp_user_admin.php
* \brief Suppression d'un utilisateur par un administrateur
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet à l'administrateur de supprimer définitivement un utilisateur, autre que lui-même, en cas de problème par exemple.
* \n Dans le cas où cet utilisateur est un client, la suppression de son compte entraîne la suppression des questionnaires qu'il aurait rempli également.
* \n Dans le cas où cet utilisateur est un rédacteur, la suppression de son compte entraîne la suppression des questionnaires qu'il aurait crée, et des réponses des clients à ses questionnaires également.
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
    
// On vérifie que l'admin est bien connecté:
if (!isset($_SESSION['userid']) or $_SESSION['statut'] != 3) 
{
    session_destroy();
    header('Location: ../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '3') 
    {
        header('Location: ../../Connexion/index.php');
        exit;
    } 
    
if (empty($_SESSION['id_user'])) 
{
    header('Location: ../console_admin.php');
    exit;
}
else
{
    // On récupère le statut du compte:
    $res = $bdd->prepare('SELECT statut FROM `utilisateur` WHERE id = :id');
    $res->execute(array('id' => $_SESSION['id_user']));
    $donnees = $res->fetch();
    $statut_compte = $donnees['statut'];
    $res->closeCursor();

    // On efface un CLIENT:
    if ($statut_compte == 1)
    {
        // On supprime les reponses de l'utilisateur:
        $req = $bdd->prepare('DELETE FROM `reponse_client` WHERE id_client = :id');
        $req->execute(array('id' => $_SESSION['id_user']));
        $req->closeCursor();

        // On supprime les résultats de l'utilisateur:
        $req = $bdd->prepare('DELETE FROM `questionnaire_client` WHERE id_client = :id');
        $req->execute(array('id' => $_SESSION['id_user']));
        $req->closeCursor();

        // On supprime l'utilisateur:
        $req = $bdd->prepare('DELETE FROM `utilisateur` WHERE id = :id');
        $req->execute(array('id' => $_SESSION['id_user']));
        $req->closeCursor();
    }
    // On efface un REDACTEUR:
    else if ($statut_compte == 2)
    {
        // On récupère la liste de ses questionnaires:
        $req = $bdd->prepare('SELECT id FROM `questionnaire` WHERE id_auteur = :id_auteur');
        $req->execute(array('id_auteur' => $_SESSION['id_user']));
        while($donnees = $req->fetch())
        {

            // On efface les réponses des clients aux questions de ce questionnaire:
            $requete = $bdd->prepare('DELETE FROM `reponse_client` WHERE id_questionnaire = :id');
		    $requete->execute(array('id' => $donnees['id']));          
            $requete->closeCursor();


            // On efface les resultats des clients à ce questionnaire:
            $requete = $bdd->prepare('DELETE FROM `questionnaire_client` WHERE id_questionnaire = :id');
		    $requete->execute(array('id' => $donnees['id']));          
            $requete->closeCursor();


            // On efface les chemins des résultars à ce questionnaire:
            $requete = $bdd->prepare('DELETE FROM `resultat_questionnaire` WHERE id_questionnaire = :id');
            $requete->execute(array('id' => $donnees['id']));          
            $requete->closeCursor();
            

            // On récupère toutes les questions du questionnaire:
            $requete = $bdd->prepare('SELECT id FROM `question` WHERE id_questionnaire = :id');
            $requete->execute(array('id' => $donnees['id']));
            while($sortie = $requete->fetch())
            {
                // On efface toutes les réponses à toutes les questions:
                $sql = $bdd->prepare('DELETE FROM `reponse_question` WHERE id_question = :id_question');
                $sql->execute(array('id_question' => $sortie['id']));
                $sql->closeCursor();
            }
            $requete->closeCursor();


            // On annule les dépendances entre questions:
            $requete = $bdd->prepare('UPDATE `question` SET `id_question_parent`= NULL WHERE id_questionnaire = :id');
            $requete->execute(array('id' => $donnees['id']));
            $requete->closeCursor();
            

            // On efface toutes les questions:
            $requete = $bdd->prepare('DELETE FROM `question` WHERE id_questionnaire = :id');
		    $requete->execute(array('id' => $donnees['id']));          
            $requete->closeCursor(); 


            // On efface finalement le questionnaire:
            $requete = $bdd->prepare('DELETE FROM `questionnaire` WHERE id = :id');
		    $requete->execute(array('id' => $donnees['id']));          
            $requete->closeCursor();
        }       
        $req->closeCursor(); 
        
        // On supprime le rédacteur:
        $req = $bdd->prepare('DELETE FROM `utilisateur` WHERE id = :id');
        $req->execute(array('id' => $_SESSION['id_user']));
        $req->closeCursor();
    }


    unset($_SESSION['id_user']);
    header('Location: ../console_admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="console_client.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Supprimer un utilisateur</title>
</head>
<body>
</body>
</html>