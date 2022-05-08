<?php
	session_start();   
	
	try 
	{
      $bdd = new PDO('mysql:host=localhost;dbname=site_web;charset=utf8', 'root', '');
	} 
	catch (Exception $e) 
	{
      die('Erreur :'.$e->getMessage());
	} 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Supprimer un questionnaire</title>
</head>
<body>
<?php

    // On vérifie que l'utilisateur est connecté:
    if (!isset($_SESSION['userid'])) 
    {
        header('Location: ../../Connexion/index.php'); 
        exit;
    }
    
    // On supprime le questionnaire de la BDD et ses questions:
    if (isset($_SESSION['id_quest_supp']))
    {
        $id_a_supprimer = $_SESSION['id_quest_supp'];
        
        // On efface les réponses des clients:
        $req = $bdd->prepare('DELETE FROM `reponse_client` WHERE id_questionnaire = :id');
		$req->execute(array('id' => $id_a_supprimer));          
        $req->closeCursor();


        // On efface les resultats des clients:
        $req = $bdd->prepare('DELETE FROM `questionnaire_client` WHERE id_questionnaire = :id');
		$req->execute(array('id' => $id_a_supprimer));          
        $req->closeCursor();
        

        // On récupère toutes les questions du questionnaire:
        $req = $bdd->prepare('SELECT id FROM `question` WHERE id_questionnaire = :id');
        $req->execute(array('id' => $id_a_supprimer));
        while($donnees = $req->fetch())
        {
            // On efface toutes les réponses à toutes les questions:
            $requete = $bdd->prepare('DELETE FROM `reponse_question` WHERE id_question = :id_question');
            $requete->execute(array('id_question' => $donnees['id']));
            $requete->closeCursor();
        }
        $req->closeCursor();


        // On annule les dépendances entre questions:
        $req = $bdd->prepare('UPDATE `question` SET `id_question_parent`= NULL WHERE id_questionnaire = :id');
        $req->execute(array('id' => $id_a_supprimer));
        $req->closeCursor();
        

        // On efface toutes les questions:
        $req = $bdd->prepare('DELETE FROM `question` WHERE id_questionnaire = :id');
        $req->execute(array('id' => $id_a_supprimer));          
        $req->closeCursor(); 


        // On efface finalement le questionnaire:
        $req = $bdd->prepare('DELETE FROM `questionnaire` WHERE id = :id');
        $req->execute(array('id' => $id_a_supprimer));          
        $req->closeCursor();
        
        unset($_SESSION['id_quest_supp']);
        header('Location: ../console_redac.php');
        exit;
    }
?> 
</body>
</html>