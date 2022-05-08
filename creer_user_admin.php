<?php

/**
* \file creer_user_admin.php
* \brief Création d'un utilisateur par un administrateur
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet à l'administrateur de Créer un compte à la place de quelqu'un, dans le cas où la personne aurait des soucis d'inscription, ou qu'elle voudrait un statut autre que Client.
* \n Les informations à remplir sont les mêmes que pour l'inscription classique, à la seule différence que l'administrateur peut sélectionner directement le statut de l'utilisateur.
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
            $statut         = $_POST['statut'];
            
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
                $req = $bdd->prepare('INSERT INTO utilisateur (nom, prenom, date_naissance, adresse, code_postal, ville, telephone, mail, mdp, statut) VALUES(:nom, :prenom, :date_naissance, :adresse, :code_postal, :ville, :telephone, :mail, :mdp, :statut)');
                $req->execute(array(
                                    'nom'            => $nom, 
                                    'prenom'         => $prenom, 
                                    'date_naissance' => $date_naissance, 
                                    'adresse'        => $adresse, 
                                    'code_postal'    => $code_postal, 
                                    'ville'          => $ville, 
                                    'telephone'      => $telephone, 
                                    'mail'           => $mail, 
                                    'mdp'            => $mdp,
                                    'statut'         => $statut
                                    ));
                $req->closeCursor();

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
    header('Location: ../console_admin.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="creer_user_admin.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Créer un compte</title>
</head>
<body>

<!-- On affiche le formulaire: -->
<div class="wrapper fadeInDown">
    <div id="formContent">
                    
        <h1>Créer un nouveau compte</h1>
                
        <form action="creer_user_admin.php" method="post">

			<input type="text"     class= "box"		name="nom"      	  placeholder="Nom"           	   required>
			<input type="text"     class= "box"		name="prenom"   	  placeholder="Prénom"        	   required>
			<input type="date"     class= "box"		name="date_naissance" placeholder="Date de Naissance"  required>
			<input type="text"     class= "box"		name="adresse" 		  placeholder="Adresse"			   required>
			<input type="text"     class= "box1"	name="code_postal"    placeholder="Code Postal"		   required>
			<input type="text"     class= "box2"	name="ville" 	      placeholder="Ville"			   required>
			<input type="text"     class= "box"		name="telephone"      placeholder="Téléphone Portable" required>
			<input type="text"     class= "box"		name="mail"     	  placeholder="Adresse email" 	   required>
            <input type="password" class= "box"		name="mdp"     		  placeholder="Mot de Passe"	   required><br/>
            
            <input type="radio" name="statut" value="1" id="1" /> <label for="1">Client</label><br />
            <input type="radio" name="statut" value="2" id="2" /> <label for="2">Rédacteur</label><br />
            <input type="radio" name="statut" value="3" id="3" /> <label for="3">Administrateur</label><br />
            
            <?php 
            if(!empty($erreur)) 
            {
                echo('<p class="msg">'. $erreur. '</p>');
            }
            ?>

			<input type="submit" class="creer"  name= "submit" value="Créer un compte">
		</form>

        <!-- Bouton Annuler -->
        <form action="creer_user_admin.php" method="post">
            <input type="submit" class="annuler" name="cancel" value="Annuler" />
        </form>

    </div>
</div>
</body>
</html>
