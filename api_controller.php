<?php
include "api_model.php";
$request_method = $_SERVER["REQUEST_METHOD"];
//accepter les requêtes de n'importe quelle origine
header("Access-Control-Allow-Origin: *");
//accepter les requêtes avec les méthodes GET, POST, PUT, DELETE
header("Access-Control-Allow-Methods: GET, POST, DELETE");
//accepter les requêtes avec les en-têtes Content-Type
header("Access-Control-Allow-Headers: Content-Type");

switch ($request_method) {

    case 'GET':
        if (isset($_GET["id"])) {
            // Récupérer les informations d'une réservation par ID en utilisant la nouvelle fonction
            $result = getReservationById($_GET["id"]);
            if ($result) {
                header('Content-Type: application/json');
                echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            } else {
                header("HTTP/1.1 404 Not Found");
                echo json_encode(array("message" => "Réservation non trouvée."));
            }
        } else {
            $result = getAllReservations($token);
            header('Content-Type: application/json');
            echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }
        break;

    case 'POST':
        if (isset($_POST['email'])) {
            $result = Connexion($_POST['email'], $_POST['passwd']);
            header('Content-Type: application/json');
            echo $result;
            exit;
        } elseif (!empty($_POST['action']) && $_POST['action'] === 'getReservation' && !empty($_POST['token'])) {
            $result = getAllReservations($_POST['token']);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } elseif (!empty($_POST['action']) && $_POST['action'] === 'delete' && !empty($_POST['token'])) {
            $delete = deleteReservation($_POST['id'], $_POST['token']);
            header('Content-Type: application/json');
            echo json_encode($delete);
            exit;
        } elseif (!empty($_POST['action']) && $_POST['action'] === 'update' && !empty($_POST['token']) && !empty($_POST['id'])) {
            // Vérifier si les données de mise à jour de réservation sont présentes dans la requête POST
            if (isset($_POST['id']) && isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['emailupdate']) && isset($_POST['dateVisite']) && isset($_POST['heureVisite']) && isset($_POST['NbPersonne'])) {
                // Récupérer les données de mise à jour de réservation depuis la requête POST
                $id = $_POST['id'];
                $nom = htmlspecialchars($_POST['nom']);
                $prenom = htmlspecialchars($_POST['prenom']);
                $email = htmlspecialchars($_POST['emailupdate']);
                $dateVisite = htmlspecialchars($_POST['dateVisite']);
                $heureVisite = htmlspecialchars($_POST['heureVisite']);
                $NbPersonne = htmlspecialchars($_POST['NbPersonne']);

                // Appeler la fonction pour mettre à jour la réservation
                $result = updateReservation($nom, $prenom, $email, $dateVisite, $heureVisite, $NbPersonne, $_POST['token'], $id);

                // Retourner le résultat de la mise à jour
                header('Content-Type: application/json');
                echo json_encode(array("message" => $result));
                exit;
            } else {
                // Si des données de mise à jour de réservation sont manquantes dans la requête POST, retourner un message d'erreur
                header("HTTP/1.1 400 Bad Request");
                echo "Données de mise à jour de réservation manquantes dans la requête.";
                exit;
            }
        } elseif (!empty($_POST['token'])) {
            $result = checkToken($_POST['token']);
            header('Content-Type: application/json');
            echo json_encode($result);
            exit;
        } elseif
        // Vérifier si les données de réservation sont présentes dans la requête POST
        (isset($_POST['nom']) && isset($_POST['prenom']) && isset($_POST['mail']) && isset($_POST['dateVisite']) && isset($_POST['heureVisite']) && isset($_POST['NbPersonne'])) {
            // Récupérer les données de réservation depuis la requête POST
            $nom = htmlspecialchars($_POST['nom']);
            $prenom = htmlspecialchars($_POST['prenom']);
            $mail = htmlspecialchars($_POST['mail']);
            $dateVisite = htmlspecialchars($_POST['dateVisite']);
            $heureVisite = htmlspecialchars($_POST['heureVisite']);
            $NbPersonne = htmlspecialchars($_POST['NbPersonne']);

            // Appeler la fonction pour enregistrer la réservation
            $result = enregistrerResa($nom, $prenom, $mail, $dateVisite, $heureVisite, $NbPersonne);

            // Vérifier le résultat de l'opération et retourner une réponse appropriée
            if ($result !== false) {
                // Si la réservation a été enregistrée avec succès, retourner l'ID de la réservation
                header("HTTP/1.1 201 Created");
                echo json_encode(array("message" => "La réservation a été enregistrée avec succès.", "id_reservation" => $result));
            } else {
                // Si la réservation a échoué, retourner un message d'erreur
                header("HTTP/1.1 400 Bad Request");
                echo json_encode(array("message" => "Erreur lors de l'enregistrement de la réservation."));
            }
        } else {
            // Si des données de réservation sont manquantes dans la requête POST, retourner un message d'erreur
            header("HTTP/1.1 400 Bad Request");
            echo "Données de réservation manquantes dans la requête.";
        }
        break;
}
