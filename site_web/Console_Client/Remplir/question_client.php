<?php

/**
* \file question_client.php
* \brief Affichage de la question à remplir par le client
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet au Client de répondre à une question lorsqu'il est entrain de remplir un questionnaire.
* \n Elle affiche ainsi l'énoncé de la question, la réponse à saisir ou à sélectionner, ainsi qu'un bouton pour valider et un autre pour quitter le questionnaire.
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
    header('Location: ../../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '1') 
    {
        header('Location: ../../Connexion/index.php');
        exit;
    }


// Si on appuie sur Retour:
if (isset($_POST['annuler']))
{
    unset($_SESSION['id']);
    unset($_SESSION['enonce']);
    unset($_SESSION['id_question_parent']);
    unset($_SESSION['type']);
    unset($_SESSION['nb_cases']);
    unset($_SESSION['chemin_client']);
    unset($_SESSION['position']);
    
    header('Location: remplir_questionnaire_client.php');
    exit;
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
<div class="wrapper fadeInDown">
    <div id="formContent">
        <h1 class="titre"><?php echo $_SESSION['nom_questionnaire'];?> </h1>
        
<?php

// On récupère le nombre de questions remplies:
$req = $bdd->prepare('SELECT COUNT(*) FROM `reponse_client` WHERE id_client = :id_client and id_questionnaire = :id_questionnaire');
$req->execute(array('id_client'        => $_SESSION['userid'],
                    'id_questionnaire' => $_SESSION['id_questionnaire']));
$donnees = $req->fetch();
$_SESSION['position'] = $donnees[0];
$req->closeCursor();

if ($_SESSION['position'] == 0)
{
    $_SESSION['position'] +=1;
    $_SESSION['chemin_client'] = array(); // Permettra de définir le résultat du client au questionnaire

    // On affiche la première question:
    $req = $bdd->prepare('SELECT * FROM `question` where id_question_parent is null and id_questionnaire = :id_questionnaire');
    $req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));

    while($donnees = $req->fetch())
    {
        $_SESSION['id']                 = $donnees['id'];
        $_SESSION['enonce']             = $donnees['enonce'];
        $_SESSION['id_question_parent'] = $donnees['id_question_parent'];
        $_SESSION['type']               = $donnees['type'];
        $_SESSION['nb_cases']           = $donnees['nb_cases'];


            if ($_SESSION['type'] == 1)
            {
                ?>
                <h1 class="nom_question"> <?php echo $_SESSION['position'] . ') ' . $donnees['enonce']?></h1>

                <form action="traitement.php" method="post"> 
                    <input type="text" name="reponse" placeholder="Saisir votre réponse" required><br> <br>

                    <input type="submit" class="termine" name="termine" value="Suivant" />
                </form>
                <?php
            }
            else if ($_SESSION['type'] == 2)
            {
                ?>
                <h1 class="nom_question"> <?php echo($_SESSION['position'] . ') ' . $donnees['enonce']);?></h1>
                
                <form action="traitement.php" method="post"> 
                    <input type="number" name="reponse" placeholder="Saisir votre réponse" required><br> <br>

                    <input type="submit" class="termine" name="termine" value="Suivant" />
                </form>
                <?php
            }
            else if ($_SESSION['type'] == 3)
            {
                ?>
                <h1 class="nom_question"> <?php echo($_SESSION['position'] . ') ' . $donnees['enonce']);?></h1>
                    
                <form action="traitement.php" method="post"> 
                    <input type="date" name="reponse" placeholder="Saisir votre réponse" required><br> <br>

                    <input type="submit" class="termine" name="termine" value="Suivant" />
                </form>
                <?php
            }
            else if ($_SESSION['type'] == 4)
            {
                ?>
                <h1 class="nom_question"> <?php echo($_SESSION['position'] . ') ' . $donnees['enonce']);?></h1>
                    
                <form action="traitement.php" method="post">
                    <?php
                    
                    // On récupère les réponses possibles de la question:
                    $requete = $bdd->prepare('SELECT * FROM reponse_question WHERE id_question = :id_question');
                    $requete->execute(array('id_question' => $_SESSION['id']));
                    while($sortie = $requete->fetch())
                    {
                        ?>
                            <input type="radio" name = "reponse" value="<?php echo $sortie['numero_case']?>" /> <label for="<?php echo $sortie['numero_case']?>"> <?php echo $sortie['reponse_possible']?></label><br />
                        <?php
                    }
                    $requete->closeCursor();

                    ?>
                    <input type="submit" class="termine" name="termine" value="Suivant" />
                </form>
                <?php    
            }


            ?>
            </form>
        <?php
    }
    $req->closeCursor();
}
else
{
    if (!isset($_SESSION['chemin_client'])) 
    {
        // On récupère le chemin du client
        $_SESSION['chemin_client'] = array();
        $count = 1;
        
        while ($count <= $_SESSION['position'])
        {
            $req = $bdd->prepare('SELECT id_question, reponse FROM `reponse_client` where id_client = :id_client and id_questionnaire = :id_questionnaire and numero = :numero');
            $req->execute(array('id_client'        => $_SESSION['userid'],
                                'id_questionnaire' => $_SESSION['id_questionnaire'],
                                'numero'           => $count));
            $donnees = $req->fetch();
            array_push($_SESSION['chemin_client'], $donnees['id_question']);
            array_push($_SESSION['chemin_client'], $donnees['reponse']);
            $req->closeCursor();
            $count+=1;
        }

    }
    

        // On récupère le nombre de questions remplies:
        $req = $bdd->prepare('SELECT COUNT(*) FROM `reponse_client` WHERE id_client = :id_client and id_questionnaire = :id_questionnaire');
        $req->execute(array('id_client'        => $_SESSION['userid'],
                            'id_questionnaire' => $_SESSION['id_questionnaire']));
        $donnees = $req->fetch();
        $_SESSION['position'] = $donnees[0]+1;
        $req->closeCursor();


    // On récupère la question suivante:
    $last = end($_SESSION['chemin_client']);
    $prev_last = prev($_SESSION['chemin_client']);

    $requete = $bdd->prepare('SELECT id_question_suivante FROM `reponse_question` where id_question = :id_question and numero_case = :numero_case');
    $requete->execute(array('id_question' => $prev_last,
                            'numero_case' => $last
                            ));
    $sortie = $requete->fetch();
    $_SESSION['id'] = $sortie['id_question_suivante'];
    $requete->closeCursor();

    // On affiche la question:
    $req = $bdd->prepare('SELECT * FROM `question` where id = :id');
    $req->execute(array('id' => $_SESSION['id']));

    while($donnees = $req->fetch())
    {
        $_SESSION['enonce']             = $donnees['enonce'];
        $_SESSION['id_question_parent'] = $donnees['id_question_parent'];
        $_SESSION['type']               = $donnees['type'];
        $_SESSION['nb_cases']           = $donnees['nb_cases'];
    }
    $req->closeCursor();


    

    if ($_SESSION['type'] == 1)
    {
        ?>
        <h1 class="nom_question"> <?php echo $_SESSION['position'] . ') ' . $_SESSION['enonce']?></h1>

        <form action="traitement.php" method="post"> 
            <input type="text" name="reponse" placeholder="Saisir votre réponse" required><br> <br>

            <input type="submit" class="termine" name="termine" value="Suivant" />
        </form>
        <?php
    }
    else if ($_SESSION['type'] == 2)
    {
        ?>
        <h1 class="nom_question"> <?php echo $_SESSION['position'] . ') ' . $_SESSION['enonce']?></h1>
                
        <form action="traitement.php" method="post"> 
            <input type="number" name="reponse" placeholder="Saisir votre réponse" required><br> <br>

            <input type="submit" class="termine" name="termine" value="Suivant" />
        </form>
        <?php
    }
    else if ($_SESSION['type'] == 3)
    {
        ?>
        <h1 class="nom_question"> <?php echo $_SESSION['position'] . ') ' . $_SESSION['enonce']?></h1>
                    
        <form action="traitement.php" method="post"> 
            <input type="date" name="reponse" placeholder="Saisir votre réponse" required><br> <br>

            <input type="submit" class="termine" name="termine" value="Suivant" />
        </form>
        <?php
    }
    else if ($_SESSION['type'] == 4)
    {
        ?>
        <h1 class="nom_question"> <?php echo $_SESSION['position'] . ') ' . $_SESSION['enonce']?></h1>
                    
        <form action="traitement.php" method="post">
            <?php
                    
            // On récupère les réponses possibles de la question:
            $requete = $bdd->prepare('SELECT * FROM reponse_question WHERE id_question = :id_question');
            $requete->execute(array('id_question' => $_SESSION['id']));
            while($sortie = $requete->fetch())
            {
                ?>
                    <input type="radio" name = "reponse" value="<?php echo $sortie['numero_case']?>" /> <label for="<?php echo $sortie['numero_case']?>"> <?php echo $sortie['reponse_possible']?></label><br />
                <?php
            }
            $requete->closeCursor();

            ?>
            <input type="submit" class="termine" name="termine" value="Suivant" />
        </form>
        <?php
    
    }
}
?>    
    <!-- Bouton Annuler -->
    <form action="question_client.php" method="post">
        <input type="submit" class="annuler" name="annuler" value="Quitter" />
    </form>   

</div>
</div>
</body>
</html>