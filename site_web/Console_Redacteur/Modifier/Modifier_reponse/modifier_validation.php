<?php

/**
* \file modifier_validation.php
* \brief Page de validation d'une modification de question
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page contient tous les traitements qui permettent au rédacteur de modifier les informations d'une question.
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
      
if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_SESSION['nb_cases']) && !empty($_SESSION['id_question_modif']))
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
                
                        <form action="modifier_validation.php" method="post"> 
                
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
                        <form action="modifier_validation.php" method="post">
                            <input type="submit" class="annuler" name="cancel" value="Annuler" />
                        </form>

                    </div>
                </div>
                <?php
            }
            else if ($test_reponse == 0)
            {
                if ($_SESSION['parent'] == NULL)
                {
                   // J'efface les réponses des clients qui ont répondu à cette question:
                    $req = $bdd->prepare('DELETE FROM `reponse_client` WHERE id_question = :id');
                    $req->execute(array('id' => $_SESSION['id_question_modif']));
                    $req->closeCursor();



                    // J'update l'/les id_question_suivante(s) de sa question parente à NULL:
                    $req = $bdd->prepare('UPDATE `reponse_question` SET `id_question_suivante`= NULL WHERE id_question_suivante = :id and id_question = (
                                    SELECT id_question_parent FROM `question` WHERE id = :id)');
                    $req->execute(array('id' => $_SESSION['id_question_modif']));
                    $req->closeCursor();
        
        
                    // On efface les réponses de la question:
                    $req = $bdd->prepare('DELETE FROM `reponse_question` WHERE id_question = :id');
                    $req->execute(array('id' => $_SESSION['id_question_modif']));
                    $req->closeCursor();


                    // On modifie la question:
                    $req = $bdd->prepare('UPDATE `question` SET `enonce`= :enonce,`id_question_parent`= :parent, `type`= :type,`nb_cases`= :nb_cases WHERE id = :id');
                    $req->execute(array(
                                    'enonce'   => $_SESSION['enonce'],
                                    'parent'   => $_SESSION['parent'],
                                    'type'     => $_SESSION['type'],
                                    'nb_cases' => $_SESSION['nb_cases'],
                                    'id'       => $_SESSION['id_question_modif']
                                    ));
                    $req->closeCursor();


                    // On ajoute les réponses possibles à la BDD:
                    $compteur  = 1;
                    $compteur2 = 0;

                    while ($compteur <= $_SESSION['nb_cases'])
                    {
                        $req = $bdd->prepare('INSERT INTO reponse_question (id_question, numero_case, reponse_possible) VALUES(:id_question, :numero_case, :reponse_possible)');
                        $req->execute(array(
                                            'id_question'      => $_SESSION['id_question_modif'],
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

                    header('Location: valid_animation2.php');
                    exit;
                }
                else if ($_SESSION['parent'] != NULL)
                {   
                    // J'efface les réponses des clients qui ont répondu à cette question:
                    $req = $bdd->prepare('DELETE FROM `reponse_client` WHERE id_question = :id');
                    $req->execute(array('id' => $_SESSION['id_question_modif']));
                    $req->closeCursor();



                    // J'update l'/les id_question_suivante(s) de sa question parente à NULL:
                    $req = $bdd->prepare('UPDATE `reponse_question` SET `id_question_suivante`= NULL WHERE id_question_suivante = :id and id_question = (
                                          SELECT id_question_parent FROM `question` WHERE id = :id)');
                    $req->execute(array('id' => $_SESSION['id_question_modif']));
                    $req->closeCursor();
        
        
                    // On efface les réponses de la question:
                    $req = $bdd->prepare('DELETE FROM `reponse_question` WHERE id_question = :id');
                    $req->execute(array('id' => $_SESSION['id_question_modif']));
                    $req->closeCursor();


                    // On modifie la question:
                    $req = $bdd->prepare('UPDATE `question` SET `enonce`= :enonce,`id_question_parent`= :parent, `type`= :type,`nb_cases`= :nb_cases WHERE id = :id');
                    $req->execute(array(
                                        'enonce'   => $_SESSION['enonce'],
                                        'parent'   => $_SESSION['parent'],
                                        'type'     => $_SESSION['type'],
                                        'nb_cases' => $_SESSION['nb_cases'],
                                        'id'       => $_SESSION['id_question_modif']
                                        ));
                    $req->closeCursor();

                    // J'ajoute l'id_question_suivante à la question parente:
                    $req = $bdd->prepare('UPDATE `reponse_question` SET `id_question_suivante`= :id_question_suivante WHERE id_question = :id_question and numero_case = :numero_case');
                    $req->execute(array(
                                        'id_question_suivante' => $_SESSION['id_question_modif'],
                                        'id_question'          => $_SESSION['parent'],
                                        'numero_case'          => $_SESSION['reponse_parente']
                                        ));    
                    $req->closeCursor();


                    // On ajoute les réponses possibles à la BDD:
                    $compteur  = 1;
                    $compteur2 = 0;

                    while ($compteur <= $_SESSION['nb_cases'])
                    {
                        $req = $bdd->prepare('INSERT INTO reponse_question (id_question, numero_case, reponse_possible) VALUES(:id_question, :numero_case, :reponse_possible)');
                        $req->execute(array(
                                        'id_question'      => $_SESSION['id_question_modif'],
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

                header('Location: valid_animation2.php');
                exit;
                }
                
            }
}
else
{
    ?>
        <div id="validate" style="text-align: center; margin-top: 15%;">
            <img src="valid.gif" style="max-width: 10%; height: auto;" alt="valid">
            <p style="font-family: 'Script MT'; font-size: 30px;">Question modifiée avec succès</p>
        </div>

        <?php
        unset($_SESSION['enonce']);
        unset($_SESSION['type']);
        unset($_SESSION['parent']);
        unset($_SESSION['numero']);
        unset($_SESSION['nb_cases']);
        unset($_SESSION['id_question_modif']);
        ?>
        
        <meta http-equiv="refresh" content="3; URL=../modifier_questionnaire_redac.php">
    <?php
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="modifier_validation.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="add_question.js"></script>
    <title>Modifier une question</title>
</head>
<body>

</body>
</html>