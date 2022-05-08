<?php

/**
* \file modifier_user_admin.php
* \brief Modification d'un utilisateur par un administrateur
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet à l'administrateur de modifier un utilisateur existant, autre que lui-même, pour modifier certaines de ses informations à sa demande, comme son statut par exemple.
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
    
        
    function check_email_address($mail) { 
		return (!preg_match( "^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$^", $mail)) ? false : true; 
    } 
    
$erreur = '';

// On vérifie que l'utilisateur est connecté:
if (!isset($_SESSION['userid'])) 
{
    header('Location: ../../Connexion/index.php');
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
    // On récupère les infos de l'user à modifier:
    $req = $bdd->prepare('SELECT * FROM `utilisateur` WHERE id = :id');
    $req->execute(array('id' => $_SESSION['id_user']));
    $donnees = $req->fetch();

    $nom            = $donnees['nom'];
    $prenom         = $donnees['prenom'];
    $date_naissance = $donnees['date_naissance'];
    $adresse        = $donnees['adresse'];
    $code_postal    = $donnees['code_postal'];
    $ville          = $donnees['ville'];
    $telephone      = $donnees['telephone'];
    $mail           = $donnees['mail'];
    $mdp            = $donnees['mdp'];
    $statut1         = $donnees['statut'];
    
    $req->closeCursor();
}

// On vérifie qu'on a rempli le formulaire:
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // On vérifie que les champs sont remplis:
    if (!empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['date_naissance']) && !empty($_POST['adresse']) && !empty($_POST['code_postal']) && !empty($_POST['ville']) && !empty($_POST['telephone']) && !empty($_POST['mail']) && !empty($_POST['mdp']) && !empty($_POST['statut']))
    {
        // On s'assure du bon format de l'adresse mail:
        if (check_email_address($_POST['mail']) == false)
        {
            $erreur ='Format de l\'email incorrect';
        }
        else
        {
            // On récupère les infos:
            $nom            = htmlspecialchars($_POST['nom']);
            $prenom         = htmlspecialchars($_POST['prenom']);
            $date_naissance = htmlspecialchars($_POST['date_naissance']);
            $adresse        = htmlspecialchars($_POST['adresse']);
            $code_postal    = htmlspecialchars($_POST['code_postal']);
            $ville          = strtoupper(htmlspecialchars($_POST['ville']));
            $telephone      = htmlspecialchars($_POST['telephone']);
            $mail           = htmlspecialchars($_POST['mail']);
            $mdp            = md5(htmlspecialchars($_POST['mdp']));
            $statut2        = $_POST['statut'];
            
            // On vérifie que l'email n'est pas déjà utilisé:
            $req = $bdd->prepare('SELECT mail FROM `utilisateur` WHERE id != :id and mail = :mail');
            $req->execute(array('id'   => $_SESSION['id_user'],
                                'mail' => $mail));
            $donnees = $req->fetch();
    
            if ($donnees != NULL)
            {
                $erreur = 'Cet email est déjà utilisé'; 
                $req->closeCursor();
            }
            else
            {
                $req->closeCursor();
                
                // Si on change le statut d'un rédacteur, on efface également ses questionnaires:
                if ($statut1 == 2 && $statut2 != 2)
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
                }
                
                // Si on change le statut d'un client, on efface également ses réponses/résultats:
                if ($statut1 = 1 && $statut2 != 1)
                {
                    $req = $bdd->prepare('DELETE FROM `questionnaire_client` WHERE id_client = :id');
                    $req->execute(array('id' => $_SESSION['id_user']));          
                    $req->closeCursor();

                    $req = $bdd->prepare('DELETE FROM `reponse_client` WHERE id_client = :id');
                    $req->execute(array('id' => $_SESSION['id_user']));          
                    $req->closeCursor();
                }
                
                // Modification du compte:
                $req = $bdd->prepare('UPDATE `utilisateur` SET `nom`= :nom, `prenom`= :prenom, `date_naissance`= :date_naissance, `adresse`= :adresse, `code_postal`= :code_postal, `ville`= :ville, `telephone`= :telephone, `mail`= :mail, `mdp`= :mdp, `statut`= :statut WHERE id = :id');
                $req->execute(array(
                                    'id'             => $_SESSION['id_user'],
                                    'nom'            => $nom, 
                                    'prenom'         => $prenom, 
                                    'date_naissance' => $date_naissance, 
                                    'adresse'        => $adresse, 
                                    'code_postal'    => $code_postal, 
                                    'ville'          => $ville, 
                                    'telephone'      => $telephone, 
                                    'mail'           => $mail, 
                                    'mdp'            => $mdp,
                                    'statut'         => $statut2
                                    ));
                $req->closeCursor();

                unset($_SESSION['id_user']);
                header('Location: ../console_admin.php');
                exit;
            }
        }
    }
    else
    {
        $erreur = 'Tous les champs sont obligatoires';
    }
}

if (isset ($_POST['cancel']))
{
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
    <link href="modifier_user_admin.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Modifier un compte</title>
</head>
<body>
    
<!-- On affiche le formulaire: -->
<div class="wrapper fadeInDown">
    <div id="formContent">
                    
        <h1>Modifier un compte</h1>
                
        <form action="modifier_user_admin.php" method="post">

			<input type="text"     class= "box"		name="nom"      	  value="<?php echo $nom ?>"            required>
			<input type="text"     class= "box"		name="prenom"   	  value="<?php echo $prenom ?>"         required>
			<input type="date"     class= "box"		name="date_naissance" value="<?php echo $date_naissance ?>" required>
			<input type="text"     class= "box"		name="adresse" 		  value="<?php echo $adresse ?>"        required>
			<input type="text"     class= "box1"	name="code_postal"    value="<?php echo $code_postal ?>"    required>
			<input type="text"     class= "box2"	name="ville" 	      value="<?php echo $ville ?>"          required>
			<input type="text"     class= "box"		name="telephone"      value="<?php echo $telephone ?>"      required>
			<input type="text"     class= "box"		name="mail"     	  value="<?php echo $mail ?>"           required>
            <input type="password" class= "box"		name="mdp"     		  placeholder="Mot de Passe"            required><br/>
            
            <input type="radio" name="statut" value="1" id="1" /> <label for="1">Client</label><br />
            <input type="radio" name="statut" value="2" id="2" /> <label for="2">Rédacteur</label><br />
            <input type="radio" name="statut" value="3" id="3" /> <label for="3">Administrateur</label><br />
            
            <?php 
            if(!empty($erreur)) 
            {
                echo('<p class="msg">'. $erreur. '</p>');
            }
            ?>

			<input type="submit" class="creer"  name= "submit" value="Valider">
		</form>

        <!-- Bouton Annuler -->
        <form action="modifier_user_admin.php" method="post">
            <input type="submit" class="annuler" name="cancel" value="Annuler" />
        </form>

    </div>
</div>
</body>
</html>