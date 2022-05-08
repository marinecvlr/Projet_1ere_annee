<?php

/**
* \file console_client.php
* \brief Page principale d'un utilisateur client
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page constitue la page personnelle d'un utilisateur Client.
*\nA partir de celle-ci, il peut effectuer différentes actions liées à son statut, telles que:
*\n- Remplir un nouveau questionnaire, s'il y en a un.
*\n- Visualiser les questionnaires qu'il aurait remplis, ou en cours de remplissage, s'il y en a.
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
    
if (isset ($_POST['deconnect']))
{
    session_destroy();
    header('Location: ../Connexion/index.php');        
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '1') 
    {
        header('Location: ../Connexion/index.php');
        exit;
    }

// On vérifie que l'utilisateur est connecté:
if (!isset($_SESSION['userid'])) 
{
    header('Location: ../Connexion/index.php');
}

// Remplir un questionnaire:
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // On vérifie que les champs sont remplis:
    if (!empty($_POST['liste'])) 
    {
        // On récupère les infos et on redirige:
        $_SESSION['id_questionnaire'] = ($_POST['liste']);
        header('Location: Remplir/remplir_questionnaire_client.php');
        exit;
    }
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
    <script type="text/javascript" src="console_client.js"></script>
    <title>Console Client</title>
</head>
<body>
    
<div class="bandeau">
    <h1><?php echo 'Bienvenue, ' . $_SESSION['prenom'] . ' ' . $_SESSION['nom'] ?></h1>
    
    <!-- Bouton Déconnexion:-->
    <form action="console_client.php" method="post">
        <input type="submit" name="deconnect" value="Déconnexion" />
    </form>
</div>
    <button onclick="window.location.href = 'Questionnaires/questionnaires_client.php';">Mes questionnaires remplis</button>
    <button class = "button"  onclick="bascule('header');"> Remplir un questionnaire </button>


<div id="header" style="visibility: hidden">
<form action="console_client.php" method="post">
<label for="liste">Sélectionnez un questionnaire</label><br />
        <select name="liste" id="liste">

<?php
$quest = array();
$quest_client = array();
$count = 0;

// On récupère la liste des questionnaires:
$req = $bdd->query('SELECT id FROM questionnaire'); 
while ($donnees = $req->fetch())
{
    array_push($quest, $donnees['id']);
}
$req->closeCursor();

//On récupère la liste des questionnaires remplis par le client:
$req = $bdd->prepare('SELECT id_questionnaire FROM questionnaire_client WHERE id_client = :id_client');
$req->execute(array('id_client' => $_SESSION['userid']));
while ($donnees = $req->fetch())
{
    array_push($quest_client, $donnees['id_questionnaire']);
}
$req->closeCursor();

// On récupère la liste des questionnaires qui ne sont pas remplis par le client et qui ont tous leurs résultats:
$result = array_diff($quest, $quest_client);
$count = array_key_first($result);
$max = sizeof($result)+$count;

while ($count < $max)
{
    $req = $bdd->prepare('SELECT DISTINCT questionnaire.id, questionnaire.nom 
                          FROM questionnaire 
                          INNER JOIN resultat_questionnaire 
                            ON questionnaire.id = resultat_questionnaire.id_questionnaire 
                          WHERE questionnaire.id = :id and resultat_questionnaire.resultat is not null');
    $req->execute(array('id' => $result[$count]));
    while ($donnees = $req->fetch())
    {
        ?>
            <option value="<?php echo $donnees['id']?>"> <?php echo $donnees['nom']?> </option>
        <?php
    }
    $req->closeCursor();
    $count +=1;
}
?>
        </select>
        <input type="submit" name="submit">
    </form>
</div>
</body>
</html>