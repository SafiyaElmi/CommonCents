<?php

require_once __DIR__ . '/../config/database.php';

class Order
{
    public function create($status, $deliveryAddress, $userID)
    {
        global $conn;

        $orderDate = date("Y-m-d H:i:s");

        $sql = "INSERT INTO `order`
                (orderDate, status, deliveryAddress, userID)
                VALUES (?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "sssi",
            $orderDate,
            $status,
            $deliveryAddress,
            $userID
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT o.*,
                       u.firstName,
                       u.lastName,
                       u.email
                FROM `order` o
                INNER JOIN `user` u
                    ON o.userID = u.userID
                ORDER BY o.orderDate DESC";

        return $conn->query($sql);
    }

    public function getById($orderID)
    {
        global $conn;

        $sql = "SELECT o.*,
                       u.firstName,
                       u.lastName,
                       u.email
                FROM `order` o
                INNER JOIN `user` u
                    ON o.userID = u.userID
                WHERE o.orderID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByUser($userID)
    {
        global $conn;

        $sql = "SELECT *
                FROM `order`
                WHERE userID = ?
                ORDER BY orderDate DESC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userID);
        $stmt->execute();

        return $stmt->get_result();
    }

    public function update(
        $orderID,
        $status,
        $deliveryAddress
    ) {
        global $conn;

        $sql = "UPDATE `order`
                SET status = ?,
                    deliveryAddress = ?
                WHERE orderID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "ssi",
            $status,
            $deliveryAddress,
            $orderID
        );

        return $stmt->execute();
    }

    public function updateStatus($orderID, $status)
    {
        global $conn;

        $sql = "UPDATE `order`
                SET status = ?
                WHERE orderID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $status, $orderID);

        return $stmt->execute();
    }

    public function delete($orderID)
    {
        global $conn;

        $sql = "DELETE FROM `order`
                WHERE orderID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderID);

        return $stmt->execute();
    }
}

?>
