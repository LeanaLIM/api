<?php
// api_model.php
require __DIR__ . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

try {
    $db = new PDO('mysql:host=localhost;dbname=museartbdd;port=8889;charset=utf8', 'root', 'root');
} catch (PDOException $e) {
    echo "Erreur de connexion à la base de données : " . $e->getMessage();
}


function Connexion($email, $passwd)
{
    global $db;
    $req = $db->prepare('SELECT * FROM Administrateurs WHERE email = ?');
    $req->execute(array($email));
    $result = $req->fetchAll();

    if (!empty($result)) {
        $result = $result[0];
        if (password_verify($passwd, $result["passwd"])) {
            // Mot de passe correct
            $secretKey = 'kjhgfdvdjfv';

            $tokenId = base64_encode(random_bytes(32));
            $issuedAt = time();
            $notBefore = $issuedAt;
            $expire = $issuedAt + 3600;

            // Données à encoder dans le JWT
            $data = [
                'id' => $tokenId,
                'email' => $result['email'],
                'iat' => $issuedAt,
                'nbf' => $notBefore,
                'exp' => $expire,
            ];

            // Clé secrète pour signer le JWT
            // Algorithme de signature
            $algorithm = 'HS256';

            // Encodage du JWT
            try {
                $jwt = JWT::encode($data, $secretKey, $algorithm);
                return $jwt;
            } catch (Exception $e) {
                // En cas d'erreur lors de l'encodage du JWT
                return "error: Erreur lors de la génération du token JWT : " . $e->getMessage();
            }
        } else {
           // Mot de passe incorrect
            $message = "error: Mot de passe incorrect";
            return $message;
        }
    } else {
        // Aucun résultat trouvé pour l'email donné
        $message = "error: Aucun utilisateur trouvé pour l'email donné";
        return $message;
    }
}



function enregistrerResa($nom, $prenom, $mail, $dateVisite, $HeureVisite, $NbPersonne)
{
    global $db;

    // Préparer la requête SQL d'insertion
    $req = $db->prepare('INSERT INTO reservation (nom, prenom, mail, dateVisite, HeureVisite, NbPersonne) VALUES (?, ?, ?, ?, ?, ?)');

    // Exécuter la requête en passant les valeurs en tant que paramètres
    $result = $req->execute(array($nom, $prenom, $mail, $dateVisite, $HeureVisite, $NbPersonne));

    // Vérifier si l'insertion a réussi
    if ($result) {
        // Récupérer l'ID de la réservation insérée
        $reservationId = $db->lastInsertId();

        // Retourner l'ID de la réservation
        return $reservationId;
    } else {
        // En cas d'échec de l'insertion, retourner false
        return false;
    }
}

function getAllReservations($token)
{
    global $db;
    $secretKey = 'kjhgfdvdjfv';
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        if ($decoded) {
            $req = $db->query('SELECT * FROM reservation');
            return $req->fetchAll();
            $message = "Toutes les réservations ont été récupérées";
            return $message;
        }
    } catch (Exception $e) {
        $message = "Token invalide";
        return $message;
    }
}


function getReservationById($id)
{
    global $db;
    $req = $db->prepare('SELECT * FROM reservation WHERE id = ?');
    $req->execute(array($id));
    return $req->fetch();
}

function deleteReservation($id, $token)
{
    global $db;
    $secretKey = 'kjhgfdvdjfv';
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        if ($decoded) {
            $req = $db->prepare('DELETE FROM reservation WHERE id = ?');
            $req->execute(array($id));
            $message = "Reservation deleted";
            return $message;
        }
        else {
            $message = "problème est survenu lors de la suppression de la réservation";
            return $message;
        }
    } catch (Exception $e) {
        $message = "Token invalide";
        return $message;
    }
}

function updateReservation($nom, $prenom, $email, $dateVisite, $HeureVisite, $NbPersonne, $token, $id)
{
    global $db;
    $secretKey = 'kjhgfdvdjfv';
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        if ($decoded) {
            $req = $db->prepare('UPDATE reservation SET nom = ?, prenom = ?, mail = ?, dateVisite = ?, HeureVisite = ?, NbPersonne = ? WHERE id = ?');
            $success = $req->execute(array($nom, $prenom, $email, $dateVisite, $HeureVisite, $NbPersonne, $id));
            if ($success) {
                $message = "Reservation modified";
            } else {
                $message = "Failed to modify reservation";
            }
        }
    } catch (Exception $e) {
        $message = "Token invalide";
    }
    return $message;
}

function checkToken($token)
{
    $secretKey = 'kjhgfdvdjfv';
    try {
        $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
        if ($decoded) {
            $message = "valide";
            return $message;
        }
    } catch (Exception $e) {
        $message = "invalide";
        return $message;
    }
}
