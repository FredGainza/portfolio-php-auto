<?php
include 'toolbox.php';

$connexion = new PDO("mysql:host=localhost:3306;dbname=cars;charset=UTF8", "xxxx", "xxxxxxxx");
// $connexion = new PDO("mysql:host=localhost:3306;dbname=cars;charset=UTF8", "root", "");
// dumpPre($_POST);exit;
if (isset($_GET)) {
    if (isset($_GET['delete']) && $_GET['delete'] === 'suppression') {
        $delete = $connexion->prepare('DELETE FROM vehicule WHERE id = :id');
        $delete->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
        $delete->execute();
        header("Location: index.php");
        exit;
    }
	// dumpPre($_FILES);exit;
    if (isset($_GET['edit']) && $_GET['edit'] === 'edition' && !empty($_GET['id'])) {  
        if (!isset($_POST["reset"])){        
			$select_id = $connexion->prepare("SELECT * FROM vehicule WHERE id = :id");
			$select_id->bindValue(":id", $_GET['id'], PDO::PARAM_INT);
			$select_id->execute();
			$res = $select_id->fetch(PDO::FETCH_OBJ);
        }

        if (!empty($_POST) && isset($_POST)) {
            // dumpPre($_POST);exit;
            $edition = $connexion->prepare("UPDATE vehicule SET marque = :marque, modele = :modele, CV = :CV, energie = :energie, immatriculation = :immatriculation WHERE id = :id");
            $edition->bindValue(":marque", $_POST["marque"], PDO::PARAM_STR);
            $edition->bindValue(":modele", $_POST["modele"], PDO::PARAM_STR);
            $edition->bindValue(":CV", $_POST["CV"], PDO::PARAM_INT);
            $edition->bindValue(":energie", $_POST["energie"], PDO::PARAM_STR);
            $edition->bindValue(":immatriculation", $_POST["immatriculation"], PDO::PARAM_STR);
            $edition->bindValue(":id", $_GET["id"], PDO::PARAM_INT);
            $edition->execute();

            // if (isset($_FILES) && !empty($_FILES)) {
            if (isset($_FILES) && $_FILES['image']['name'] != "") {
                $maxSize = 1024 * 1024; // 1 048 576

                if ($_FILES['image']['size'] <= $maxSize) {

                    $fileInfo = pathinfo($_FILES['image']['name']);
                    // dumpPre($fileInfo);

                    $extension = strtolower($fileInfo['extension']);
                    $extension_autorise = ['jpg', 'jpeg', 'png', 'gif'];
                    // dumpPre($extension);

                    if (in_array($extension, $extension_autorise)) {

                        $image_name = md5(uniqid(rand(), true));
                        // dumpPre($image_name);

                        $config_miniature_width = 100;

                        if ($extension === 'jpg' || $extension === 'jpeg') {
                        $new_image = imagecreatefromjpeg($_FILES['image']['tmp_name']);
                        } elseif ($extension === 'png') {
                        $new_image = imagecreatefrompng($_FILES['image']['tmp_name']);
                        } elseif ($extension === 'gif') {
                        $new_image = imagecreatefromgif($_FILES['image']['tmp_name']);
                        }

                        $original_width = imagesx($new_image);
                        $original_heigth = imagesy($new_image);
                        $miniature_heigth = ($original_heigth * $config_miniature_width) / $original_width;
                        $miniature = imagecreatetruecolor($config_miniature_width, $miniature_heigth);
                        imagecopyresampled($miniature, $new_image, 0, 0, 0, 0, $config_miniature_width, $miniature_heigth, $original_width, $original_heigth);

                        $folder = 'images/miniatures/';

                        if ($extension === 'jpg' || $extension === 'jpeg') {
                        imagejpeg($miniature, $folder . $image_name . '.' . $extension);
                        } elseif ($extension === 'png') {
                        imagepng($miniature, $folder . $image_name . '.' . $extension);
                        } elseif ($extension === 'gif') {
                        imagegif($miniature, $folder . $image_name . '.' . $extension);
                        }
                        move_uploaded_file($_FILES['image']['tmp_name'], 'images/' .
                        $image_name . '.' . $extension);
                        $msg = 'image transférée';

                        $edition_img = $connexion->prepare("UPDATE vehicule SET image = :image WHERE id = :id");

                        $edition_img->bindValue(":image", $image_name . '.' . $extension, PDO::PARAM_STR);
                        $edition_img->bindValue(":id", $_GET["id"], PDO::PARAM_INT);
                        $edition_img->execute();

                        header("Location: index.php");
                        exit;

                    } else {
                        $msg = 'Extension non autorisée';
                        print_r($msg);
                    }

                } else {
                $msg = 'Fichier trop lourd';
                }

            } else {
                $msg = 'Erreur lors du transfert de fichier';
            }

        }
    
    }
}
?>

<!doctype html>
<html lang="fr">

    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <meta name="description" content="">
        <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
        <meta name="generator" content="Jekyll v3.8.5">

        <!--Favicons-->
        <link rel="apple-touch-icon" sizes="180x180" href="images/favicons/apple-touch-icon.png">
        <link rel="icon" type="image/png" sizes="32x32" href="images/favicons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="16x16" href="images/favicons/favicon-16x16.png">
        <link rel="manifest" href="images/favicons/site.webmanifest">

        <title>Gestion de véhicules</title>

        <link rel="canonical" href="https://getbootstrap.com/docs/4.3/examples/album/">

        <!-- Bootstrap core CSS -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

        <style>
            body {
            font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;
            background-color: #fcfcfc;
            }

            table {
            width: 90%;
            margin: 0 auto;
            border-collapse: collapse;
            border: 1px solid black;
            }

            th, td {
            border: 1px solid black;
            text-align: right;
            width: auto;
            padding: 10px 20px;
            }

            thead th {
            background-color: #3f492c;
            color: white;
            }

            h2 a {
                color: #212529;
                text-decoration: none;
            }

            h2 a:hover {
                color: #22262a99;
                text-decoration: none !important;
            }

            .white {
            color: white;
            }

            .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
            }

            hr.ligneSeparation {
            background-color: #fff;
            border-top: 2px dotted #8c8b8b;
            }

            .bg-jumbotron {
            background-color: #dfe9ce;
            -webkit-box-shadow: 0px 9px 36px -21px rgba(0,0,0,0.75);
            -moz-box-shadow: 0px 9px 36px -21px rgba(0,0,0,0.75);
            box-shadow: 0px 9px 36px -21px rgba(0,0,0,0.75);
            }

            @media (min-width: 768px) {
                .bd-placeholder-img-lg {
                    font-size: 3.5rem;
                }

            }
            @media screen and (max-width: 768px) {
                table td, table th{
                    padding: 5px 10px;
                    font-size: .9rem;
                }

            }
        </style>
    </head>

    <body>
        <main role="main">

            <section class="jumbotron jumbotron-fluid py-4 text-center bg-jumbotron">
                <div class="container">
                    <h2><a href="index.php">Gestion de véhicules</a></h2>
                    <p>Mini CRUD + Tri des colonnes</p>
                </div>
            </section>

            <!-- 
            ##############################################################################################
                Objectifs :
            - Créer une base de données (cars)           
            - Créer une table (vehicule)
            - Créer un formulaire qui permet d'enregistrer :
            - La marque, le modèle, les CV, l'energie, l'immatriculation
            - Afficher tous les véhicules dans un tableau
            - Sur chaque ligne du tableau vous devez avoir 2 boutons (bouton action)
            - Un bouton pour éditer le véhicule
            - Un bouton pour supprimer le véhicule
            Optionnel : Afficher les véhicules par ordre alphabétique croissant sur le champs "marque" 
            ##############################################################################################
            -->

            <div class="container mb-3">
                <div class="row mx-2">
                    <form action="" method="POST" class="needs-validation mb-5" enctype="multipart/form-data">
                        <h3>Formulaire de saisie</h3>
                        <div class="form-row">
                            <div class="col-md mb-3">
                                <input type="text" class="form-control" id="marque" name="marque" placeholder="Indiquez la marque" value="<?= (isset($res) && !empty($res)) ? $res->marque : ""; ?>">
                            </div>
                            <div class="col-md mb-3">
                                <input type="text" class="form-control" id="modele" name="modele" placeholder="Indiquez le modèle" value="<?= isset($res) && !empty($res) ? $res->modele : ""; ?>">
                            </div>
                            <div class="col-md mb-3">
                                <input type="number" class="form-control" id="CV" name="CV" placeholder="Indiquez le nombre de cheveaux" value="<?= isset($res) && !empty($res) ? $res->CV : ""; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md mb-3">
                                <input type="text" class="form-control" id="energie" name="energie" placeholder="Indiquez le type d'énergie" value="<?= isset($res) && !empty($res) ? $res->energie : ""; ?>">
                            </div>
                            <div class="col-md mb-3">
                                <input type="text" class="form-control" id="immatriculation" name="immatriculation" placeholder="Indiquez immatriculation" value="<?= isset($res) && !empty($res) ? $res->immatriculation : ""; ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="col-md mb-3">
                                <div class="form-group custom-file">
                                    <input type="file" class="custom-file-input" id="image" name="image">
                                    <label class="custom-file-label" for="customFile">Choisissez une image</label>
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="reset" value="<?= isset($res) && !empty($res) ? "1" : "0"; ?>">
                        <button type="submit" class="btn btn-dark btn-block mt-3"><?= isset($res) && !empty($res) ? "Mise à jour" : "Ajouter"; ?></button>
                    </form>
                </div>
                <hr class="ligneSeparation">

                <?php
                if (!empty($_POST) && !isset($_GET['edit'])) {
                    if (!empty($_FILES)) {
                        $maxSize = 1024 * 1024; // 1 048 576

                        if ($_FILES['image']['size'] <= $maxSize) {

                            $fileInfo = pathinfo($_FILES['image']['name']);
                            // dumpPre($fileInfo);

                            $extension = strtolower($fileInfo['extension']);
                            $extension_autorise = ['jpg', 'jpeg', 'png', 'gif'];
                            // dumpPre($extension);

                            if (in_array($extension, $extension_autorise)) {

                                $image_name = md5(uniqid(rand(), true));
                                // dumpPre($image_name);

                                $config_miniature_width = 100;

                                if ($extension === 'jpg' || $extension === 'jpeg') {
                                    $new_image = imagecreatefromjpeg($_FILES['image']['tmp_name']);
                                } elseif ($extension === 'png') {
                                    $new_image = imagecreatefrompng($_FILES['image']['tmp_name']);
                                } elseif ($extension === 'gif') {
                                    $new_image = imagecreatefromgif($_FILES['image']['tmp_name']);
                                }

                                $original_width = imagesx($new_image);
                                $original_heigth = imagesy($new_image);
                                $miniature_heigth = ($original_heigth * $config_miniature_width) / $original_width;
                                $miniature = imagecreatetruecolor($config_miniature_width, $miniature_heigth);
                                imagecopyresampled($miniature, $new_image, 0, 0, 0, 0, $config_miniature_width, $miniature_heigth, $original_width, $original_heigth);

                                $folder = 'images/miniatures/';

                                if ($extension === 'jpg' || $extension === 'jpeg') {
                                    imagejpeg($miniature, $folder . $image_name . '.' . $extension);
                                } elseif ($extension === 'png') {
                                    imagepng($miniature, $folder . $image_name . '.' . $extension);
                                } elseif ($extension === 'gif') {
                                    imagegif($miniature, $folder . $image_name . '.' . $extension);
                                }

                                move_uploaded_file($_FILES['image']['tmp_name'], 'images/' .
                                    $image_name . '.' . $extension);
                                $msg = 'image transférée';

                                $ajout = $connexion->prepare("INSERT INTO vehicule (marque, modele, CV, energie, immatriculation, image) VALUES (:marque, :modele, :CV, :energie, :immatriculation, :image)");

                                $ajout->bindValue(":marque", $_POST["marque"], PDO::PARAM_STR);
                                $ajout->bindValue(":modele", $_POST["modele"], PDO::PARAM_STR);
                                $ajout->bindValue(":CV", $_POST["CV"], PDO::PARAM_INT);
                                $ajout->bindValue(":energie", $_POST["energie"], PDO::PARAM_STR);
                                $ajout->bindValue(":immatriculation", $_POST["immatriculation"], PDO::PARAM_STR);
                                $ajout->bindValue(":image", $image_name . '.' . $extension, PDO::PARAM_STR);
                                $ajout->execute();

                            } else {
                            $msg = 'Extension non autorisée';
                            }

                        } else {
                            $msg = 'Fichier trop lourd';
                        }

                    } else {
                        $msg = 'Erreur lors du transfert de fichier';
                    }
                }
                ?>

                <?php
                // Selection de tous les elements de la table vehicule
                if (isset($_GET['affichage']) && !empty($_GET['affichage'])) {
                    switch ($_GET['affichage']) {
                        case 'MarqueCroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY marque");
                            $requete->execute();
                            break;

                        case 'MarqueDecroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY marque DESC");
                            $requete->execute();
                            break;

                        case 'ModeleCroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY modele");
                            $requete->execute();
                            break;

                        case 'ModeleDecroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY modele DESC");
                            $requete->execute();
                            break;

                        case 'CVCroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY CV");
                            $requete->execute();
                            break;

                        case 'CVDecroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY CV DESC");
                            $requete->execute();
                            break;

                        case 'EnergieCroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY energie");
                            $requete->execute();
                            break;

                        case 'EnergieDecroissant':
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY energie DESC");
                            $requete->execute();
                            break;

                        default:
                            $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY id");
                            $requete->execute();
                            break;
                    }
                } else {
                    $requete = $connexion->prepare("SELECT * FROM vehicule ORDER BY id");
                    $requete->execute();
                }

                $resultats = $requete->fetchAll(PDO::FETCH_OBJ);

                //print_r($resultats);
                ?>

                <div class="row mx-2">
                    <table class="my-3 w-100">
                        <thead>
                            <tr>
                                <th>Id</th>
                                <th>Photo</th>
                                <th>Marque
                                    <div class="btn-group" role="group" aria-label="Menu déroulant">
                                        <button id="affichageMarque" type="button" class="btn white dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                        <div class="dropdown-menu" aria-labelledby="affichageMarque">
                                            <a class="dropdown-item" href="?affichage=MarqueCroissant">Affichage par ordre croissant</a>
                                            <a class="dropdown-item" href="?affichage=MarqueDecroissant">Affichage par ordre decroissant</a>
                                        </div>
                                    </div>
                                </th>

                                <th>Modèle
                                    <div class="btn-group" role="group" aria-label="Menu déroulant">
                                        <button id="affichageModel" type="button" class="btn white dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                        <div class="dropdown-menu" aria-labelledby="affichageModel">
                                            <a class="dropdown-item" href="?affichage=ModeleCroissant">Affichage par ordre croissant</a>
                                            <a class="dropdown-item" href="?affichage=ModeleDecroissant">Affichage par ordre decroissant</a>
                                        </div>
                                    </div>
                                </th>

                                <th>CV
                                    <div class="btn-group" role="group" aria-label="Menu déroulant">
                                        <button id="affichageCV" type="button" class="btn white dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                        <div class="dropdown-menu" aria-labelledby="affichageCV">
                                            <a class="dropdown-item" href="?affichage=CVCroissant">Affichage par ordre croissant</a>
                                            <a class="dropdown-item" href="?affichage=CVDecroissant">Affichage par ordre decroissant</a>
                                        </div>
                                    </div>
                                </th>

                                <th>Energie
                                    <div class="btn-group" role="group" aria-label="Menu déroulant">
                                        <button id="affichageEnergie" type="button" class="btn white dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>
                                        <div class="dropdown-menu" aria-labelledby="affichageEnergie">
                                            <a class="dropdown-item" href="?affichage=EnergieCroissant">Affichage par ordre croissant</a>
                                            <a class="dropdown-item" href="?affichage=EnergieDecroissant">Affichage par ordre decroissant</a>
                                        </div>
                                    </div>
                                </th>

                                <th>Immatriculation</th>
                                <th>Edition</th>
                                <th>Suppression</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php foreach ($resultats as $value) : ?>
                                <tr>
                                    <td><?= $value->id; ?></td>
                                    <td><a href="images/<?= $value->image; ?>" target="_blank"><img src="images/miniatures/<?= $value->image; ?>"></a></td>
                                    <td><?= $value->marque; ?></td>
                                    <td><?= $value->modele; ?></td>
                                    <td><?= $value->CV; ?></td>
                                    <td><?= $value->energie; ?></td>
                                    <td><?= $value->immatriculation; ?></td>
                                    <td><button type="submit" class="btn btn-info btn-sm"><a href="?edit=edition&id=<?= $value->id ?>" class="text-white">EDITER</a></button></td>
                                    <td><button type="submit" class="btn btn-danger btn-sm"><a href="?delete=suppression&id=<?= $value->id ?>" class="text-white" onclick="return confirm('Confirmez la suppression de cet élément')">SUPPRIMER</a></button></td>
                                </tr>
                                <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer class="text-muted">
            <div class="container">
            </div>
        </footer>
        <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>

    </body>

</html>
