<?php

/**
* \file add_reponse.php
* \brief Page de configuration des réponses à la nouvelle question
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page, accessible une fois que le Rédacteur a saisi une nouvelle question à ajouter, permet de configurer en détail cette nouvelle question.
* \nEn fonction des informations données à la page d'ajout d'une question, cette page-là affichera des éléments différents.
* \n\n- Si l'utilisateur n'a pas choisi de Question Parente, (ce choix est obligatoire à partir du moment où le questionnaire contient au moins une question) on réaffiche alors le formulaire pour ajouter une question, en précisant l'erreur.
* \n\n- Si la question possède un type de réponse de saisie de texte, de nombre ou de date, et sans question parente, alors la configuration est terminée.
* \n\n- Si la question possède un type de réponse de saisie de texte, de nombre ou de date, avec question parente, la page demandera à l'utilisateur de sélectionner la réponse de la question précédente qui amènera vers cette question.
* \n\n- Si la question possède un type de réponse à choix multiple, sans question parente, la page demandera à l'utilisateur de sélectionner combien de réponses maximum, et de donner un énoncé à chacune.
* \n\n- Si la question possède un type de réponse à choix multiple, avec question parente, la page demandera à l'utilisateur de sélectionner quelle réponse amènera vers cette question et combien de réponses elle possèdera, ainsi que leur énoncé.
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

if (empty($_SESSION['id_questionnaire']) or empty($_SESSION['nom_questionnaire'])) 
{
    header('Location: ../../console_redac.php');
    exit;
}
      
// On récupère les infos du formulaire:
if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    if(isset($_POST['enonce']) && isset($_POST['type']))
    {
        // On vérifie que la question peut ne pas avoir de parent:
        $req = $bdd->prepare('SELECT id FROM `question` WHERE id_question_parent is NULL and id_questionnaire = :id_questionnaire');
        $req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
        $donnees = $req->fetch();
        $id = $donnees['id'];
        $req->closeCursor();

        if ($id != NULL && $_POST['parent'] == 0)
        {
            ?>

            <!-- On affiche le formulaire: -->
            <div class="wrapper fadeInDown">
                <div id="formContent">
 
                    <h1>Ajouter une nouvelle question</h1>
                    <p class="nom_questionnaire"><?php echo $_SESSION['nom_questionnaire'];?> </p>
                
                    <form action="add_reponse.php" method="post"> 
                        <label>Enoncé de la question: </label><input type="text"   name="enonce" required/> <br>

                        <!-- On affiche la liste des questions parentes possibles, celles qui ont au moins une réponse avec l'id_question_suivante à NULL-->
                        <label for="parent">Question parente: </label> 

                        <select name="parent" id="parent">
                            <option value="0">Aucune</option>

                            <?php 
                            $req = $bdd->prepare('SELECT DISTINCT id_question FROM reponse_question WHERE id_question_suivante is null and id_question IN (
                                  SELECT id FROM question WHERE id_questionnaire = :id)  ORDER BY id_question');
                            $req->execute(array('id' => $_SESSION['id_questionnaire']));
                            while ($donnees = $req->fetch())
                            {
                                $requete = $bdd->prepare('SELECT id, numero, enonce FROM `question` WHERE id = :id ORDER BY numero desc');
                                $requete->execute(array('id' => $donnees['id_question']));
                                while ($sortie = $requete->fetch())
                                {
                                    ?> <option value="<?php echo $sortie['id']?>"><?php echo $sortie['numero'] . '- ' . $sortie['enonce']?></option> <?php
                                }
                                $requete->closeCursor();
                            }
                            $req->closeCursor();
                            ?>
                        </select> <br>

                        <label for="type">Type de question :</label> <select name="type" id="type">
                                                                        <option value="1">Saisie de texte</option>
                                                                        <option value="2">Saisie de nombre</option>
                                                                        <option value="3">Saisie de date</option>
                                                                        <option value="4">Cases à cocher</option>
                                                                        <option value="5">Boutons radios</option>
                                                                    </select> <br>
                        <?php    
                            echo('<p class="msg">Vous devez sélectionner une question parente</p>');
                        ?>                                       
                        <input type="submit" class="termine" value="Terminer">
                    </form>

                    <!-- Bouton Annuler -->
                    <form action="add_reponse.php" method="post">
                        <input type="submit" class="annuler" name="cancel" value="Annuler" />
                    </form>

                </div>
            </div>
            <?php
        }
        else
        {
            // On récupère les infos:
            $enonce = htmlspecialchars($_POST['enonce']);
            $type = $_POST['type'];

            if ($_POST['parent'] != 0)
            {
                $parent = $_POST['parent'];
            }
            else
            {
                $parent = NULL;
            }
        
            // On récupère le nombre de questions :
            $req = $bdd->prepare('SELECT COUNT(*) FROM `question` WHERE id_questionnaire = :id');
            $req->execute(array('id' => $_SESSION['id_questionnaire']));
            $donnees = $req->fetch();
            $count = $donnees[0];
            $numero = $count+1;
            $req->closeCursor();

            $_SESSION['enonce'] = $enonce;
            $_SESSION['type']   = $type;
            $_SESSION['parent'] = $parent;
            $_SESSION['numero'] = $numero;

            // On vérifie le type:
            if ($type == 1 or $type == 2 or $type == 3)
            {
                if ($_SESSION['parent'] == NULL)
                {
                    // On ajoute la question à la BDD:
                    $req = $bdd->prepare('INSERT INTO question (numero, enonce, id_question_parent, id_questionnaire, type, nb_cases) VALUES(:numero, :enonce, :id_question_parent, :id_questionnaire, :type, :nb_cases)');
                    $req->execute(array(
                                    'numero'             => $numero,
                                    'enonce'             => $enonce,
                                    'id_question_parent' => $parent,
                                    'id_questionnaire'   => $_SESSION['id_questionnaire'],
                                    'type'               => $type,
                                    'nb_cases'           => 1
                                    ));
                    $req->closeCursor();

                    // On récupère l'ID de la question:
                    $req = $bdd->prepare('SELECT id FROM question WHERE numero = :numero and id_questionnaire = :id_questionnaire ');
                    $req->execute(array('numero'           => $numero,
                                    'id_questionnaire' => $_SESSION['id_questionnaire']
                                    ));
                                
                    $donnees = $req->fetch();
                    $id_question = $donnees['id'];
                    $req->closeCursor();

                    // On ajoute la réponse possible à la BDD également:
                    $req = $bdd->prepare('INSERT INTO reponse_question (id_question, numero_case) VALUES(:id_question, :numero_case)');
                    $req->execute(array(
                                    'id_question'      => $id_question,
                                    'numero_case'      => 1
                                ));    
                    $req->closeCursor();

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
                else
                {
                    //On affiche le formulaire:?>
                    <div class="wrapper fadeInDown">
                        <div id="formContent">
                    
                            <h1>Configurer la question</h1>
                                <p class="nom_question"><?php echo $enonce;?> </p>
                
                                    <form action="add_reponse.php" method="post"> 
                
                                        <label>Quel choix amènera vers cette question: </label><select name="reponse_parente" id="reponse_parente"><?php

                                        // On sélectionne les réponses de la question parente:
                                        $req = $bdd->prepare('SELECT * FROM reponse_question WHERE id_question = :id_question and id_question_suivante is null');
                                        $req->execute(array('id_question' => $_SESSION['parent']));
                                        while ($donnees = $req->fetch())
                                        {
                                            ?> <option value="<?php echo $donnees['numero_case']?>"><?php echo $donnees['numero_case'] . '- ' . $donnees['reponse_possible']?></option> <?php
                                        }
                                        $req->closeCursor();
                                        ?>
                                            </select> <br>
			                        
                                        <input type="submit" class="termine" value="Terminer">
                                    </form>

                                    <!-- Bouton Annuler -->
                                    <form action="add_reponse.php" method="post">
                                        <input type="submit" class="annuler" name="cancel" value="Annuler" />
                                    </form>
                        </div>
                    </div> <?php

                }
            }
            else if ($type == 4)
            {
                if ($_SESSION['parent'] == NULL)
                {
                    //On affiche le formulaire:?>
                    <div class="wrapper fadeInDown">
                        <div id="formContent">
                    
                            <h1>Configurer la question</h1>
                            <p class="nom_question"><?php echo $enonce;?> </p>
                
                            <form action="add_reponse.php" method="post"> 
                
                                <label>Nombre de réponses proposées: </label><select name="case" id="case">
                                                                                <option value="1">1</option>
                                                                                <option value="2">2</option>
                                                                                <option value="3">3</option>
                                                                                <option value="4">4</option>
                                                                                <option value="5">5</option>
                                                                                <option value="6">6</option>
                                                                                <option value="7">7</option>
                                                                                <option value="8">8</option>
                                                                                <option value="9">9</option>
                                                                                <option value="10">10</option>
                                                                            </select> <br>
			                    <input type="submit" class="termine" value="Terminer">
                            </form>

                            <!-- Bouton Annuler -->
                            <form action="add_reponse.php" method="post">
                                <input type="submit" class="annuler" name="cancel" value="Annuler" />
                            </form>
                        </div>
                    </div> <?php
                }
                else
                {
                
                    //On affiche le formulaire:?>
                    <div class="wrapper fadeInDown">
                    <div id="formContent">
                    
                        <h1>Configurer la question</h1>
                            <p class="nom_question"><?php echo $_SESSION['enonce'];?> </p>
            
                                <form action="add_reponse.php" method="post"> 
                
                                    <label>Quel choix amènera vers cette question: </label><select name="reponse_parente2" id="reponse_parente2"><?php

                                    // On sélectionne les réponses de la question parente:
                                    $req = $bdd->prepare('SELECT * FROM reponse_question WHERE id_question = :id_question and id_question_suivante is null');
                                    $req->execute(array('id_question' => $_SESSION['parent']));
                                    while ($donnees = $req->fetch())
                                    {
                                        ?> <option value="<?php echo $donnees['numero_case']?>"><?php echo $donnees['numero_case'] . '- ' . $donnees['reponse_possible']?></option> <?php
                                    }
                                    $req->closeCursor();
                                    ?>
                                                                            </select> <br>
                                    <input type="submit" class="termine" value="Terminer">
                                </form>

                        <!-- Bouton Annuler -->
                        <form action="validation.php" method="post">
                            <input type="submit" class="annuler" name="cancel" value="Annuler" />
                        </form>
                    </div>
                    </div> <?php  
                }
            }
        }
    }
}
else
{
    header('Location: ../modifier_questionnaire_redac.php');
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="add_reponse.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="add_question.js"></script>
    <title>Ajouter une question</title>
</head>
<body>
<?php

if (isset($_POST['reponse_parente']))
{
    $_SESSION['reponse_parente'] = $_POST['reponse_parente'];

    // J'ajoute la question:
    $req = $bdd->prepare('INSERT INTO question (numero, enonce, id_question_parent, id_questionnaire, type, nb_cases) VALUES(:numero, :enonce, :id_question_parent, :id_questionnaire, :type, :nb_cases)');
    $req->execute(array(
                    'numero'             => $_SESSION['numero'],
                    'enonce'             => $_SESSION['enonce'],
                    'id_question_parent' => $_SESSION['parent'],
                    'id_questionnaire'   => $_SESSION['id_questionnaire'],
                    'type'               => $_SESSION['type'],
                    'nb_cases'           => 1
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
    
    // J'ajoute l'id_question_suivante à la question parente:
    $req = $bdd->prepare('UPDATE `reponse_question` SET `id_question_suivante`= :id_question_suivante WHERE id_question = :id_question and numero_case = :numero_case');
    $req->execute(array(
                        'id_question_suivante' => $id_question,
                        'id_question'          => $_SESSION['parent'],
                        'numero_case'          => $_SESSION['reponse_parente']
                        ));    
    $req->closeCursor();

    // On ajoute la réponse possible à la BDD également:
    $req = $bdd->prepare('INSERT INTO reponse_question (id_question, numero_case) VALUES(:id_question, :numero_case)');
    $req->execute(array(
                    'id_question'      => $id_question,
                    'numero_case'      => 1
                ));    
    $req->closeCursor();

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

// On affiche le nombre correspondant d'entrées:
if (isset($_POST['case']))
{
    $_SESSION['nb_cases']= $_POST['case'];
    $compteur = 0;
    ?>

    <div class="wrapper fadeInDown">
        <div id="formContent">
                    
            <h1>Configurer les réponses</h1>
            <p class="nom_question"><?php echo $_SESSION['enonce'];?> </p>
                
            <form action="validation.php" method="post"> 
                
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

        </div>
    </div>
    <?php
}

if (isset($_POST['reponse_parente2']))
{
    $_SESSION['reponse_parente'] = $_POST['reponse_parente2'];
    
    ?>
    <div class="wrapper fadeInDown">
                    <div id="formContent">
                    
                        <h1>Configurer la question</h1>
                        <p class="nom_question"><?php echo $_SESSION['enonce'];?> </p>
                
                        <form action="validation.php" method="post"> 
                
                            <label>Nombre de réponses proposées: </label><select name="case2" id="case2">
                                                                                <option value="1">1</option>
                                                                                <option value="2">2</option>
                                                                                <option value="3">3</option>
                                                                                <option value="4">4</option>
                                                                                <option value="5">5</option>
                                                                                <option value="6">6</option>
                                                                                <option value="7">7</option>
                                                                                <option value="8">8</option>
                                                                                <option value="9">9</option>
                                                                                <option value="10">10</option>
                                                                            </select> <br>
			                <input type="submit" class="termine" value="Terminer">
                        </form>

                        <!-- Bouton Annuler -->
                        <form action="add_reponse.php" method="post">
                            <input type="submit" class="annuler" name="cancel" value="Annuler" />
                        </form>
                    </div>
                </div> <?php
}
?>
</body>
</html>