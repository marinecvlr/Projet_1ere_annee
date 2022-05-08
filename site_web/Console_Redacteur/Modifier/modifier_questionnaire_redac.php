<?php

/**
* \file modifier_questionnaire_redac.php
* \brief Page principale de modification d'un questionnaire
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet au Rédacteur de modifier un questionnaire.
* \nSur cette page est affichée la liste des questions crées pour ce questionnaire. Ainsi, pour chaque question, est affiché son énoncé, le type de réponse(s) attendue(s) et quel est la question qui la précède, si elle existe.
* \nDes actions sont disponibles, Supprimer une question ou Modifier une question. Ces actions sont possibles uniquement si la question ne possède pas de question après elle, autrement le questionnaire n'aurait plus de sens.
*\n\nDeux autres boutons principaux permettent au Rédacteur soit d'Ajouter une nouvelle question, soit d'Ajouter les résultats au questionnaire.
*\nCette dernière action est possible si le questionnaire contient au moins une question.
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

$titre = "";
$id_question_supp = 0;
$count_case = 0;
$save_case1 = 0;
$save_case2 = 0;

// On vérifie que l'utilisateur est connecté:
if (empty($_SESSION['userid'])) 
{
    header('Location: ../../Connexion/index.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '2') 
    {
        header('Location: ../../Connexion/index.php');
        exit;
    }

if (empty($_SESSION['id_questionnaire'])) 
{
    header('Location: ../console_redac.php');
    exit;
}

if (!empty($_SESSION['id_question_modif']))
{
    unset($_SESSION['id_question_modif']);
}
if (!empty($_SESSION['numero']))
{
    unset($_SESSION['numero']);
}
if (!empty($_SESSION['nb_cases']))
{
    unset($_SESSION['nb_cases']);
}

// Si on appuie sur terminer:
if (isset($_POST['termine']))
{
    unset($_SESSION['id_questionnaire']);
    unset($_SESSION['nom_questionnaire']);
    header('Location: ../console_redac.php');
}

// On récupère le nom du questionnaire:
if (!empty($_SESSION['id_questionnaire']))
{
    $req = $bdd->prepare('SELECT nom FROM questionnaire WHERE id = :id');
    $req->execute(array('id' => $_SESSION['id_questionnaire']));
    $donnees = $req->fetch();
    $titre = $donnees['nom'];
    $_SESSION['nom_questionnaire'] = $titre; 
    $req->closeCursor();
}

// Si on appuie sur SUPPRIMER UNE QUESTION:
if (isset($_POST['supp']))
{
    $id_question_supp = $_POST['supp'];

    // On vérifie que cette question n'a pas d'enfants:
    $req = $bdd->prepare('SELECT id_question_parent FROM `question` WHERE id_question_parent = :id');
    $req->execute(array('id' => $id_question_supp));
    $donnees = $req->fetch();

    if ($donnees['id_question_parent'] != NULL)
    {
        $req->closeCursor();?>

        <script>
                alert("Cette question est parente d'autres questions dans ce questionnaire. Vous ne pouvez pas la supprimer.");
        </script>
        <?php
    }

    else
    {
        $req->closeCursor();

        // On vérifie que cette question n'a pas d'autres questions après elle:
        $req = $bdd->prepare('SELECT id_question_suivante FROM `reponse_question` WHERE id_question = :id');
        $req->execute(array('id' => $id_question_supp));
        $donnees = $req->fetch();

        if ($donnees['id_question_suivante'] != NULL)
        {
            $req->closeCursor();?>

        <script>
                alert("Vous ne pouvez pas supprimer une question qui possède d'autres questions après elle.");
        </script>
        <?php
        }

        $req->closeCursor();
        
        // J'efface les réponses des clients qui ont répondu à cette question:
        $req = $bdd->prepare('DELETE FROM `reponse_client` WHERE id_question = :id');
        $req->execute(array('id' => $id_question_supp));
        $req->closeCursor();


        // J'update l'/les id_question_suivante(s) de sa question parente à NULL:
        $req = $bdd->prepare('UPDATE `reponse_question` SET `id_question_suivante`= NULL WHERE id_question_suivante = :id and id_question = (
            SELECT id_question_parent FROM `question` WHERE id = :id)');
        $req->execute(array('id' => $id_question_supp));
        $req->closeCursor();


        // J'efface les réponses associées à la question:
        $req = $bdd->prepare('DELETE FROM `reponse_question` WHERE id_question = :id');
        $req->execute(array('id' => $id_question_supp));
        $req->closeCursor();


        // J'update à NULL l'id_question_parent de la question:
        $req = $bdd->prepare('UPDATE `question` SET `id_question_parent`= NULL WHERE id = :id ');
        $req->execute(array('id' => $id_question_supp));
        $req->closeCursor();


        // J'efface la question:
        $req = $bdd->prepare('DELETE FROM `question` WHERE id = :id');
        $req->execute(array('id' => $id_question_supp));
        $req->closeCursor();


        // Je modifie les numéros des questions suivantes:
        $req = $bdd->prepare('SELECT numero FROM `question` WHERE id_questionnaire = :id_questionnaire and id > :id_question_supp ORDER BY CAST(numero AS UNSIGNED)');
        $req->execute(array('id_question_supp' => $id_question_supp,
                            'id_questionnaire' => $_SESSION['id_questionnaire']));
        while ($donnees = $req->fetch())
        {
            $requete = $bdd->prepare('UPDATE `question` SET `numero`= :nouveau_numero WHERE `numero`= :ancien_numero');
            $requete->execute(array(
                                    'nouveau_numero' => $donnees['numero']-1,
                                    'ancien_numero'  => $donnees['numero']
                                    ));
            $requete->closeCursor();
        }
        $req->closeCursor();    

        header('Location: valid_animation_supp.php');
        exit;
    }
}

// Si on appuie sur MODIFIER UNE QUESTION:
if (isset($_POST['modif']))
{
    $_SESSION['id_question_modif'] = $_POST['modif'];

    header('Location: Modifier_question/modifier_question.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="modifier_questionnaire_redac.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Modifier un questionnaire</title>
</head>
<body>

<div class="wrapper fadeInDown">
    <div id="formContent">

        <h1>Modifier un questionnaire</h1>       


<!-- Ajouter une question -->
<?php
$req = $bdd->prepare('SELECT COUNT(*) FROM `question` WHERE id_questionnaire = :id_questionnaire');
$req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
$donnees = $req->fetch();
$nb_questions = $donnees[0];
$req->closeCursor();

if ($nb_questions <= 20)
{
    ?>
        <button id="add_question" onclick="window.location.href = 'Ajouter_question/add_question.php';">Ajouter une question</button>
    <?php
}

// J'insère les résultats:
if ($nb_questions >= 1)
{
    ?>
        <button id="add_result" onclick="window.location.href = 'Ajouter_resultat/add_result.php';">Ajouter les résultats</button>
    <?php
}
?>

        <p class="nom_questionnaire"><?php echo $titre;?> </p>
<?php

// On récupère la liste des questions:
$req = $bdd->prepare('SELECT * 
                      FROM question 
                      INNER JOIN reponse_question 
                      ON question.id = reponse_question.id_question 
                      WHERE question.id_questionnaire = :id
                      ORDER BY CAST(question.numero AS UNSIGNED)');

$req->execute(array('id' => $_SESSION['id_questionnaire']));

while ($donnees = $req->fetch())
    {      
        if ($donnees['type'] == 1) // Saisie de texte
        {            
            ?>
            <div class = "question">

            <?php
                // Je vérifie que cette question ne possède pas de suivante:
                if ($donnees['id_question_suivante'] == NULL)
                {
                    ?>
                    <form action="modifier_questionnaire_redac.php" method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer la question ? Cette action est irréversible');">
                        <input type="hidden" name="supp" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="poubelle" class="poubelle" value="" ></input>
                    </form>

                    <form action="modifier_questionnaire_redac.php" method="post">
                        <input type="hidden" name="modif" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="crayon" class="crayon" value="" ></input>
                    </form>
                    <?php
                }
            ?>


                <p class="enonce"> <?php echo($donnees['numero'] . ') ' . $donnees['enonce']);?></p>

                <form action="modifier_questionnaire_redac.php" method="post"> 
                    <input type="text" name="rep<?php echo $donnees['numero']?>" placeholder="Saisir votre réponse" required><br> <br>

                    <?php
                        if ($donnees['id_question_parent'] != NULL)
                        {
                            // On récupère le nom de la question parente:
                            $requete = $bdd->prepare('SELECT enonce FROM `question` WHERE id = :id_quest_parent');
                            $requete->execute(array('id_quest_parent' => $donnees['id_question_parent']));
                            $donnees = $requete->fetch();
                            $nom_parent = $donnees['enonce'];
                            $requete->closeCursor();

                            ?>
                            <p class= 'enfant'>Enfant de "<?php echo $nom_parent?>"</p><?php
                        }?> 
                </form>
            </div> <?php
        }
        else if ($donnees['type'] == 2) // Saisie de nombre
        {
            ?> <div class = "question">
                
                <?php
                // Je vérifie que cette question ne possède pas de suivante:
                if ($donnees['id_question_suivante'] == NULL)
                {
                    ?>
                    <form action="modifier_questionnaire_redac.php" method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer la question ? Cette action est irréversible');">
                        <input type="hidden" name="supp" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="poubelle" class="poubelle" value="" ></input>
                    </form>

                    <form action="modifier_questionnaire_redac.php" method="post">
                        <input type="hidden" name="modif" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="crayon" class="crayon" value="" ></input>
                    </form>
                    <?php
                }
            ?>

                    <p class="enonce"><?php echo($donnees['numero'] . ') ' . $donnees['enonce']);?></p>

                    <form action="modifier_questionnaire_redac.php" method="post"> 
                        <input type="number" name="rep<?php echo $donnees['numero']?>" placeholder="Saisir votre réponse" required><br> <br>

                        <?php
                        if ($donnees['id_question_parent'] != NULL)
                        {
                            // On récupère le nom de la question parente:
                            $requete = $bdd->prepare('SELECT enonce FROM `question` WHERE id = :id_quest_parent');
                            $requete->execute(array('id_quest_parent' => $donnees['id_question_parent']));
                            $donnees = $requete->fetch();
                            $nom_parent = $donnees['enonce'];
                            $requete->closeCursor();

                            ?>
                            <p class= 'enfant'>Enfant de "<?php echo $nom_parent?>"</p><?php
                        }?>

                    </form>

            </div> <?php 
        }
        else if ($donnees['type'] == 3) // Saisie de date
        {
            ?> <div class = "question">

            <?php
                // Je vérifie que cette question ne possède pas de suivante:
                if ($donnees['id_question_suivante'] == NULL)
                {
                    ?>
                    <form action="modifier_questionnaire_redac.php" method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer la question ? Cette action est irréversible');">
                        <input type="hidden" name="supp" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="poubelle" class="poubelle" value="" ></input>
                    </form>

                    <form action="modifier_questionnaire_redac.php" method="post">
                        <input type="hidden" name="modif" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="crayon" class="crayon" value="" ></input>
                    </form>
                    <?php
                }
            ?>

            <p class="enonce"><?php echo($donnees['numero'] . ') ' . $donnees['enonce']);?></p>

            <form action="modifier_questionnaire_redac.php" method="post"> 
                <input type="date" name="rep<?php echo $donnees['numero']?>" placeholder="Saisir votre réponse" required><br> <br>

                <?php
                        if ($donnees['id_question_parent'] != NULL)
                        {
                            // On récupère le nom de la question parente:
                            $requete = $bdd->prepare('SELECT enonce FROM `question` WHERE id = :id_quest_parent');
                            $requete->execute(array('id_quest_parent' => $donnees['id_question_parent']));
                            $donnees = $requete->fetch();
                            $nom_parent = $donnees['enonce'];
                            $requete->closeCursor();

                            ?>
                            <p class= 'enfant'>Enfant de "<?php echo $nom_parent?>"</p><?php
                        }?>
            </form>
            
            </div> <?php 
        }
        else if ($donnees['type'] == 4) // Boutons radio
        {
            $save_case2 = $save_case1;
            $save_case1 = $donnees['id_question'];


            if ($save_case1 != $save_case2) // On affiche la question et une réponse
            {       
                ?> <div class = "question">

                <?php
                // Je vérifie que cette question ne possède pas de suivante:
                if ($donnees['id_question_suivante'] == NULL)
                {
                    ?>
                    <form action="modifier_questionnaire_redac.php" method="post" onsubmit="return confirm('Voulez-vous vraiment supprimer la question ? Cette action est irréversible');">
                        <input type="hidden" name="supp" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="poubelle" class="poubelle" value="" ></input>
                    </form>

                    <form action="modifier_questionnaire_redac.php" method="post">
                        <input type="hidden" name="modif" value="<?php echo($donnees['id'])?>">
                        <input type="submit" name="crayon" class="crayon" value="" ></input>
                    </form>
                    <?php
                }
                ?>

                        <p class="enonce"><?php echo($donnees['numero'] . ') ' . $donnees['enonce']);?></p>

                <form action="modifier_questionnaire_redac.php" method="post"> 
                    <input type="radio" name="rep<?php echo $donnees['id_question'] . '-' . $donnees['numero']?>" value ="<?php echo $donnees['reponse_possible']?>"/> <label for="<?php echo $donnees['reponse_possible']?>"> <?php echo $donnees['reponse_possible']?></label><br />
                 
                 <?php 
            }
            else // On affiche seulement une réponse
            {
                ?>
                    <input type="radio" name="rep<?php echo $donnees['id_question'] . '-' . $donnees['numero']?>" value ="<?php echo $donnees['reponse_possible']?>"/> <label for="<?php echo $donnees['reponse_possible']?>"> <?php echo $donnees['reponse_possible']?></label><br />
                <?php
            }

            $count_case +=1;
            if ($count_case == $donnees['nb_cases'])
            {
                if ($donnees['id_question_parent'] != NULL)
                {
                    // On récupère le nom de la question parente:
                    $requete = $bdd->prepare('SELECT enonce FROM `question` WHERE id = :id_quest_parent');
                    $requete->execute(array('id_quest_parent' => $donnees['id_question_parent']));
                    $donnees = $requete->fetch();
                    $nom_parent = $donnees['enonce'];
                    $requete->closeCursor();

                    ?>
                    <p class= 'enfant'>Enfant de "<?php echo $nom_parent?>"</p><?php
                }
                echo '</form></div>';
                $count_case = 0;
            }
        }

    }
    $req->closeCursor();

    // Je récupère les résultat du questionnaire:
    $requete = $bdd->prepare('SELECT resultat FROM `resultat_questionnaire` where id_questionnaire = :id_questionnaire and resultat is null');
    $requete->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
    if ($donnees = $requete->fetch())
    {
        ?>
        <!-- Bouton Valider -->
        <form action="modifier_questionnaire_redac.php" method="post" onsubmit="return confirm('Vous n\'avez pas encore défini les résultats pour votre questionnaire. Si vous ne le faites pas, il ne pourra pas être rempli par les clients. Etes-vous sûr de quitter ?');">
            <input type="submit" class="termine" name="termine" value="Terminer" />
        </form> 
        <?php
    }
    else
    {
        ?>
        <!-- Bouton Valider -->
        <form action="modifier_questionnaire_redac.php" method="post">
            <input type="submit" class="termine" name="termine" value="Terminer" />
        </form> 
        <?php
    }
    $requete->closeCursor();      
?>
</div>
</div>
</body>
</html>