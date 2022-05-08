<?php

/**
* \file validation.php
* \brief Page de traitement de l'ajout d'une nouvelle question
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page, en fonction des informations saisies pour ajouter la question, affiche les différents formulaires pour saisir les réponses.
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


// On teste la session:
if (empty($_SESSION['userid'])) 
{
    header('Location: ../../../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '2') 
    {
        header('Location: ../../../Connexion/index.php');
        exit;
    }

if (empty($_SESSION['id_questionnaire']) or empty($_SESSION['nom_questionnaire']) or empty($_SESSION['numero']) or empty($_SESSION['enonce']) or empty($_SESSION['type'])) 
{
    header('Location: ../../console_redac.php');
    exit;
}
    
// Si on appuie sur ANNULER:
if (isset($_POST['cancel']))
{
    unset($_SESSION['enonce']);
    unset($_SESSION['type']);
    unset($_SESSION['parent']);
    unset($_SESSION['numero']);
    unset($_SESSION['nb_cases']);
    unset($_SESSION['reponse_parente']);

    header('Location: ../modifier_questionnaire_redac.php');
    exit;
}


if ($_SERVER["REQUEST_METHOD"] == "POST")
{
      
    if ($_SESSION['parent'] == NULL)
        {     
            $test_reponse = 0;
            
            // On récupère les infos du formulaire:
            $compteur = 0;
            $compteur2 = 1;
            $tableau = array();

            while ($compteur < $_SESSION['nb_cases'])
            {
                array_push($tableau, $_POST["case_$compteur2"]);
                $compteur2 +=1;
                $compteur +=1;
            }
            
            $dernier = sizeof($tableau);
            $base = 0;
            
            while ($base < $dernier)
            {
                $compteur = $base;
                $compteur2 = $compteur+1;
                
                while ($compteur2 < $dernier)
                {    
                    if ($tableau[$compteur] == $tableau[$compteur2])
                    {
                        $erreur = 'Vous ne pouvez pas donner deux réponses identiques';
                        $test_reponse = 1;
                    }
                    $compteur2 +=1;
                }
                $base +=1;
            }

            if ($test_reponse == 1)
            {
                $count = 0;
                ?>

                <div class="wrapper fadeInDown">
                    <div id="formContent">
                    
                        <h1>Configurer les réponses2</h1>
                             <p class="nom_question"><?php echo $_SESSION['enonce'];?> </p>
                
                        <form action="validation.php" method="post"> 
                
                            <?php
                            while ($count < $_SESSION['nb_cases'])
                            {
                                $count +=1;
                                ?> <label>Réponse <?php echo $count?> :</label><input type="text" name="case_<?php echo $count?>" required/> <br> <?php
                            }
                            ?>
			                <input type="submit" class="termine" value="Terminer">
                        </form>
                        
                        <?php
                        echo('<p class="msg">'. $erreur. '</p>');
                        ?>

                        <!-- Bouton Annuler -->
                        <form action="validation.php" method="post">
                            <input type="submit" class="annuler" name="cancel" value="Annuler" />
                        </form>

                    </div>
                </div>
                <?php
            }
            else if ($test_reponse == 0)
            {
                // On ajoute la question à la BDD:
                $req = $bdd->prepare('INSERT INTO question (numero, enonce, id_question_parent, id_questionnaire, type, nb_cases) VALUES(:numero, :enonce, :id_question_parent, :id_questionnaire, :type, :nb_cases)');
                $req->execute(array(
                                    'numero'             => $_SESSION['numero'],
                                    'enonce'             => $_SESSION['enonce'],
                                    'id_question_parent' => $_SESSION['parent'],
                                    'id_questionnaire'   => $_SESSION['id_questionnaire'],
                                    'type'               => $_SESSION['type'],
                                    'nb_cases'           => $_SESSION['nb_cases']
                                    ));
                $req->closeCursor();

                // On récupère l'ID de la question:
                $req = $bdd->prepare('SELECT id FROM question WHERE numero = :numero and id_questionnaire = :id_questionnaire ');
                $req->execute(array('numero'           => $_SESSION['numero'],
                                    'id_questionnaire' => $_SESSION['id_questionnaire']
                                    ));

                $donnees = $req->fetch();
                $id_question = $donnees['id'];
                $req->closeCursor();
    
                // On ajoute les réponses possibles à la BDD:
                $compteur  = 1;
                $compteur2 = 0;
        
                while ($compteur <= $_SESSION['nb_cases'])
                {
                    $req = $bdd->prepare('INSERT INTO reponse_question (id_question, numero_case, reponse_possible) VALUES(:id_question, :numero_case, :reponse_possible)');
                    $req->execute(array(
                                        'id_question'      => $id_question,
                                        'numero_case'      => $compteur,
                                        'reponse_possible' => $tableau[$compteur2]
                                        ));    
                    $req->closeCursor();
                    $compteur  +=1;
                    $compteur2 +=1;
                }

                // On efface les résultats du client puisque les chemins ont changé:
                $req = $bdd->prepare('DELETE FROM `questionnaire_client` WHERE id_questionnaire = :id');
                $req->execute(array('id' => $_SESSION['id_questionnaire'] ));    
                $req->closeCursor();

                // On efface les résultats du questionnaire puisque les chemins ont changé:
                $req = $bdd->prepare('DELETE FROM `resultat_questionnaire` WHERE id_questionnaire = :id');
                $req->execute(array('id' => $_SESSION['id_questionnaire'] ));    
                $req->closeCursor();
        
                header('Location: valid_animation.php');
                exit;
            }
        }
    }
            
           
    if (isset($_POST['case2']))
    {
        $nb_cases = $_POST['case2'];
        $_SESSION['nb_cases'] = $nb_cases;
        $compteur = 0;
        ?>

        <div class="wrapper fadeInDown">
        <div id="formContent">
                    
            <h1>Configurer les réponses</h1>
            <p class="nom_question"><?php echo $_SESSION['enonce'];?> </p>
                
            <form action="validation2.php" method="post"> 
                
                <?php
                while ($compteur < $_SESSION['nb_cases'])
                {
                    $compteur +=1;
                    ?> <label>Réponse <?php echo $compteur?> :</label><input type="text" name="case_<?php echo $compteur?>" required/> <br> <?php
                }
                ?>
			        <input type="submit" class="termine" value="Terminer">
            </form>

            <!-- Bouton Annuler -->
            <form action="add_reponse.php" method="post">
                <input type="submit" class="annuler" name="cancel" value="Annuler" />
            </form>
            <?php
      
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="validation.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="add_question.js"></script>
    <title>Ajouter une question</title>
</head>
<body>

</body>
</html>