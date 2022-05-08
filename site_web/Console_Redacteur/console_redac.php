<?php

/**
* \file console_redac.php
* \brief Page principale d'un utilisateur rédacteur
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page constitue la page personnelle d'un utilisateur Rédacteur.
*\nA partir de celle-ci, il peut effectuer différentes actions liées à son statut, telles que:
*\n- Créer un nouveau questionnaire
*\n- Modifier un questionnaire existant
*\n- Supprimer un de ses questionnaires, ce qui aura pour effet d'effacer les résultats des clients au questionnaire en question.
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
if ($_SESSION['statut'] != '2') 
{
    header('Location: ../Connexion/index.php');
    exit;
}

// Modifier un questionnaire:
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // On vérifie que les champs sont remplis:
    if (!empty($_POST['liste'])) 
    {
        // On récupère les infos et on redirige:
        $_SESSION['id_questionnaire'] = ($_POST['liste']);
        header('Location: Modifier/modifier_questionnaire_redac.php');
        exit;
    }
}

// Supprimer un questionnaire:
    if ($_SERVER["REQUEST_METHOD"] == "POST")
    {
        // On vérifie que les champs sont remplis:
        if (!empty($_POST['liste2'])) 
        {
            // On récupère les infos et on redirige:
            $_SESSION['id_quest_supp'] = ($_POST['liste2']);
    
            header('Location: Supprimer/supprimer_questionnaire_redac.php');
            exit;
        }
    }

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="console_redac.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="console_redac.js"></script>
    <title>Console Rédacteur</title>
</head>
<body>
    
<div class="bandeau">
    <h1><?php echo 'Bienvenue, ' . $_SESSION['prenom'] . ' ' . $_SESSION['nom'] ?></h1>
    
    <!-- Bouton Déconnexion:-->
    <form action="console_redac.php" method="post">
        <input type="submit" name="deconnect" value="Déconnexion" />
    </form>
</div>

<button onclick="window.location.href = 'Creer/creer_questionnaire_redac.php';">Créer un questionnaire</button>
<button class = "button"  onclick="bascule('header');"> Modifier un questionnaire </button>
<button class = "button"  onclick="bascule('header2');">Supprimer un questionnaire</button>

<?php   // Modifier un questionnaire:
$req = $bdd->prepare('SELECT id, nom FROM `questionnaire` WHERE id_auteur = :id');
$req->execute(array('id' => $_SESSION['userid']));
?>

<div id="header" style="visibility: hidden">
    <form action="console_redac.php" method="post">
        <label for="liste">Sélectionnez un questionnaire</label><br />
        <select name="liste" id="liste">


<?php
     while ($donnees = $req->fetch())
    {
    ?>
        <option value="<?php echo $donnees['id']?>"> <?php echo $donnees['nom']?> </option>
    <?php
    }
    $req->closeCursor();
    ?>   
        </select>
        <input type="submit" name="submit">
    </form>
</div>



<?php   // Supprimer un questionnaire:
$req = $bdd->query('SELECT id, nom FROM `questionnaire`');
?>

<div id="header2" style="visibility: hidden">
    <form onsubmit = "return confirm('Vous êtes sur le point d\'effacer définitivement un questionnaire et tout ce qu\'il contient. Cette action est irréversible. Voulez-vous continuer ?');" action="console_redac.php" method="post">
        <label for="liste2">Sélectionnez un questionnaire</label><br />
        <select name="liste2" id="liste2">


<?php
     while ($donnees = $req->fetch())
    {
    ?>
        <option value="<?php echo $donnees['id']?>"> <?php echo $donnees['nom']?> </option>
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