<?php
header('Content-Type: application/json');

function getReceipes() {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');
        $retour["success"] = true;
        $retour["message"] = "Connexion à la base de données réussie";

        $requete = $pdo->prepare("SELECT * FROM `receipes`");
        $requete->execute();
        $resultats = $requete->fetchAll();

        $retour["success"] = true;
        $retour["message"] = "Voici les recettes";
        $retour["results"]["nb"] = count($resultats);
        $retour["results"] = $resultats;
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Connexion à la base de données impossible";
    }

    return $retour;
}

function getIngredients() {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');
        $retour["success"] = true;
        $retour["message"] = "Connexion à la base de données réussie";

        $requete = $pdo->prepare("SELECT * FROM `ingredients`");
        $requete->execute();
        $resultats = $requete->fetchAll();

        $retour["success"] = true;
        $retour["message"] = "Voici les ingrédients";
        $retour["results"]["nb"] = count($resultats);
        $retour["results"] = $resultats;
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Connexion à la base de données impossible";
    }

    return $retour;
}

function getUser() {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');
        $retour["success"] = true;
        $retour["message"] = "Connexion à la base de données réussie";

        $requete = $pdo->prepare("SELECT * FROM `users`");
        $requete->execute();
        $resultats = $requete->fetchAll();

        $retour["success"] = true;
        $retour["message"] = "Voici les utilisateurs";
        $retour["results"]["nb"] = count($resultats);
        $retour["results"] = $resultats;
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Connexion à la base de données impossible";
    }

    return $retour;
}

function addIngredient($Name) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');

        $requete = $pdo->prepare("INSERT INTO `ingredients` (`Name`) VALUES (?)");
        $requete->execute([$Name]);

        $retour["success"] = true;
        $retour["message"] = "Ingrédient ajouté avec succès";
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Erreur lors de l'ajout de l'ingrédient : " . $e->getMessage();
    }

    return $retour;
}

function addUser($User_uid, $User_email, $User_password) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');

        $requete = $pdo->prepare("INSERT INTO `users` (`User_uid`, `User_email`, `User_password`) VALUES (?, ?, ?)");
        $hashedPwd = password_hash($User_password, PASSWORD_DEFAULT);
        $requete->execute([$User_uid, $User_email, $hashedPwd]);

        $retour["success"] = true;
        $retour["message"] = "Utilisateur ajouté avec succès";
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Erreur lors de l'ajout de l'utilisateur : " . $e->getMessage();
    }

    return $retour;
}

/////////////////////////////////////////////// 

//Routes recettes

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['route']) && $_GET['route'] === 'get_receipe') {
    $resultat = getReceipes();
    echo json_encode($resultat);
}

//Routes ingrédients

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['route']) && $_GET['route'] === 'get_ingredient') {
    $resultat = getIngredients();
    echo json_encode($resultat);
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['route']) && $_GET['route'] === 'create_ingredient') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->Name)) {
        $resultat = addIngredient($data->Name);
        echo json_encode($resultat);
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes"));
    }
} 

//Route users
// Gestion des routes
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !isset($_GET['route']) && $_GET['route'] === 'get_user') {
    $resultat = getUser();
    echo json_encode($resultat);
} 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['route']) && $_GET['route'] === 'create_user') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->User_uid) && isset($data->User_email) && isset($data->User_password)) {
        $resultat = addUser($data->User_uid, $data->User_email, $data->User_password);
        echo json_encode($resultat);
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes"));
    }
} 


?>