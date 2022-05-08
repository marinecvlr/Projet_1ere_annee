<?php

/**
* \file register.php
* \brief Page d'inscription en tant que client au site
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet aux nouveaux clients de s'inscrire au site de questionnaires,
* \nen remplissant des champs tels que le nom, le prénom, la date de naissance, les coordonnées, et le mot de passe.
*\nA partir de cette page, ils peuvent également accéder à la page de connexion s'ils sont déjà enregistrés.
*\nSi toutes les informations sont correctes, ils sont inscrits puis redirigés vers leur page personnelle.
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

// On vérifie que l'utilisateur n'est pas déjà connecté:
if (isset($_SESSION['userid']) && isset($_SESSION['statut'])) 
{
    if      ($_SESSION['statut'] == 1)
    {
        header('Location: ../Console_Client/console_client.php');
        exit();
    }
    else if ($_SESSION['statut'] == 2)
    {
        header('Location: ../Console_Redacteur/console_redac.php');
        exit();
    }
    else if ($_SESSION['statut'] == 3)
    {
        header('Location: ../Console_Admin/console_admin.php');
        exit();
    }
}

// On vérifie qu'on vient bien du formulaire:
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // On vérifie que les champs sont remplis:
    if (!empty($_POST['nom']) && !empty($_POST['prenom']) && !empty($_POST['date_naissance']) && !empty($_POST['adresse']) && !empty($_POST['code_postal']) && !empty($_POST['ville']) && !empty($_POST['telephone']) && !empty($_POST['mail']) && !empty($_POST['mdp']))
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
            $ville          = htmlspecialchars($_POST['ville']);
            $telephone      = htmlspecialchars($_POST['telephone']);
            $mail           = htmlspecialchars($_POST['mail']);
            $mdp            = htmlspecialchars($_POST['mdp']);
            $mdp            = md5($mdp);
        

            // On vérifie que l'email n'est pas déjà utilisé:
            $req = $bdd->prepare('SELECT mail FROM `utilisateur` WHERE mail = :mail');
            $req->execute(array('mail' => $mail));
            $donnees = $req->fetch();

            if ($donnees != NULL)
            {
                $erreur = 'Cet email est déjà utilisé'; 
            }
            else
            {
                $req->closeCursor();
                
                // Ajout du nouvel utilisateur dans la bdd:
                $req = $bdd->prepare('INSERT INTO utilisateur (nom, prenom, date_naissance, adresse, code_postal, ville, telephone, mail, mdp, statut) VALUES(:nom, :prenom, :date_naissance, :adresse, :code_postal, :ville, :telephone, :mail, :mdp, 1)');
                $req->execute(array(
                                    'nom'            => $nom, 
                                    'prenom'         => $prenom, 
                                    'date_naissance' => $date_naissance, 
                                    'adresse'        => $adresse, 
                                    'code_postal'    => $code_postal, 
                                    'ville'          => $ville, 
                                    'telephone'      => $telephone, 
                                    'mail'           => $mail, 
                                    'mdp'            => $mdp
                                    ));
                $req->closeCursor(); 

                // On connecte l'utilisateur:
		        $req = $bdd->prepare('SELECT * FROM utilisateur WHERE mail = :mail');
		        $req->execute(array('mail'=> $mail));
		        $donnees = $req->fetch();
				
		        $_SESSION['userid'] = $donnees['id'];
		        $_SESSION['nom']    = $donnees['nom'];
		        $_SESSION['prenom'] = $donnees['prenom'];
		        $_SESSION['statut'] = $donnees['statut'];
                
                $req->closeCursor(); 

                // Redirection:
                if (isset($_SESSION['userid']))
		        {
                    header('Location: ../Console_Client/console_client.php');
                    exit();
		        }
            }
	    }
    }
    else
    {
        $erreur = 'Tous les champs sont obligatoires';
    }
}

?>
<!DOCTYPE html>
<html lang="fr">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<link href="register.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
	<title>Inscription</title>
</head>
<body>

<!--// On affiche le formulaire : -->
 
<div class="wrapper fadeInDown">
    <div id="formContent">

		<h1>Rejoignez-nous</h1>
		<form action="register.php" method="post">

			<input type="text"     class= "box"		name="nom"      	  placeholder="Nom"           	   required>
			<input type="text"     class= "box"		name="prenom"   	  placeholder="Prénom"        	   required>
			<input type="date"     class= "box"		name="date_naissance" placeholder="Date de Naissance"  required>
			<input type="text"     class= "box"		name="adresse" 		  placeholder="Adresse"			   required>
			<input type="text"     class= "box1"	name="code_postal"    placeholder="Code Postal"		   required>
			<input type="text"     class= "box2"	name="ville" 	      placeholder="Ville"			   required>
			<input type="text"     class= "box"		name="telephone"      placeholder="Téléphone Portable" required>
			<input type="text"     class= "box"		name="mail"     	  placeholder="Adresse email" 	   required>
            <input type="password" class= "box"		name="mdp"     		  placeholder="Mot de Passe"	   required>
            
            <?php 
            if(!empty($erreur)) 
            {
                echo('<p class="msg">'. $erreur. '</p>');
            }
            ?>

			<input type="submit"   name= "submit"   value="S 'enregistrer">

		</form>

		<div id="formFooter">
			<a href="../Connexion/index.php">Déjà un compte ? Connectez-vous</a>
		</div>

	</div>
</div>

</body>
</html>
