<?php

/**
* \file add_result.php
* \brief Page de rédaction des résultats du questionnaire
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page, accessible par le Rédacteur une fois qu'il a ajouté au moins une question à son questionnaire, lui permet de visualiser les différents chemins que possède son questionnaire et de saisir un résultat pour chacun d'entre eux.
* \nCe résultat sera celui obtenu par le Client, en fonction des réponses qu'il aura donné.
*
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

// On vérifie que l'utilisateur est connecté:
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

if (empty($_SESSION['id_questionnaire'])) 
{
    header('Location: ../../console_redac.php');
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

if (isset($_POST['annule']))
{
    header('Location: ../modifier_questionnaire_redac.php');
    exit;
}

// On récupère le nombre de chemins possibles:
$req = $bdd->prepare('SELECT COUNT(*) FROM `reponse_question` where id_question_suivante is NULL and id_question IN (SELECT id FROM `question` where id_questionnaire = :id_questionnaire)');
$req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
$donnees = $req->fetch();
$chemins_totals = $donnees[0];
$req->closeCursor();

// Déclaration de la matrice qui contient les tableaux de chemins
$count=0;
$matrice = array();
while ($count < $chemins_totals)
{
    $matrice[$count] = array();
    $count+=1;
}
$count = 0;

// On récupère le nombre de questions du questionnaire:
$req = $bdd->prepare('SELECT COUNT(*) FROM `question` where id_questionnaire = :id_questionnaire');
$req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
$donnees = $req->fetch();
$nb_questions = $donnees[0];
$req->closeCursor();

// Déclaration de la matrice qui contient les tableaux de stocks
$stocks = array();
while ($count < $nb_questions)
{
    $stocks[$count] = array();
    $count+=1;
}

$count = 1; // correspond au numéro de la case
$count_de_matrice = 0;
$compteur_de_stock = 0;
$reference = array();
$test = 0;

// On récupère l'id et le nb_cases de la 1ere question':
$req = $bdd->prepare('SELECT id, nb_cases FROM `question` WHERE id_questionnaire = :id_questionnaire and id_question_parent is NULL');
$req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
$donnees = $req->fetch();
$var1 = $donnees['id'];
$nb_cases = $donnees['nb_cases'];
$req->closeCursor();

array_push($reference, $var1);

// Pour chaque case de la premiere question, je regarde si suivant vaut null ou pas:
while ($count <= $nb_cases)
{
    $req = $bdd->prepare('SELECT id_question_suivante FROM `reponse_question` WHERE id_question = :id_question and numero_case = :numero_case');
    $req->execute(array('id_question' => end($reference),
                        'numero_case' => $count));
    while ($donnees = $req->fetch())
    {
        if ($donnees['id_question_suivante'] != NULL)
        {
            // On récupère les id suivants et leur numero de case dans stock_id
            array_push($stocks[$compteur_de_stock], $count);
            array_push($stocks[$compteur_de_stock], $donnees['id_question_suivante']);
            $test = 1; // On doit descendre dans l'arborescence, donc on met test = 1.
        }
        else
        {
            // on récupère le chemin et on l'ajoute direct à la matrice:
            array_push($reference, $count);
            $matrice[$count_de_matrice] = $reference;
            $count_de_matrice+=1;
            array_pop($reference);
        }
    }
    $req->closeCursor();
    $count+=1;
}

    // Si j'ai eu que des NULL:
    if ($test == 0)
    {
        array_pop($reference);  // enleve le dernier id
        array_pop($reference);  // enleve le dernier numero

        // Est-ce qu'on a tout rempli ?
        if ($count_de_matrice == $chemins_totals)
        {
            echo 'fini!!';
            // header
        }
    }
    else
    {
        while ($count_de_matrice != $chemins_totals)
        {
            $count = 0;
            $test = 0;

            array_push($reference, $stocks[$compteur_de_stock][0]);
            array_shift($stocks[$compteur_de_stock]);

            array_push($reference, $stocks[$compteur_de_stock][0]);
            array_shift($stocks[$compteur_de_stock]);

            $compteur_de_stock+=1;

            
            // Pour chaque case de la premiere question, je regarde si suivant vaut null ou pas:
            $req = $bdd->prepare('SELECT numero_case, id_question_suivante FROM `reponse_question` WHERE id_question = :id_question');
            $req->execute(array('id_question' => end($reference)));
            while ($donnees = $req->fetch())
            {
                if ($donnees['id_question_suivante'] != NULL)
                {
                    // On récupère les id suivants et leur numero de case dans stock_id
                    array_push($stocks[$compteur_de_stock], $donnees['numero_case']);
                    array_push($stocks[$compteur_de_stock], $donnees['id_question_suivante']);
                    $test = 1;
                }
                else
                {
                    //on récupère le chemin et on l'ajoute direct à la matrice:
                    array_push($reference, $donnees['numero_case']);
                    $matrice[$count_de_matrice] = $reference;
                    $count_de_matrice+=1;
                    array_pop($reference);
                }
            }
            $req->closeCursor();
            
            // Si j'ai eu que des NULL:
            if ($test == 0)
            {
                if ($count_de_matrice != $chemins_totals)
                {
                    while (empty($stocks[$compteur_de_stock]))
                    {
                        array_pop($reference);  // enleve le dernier id
                        array_pop($reference);  // enleve le dernier numero
                        $compteur_de_stock-=1;
                    }
                }
            }
            else
            {
                while (empty($stocks[$compteur_de_stock]))
                    {
                        $compteur_de_stock-=1;
                    }
            }
        }
    }


// On efface les anciens résultats s'il y en a:
$req = $bdd->prepare('DELETE FROM `resultat_questionnaire` where id_questionnaire = :id_questionnaire');
$req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
$req->closeCursor();

$count=0;

// On insère dans resultat_questionnaire les chemins récupérés:    
while ($count < $chemins_totals)
{
    $text = "";
    $text = implode(",", $matrice[$count]);
    
    $req = $bdd->prepare('INSERT INTO `resultat_questionnaire` (`chemin`, `id_questionnaire`) VALUES (:chemin, :id_questionnaire)');
    $req->execute(array('chemin'           => $text,
                        'id_questionnaire' => $_SESSION['id_questionnaire']));
    $donnees = $req->fetch();
    $req->closeCursor();

    $count+=1;
}

    // Je récupère dans un tableau à deux dimensions chaque partie de chaque chemin:
    $tab2d = array();
    $count=0;

    $req = $bdd->prepare('SELECT chemin FROM `resultat_questionnaire` WHERE id_questionnaire = :id_questionnaire');
    $req->execute(array('id_questionnaire' => $_SESSION['id_questionnaire']));
    while ($donnees = $req->fetch())
    {
        $tab2d[$count] = explode(",", $donnees['chemin']);
        $count+=1;
    }
    $req->closeCursor();

    

    // Je récupère les énoncés et les réponses dans un tableau à deux dimensions:
    $tab = array();
    $count= 0;


while ($count < $chemins_totals)
{
    $id = 0;
    $numero = 1;
    $tab[$count] = array();


    do
    {
        $requete = $bdd->prepare('SELECT question.enonce, reponse_question.reponse_possible, reponse_question.id_question_suivante 
                                  FROM question INNER JOIN reponse_question 
                                  ON question.id = reponse_question.id_question 
                                  WHERE question.id = :id and reponse_question.numero_case = :numero');

        $requete->execute(array('id'     => $tab2d[$count][$id],
                                'numero' => $tab2d[$count][$numero]));

        while ($donnees = $requete->fetch())
        {
            

            array_push($tab[$count], $donnees['enonce']);

            if ($donnees['reponse_possible'] == NULL)
            {
                array_push($tab[$count], "\"Réponse attendue\"");
            }
            else
            {
                array_push($tab[$count], $donnees['reponse_possible']);
            }
            
            $suivant = $donnees['id_question_suivante'];
        }
        $requete->closeCursor();

        $id +=2;
        $numero +=2;

    }while ($suivant != NULL);
    $count+=1;
}




if ($_SERVER["REQUEST_METHOD"] == "POST")
{
    // Récupération des variables POST
    $resultat = array();

    foreach ( $_POST as $post => $val )  {            
        array_push($resultat, htmlspecialchars($val));
    }
    array_pop($resultat); // On enlève la dernière valeur qui correspond au bouton d'envoi
    

    // je récupère chaque chemin:
    $chemins = array();
    $count = 0;
    while ($count < $chemins_totals)
    {
        $text = "";
        $text = implode(",", $tab2d[$count]);
        array_push($chemins, $text);

        $count+=1;
    }

    //Je remplis la BDD avec les résultats soumis:
    $count = 0;
    while ($count < $chemins_totals)
    {
        $req = $bdd->prepare('UPDATE `resultat_questionnaire` SET `resultat`= :resultat WHERE chemin = :chemin');
        $req->execute(array('resultat' => $resultat[$count],
                            'chemin'   => $chemins[$count]));
        $req->closeCursor();
        $count+=1;
    }

    //Je redirige:
    header('Location: ../modifier_questionnaire_redac.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="add_result.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Ajouter les résultats</title>
</head>
<body>

<div class="wrapper fadeInDown">
    <div id="formContent">
        <h1>Ajouter les résultats</h1> 

<form action="add_result.php" method="post">
<?php

    $count = 0;
    while ($count < $chemins_totals)
    {

        echo '<div class="box"> <p class="chemin">';
        
        foreach ($tab[$count] as $val)
        {
            echo $val . ' &rarr; ';
        }
        echo '</p>';
        $count+=1;
        ?>
            
                <input type="text" name= "res<?php echo $count?>"placeholder="Résultat obtenu" required>
            

        </div>
        <?php
    }
?>

    <input type="submit" class="termine" name="termine" value="Valider">
</form>

<form action="add_result.php" method="post">
    <input type="submit" class="annule" name ="annule" value="Annuler">
</form>
</div>
</div>
</body>
</html>