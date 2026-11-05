<?php

require_once __DIR__ . '/../config/database.php';

class OrderItem
{
    public function create(
        $quantity,
        $price,
        $orderID,
        $listingID
    ) {
        global $conn;

        $sql = "INSERT INTO orderitem
                (quantity, price, orderID, listingID)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "idii",
            $quantity,
            $price,
            $orderID,
            $listingID
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT oi.*,
                       l.title
                FROM orderitem oi
                INNER JOIN listing l
                    ON oi.listingID = l.listingID";

        return $conn->query($sql);
    }

    public function getById($orderItemID)
    {
        global $conn;

        $sql = "SELECT oi.*,
                       l.title
                FROM orderitem oi
                INNER JOIN listing l
                    ON oi.listingID = l.listingID
                WHERE oi.orderItemID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderItemID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByOrder($orderID)
    {
        global $conn;

        $sql = "SELECT oi.*,
                       l.title,
                       l.imageURL
                FROM orderitem oi
                INNER JOIN listing l
                    ON oi.listingID = l.listingID
                WHERE oi.orderID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function update(
        $orderItemID,
        $quantity,
        $price
    ) {
        global $conn;

        $sql = "UPDATE orderitem
                SET quantity = ?,
                    price = ?
                WHERE orderItemID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "idi",
            $quantity,
            $price,
            $orderItemID
        );

        return $stmt->execute();
    }

    public function delete($orderItemID)
    {
        global $conn;

        $sql = "DELETE FROM orderitem
                WHERE orderItemID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderItemID);

        return $stmt->execute();
    }
}

?>
