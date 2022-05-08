<?php

/**
* \file questionnaires_client.php
* \brief Page des questionnaires remplis par un client
* \author Marine CUVELIER
* \version 1.0
* \date 21 juin 2020
*
* Cette page permet au Client de visualiser la liste de ses questionnaires remplis ou en cours.
* \nIl peut également accéder à différentes options pour chacun d'entre eux:
* \nS'il visualise un questionnaire terminé, il peut l'effacer, ce qui aura pour effet de réinitialiser ses réponses à celui-ci. A la suite de quoi il pourra le recommencer s'il le souhaite.
* \nS'il visualise un questionnaire en cours, il peut l'effacer également. Il peut aussi le poursuivre, en reprenant à l'endroit où il s'était arrêté.
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

    // Si on appuie sur terminer:
if (isset($_POST['termine']))
{
    header('Location: ../console_client.php');
    exit;
}

// On vérifie qu'il peut accéder à cette page:
    if ($_SESSION['statut'] != '1') 
    {
        header('Location: ../../Connexion/index.php');
        exit;
    }


// Si on appuie sur REINITIALISER QUESTIONNAIRE:
if (isset($_POST['supp']))
{
    $id_questionnaire_supp = $_POST['supp'];;

    $req = $bdd->prepare('DELETE FROM questionnaire_client WHERE id_client = :id_client and id_questionnaire = :id_questionnaire');
    $req->execute(array('id_client' => $_SESSION['userid'],
                        'id_questionnaire' => $id_questionnaire_supp));
    $req->closeCursor();

    $req = $bdd->prepare('DELETE FROM reponse_client WHERE id_client = :id_client and id_questionnaire = :id_questionnaire');
    $req->execute(array('id_client' => $_SESSION['userid'],
                        'id_questionnaire' => $id_questionnaire_supp));
    $req->closeCursor();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="questionnaires_client.css" rel="stylesheet" id="bootstrap-css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <title>Questionnaires Client</title>
</head>
<body>
<div class="wrapper fadeInDown">
    <div id="formContent">

        <h1>Mes questionnaires remplis</h1>       
        <?php
        // On récupère la liste des questionnaire remplis par le client:
        $req = $bdd->prepare('SELECT id_questionnaire FROM questionnaire_client WHERE id_client = :id_client');
        $req->execute(array('id_client' => $_SESSION['userid']));
        while ($donnees = $req->fetch())
        {
            // On récupère les infos du questionnaire:
            $requete = $bdd->prepare('SELECT * FROM questionnaire WHERE id = :id');
            $requete->execute(array('id' => $donnees['id_questionnaire']));
            while ($sortie = $requete->fetch())
            {
                ?>
                    <div class = "question">
                        
                        <p class="nom_questionnaire"><?php echo $sortie['nom'];?> </p>

                            <?php
                            // On récupère les infos du client:
                            $reponse = $bdd->prepare('SELECT resultat FROM questionnaire_client WHERE id_client = :id_client and id_questionnaire = :id_questionnaire');
                            $reponse->execute(array('id_client'        => $_SESSION['userid'],
                                                    'id_questionnaire' => $donnees['id_questionnaire']));
                            $infos = $reponse->fetch();
                            $resultat = $infos['resultat'];
                            $reponse->closeCursor();

                            if ($resultat != NULL)
                            {
                                ?>
                                <form action="questionnaires_client.php" method="post" onsubmit="return confirm('Voulez-vous vraiment réinitialiser le questionnaire ? Cette action est irréversible');">
                                    <input type="hidden" name="supp" value="<?php echo $donnees['id_questionnaire'] ?>">
                                    <input type="submit" name="poubelle" class="poubelle" value="" ></input>
                                </form>
                                
                                <p class="libelle">Statut: Rempli </p>
                                <p class="libelle">Résultat: <?php echo $resultat ?></p>
                                <?php
                            }
                            else
                            {
                                // On récupère le nombre de questions:
                                $reponse = $bdd->prepare('SELECT COUNT(*) FROM `question` WHERE id_questionnaire = :id_questionnaire');
                                $reponse->execute(array('id_questionnaire' => $donnees['id_questionnaire']));
                                $infos = $reponse->fetch();
                                $nb_questions_totales = $infos[0];
                                $reponse->closeCursor();


                                // On récupère le nombre de questions remplies par le client:
                                $reponse = $bdd->prepare('SELECT COUNT(*) FROM `reponse_client` WHERE id_client = :id_client and id_questionnaire = :id_questionnaire');
                                $reponse->execute(array('id_client'        => $_SESSION['userid'],
                                                        'id_questionnaire' => $donnees['id_questionnaire']));
                                $infos = $reponse->fetch();
                                $nb_questions_client = $infos[0];
                                $reponse->closeCursor();
                                
                                ?> 

                                <form action="questionnaires_client.php" method="post" onsubmit="return confirm('Voulez-vous vraiment réinitialiser le questionnaire ? Cette action est irréversible');">
                                    <input type="hidden" name="supp" value="<?php echo $donnees['id_questionnaire'] ?>">
                                    <input type="submit" name="poubelle" class="poubelle" value="" ></input>
                                </form>

                                <form action="../Remplir/remplir_questionnaire_client.php" method="post">
                                    <input type="hidden" name="modif" value="<?php echo $donnees['id_questionnaire'] ?>">
                                    <input type="submit" name="crayon" class="crayon" value="Continuer" ></input>
                                </form>

                                <p class="libelle">Statut: En cours </p>
                                <?php
                            }
                            ?>
                            
                    </div>
                <?php
            }
            $requete->closeCursor();
        }
        $req->closeCursor();
        ?>
    <!-- Bouton Valider -->
    <form action="questionnaires_client.php" method="post">
        <input type="submit" class="termine" name="termine" value="Terminer" />
    </form>       

</div>
</div>
</body>
</html>