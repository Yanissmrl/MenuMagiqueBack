<?php
header('Content-Type: application/json');

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
        $retour["results"]["ingredients"] = $resultats;
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Connexion à la base de données impossible";
    }

    return $retour;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $resultat = getIngredients();
    echo json_encode($resultat);
} else {
    http_response_code(405);
    echo json_encode(array("message" => "Méthode non autorisée"));
}
?>