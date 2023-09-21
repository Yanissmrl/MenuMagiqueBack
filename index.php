<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Allow-Methods: *");
header('Content-Type: application/json');

function getRecipesWithIngredients() {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');
        $retour["success"] = true;
        $retour["message"] = "Connexion à la base de données réussie";

        $requete = $pdo->prepare("SELECT r.Receipe_Id, r.Receipe_Title, r.Receipe_Desc, GROUP_CONCAT(i.Name SEPARATOR ', ') AS Ingredients
                                    FROM receipes AS r
                                    JOIN recipe_ingredients AS ri ON r.Receipe_Id = ri.Recipe_Id
                                    JOIN ingredients AS i ON ri.Ingredient_Id = i.Ingredients_Id
                                    GROUP BY r.Receipe_Id");
        $requete->execute();
        $resultats = $requete->fetchAll();

        $retour["success"] = true;
        $retour["message"] = "Voici les recettes avec leurs ingrédients";
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

function addRecipeWithIngredients($data) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');
        $retour["success"] = true;
        $retour["message"] = "Connexion à la base de données réussie";
        $receipeTitle = $data['Receipe_Title'];
        $receipeDesc = $data['Receipe_Desc'];

        $receipeInsertQuery = "INSERT INTO receipes (Receipe_Title, Receipe_Desc) VALUES (:Receipe_Title, :Receipe_Desc)";
        $receipeInsertStmt = $pdo->prepare($receipeInsertQuery);
        $receipeInsertStmt->bindParam(':Receipe_Title', $receipeTitle);
        $receipeInsertStmt->bindParam(':Receipe_Desc', $receipeDesc);
        $receipeInsertStmt->execute();

        $receipeId = $pdo->lastInsertId();

        $ingredients = $data['Ingredients'];
        foreach ($ingredients as $ingredient) {
            $ingredientId = $ingredient['Ingredient_Id'];

            $ingredientInsertQuery = "INSERT INTO recipe_ingredients (Recipe_Id, Ingredient_Id) VALUES (:Recipe_Id, :Ingredient_Id)";
            $ingredientInsertStmt = $pdo->prepare($ingredientInsertQuery);
            $ingredientInsertStmt->bindParam(':Recipe_Id', $receipeId);
            $ingredientInsertStmt->bindParam(':Ingredient_Id', $ingredientId);
            $ingredientInsertStmt->execute();
        }

        $retour["success"] = true;
        $retour["message"] = "Recette ajoutée avec succès";
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Erreur lors de l'ajout de la recette";
    }

    return $retour;
}

function loginUser($email, $password) {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=menu-magique-bdd;', 'root', '');

        $requete = $pdo->prepare("SELECT * FROM `users` WHERE `User_uid` = ?");
        $requete->execute([$email]);
        $utilisateur = $requete->fetch();

        if ($utilisateur && password_verify($password, $utilisateur['User_password'])) {
            $retour["success"] = true;
            $retour["message"] = "Connexion réussie";
        } else {
            $retour["success"] = false;
            $retour["message"] = "Nom d'utilisateur ou mot de passe incorrect";
        }
    } catch (Exception $e) {
        $retour["success"] = false;
        $retour["message"] = "Erreur lors de la connexion : " . $e->getMessage();
    }

    return $retour;
}

/////////////////////////////////////////////// 

//Routes recettes

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['route']) && $_GET['route'] === 'get_recipes_with_ingredients') {
    $resultat = getRecipesWithIngredients();
    echo json_encode($resultat);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['route']) && $_GET['route'] === 'add_recipe') {
    $postData = json_decode(file_get_contents('php://input'), true);
    $resultat = addRecipeWithIngredients($postData);
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

// Route login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['route']) && $_GET['route'] === 'login') {
    $data = json_decode(file_get_contents("php://input"));

    if (isset($data->User_uid) && isset($data->User_password)) {
        $resultat = loginUser($data->User_uid, $data->User_password);
        echo json_encode($resultat);
    } else {
        http_response_code(400);
        echo json_encode(array("message" => "Données incomplètes"));
    }
}



?>