<?php

require_once __DIR__ . '/../config/database.php';

class Payment
{
    public function create(
        $amount,
        $paymentMethod,
        $paymentStatus,
        $orderID
    ) {
        global $conn;

        $paymentDate = date("Y-m-d H:i:s");

        $sql = "INSERT INTO payment
                (amount, paymentMethod, paymentStatus,
                 paymentDate, orderID)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "dsssi",
            $amount,
            $paymentMethod,
            $paymentStatus,
            $paymentDate,
            $orderID
        );

        return $stmt->execute();
    }

    public function getAll()
    {
        global $conn;

        $sql = "SELECT p.*,
                       o.userID
                FROM payment p
                INNER JOIN `order` o
                    ON p.orderID = o.orderID
                ORDER BY p.paymentDate DESC";

        return $conn->query($sql);
    }

    public function getById($paymentID)
    {
        global $conn;

        $sql = "SELECT *
                FROM payment
                WHERE paymentID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $paymentID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function getByOrder($orderID)
    {
        global $conn;

        $sql = "SELECT *
                FROM payment
                WHERE orderID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderID);
        $stmt->execute();

        return $stmt->get_result()->fetch_assoc();
    }

    public function update(
        $paymentID,
        $amount,
        $paymentMethod,
        $paymentStatus
    ) {
        global $conn;

        $sql = "UPDATE payment
                SET amount = ?,
                    paymentMethod = ?,
                    paymentStatus = ?
                WHERE paymentID = ?";

        $stmt = $conn->prepare($sql);

        $stmt->bind_param(
            "dssi",
            $amount,
            $paymentMethod,
            $paymentStatus,
            $paymentID
        );

        return $stmt->execute();
    }

    public function updateStatus($paymentID, $paymentStatus)
    {
        global $conn;

        $sql = "UPDATE payment
                SET paymentStatus = ?
                WHERE paymentID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("si", $paymentStatus, $paymentID);

        return $stmt->execute();
    }

    public function delete($paymentID)
    {
        global $conn;

        $sql = "DELETE FROM payment
                WHERE paymentID = ?";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $paymentID);

        return $stmt->execute();
    }
}

?>
