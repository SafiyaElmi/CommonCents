<?php

require_once __DIR__ . '/../config/database.php';

class Cart
{
    public function create($userID)
    {
        global $conn;

        $sql = "INSERT INTO cart (userID)
                VALUES (?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT c.cartID,
                       c.userID,
                       u.firstName,
                       u.lastName
                FROM cart c
                INNER JOIN `user` u
                    ON c.userID = u.userID";

        return $conn->query($sql);
    }

    public function getById($cartID)
    {
        global $conn;

        $sql = "SELECT *
                FROM cart
                WHERE cartID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cartID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByUser($userID)
    {
        global $conn;

        $sql = "SELECT *
                FROM cart
                WHERE userID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function update($cartID, $userID)
    {
        global $conn;

        $sql = "UPDATE cart
                SET userID = ?
                WHERE cartID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $userID, $cartID);

        return $stmt->execute();
    }

    public function delete($cartID)
    {
        global $conn;

        $sql = "DELETE FROM cart
                WHERE cartID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cartID);

        return $stmt->execute();
    }
}

?>
