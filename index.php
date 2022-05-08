<?php
/**
* \file index.php
* \brief Page de connexion au site
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet aux utilisateur de se connecter au site de questionnaires.
* \nEn remplissant le champ de l'adresse e-mail et du mot de passe, ils peuvent accéder à leur page principale personnelle en fonction de leur statut.
*\nA partir de cette page, ils peuvent également accéder à la page d'inscription s'ils ne sont pas encore enregistrés.
*\nSi les informations de connexion sont correctes, ils sont redirigés vers leur page personnelle.
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
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="index.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Bienvenue !</title>
</head>
<body>
<?php

    // On vérifie que l'utilisateur n'est pas connecté:
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

    // Si non, Connexion:
	if (!isset($_POST['mdp']) && !isset($_POST['mail']))
	{
      ?>
        
      <!-- On affiche le formulaire de Connexion -->
      <div class="wrapper fadeInDown">
        <div id="formContent">

            <h1>Se connecter</h1>

    	    <form action="index.php" method="post">
            
                <input type="text"     name="mail"     placeholder="Adresse email" required><br>
			    <input type="password" name="mdp"      placeholder="Mot de passe"  required><br>
                <input type="submit" value="Se connecter">
                
            </form>

                <div id="formFooter">
                    <a href="../Inscription/register.php">Pas encore inscrit ?</a>
                </div>
        </div>
      </div>
      <?php
    } 
    
	// On récupère les infos:
	else if (isset($_POST['mdp']) && isset($_POST['mail']))
    {
        
        $mail   = htmlspecialchars($_POST['mail']);
        $mdp    = htmlspecialchars($_POST['mdp']);
        $mdp    = md5($mdp);

        $req = $bdd->prepare('SELECT * FROM utilisateur WHERE mail = :mail AND mdp = :mdp');
        $req->execute(array('mail' => $mail, 'mdp' => $mdp));
        $donnees = $req->fetch();

        // Si l'utilisateur existe dans la BDD, on le connecte :
        if ($donnees != NULL)
        {
            $_SESSION['userid'] = $donnees['id'];
            $_SESSION['nom']    = $donnees['nom'];
            $_SESSION['prenom'] = $donnees['prenom'];
            $_SESSION['statut'] = $donnees['statut'];

            if      ($_SESSION['statut'] == 1)      // Client
            {
                header('Location: ../Console_Client/console_client.php');
            }
            else if ($_SESSION['statut'] == 2)      // Rédacteur
            {
                header('Location: ../Console_Redacteur/console_redac.php');
            }
            else if ($_SESSION['statut'] == 3)      // Administrateur
            {
                header('Location: ../Console_Admin/console_admin.php');
            }
        }
        else
        {
            ?> <p>Email ou mot de passe incorrect</p>
            <a href="index.php">Réessayer</a>
            <?php
        }
        $req->closeCursor();
    }
?>
</body>
</html>
