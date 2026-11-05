<?php

require_once __DIR__ . '/../config/database.php';

class CartItem
{
    public function create($quantity, $cartID, $listingID)
    {
        global $conn;

        $sql = "INSERT INTO cartitem
                (quantity, cartID, listingID)
                VALUES (?, ?, ?)";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "iii",
            $quantity,
            $cartID,
            $listingID
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT ci.*,
                       l.title,
                       l.price
                FROM cartitem ci
                INNER JOIN listing l
                    ON ci.listingID = l.listingID";

        return $conn->query($sql);
    }

    public function getById($cartItemID)
    {
        global $conn;

        $sql = "SELECT ci.*,
                       l.title,
                       l.price
                FROM cartitem ci
                INNER JOIN listing l
                    ON ci.listingID = l.listingID
                WHERE ci.cartItemID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cartItemID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByCart($cartID)
    {
        global $conn;

        $sql = "SELECT ci.*,
                       l.title,
                       l.price,
                       l.imageURL
                FROM cartitem ci
                INNER JOIN listing l
                    ON ci.listingID = l.listingID
                WHERE ci.cartID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cartID);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function update($cartItemID, $quantity)
    {
        global $conn;

        $sql = "UPDATE cartitem
                SET quantity = ?
                WHERE cartItemID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $quantity, $cartItemID);

        return $stmt->execute();
    }

    public function delete($cartItemID)
    {
        global $conn;

        $sql = "DELETE FROM cartitem
                WHERE cartItemID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $cartItemID);

        return $stmt->execute();
    }
}

?>
