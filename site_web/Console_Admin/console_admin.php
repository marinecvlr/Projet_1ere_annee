<?php

/**
* \file console_admin.php
* \brief Page principale d'un utilisateur administrateur
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page constitue la page personnelle d'un utilisateur Administrateur.
*\nA partir de celle-ci, il peut effectuer différentes actions liées à son statut, telles que:
*\n- Créer un utilisateur
*\n- Modifier un utilisateur
*\n- Supprimer un utilisateur
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

// Bouton déconnexion:
if (isset ($_POST['deconnect']))
{
    session_destroy();
    header('Location: ../Connexion/index.php');
    exit;  
}

// On vérifie que l'utilisateur est bien connecté:
if (!isset($_SESSION['userid'])) 
{
    header('Location: ../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '3') 
    {
        header('Location: ../Connexion/index.php');
        exit;
    }

// Modifier un compte:
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if (!empty($_POST['liste'])) 
    {
        // On récupère les infos et on redirige:
        $_SESSION['id_user'] = ($_POST['liste']);
        header('Location: Modifier/modifier_user_admin.php');
        exit;
    }
}

// Supprimer un compte:
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        if (!empty($_POST['liste2'])) 
        {
            // On récupère les infos et on redirige:
            $_SESSION['id_user'] = ($_POST['liste2']);
            header('Location: Supprimer/supp_user_admin.php');
            exit;
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="console_admin.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="console_admin.js"></script>
    <title>Console Administrateur</title>
</head>
<body>
    
<div class="bandeau">
    <h1><?php echo 'Bienvenue, ' . $_SESSION['prenom'] . ' ' . $_SESSION['nom'] ?></h1>
    
    <!-- Bouton Déconnexion:-->
    <form action="console_admin.php" method="post">
        <input type="submit" name="deconnect" value="Déconnexion" />
    </form>
</div>

<button onclick="window.location.href = 'Creer/creer_user_admin.php';">Créer un compte</button>
<button class = "button"  onclick="bascule('header');" >Modifier un compte </button>
<button class = "button"  onclick="bascule('header2');">Supprimer un compte</button>

<?php   // Modifier un compte:
$req = $bdd->prepare('SELECT id, mail, statut FROM `utilisateur` WHERE id != :id');
$req->execute(array('id' => $_SESSION['userid']));
?>

<div id="header" style="visibility: hidden">
    <form action="console_admin.php" method="post">
        <label for="liste">Sélectionnez un utilisateur</label><br />
        <select name="liste" id="liste">


<?php
     while ($donnees = $req->fetch())
    {
    ?>
        <option value="<?php echo $donnees['id']?>"> <?php if($donnees['statut'] == 1)
                                                            {
                                                                ?> Client - <?php
                                                            }
                                                            else if($donnees['statut'] == 2)
                                                            {
                                                                ?> Rédacteur - <?php
                                                            }
                                                            else if($donnees['statut'] == 3)
                                                            {
                                                                ?> Admin - <?php
                                                            }
                                                                echo $donnees['mail']?> </option>
    <?php
    }
    
    ?>   
        </select>
        <input type="submit" name="submit">
    </form>
</div>



<?php   // Affiche tous les comptes sauf le sien:
$req = $bdd->prepare('SELECT id, mail, statut FROM `utilisateur` WHERE id != :id');
$req->execute(array('id' => $_SESSION['userid']));
?>

<div id="header2" style="visibility: hidden">
    <form onsubmit = "return confirm('Voulez-vous supprimer définitivement cet utilisateur ? S\'il possède des questionnaires, ceux-ci seront détruits.');" action="console_admin.php" method="post">
        <label for="liste2">Sélectionnez un utilisateur</label><br />
        <select name="liste2" id="liste2">


<?php
     while ($donnees = $req->fetch())
    {
        ?>
        <option value="<?php echo $donnees['id']?>"> <?php if($donnees['statut'] == 1)
                                                            {
                                                                ?> Client - <?php
                                                            }
                                                            else if($donnees['statut'] == 2)
                                                            {
                                                                ?> Rédacteur - <?php
                                                            }
                                                            else if($donnees['statut'] == 3)
                                                            {
                                                                ?> Admin - <?php
                                                            }
                                                                echo $donnees['mail']?> </option>
    <?php
    }
    $req->closeCursor();
    ?>   
        </select>
        <input type="submit" name="submit2" id="submit2">
    </form>
</div>


</body>
</html>